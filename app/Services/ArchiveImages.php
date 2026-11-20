<?php
namespace App\Services;

use App\Models\Drop;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ArchiveImages
{
    private const TYPES = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];

    public function isManaged(?string $path): bool
    {
        return $path !== null && preg_match('~\Aarchive-drops/[a-f0-9-]{36}\.(?:jpg|png|webp)\z~', $path) === 1;
    }

    public function store(UploadedFile $file): string
    {
        $ext = array_search($file->getMimeType(), self::TYPES, true);
        if ($ext === false) {
            throw ValidationException::withMessages(['archive_image' => 'Formato de imagen no válido.']);
        }
        $path = Storage::disk('local')->putFileAs('archive-drops', $file, Str::uuid().'.'.$ext, ['visibility' => 'private']);
        if (!$path) {
            throw ValidationException::withMessages(['archive_image' => 'No se pudo guardar la imagen.']);
        }
        return $path;
    }

    public function exists(?string $path): bool
    {
        return $this->isManaged($path) && Storage::disk('local')->exists($path);
    }

    public function publicPath(Drop $drop): string
    {
        return $drop->is_published && $this->exists($drop->archive_image)
            ? ltrim(route('archive.image', ['drop' => $drop->id, 'file' => basename($drop->archive_image)], false), '/') : '';
    }

    public function serve(Drop $drop, string $file, bool $public): Response
    {
        abort_if($public && !$drop->is_published, 404);
        $path = $drop->archive_image;
        abort_unless($this->exists($path) && hash_equals(basename($path), $file), 404);
        return Storage::disk('local')->response($path, $file, [
            'Content-Type' => self::TYPES[pathinfo($path, PATHINFO_EXTENSION)],
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ], 'inline');
    }

    public function remove(?string $path): void
    {
        if ($this->isManaged($path)) Storage::disk('local')->delete($path);
    }
}
