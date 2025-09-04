<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'image_id',
        'api_image_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(UserImage::class, 'image_id');
    }

    public function getApiImageAttribute()
    {
        if ($this->api_image_id) {
            $response = Http::get("https://api.artic.edu/api/v1/artworks/{$this->api_image_id}", [
                'fields' => 'id,title,image_id,description',
            ]);
            if ($response->successful()) {
                $data = $response->json('data');
                return [
                    'title' => $data['title'] ?? 'Untitled',
                    'description' => $data['description'] ?? 'No description available',
                    'image_url' => $this->buildImageUrl($data['image_id']),
                ];
            }
        }
        return null;
    }

    private function buildImageUrl(?string $imageId, int $width = 600): ?string
    {
        return $imageId ? "https://www.artic.edu/iiif/2/{$imageId}/full/{$width},/0/default.jpg" : null;
    }
}