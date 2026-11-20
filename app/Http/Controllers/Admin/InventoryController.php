<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'drop_id' => ['nullable', 'integer', 'exists:drops,id'],
            'availability' => ['nullable', Rule::in(['available', 'soldout'])],
        ]);
        $products = Product::query()->with(['drop', 'variants'])->withSum('variants as total_stock', 'stock')
            ->when(isset($filters['q']), fn ($query) => $query->where('name', 'like', '%'.$filters['q'].'%'))
            ->when($filters['drop_id'] ?? null, fn ($query, $id) => $query->where('drop_id', $id));
        if (($filters['availability'] ?? null) === 'available') {
            $products->whereHas('variants', fn ($q) => $q->where('stock', '>', 0));
        } elseif (($filters['availability'] ?? null) === 'soldout') {
            $products->whereDoesntHave('variants', fn ($q) => $q->where('stock', '>', 0));
        }
        $availableCount = Product::whereHas('variants', fn ($q) => $q->where('stock', '>', 0))->count();
        return view('admin.inventory.index', [
            'products' => $products->orderByDesc(Drop::select('number')->whereColumn('drops.id', 'products.drop_id'))->orderBy('id')->paginate(25)->withQueryString(),
            'drops' => Drop::orderByDesc('number')->get(),
            'sizes' => Size::orderBy('sort_order')->orderBy('id')->get(),
            'stockCount' => (int) ProductVariant::sum('stock'),
            'variantCount' => ProductVariant::count(),
            'availableCount' => $availableCount,
            'soldOutCount' => Product::count() - $availableCount,
        ]);
    }

    public function edit(Product $product): View
    {
        $product->load(['drop', 'variants.size']);
        return view('admin.inventory.edit', [
            'product' => $product,
            'variants' => $product->variants->sortBy(fn ($variant) => $variant->size->sort_order),
            'unusedSizes' => Size::whereNotIn('id', $product->variants->pluck('size_id'))->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'stock' => ['required', 'array'],
            'stock.*' => ['required', 'integer', 'min:0', 'max:1000000'],
            'expected' => ['required', 'array'],
            'expected.*' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);
        DB::transaction(function () use ($product, $data) {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $variants = $locked->variants()->lockForUpdate()->get();
            $ids = $variants->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all();
            foreach (['stock', 'expected'] as $field) {
                $submitted = collect(array_keys($data[$field]))->map(fn ($id) => (string) $id)->sort()->values()->all();
                if ($submitted !== $ids) {
                    throw ValidationException::withMessages(['stock' => 'Las tallas cambiaron o no pertenecen a esta prenda. Recargá la página y revisá las cantidades.']);
                }
            }
            foreach ($variants as $variant) {
                if ($variant->stock !== (int) $data['expected'][$variant->id]) {
                    throw ValidationException::withMessages(['stock' => 'El inventario cambió desde que abriste el formulario. Revisá las cantidades actuales antes de guardar.']);
                }
            }
            foreach ($variants as $variant) {
                $variant->stock = (int) $data['stock'][$variant->id];
                if ($variant->isDirty('stock')) $variant->save();
            }
        }, 3);
        return redirect()->route('admin.inventory.edit', $product)->with('status', 'Inventario actualizado correctamente.');
    }

    public function addVariant(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'size_id' => ['required', 'integer', 'exists:sizes,id'],
            'initial_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);
        DB::transaction(function () use ($product, $data) {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            if ($locked->variants()->where('size_id', $data['size_id'])->exists()) {
                throw ValidationException::withMessages(['size_id' => 'Esta talla ya está registrada para la prenda.']);
            }
            $variant = new ProductVariant();
            $variant->product_id = $locked->id;
            $variant->size_id = $data['size_id'];
            $variant->stock = (int) $data['initial_stock'];
            $variant->save();
        }, 3);
        return redirect()->route('admin.inventory.edit', $product)->with('status', 'Talla agregada correctamente.');
    }

    public function sizes(): View
    {
        return view('admin.inventory.sizes', [
            'sizes' => Size::withCount('variants')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function storeSize(Request $request): RedirectResponse
    {
        if (is_string($request->input('code'))) {
            $request->merge(['code' => strtoupper(trim($request->input('code')))]);
        }
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'regex:/\A[A-Z][A-Z0-9-]*\z/', 'unique:sizes,code'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
        try {
            $size = new Size();
            $size->code = $data['code'];
            $size->sort_order = (int) $data['sort_order'];
            $size->save();
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages(['code' => 'Esta talla ya existe.']);
        }
        return redirect()->route('admin.inventory.sizes')->with('status', 'Talla creada. Ahora podés asociarla a una prenda.');
    }
}
