<?php
namespace Tests\Feature;

use App\Models\Drop;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectPaceProductsTest extends TestCase
{
    private Drop $drop;
    private Size $size;

    protected function setUp(): void
    {
        parent::setUp();
        if (!$this->app->environment('testing')) throw new \RuntimeException('Testing environment required.');
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:',
            'database.connections.sqlite.url'=>null,'session.driver'=>'array','cache.default'=>'array']);
        DB::purge('sqlite');
        $this->artisan('migrate',['--database'=>'sqlite','--force'=>true])->assertExitCode(0);
        Storage::fake('local');
        $this->drop = new Drop(); $this->drop->number=9; $this->drop->name='Drop 09'; $this->drop->is_published=false; $this->drop->save();
        $this->size = new Size(); $this->size->code='M'; $this->size->sort_order=1; $this->size->save();
    }

    private function account(bool $admin=true): User
    {
        $user=new User(); $user->name='Tests'; $user->email=($admin?'admin':'customer').'@example.test';
        $user->password=Hash::make('Test-password-123'); $user->is_admin=$admin; $user->save(); return $user;
    }

    private function fields(array $overrides=[]): array
    {
        return array_replace(['drop_id'=>$this->drop->id,'name'=>'Test Tee','description'=>'Test only','price'=>'250.00',
            'is_published'=>'0','size_ids'=>[$this->size->id],'stock'=>[$this->size->id=>1],
            'front_image'=>UploadedFile::fake()->image('front.png',100,100),
            'back_image'=>UploadedFile::fake()->image('back.png',100,100)],$overrides);
    }

    private function createProduct(array $overrides=[]): Product
    {
        $this->post('/admin/products',$this->fields($overrides))->assertSessionHasNoErrors()->assertRedirect();
        return Product::firstOrFail();
    }

    private function editFields(array $overrides=[]): array
    {
        return array_replace(['drop_id'=>$this->drop->id,'name'=>'Edited Tee','description'=>'Edited','price'=>'260.00','is_published'=>'0'],$overrides);
    }

    public function test_guest_and_non_admin_cannot_manage_products(): void
    {
        $this->get('/admin/products')->assertRedirect(route('login'));
        $this->post('/admin/products',$this->fields())->assertRedirect(route('login'));
        $this->actingAs($this->account(false));
        $this->get('/admin/products')->assertForbidden();
        $this->get('/admin/products/create')->assertForbidden();
        $this->post('/admin/products',$this->fields())->assertForbidden();
    }

    public function test_admin_creates_product_with_independent_photos_and_initial_stock(): void
    {
        $this->actingAs($this->account());
        $this->get('/admin/products/create')->assertOk();
        $product=$this->createProduct();
        $this->assertDatabaseHas('products',['id'=>$product->id,'drop_id'=>$this->drop->id,'price'=>250,'is_published'=>0]);
        $this->assertDatabaseHas('product_variants',['product_id'=>$product->id,'size_id'=>$this->size->id,'stock'=>1]);
        $this->assertDatabaseCount('product_images',2);
        $front=$product->images()->where('view','front')->firstOrFail();
        $back=$product->images()->where('view','back')->firstOrFail();
        $this->assertNotSame($front->path,$back->path);
        Storage::disk('local')->assertExists([$front->path,$back->path]);
        $this->get('/admin/products')->assertOk()->assertSee('Test Tee');
        $this->get("/admin/products/{$product->id}/edit")->assertOk();
        $this->getJson('/api/catalog')->assertJsonCount(0,'products');
    }

    public function test_validation_rejects_invalid_price_size_stock_and_image(): void
    {
        $this->actingAs($this->account());
        $this->post('/admin/products',$this->fields(['price'=>'-1','size_ids'=>[999999],'stock'=>[$this->size->id=>-1], 'back_image'=>UploadedFile::fake()->create('bad.pdf',10,'application/pdf')]))
            ->assertSessionHasErrors(['price','size_ids.0','stock.'.$this->size->id,'back_image']);
        $this->assertDatabaseCount('products',0);
        $this->assertDatabaseCount('product_images',0);
    }

    public function test_both_publication_flags_control_product_and_photos(): void
    {
        $this->actingAs($this->account());
        $product=$this->createProduct(['is_published'=>'1']);
        $path=$product->images()->where('view','front')->firstOrFail()->path; $file=basename($path);
        $public="/product-image/{$product->id}/{$file}";
        $this->get("/admin/products/{$product->id}/image/{$file}")->assertOk();
        $this->get($public)->assertNotFound();
        $this->getJson('/api/catalog')->assertJsonCount(0,'products');
        $this->drop->is_published=true; $this->drop->save();
        $this->getJson('/api/catalog')->assertJsonCount(1,'products')->assertJsonPath('products.0.price',250)->assertJsonPath('products.0.image',ltrim($public,'/'));
        $this->get($public)->assertOk()->assertHeader('Content-Type','image/png');
        $this->put("/admin/products/{$product->id}",$this->editFields())->assertSessionHasNoErrors();
        $this->get($public)->assertNotFound(); $this->getJson('/api/catalog')->assertJsonCount(0,'products');
        $this->post('/admin/logout')->assertRedirect();
        $this->get("/admin/products/{$product->id}/image/{$file}")->assertRedirect(route('login'));
    }

    public function test_edit_preserves_stock_and_other_photo_and_soldout_price_is_private(): void
    {
        $this->actingAs($this->account());
        $this->drop->is_published=true; $this->drop->save();
        $product=$this->createProduct(['is_published'=>'1','stock'=>[$this->size->id=>0]]);
        $front=$product->images()->where('view','front')->firstOrFail()->path;
        $back=$product->images()->where('view','back')->firstOrFail()->path;
        $this->put("/admin/products/{$product->id}",$this->editFields(['is_published'=>'1','front_image'=>UploadedFile::fake()->image('replacement.png',100,100),'stock'=>[$this->size->id=>999]]))->assertSessionHasNoErrors();
        $new=$product->images()->where('view','front')->firstOrFail()->path;
        $this->assertNotSame($front,$new); Storage::disk('local')->assertMissing($front); Storage::disk('local')->assertExists([$new,$back]);
        $this->assertSame(0,$product->variants()->firstOrFail()->stock);
        $this->assertDatabaseHas('products',['id'=>$product->id,'price'=>260]);
        $this->getJson('/api/catalog')->assertJsonPath('products.0.price',null)->assertJsonPath('products.0.sizes.M',false);
    }
}
