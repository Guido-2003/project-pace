<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProjectPaceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Initial import only: never reset an existing catalog or stock.
        $file = storage_path('app/private/project-pace-catalog.json');
        if (!is_file($file)) {
            throw new RuntimeException('Missing private catalog: storage/app/private/project-pace-catalog.json');
        }

        $data = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $products = $data['products'] ?? [];
        $sizes = ['S', 'M', 'L', 'XL'];
        if (($data['currency'] ?? null) !== 'BOB' || count($products) !== 17) {
            throw new RuntimeException('Unexpected initial catalog. Expected 17 products in BOB.');
        }

        $ids = [];
        $dropCounts = [];
        $variantCount = 0;
        foreach ($products as $product) {
            $id = $product['id'] ?? null;
            $drop = $product['dropNumber'] ?? null;
            $price = $product['price'] ?? null;
            if (!is_int($id) || $id < 1 || isset($ids[$id]) || !is_int($drop) || $drop < 1 || $drop > 8
                || !is_numeric($price) || $price < 0 || $price > 99999999.99
                || !is_string($product['name'] ?? null) || trim($product['name']) === '') {
                throw new RuntimeException('Invalid product in private catalog.');
            }
            $ids[$id] = true;
            $dropCounts[$drop] = ($dropCounts[$drop] ?? 0) + 1;
            foreach (['image', 'backImage'] as $field) {
                $path = $product[$field] ?? '';
                if (!is_string($path) || !preg_match('~^assets/images/drops/drop-\d{2}/[a-z0-9-]+\.webp$~D', $path)
                    || !is_file(public_path($path))) {
                    throw new RuntimeException('Missing or invalid image for product '.$id.': '.$field);
                }
            }
            if (!is_array($product['sizes'] ?? null) || count($product['sizes']) === 0) {
                throw new RuntimeException('Missing sizes for product '.$id);
            }
            foreach ($product['sizes'] as $code => $available) {
                if (!in_array($code, $sizes, true) || $available !== false) {
                    throw new RuntimeException('Unexpected size or availability for product '.$id);
                }
                $variantCount++;
            }
        }
        ksort($ids);
        ksort($dropCounts);
        if (array_keys($ids) !== range(1, 17) || $dropCounts !== [1=>2, 2=>1, 3=>1, 4=>2, 5=>2, 6=>2, 7=>3, 8=>4] || $variantCount !== 58) {
            throw new RuntimeException('Catalog counts do not match the original release.');
        }

        DB::transaction(function () use ($products, $sizes) {
            foreach (['drops', 'products', 'sizes', 'product_images', 'product_variants'] as $table) {
                if (DB::table($table)->exists()) {
                    throw new RuntimeException('Initial import cancelled: '.$table.' already contains data. No data changed.');
                }
            }

            $now = now();
            $timestamps = ['created_at' => $now, 'updated_at' => $now];
            $dropIds = [];
            for ($number = 1; $number <= 8; $number++) {
                $dropIds[$number] = DB::table('drops')->insertGetId([
                    'number' => $number,
                    'name' => sprintf('Drop %02d', $number),
                    'is_published' => true,
                ] + $timestamps);
            }
            $sizeIds = [];
            foreach ($sizes as $position => $code) {
                $sizeIds[$code] = DB::table('sizes')->insertGetId([
                    'code' => $code,
                    'sort_order' => $position + 1,
                ] + $timestamps);
            }
            foreach ($products as $product) {
                DB::table('products')->insert([
                    'id' => $product['id'],
                    'drop_id' => $dropIds[$product['dropNumber']],
                    'name' => $product['name'],
                    'description' => $product['description'] ?? null,
                    'price' => number_format((float) $product['price'], 2, '.', ''),
                    'is_published' => true,
                ] + $timestamps);
                foreach (['front' => 'image', 'back' => 'backImage'] as $view => $field) {
                    DB::table('product_images')->insert([
                        'product_id' => $product['id'],
                        'path' => $product[$field],
                        'view' => $view,
                        'alt_text' => $product['name'].' - '.$view,
                        'sort_order' => $view === 'front' ? 1 : 2,
                    ] + $timestamps);
                }
                foreach (array_keys($product['sizes']) as $code) {
                    DB::table('product_variants')->insert([
                        'product_id' => $product['id'],
                        'size_id' => $sizeIds[$code],
                        'stock' => 0,
                    ] + $timestamps);
                }
            }
        });

        $this->command?->info('Project Pace: 8 drops, 17 products, 34 images, 4 sizes, 58 variants. Stock: 0.');
    }
}
