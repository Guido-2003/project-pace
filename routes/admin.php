<?php
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DropController;
use App\Http\Middleware\EnsurePaceAdmin;
use App\Http\Middleware\PreventAdminCaching;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(PreventAdminCaching::class)->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1')->name('admin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('admin.logout');
    Route::middleware(['auth', EnsurePaceAdmin::class])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/drops', [DropController::class, 'index'])->name('admin.drops.index');
        Route::get('/drops/create', [DropController::class, 'create'])->name('admin.drops.create');
        Route::post('/drops', [DropController::class, 'store'])->name('admin.drops.store');
        Route::get('/drops/{drop}/edit', [DropController::class, 'edit'])->name('admin.drops.edit');
        Route::put('/drops/{drop}', [DropController::class, 'update'])->name('admin.drops.update');
        Route::delete('/drops/{drop}', [DropController::class, 'destroy'])->name('admin.drops.destroy');
        Route::get('/drops/{drop}/image/{file}', [DropController::class, 'image'])->name('admin.drops.image');
        Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products.index');
        Route::get('/products/create', [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('admin.products.create');
        Route::post('/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/products/{product}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('admin.products.update');
        Route::get('/products/{product}/image/{file}', [\App\Http\Controllers\Admin\ProductController::class, 'image'])->name('admin.products.image');
        Route::get('/inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('admin.inventory.index');
        Route::get('/inventory/sizes', [\App\Http\Controllers\Admin\InventoryController::class, 'sizes'])->name('admin.inventory.sizes');
        Route::post('/inventory/sizes', [\App\Http\Controllers\Admin\InventoryController::class, 'storeSize'])->name('admin.inventory.sizes.store');
        Route::get('/inventory/{product}/edit', [\App\Http\Controllers\Admin\InventoryController::class, 'edit'])->name('admin.inventory.edit');
        Route::put('/inventory/{product}', [\App\Http\Controllers\Admin\InventoryController::class, 'update'])->name('admin.inventory.update');
        Route::post('/inventory/{product}/variants', [\App\Http\Controllers\Admin\InventoryController::class, 'addVariant'])->name('admin.inventory.variants.store');
    });
});
