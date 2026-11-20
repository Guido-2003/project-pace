<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Services\ArchiveImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DropController extends Controller
{
    public function index(): View
    {
        $drops = Drop::query()->withCount(['products', 'products as published_products_count' => fn ($q) => $q->where('is_published', true)])
            ->orderByDesc('number')->get();
        $latest = $drops->first(fn ($drop) => $drop->is_published && $drop->published_products_count > 0)?->id;
        return view('admin.drops.index', compact('drops', 'latest'));
    }

    public function create(): View
    {
        return view('admin.drops.form', ['drop' => new Drop(), 'nextNumber' => (int) Drop::max('number') + 1, 'products' => collect(), 'imageUrl' => null]);
    }

    public function edit(Drop $drop, ArchiveImages $images): View
    {
        $products = $drop->products()->withSum('variants as total_stock', 'stock')->orderBy('id')->get();
        $imageUrl = $images->exists($drop->archive_image)
            ? route('admin.drops.image', ['drop' => $drop, 'file' => basename($drop->archive_image)]) : null;
        return view('admin.drops.form', ['drop' => $drop, 'nextNumber' => $drop->number, 'products' => $products, 'imageUrl' => $imageUrl]);
    }

    public function store(Request $request, ArchiveImages $images): RedirectResponse
    {
        $drop = $this->save($request, null, $images);
        return redirect()->route('admin.drops.edit', $drop)->with('status', 'Drop creado correctamente.');
    }

    public function update(Request $request, Drop $drop, ArchiveImages $images): RedirectResponse
    {
        $drop = $this->save($request, $drop, $images);
        return redirect()->route('admin.drops.edit', $drop)->with('status', 'Cambios guardados.');
    }

    private function save(Request $request, ?Drop $drop, ArchiveImages $images): Drop
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'archive_title' => ['nullable', 'string', 'max:200'],
            'archive_description' => ['nullable', 'string', 'max:12000'],
            'archive_image_alt' => ['nullable', 'string', 'max:255'],
            'archive_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'remove_image' => ['nullable', 'boolean'],
            'is_published' => ['required', 'boolean'],
        ];
        if (!$drop) $rules['number'] = ['required', 'integer', 'min:1', 'max:9999', Rule::unique('drops', 'number')];
        $data = $request->validate($rules);
        if ($request->hasFile('archive_image') && $request->boolean('remove_image')) {
            throw ValidationException::withMessages(['archive_image' => 'Elegí reemplazar o quitar la imagen, no ambas opciones.']);
        }
        $newPath = $request->hasFile('archive_image') ? $images->store($request->file('archive_image')) : null;
        $oldPath = null;
        try {
            $saved = DB::transaction(function () use ($drop, $data, $request, $newPath, &$oldPath): Drop {
                $record = $drop ? Drop::query()->lockForUpdate()->findOrFail($drop->id) : new Drop();
                if (!$drop) $record->number = $data['number'];
                $record->name = $data['name'];
                $record->archive_title = $data['archive_title'] ?? null;
                $record->archive_description = $data['archive_description'] ?? null;
                $record->archive_image_alt = $data['archive_image_alt'] ?? null;
                $record->is_published = $request->boolean('is_published');
                if ($newPath || $request->boolean('remove_image')) {
                    $oldPath = $record->archive_image;
                    $record->archive_image = $newPath;
                }
                $record->save();
                return $record;
            });
        } catch (Throwable $e) {
            $images->remove($newPath);
            throw $e;
        }
        if ($oldPath && $oldPath !== $newPath) $images->remove($oldPath);
        return $saved;
    }

    public function destroy(Request $request, Drop $drop, ArchiveImages $images): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);
        $oldPath = DB::transaction(function () use ($drop) {
            $record = Drop::query()->lockForUpdate()->findOrFail($drop->id);
            if ($record->is_published || $record->products()->exists()) {
                throw ValidationException::withMessages(['confirm_delete' => 'Solo se pueden eliminar borradores sin prendas asociadas.']);
            }
            $path = $record->archive_image;
            $record->delete();
            return $path;
        });
        $images->remove($oldPath);
        return redirect()->route('admin.drops.index')->with('status', 'Borrador eliminado.');
    }

    public function image(Drop $drop, string $file, ArchiveImages $images): Response
    {
        return $images->serve($drop, $file, false);
    }
}
