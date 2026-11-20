<?php

namespace App\Http\Controllers;

use App\Services\PublicCatalog;
use Illuminate\Http\Response;

class StorefrontController extends Controller
{
    public function show(PublicCatalog $catalog): Response
    {
        $page = match (request()->route()->getName()) {
            'home' => 'home',
            'shop' => 'shop',
            'archive' => 'archive',
            'product' => 'product',
            'cart' => 'cart',
            'checkout' => 'checkout',
            default => abort(404),
        };

        return response()->view($page, ['publicCatalog' => $catalog->get()])
            ->header('Cache-Control', 'no-store, private');
    }
}
