<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

class ImageDownloadController extends Controller
{
    public function download($apiId)
    {
        // Récupérer l’image via l’API Art Institute
        $endpoint = "https://api.artic.edu/api/v1/artworks/{$apiId}";
        $response = Http::get($endpoint, [
            'fields' => 'id,title,image_id'
        ]);

        if (! $response->successful()) {
            abort(404, 'Image not found.');
        }

        $data = $response->json('data');

        $imageId = $data['image_id'] ?? null;

        if (! $imageId) {
            abort(404, 'No image available.');
        }

        $imageUrl = "https://www.artic.edu/iiif/2/{$imageId}/full/1000,/0/default.jpg";

        $imgContent = Http::get($imageUrl)->body();
        $filename = \Str::slug($data['title'] ?? 'artwork') . '.jpg';

        return Response::make($imgContent, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ]);
    }
}
