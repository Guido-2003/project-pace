<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ArchiveImageController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'show'])->name('home');
Route::get('/shop', [StorefrontController::class, 'show'])->name('shop');
Route::get('/archive', [StorefrontController::class, 'show'])->name('archive');
Route::get('/product', [StorefrontController::class, 'show'])->name('product');
Route::get('/cart', [StorefrontController::class, 'show'])->name('cart');
Route::get('/checkout', [StorefrontController::class, 'show'])->name('checkout');

// Read-only public endpoint; same filtered catalog as the Blade pages.
Route::get('/api/catalog', [CatalogController::class, 'index'])->name('catalog.index');

Route::get('/archive-image/{drop}/{file}', [ArchiveImageController::class, 'show'])->name('archive.image');

Route::get('/product-image/{product}/{file}', [\App\Http\Controllers\ProductImageController::class, 'show'])->name('product.image');

// Private administration routes.
require __DIR__.'/admin.php';
