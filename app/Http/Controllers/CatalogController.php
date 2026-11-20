<?php

namespace App\Http\Controllers;

use App\Services\PublicCatalog;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function index(PublicCatalog $catalog): JsonResponse
    {
        return response()->json($catalog->get())
            ->header('Cache-Control', 'no-store, private');
    }
}
