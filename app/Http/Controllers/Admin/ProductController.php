<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Services\ProductImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request, ProductImages $images): View
    {
        $filters = $request->validate(['q'=>['nullable','string','max:120'], 'drop_id'=>['nullable','integer','exists:drops,id']]);
        $products = Product::query()->with(['drop','images'])->withSum('variants as total_stock','stock')
            ->when($filters['q'] ?? null, fn($q,$term)=>$q->where('name','like','%'.$term.'%'))
            ->when($filters['drop_id'] ?? null, fn($q,$id)=>$q->where('drop_id',$id))
            ->orderByDesc(Drop::select('number')->whereColumn('drops.id','products.drop_id'))->orderBy('id')->paginate(25)->withQueryString();
        return view('admin.products.index', ['products'=>$products,'drops'=>Drop::orderByDesc('number')->get(),'images'=>$images]);
    }

    public function create(Request $request, ProductImages $images): View
    {
        return $this->form(new Product(), $images, $request->integer('drop_id') ?: null);
    }

    public function edit(Product $product, ProductImages $images): View
    {
        return $this->form($product, $images, $product->drop_id);
    }

    private function form(Product $product, ProductImages $images, ?int $dropId): View
    {
        $product->loadMissing(['images','variants.size','drop']);
        return view('admin.products.form', ['product'=>$product,'dropId'=>$dropId,
            'drops'=>Drop::orderByDesc('number')->get(),'sizes'=>Size::orderBy('sort_order')->get(), 'images'=>$images]);
    }

    public function store(Request $request, ProductImages $images): RedirectResponse
    {
        $product = $this->save($request, null, $images);
        return redirect()->route('admin.products.edit',$product)->with('status','Producto creado correctamente.');
    }

    public function update(Request $request, Product $product, ProductImages $images): RedirectResponse
    {
        $product = $this->save($request, $product, $images);
        return redirect()->route('admin.products.edit',$product)->with('status','Cambios guardados.');
    }

    private function save(Request $request, ?Product $product, ProductImages $images): Product
    {
        $image = ['image','mimes:jpg,jpeg,png,webp','max:5120','dimensions:max_width=6000,max_height=6000'];
        $rules = ['drop_id'=>['required','integer','exists:drops,id'], 'name'=>['required','string','max:255'],
            'description'=>['nullable','string','max:12000'], 'price'=>['required','numeric','decimal:0,2','min:0.01','max:99999999.99'],
            'is_published'=>['required','boolean'], 'front_image'=>array_merge([$product?'nullable':'required'],$image),
            'back_image'=>array_merge([$product?'nullable':'required'],$image)];
        if (!$product) {
            $rules += ['size_ids'=>['required','array','min:1'], 'size_ids.*'=>['required','integer','distinct','exists:sizes,id'],
                'stock'=>['nullable','array'], 'stock.*'=>['nullable','integer','min:0','max:1000000']];
        }
        $data = $request->validate($rules);
        $new = []; $old = [];
        try {
            foreach (['front','back'] as $view) {
                if ($request->hasFile($view.'_image')) $new[$view] = $images->store($request->file($view.'_image'),$view.'_image');
            }
            $saved = DB::transaction(function () use ($product,$data,$new,$request,$images,&$old) {
                // Lock the destination drop first: protects against deletion during creation.
                $drop = Drop::query()->lockForUpdate()->findOrFail($data['drop_id']);
                $record = $product ? Product::query()->lockForUpdate()->findOrFail($product->id) : new Product();
                $record->drop_id = $drop->id;
                $record->name = $data['name']; $record->description = $data['description'] ?? null;
                $record->price = $data['price']; $record->is_published = $request->boolean('is_published');
                $record->save();
                foreach (['front','back'] as $view) {
                    $row = $record->images()->where('view',$view)->first();
                    if (isset($new[$view])) {
                        if ($row) $old[] = $row->path;
                        else { $row = new ProductImage(); $row->product_id = $record->id; $row->view = $view;
                            $row->sort_order = $record->images()->exists() ? (int)$record->images()->max('sort_order')+1 : 0; }
                        $row->path = $new[$view]; $row->alt_text = mb_substr($record->name.' - '.($view==='front'?'Vista frontal':'Vista posterior'), 0, 255);
                        $row->save();
                    }
                    if ($record->is_published && (!$row || (!$images->exists($row->path) && !$images->legacy($row->path)))) {
                        throw ValidationException::withMessages([$view.'_image'=>'Subí una fotografía válida antes de publicar.']);
                    }
                }
                if (!$product) {
                    foreach ($data['size_ids'] as $sizeId) {
                        $variant = new ProductVariant(); $variant->product_id = $record->id; $variant->size_id = $sizeId;
                        $variant->stock = (int)($data['stock'][$sizeId] ?? 0); $variant->save();
                    }
                }
                return $record;
            });
        } catch (Throwable $e) {
            foreach ($new as $path) $images->remove($path);
            throw $e;
        }
        foreach ($old as $path) $images->remove($path);
        return $saved;
    }

    public function image(Product $product, string $file, ProductImages $images): Response
    {
        return $images->serve($product,$file,false);
    }
}
