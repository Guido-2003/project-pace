<?php
namespace App\Http\Controllers;

use App\Models\Drop;
use App\Services\ArchiveImages;
use Symfony\Component\HttpFoundation\Response;

class ArchiveImageController extends Controller
{
    public function show(Drop $drop, string $file, ArchiveImages $images): Response
    {
        return $images->serve($drop, $file, true);
    }
}
