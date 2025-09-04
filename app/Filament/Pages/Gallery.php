<?php

namespace App\Filament\Pages;

use App\Models\Favorite;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class Gallery extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Image Gallery';
    protected static string $view = 'filament.pages.gallery';

    public array $images = [];
    public string $search = '';
    public int $page = 1;
    public int $perPage = 12;
    public int $totalPages = 1;
    public bool $loading = false;

    public function mount(): void
    {
        $this->loadImages();
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
        $this->loadImages();
    }

    public function nextPage(): void
    {
        if ($this->page < $this->totalPages) {
            $this->page++;
            $this->loadImages();
        }
    }

    public function prevPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadImages();
        }
    }

    private function buildImageUrl(?string $imageId, int $width = 600): ?string
    {
        return $imageId ? "https://www.artic.edu/iiif/2/{$imageId}/full/{$width},/0/default.jpg" : null;
    }

    private function loadImages(): void
    {
        $this->loading = true;

        $endpoint = 'https://api.artic.edu/api/v1/artworks';
        $params = [
            'page' => $this->page,
            'limit' => $this->perPage,
            'fields' => 'id,title,image_id',
        ];

        // ❌ On n’utilise pas 'q' ici, on filtre localement après
        $response = Http::get($endpoint, $params);

        if (! $response->successful()) {
            $this->images = [];
            $this->totalPages = 1;
            $this->loading = false;
            return;
        }

        $payload = $response->json();

        // Mapping des résultats API
        $images = collect($payload['data'] ?? [])->map(function ($item) {
            $apiId = $item['id'] ?? null;
            $imageId = $item['image_id'] ?? null;

            return [
                'api_id' => $apiId,
                'title' => $item['title'] ?? 'Untitled',
                'image_url' => $this->buildImageUrl($imageId),
                'is_favorite' => Auth::check() && Favorite::where('user_id', Auth::id())
                    ->where('api_image_id', $apiId)
                    ->exists(),
            ];
        });

        // ✅ Filtrage par title en local
        if (trim($this->search) !== '') {
            $images = $images->filter(function ($img) {
                return stripos($img['title'], $this->search) !== false;
            });
        }

        $this->images = $images->values()->toArray();

        $this->totalPages = $payload['pagination']['total_pages'] ?? 1;
        $this->loading = false;
    }

    public function toggleFavorite(int $apiId): void
    {
        if (! Auth::check()) return;

        $userId = Auth::id();
        $favorite = Favorite::where('user_id', $userId)
            ->where('api_image_id', $apiId)
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            Favorite::create([
                'user_id' => $userId,
                'api_image_id' => $apiId,
            ]);
        }

        $this->loadImages();
    }
}
