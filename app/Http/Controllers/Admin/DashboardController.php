<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->with('drop')->withSum('variants as total_stock', 'stock')
            ->orderByDesc('drop_id')->orderBy('id')->get();
        $available = $products->filter(fn (Product $product) => (int) $product->total_stock > 0)->count();
        return view('admin.dashboard', [
            'products' => $products,
            'dropCount' => Drop::count(),
            'productCount' => $products->count(),
            'availableCount' => $available,
            'soldOutCount' => $products->count() - $available,
            'stockCount' => (int) $products->sum('total_stock'),
            'variantCount' => ProductVariant::count(),
        ]);
    }
}
