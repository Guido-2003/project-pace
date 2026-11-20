<?php
namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ProductImages
{
    private const TYPES = ['jpg'=>'image/jpeg', 'png'=>'image/png', 'webp'=>'image/webp'];

    public function managed(?string $path): bool
    {
        return $path !== null && preg_match('~\Aproduct-photos/[a-f0-9-]{36}\.(?:jpg|png|webp)\z~D', $path) === 1;
    }

    public function store(UploadedFile $file, string $field): string
    {
        $ext = array_search($file->getMimeType(), self::TYPES, true);
        if ($ext === false) throw ValidationException::withMessages([$field => 'Formato de imagen no válido.']);
        $path = Storage::disk('local')->putFileAs('product-photos', $file, Str::uuid().'.'.$ext, ['visibility'=>'private']);
        if (!$path) throw ValidationException::withMessages([$field => 'No se pudo guardar la imagen.']);
        return $path;
    }

    public function exists(?string $path): bool
    {
        return $this->managed($path) && Storage::disk('local')->exists($path);
    }

    public function preview(Product $product, ?string $path): ?string
    {
        if ($this->exists($path)) return route('admin.products.image', ['product'=>$product,'file'=>basename($path)]);
        return $this->legacy($path) ? asset($path) : null;
    }

    public function legacy(?string $path): bool
    {
        return $path !== null && preg_match('~\Aassets/images/[a-zA-Z0-9/_-][a-zA-Z0-9/_.-]*\.(?:webp|png|jpg|jpeg)\z~iD', $path) === 1
            && !str_contains($path, '..') && is_file(public_path($path));
    }

    public function publicPath(Product $product, ?string $path): string
    {
        if ($this->exists($path)) return ltrim(route('product.image', ['product'=>$product,'file'=>basename($path)], false), '/');
        return $this->legacy($path) ? $path : '';
    }

    public function serve(Product $product, string $file, bool $public): Response
    {
        abort_if($public && (!$product->is_published || !$product->drop()->where('is_published', true)->exists()), 404);
        $path = $product->images()->pluck('path')->first(fn ($path) => $this->managed($path) && hash_equals(basename($path), $file));
        abort_unless($this->exists($path), 404);
        return Storage::disk('local')->response($path, $file, [
            'Content-Type'=>self::TYPES[pathinfo($path, PATHINFO_EXTENSION)],
            'X-Content-Type-Options'=>'nosniff', 'Cache-Control'=>'private, no-store, max-age=0',
        ], 'inline');
    }

    public function remove(?string $path): void
    {
        if ($this->managed($path)) Storage::disk('local')->delete($path);
    }
}
