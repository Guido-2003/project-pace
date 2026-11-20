<?php
namespace Tests\Feature;

use App\Models\Drop;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProjectPaceInventoryTest extends TestCase
{
    private Drop $drop;
    private Product $product;
    private Size $size;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();
        if (!$this->app->environment('testing')) throw new \RuntimeException('Testing environment required.');
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:',
            'database.connections.sqlite.url'=>null,'session.driver'=>'array','cache.default'=>'array']);
        DB::purge('sqlite');
        $this->artisan('migrate',['--database'=>'sqlite','--force'=>true])->assertExitCode(0);
        $this->drop = new Drop(); $this->drop->number=8; $this->drop->name='Drop 08'; $this->drop->is_published=true; $this->drop->save();
        $this->product = $this->makeProduct('Test Tee');
        $this->size = new Size(); $this->size->code='M'; $this->size->sort_order=2; $this->size->save();
        $this->variant = $this->makeVariant($this->product,0);
    }

    private function makeProduct(string $name): Product
    {
        $product = new Product(); $product->drop_id=$this->drop->id; $product->name=$name; $product->price='220.00'; $product->is_published=true; $product->save(); return $product;
    }

    private function makeVariant(Product $product,int $stock): ProductVariant
    {
        $variant=new ProductVariant(); $variant->product_id=$product->id; $variant->size_id=$this->size->id; $variant->stock=$stock; $variant->save(); return $variant;
    }

    private function account(bool $admin=true): User
    {
        $user=new User(); $user->name='Tests'; $user->email=($admin?'admin':'customer').'@example.test';
        $user->password=Hash::make('Test-password-123'); $user->is_admin=$admin; $user->save(); return $user;
    }

    private function path(): string { return '/admin/inventory/'.$this->product->id; }
    private function fields(int $stock,int $expected=0): array { return ['stock'=>[$this->variant->id=>$stock],'expected'=>[$this->variant->id=>$expected]]; }

    public function test_guest_and_non_admin_cannot_manage_inventory(): void
    {
        $this->get('/admin/inventory')->assertRedirect(route('login'));
        $this->put($this->path(),$this->fields(1))->assertRedirect(route('login'));
        $this->post('/admin/inventory/sizes',['code'=>'XL','sort_order'=>4])->assertRedirect(route('login'));
        $this->actingAs($this->account(false));
        $this->get('/admin/inventory')->assertForbidden();
        $this->get($this->path().'/edit')->assertForbidden();
        $this->put($this->path(),$this->fields(1))->assertForbidden();
        $this->post($this->path().'/variants',['size_id'=>$this->size->id,'initial_stock'=>1])->assertForbidden();
        $this->post('/admin/inventory/sizes',['code'=>'XL','sort_order'=>4])->assertForbidden();
        $this->assertSame(0,$this->variant->fresh()->stock);
    }

    public function test_list_filters_and_edit_page(): void
    {
        $this->actingAs($this->account());
        $this->makeVariant($this->makeProduct('Other Tank'),3);
        $this->get('/admin/inventory')->assertOk()->assertSee('Test Tee')->assertSee('Other Tank');
        $this->get('/admin/inventory?availability=available')->assertOk()->assertSee('Other Tank')->assertDontSee('Test Tee');
        $this->get('/admin/inventory?availability=soldout')->assertOk()->assertSee('Test Tee')->assertDontSee('Other Tank');
        $this->get('/admin/inventory?q=Test')->assertOk()->assertSee('Test Tee')->assertDontSee('Other Tank');
        $this->get('/admin/inventory?drop_id='.$this->drop->id)->assertOk()->assertSee('Test Tee');
        $this->get($this->path().'/edit')->assertOk()->assertSee('Test Tee');
    }

    public function test_stock_update_changes_public_price_and_availability_without_exposing_quantity(): void
    {
        $this->actingAs($this->account());
        $this->getJson('/api/catalog')->assertJsonPath('products.0.price',null)->assertJsonPath('products.0.sizes.M',false);
        $this->put($this->path(),$this->fields(3))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(3,$this->variant->fresh()->stock);
        $response=$this->getJson('/api/catalog')->assertJsonPath('products.0.price',220)->assertJsonPath('products.0.sizes.M',true);
        $this->assertArrayNotHasKey('stock',$response->json('products.0'));
        $this->put($this->path(),$this->fields(0,3))->assertSessionHasNoErrors();
        $this->getJson('/api/catalog')->assertJsonPath('products.0.price',null)->assertJsonPath('products.0.sizes.M',false);
        $this->assertDatabaseHas('products',['id'=>$this->product->id,'price'=>220,'is_published'=>1]);
    }

    public function test_invalid_and_foreign_stock_values_are_rejected(): void
    {
        $this->actingAs($this->account());
        $other=$this->makeVariant($this->makeProduct('Other Tank'),5);
        foreach ([-1,1.5,1000001] as $bad) {
            $this->put($this->path(),['stock'=>[$this->variant->id=>$bad],'expected'=>[$this->variant->id=>0]])->assertSessionHasErrors('stock.'.$this->variant->id);
        }
        $this->put($this->path(),['stock'=>[$this->variant->id=>2,$other->id=>0],'expected'=>[$this->variant->id=>0,$other->id=>5]])->assertSessionHasErrors('stock');
        $this->assertSame(0,$this->variant->fresh()->stock);
        $this->assertSame(5,$other->fresh()->stock);
    }

    public function test_stale_stock_form_does_not_overwrite_newer_values(): void
    {
        $this->actingAs($this->account());
        $this->put($this->path(),$this->fields(4))->assertSessionHasNoErrors();
        $this->put($this->path(),$this->fields(2))->assertSessionHasErrors('stock');
        $this->assertSame(4,$this->variant->fresh()->stock);
    }

    public function test_create_size_and_assign_without_modifying_existing_stock(): void
    {
        $this->actingAs($this->account());
        $this->get('/admin/inventory/sizes')->assertOk();
        $this->post('/admin/inventory/sizes',['code'=>'xxl','sort_order'=>5])->assertSessionHasNoErrors()->assertRedirect();
        $size=Size::where('code','XXL')->firstOrFail();
        $this->assertDatabaseCount('product_variants',1);
        $this->post($this->path().'/variants',['size_id'=>$size->id,'initial_stock'=>2])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('product_variants',['product_id'=>$this->product->id,'size_id'=>$size->id,'stock'=>2]);
        $this->assertSame(0,$this->variant->fresh()->stock);
        $this->getJson('/api/catalog')->assertJsonPath('products.0.sizes.XXL',true)->assertJsonPath('products.0.price',220);
        $this->post($this->path().'/variants',['size_id'=>$size->id,'initial_stock'=>0])->assertSessionHasErrors('size_id');
        $this->post('/admin/inventory/sizes',['code'=>'XXL','sort_order'=>5])->assertSessionHasErrors('code');
        $this->assertDatabaseCount('product_variants',2);
    }

    public function test_inventory_does_not_publish_hidden_products_or_drops(): void
    {
        $this->actingAs($this->account());
        $this->product->is_published=false; $this->product->save();
        $this->put($this->path(),$this->fields(2))->assertSessionHasNoErrors();
        $this->getJson('/api/catalog')->assertJsonCount(0,'products');
        $this->product->is_published=true; $this->product->save();
        $this->drop->is_published=false; $this->drop->save();
        $this->getJson('/api/catalog')->assertJsonCount(0,'products');
    }
}
