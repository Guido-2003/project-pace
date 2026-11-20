<?php
namespace Tests\Feature;

use App\Models\Drop;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectPaceDropsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!$this->app->environment('testing')) throw new \RuntimeException('Testing environment required.');
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        Storage::fake('local');
    }

    private function account(bool $admin = true): User
    {
        $user = new User();
        $user->name = 'Tests';
        $user->email = ($admin ? 'admin' : 'customer').'@example.test';
        $user->password = Hash::make('Test-password-123');
        $user->is_admin = $admin;
        $user->save();
        return $user;
    }

    private function drop(int $number = 8, bool $published = true): Drop
    {
        $drop = new Drop();
        $drop->number = $number;
        $drop->name = 'Drop '.str_pad($number, 2, '0', STR_PAD_LEFT);
        $drop->is_published = $published;
        $drop->save();
        return $drop;
    }

    private function product(Drop $drop): Product
    {
        $product = new Product();
        $product->drop_id = $drop->id;
        $product->name = 'Test Tee';
        $product->price = 220;
        $product->is_published = true;
        $product->save();
        return $product;
    }

    private function fields(array $overrides = []): array
    {
        return array_replace(['number'=>9, 'name'=>'Drop 09', 'is_published'=>'0', 'archive_title'=>'Test chapter', 'archive_description'=>'Test story'], $overrides);
    }

    public function test_guest_and_non_admin_cannot_manage_drops(): void
    {
        $drop = $this->drop();
        $this->get('/admin/drops')->assertRedirect(route('login'));
        $this->post('/admin/drops', $this->fields())->assertRedirect(route('login'));
        $this->actingAs($this->account(false));
        $this->get('/admin/drops')->assertForbidden();
        $this->get('/admin/drops/create')->assertForbidden();
        $this->get("/admin/drops/{$drop->id}/edit")->assertForbidden();
        $this->post('/admin/drops', $this->fields())->assertForbidden();
        $this->put("/admin/drops/{$drop->id}", $this->fields())->assertForbidden();
        $this->delete("/admin/drops/{$drop->id}", ['confirm_delete'=>1])->assertForbidden();
    }

    public function test_admin_can_create_and_edit_draft_without_exposing_it(): void
    {
        $this->actingAs($this->account());
        $this->get('/admin/drops')->assertOk();
        $this->get('/admin/drops/create')->assertOk();
        $this->post('/admin/drops', $this->fields())->assertSessionHasNoErrors()->assertRedirect();
        $drop = Drop::where('number', 9)->firstOrFail();
        $this->get("/admin/drops/{$drop->id}/edit")->assertOk();
        $this->put("/admin/drops/{$drop->id}", $this->fields(['number'=>99,'archive_title'=>'Edited']))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('drops', ['id'=>$drop->id,'number'=>9,'archive_title'=>'Edited','is_published'=>0]);
        $this->getJson('/api/catalog')->assertOk()->assertJsonCount(0,'drops');
    }

    public function test_validation_rejects_duplicate_number_and_invalid_image(): void
    {
        $this->drop(9);
        $this->actingAs($this->account());
        $this->post('/admin/drops', $this->fields())->assertSessionHasErrors('number');
        $this->post('/admin/drops', $this->fields(['number'=>10,'archive_image'=>UploadedFile::fake()->create('notes.pdf', 10,'application/pdf')]))->assertSessionHasErrors('archive_image');
        $this->assertDatabaseCount('drops',1);
    }

    public function test_publication_controls_products_and_latest(): void
    {
        $old = $this->drop(); $this->product($old);
        $new = $this->drop(9, false); $product = $this->product($new);
        $this->getJson('/api/catalog')->assertOk()->assertJsonCount(1,'products')->assertJsonPath('products.0.dropNumber',8)->assertJsonPath('products.0.isLatest',true);
        $this->actingAs($this->account())->put("/admin/drops/{$new->id}",$this->fields(['is_published'=>'1']))->assertSessionHasNoErrors();
        $this->getJson('/api/catalog')->assertOk()->assertJsonCount(2,'products')->assertJsonPath('products.0.id',$product->id)->assertJsonPath('products.0.isLatest',true)->assertJsonPath('products.0.price',null);
        $this->put("/admin/drops/{$new->id}",$this->fields())->assertSessionHasNoErrors();
        $this->getJson('/api/catalog')->assertOk()->assertJsonCount(1,'products')->assertJsonPath('products.0.dropNumber',8);
    }

    public function test_empty_published_drop_does_not_replace_latest_products(): void
    {
        $this->product($this->drop()); $this->drop(9,true);
        $this->getJson('/api/catalog')->assertOk()->assertJsonCount(2,'drops')->assertJsonPath('products.0.dropNumber',8)->assertJsonPath('products.0.isLatest',true);
    }

    public function test_image_is_private_until_publication_and_after_unpublication(): void
    {
        $admin = $this->account();
        $drop = $this->drop(9,false);
        $this->actingAs($admin)->put("/admin/drops/{$drop->id}",$this->fields(['archive_image'=>UploadedFile::fake()->image('archive.png',100,100)]))->assertSessionHasNoErrors();
        $path = $drop->fresh()->archive_image;
        Storage::disk('local')->assertExists($path);
        $file = basename($path);
        $public = "/archive-image/{$drop->id}/{$file}";
        $this->get("/admin/drops/{$drop->id}/image/{$file}")->assertOk()->assertHeader('Content-Type','image/png');
        $this->get($public)->assertNotFound();
        $this->put("/admin/drops/{$drop->id}",$this->fields(['is_published'=>'1']))->assertSessionHasNoErrors();
        $this->get($public)->assertOk();
        $this->getJson('/api/catalog')->assertJsonPath('drops.0.image',ltrim($public,'/'));
        $this->put("/admin/drops/{$drop->id}",$this->fields())->assertSessionHasNoErrors();
        $this->get($public)->assertNotFound();
        $this->getJson('/api/catalog')->assertJsonCount(0,'drops');
        $this->put("/admin/drops/{$drop->id}",$this->fields(['remove_image'=>'1']))->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($path);
        $this->assertNull($drop->fresh()->archive_image);
        $this->post('/admin/logout')->assertRedirect();
        $this->get("/admin/drops/{$drop->id}/image/{$file}")->assertRedirect(route('login'));
    }

    public function test_only_empty_drafts_can_be_deleted(): void
    {
        $published = $this->drop(8,true);
        $withProduct = $this->drop(9,false); $this->product($withProduct);
        $empty = $this->drop(10,false);
        $this->actingAs($this->account());
        $this->delete("/admin/drops/{$published->id}",['confirm_delete'=>1])->assertSessionHasErrors('confirm_delete');
        $this->delete("/admin/drops/{$withProduct->id}",['confirm_delete'=>1])->assertSessionHasErrors('confirm_delete');
        $this->delete("/admin/drops/{$empty->id}")->assertSessionHasErrors('confirm_delete');
        $this->delete("/admin/drops/{$empty->id}",['confirm_delete'=>1])->assertSessionHasNoErrors()->assertRedirect(route('admin.drops.index'));
        $this->assertDatabaseMissing('drops',['id'=>$empty->id]);
        $this->assertDatabaseCount('drops',2);
    }
}
