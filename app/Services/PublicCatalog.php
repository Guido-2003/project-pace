<?php

namespace App\Services;

use App\Models\Drop;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PublicCatalog
{
    public function get(): array
    {
        // Read related records as a unit, without modifying any stock.
        return DB::transaction(function (): array {
            $drops = Drop::query()
                ->where('is_published', true)
                ->orderByDesc('number')
                ->with(['products' => fn ($query) => $query->where('is_published', true)
                    ->orderBy('id')->with(['images', 'variants.size'])])
                ->get();

            // An empty published drop may appear in Archive, but does not replace Latest Drop.
            $latest = $drops->first(fn (Drop $drop) => $drop->products->isNotEmpty())?->number;
            $products = [];
            $publicDrops = [];

            foreach ($drops as $drop) {
                $publicDrops[] = [
                    'number' => $drop->number,
                    'name' => $drop->name,
                    'title' => $drop->archive_title,
                    'description' => $drop->archive_description,
                    'image' => app(ArchiveImages::class)->publicPath($drop) ?: $this->localImage($drop->archive_image),
                    'imageAlt' => $drop->archive_image_alt,
                ];

                foreach ($drop->products as $product) {
                    $products[] = $this->product($product, $drop, $latest);
                }
            }

            return ['products' => $products, 'drops' => $publicDrops];
        });
    }

    // Explicit allowlist: never serialize full Eloquent models to the public store.
    public function product(Product $product, Drop $drop, ?int $latest): array
    {
        $sizes = [];
        foreach ($product->variants->sortBy(fn ($variant) => $variant->size->sort_order) as $variant) {
            $sizes[$variant->size->code] = $variant->stock > 0;
        }
        $available = in_array(true, $sizes, true);
        $images = app(ProductImages::class);
        $front = $images->publicPath($product, $product->images->firstWhere('view', 'front')?->path);
        $back = $images->publicPath($product, $product->images->firstWhere('view', 'back')?->path);
        $fallback = 'assets/images/02_project_pace_isotipo_circular.svg';

        return [
            'id' => (int) $product->id,
            'name' => $product->name,
            'drop' => $drop->name,
            'dropNumber' => $drop->number,
            'isLatest' => $drop->number === $latest,
            'price' => $available ? (float) $product->price : null,
            'image' => $front ?: ($back ?: $fallback),
            'backImage' => $back,
            'description' => $product->description ?? '',
            'sizes' => (object) $sizes,
        ];
    }

    private function localImage(?string $path): string
    {
        // Only local public image paths; no external URLs or traversal segments.
        if (!$path || !preg_match('~^(?:assets/images|storage)/[a-zA-Z0-9/_-][a-zA-Z0-9/_.-]*\.(?:webp|png|jpg|jpeg|svg)$~iD', $path)
            || str_contains($path, '..') || !is_file(public_path($path))) {
            return '';
        }
        return $path;
    }
}
