<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Services\ProductImages;
use Symfony\Component\HttpFoundation\Response;

class ProductImageController extends Controller
{
    public function show(Product $product, string $file, ProductImages $images): Response
    {
        return $images->serve($product, $file, true);
    }
}
