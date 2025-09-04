<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        Log::info("Fetching favorites for user_id: {$userId}");

        if (!$userId) {
            Log::warning("No authenticated user");
            return view('favorites', ['favorites' => [], 'total' => 0, 'per_page' => 12, 'current_page' => 1, 'last_page' => 1, 'search' => '']);
        }

        $search = $request->input('search', '');
        $perPage = 12;
        $page = max(1, $request->input('page', 1));

        $query = Favorite::where('user_id', $userId)->with('image');
        $total = $query->count();

        $favorites = $query->forPage($page, $perPage)->get()->map(function ($favorite) {
            $imageUrl = null;
            $type = 'unknown';
            $imageId = $favorite->image_id;

            Log::debug("Processing favorite ID: {$favorite->id}", [
                'image_id' => $imageId,
                'api_image_id' => $favorite->api_image_id,
                'has_image_relation' => $favorite->relationLoaded('image') ? 'yes' : 'no',
            ]);

            if ($favorite->api_image_id) {
                $apiImage = self::fetchApiImage($favorite->api_image_id);
                if ($apiImage && isset($apiImage['image_id'])) {
                    $imageUrl = self::buildImageUrl($apiImage['image_id']);
                    $type = 'api';
                    Log::info("API image URL generated: {$imageUrl} for api_image_id: {$favorite->api_image_id}");
                } else {
                    Log::warning("Failed to fetch API image for api_image_id: {$favorite->api_image_id}");
                }
            } elseif ($favorite->image_id && $favorite->image) {
                $filePath = $favorite->image->path; // Changed from file_path to path
                Log::info("Checking user image for image_id: {$favorite->image_id}", [
                    'file_path' => $filePath,
                    'image_exists_in_db' => $favorite->image ? 'yes' : 'no',
                ]);

                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    $imageUrl = Storage::url($filePath);
                    $type = 'user';
                    Log::info("User image URL generated: {$imageUrl}", [
                        'file_path' => $filePath,
                        'storage_path' => storage_path('app/public/' . $filePath),
                    ]);
                } else {
                    $imageUrl = asset('images/placeholder.jpg'); // Fallback image
                    Log::error("File not found or storage issue for image_id: {$favorite->image_id}", [
                        'file_path' => $filePath ?? 'null',
                        'file_exists' => $filePath ? Storage::disk('public')->exists($filePath) : false,
                        'storage_path' => $filePath ? storage_path('app/public/' . $filePath) : 'null',
                        'disk_config' => config('filesystems.disks.public'),
                    ]);
                }
            } else {
                Log::warning("No valid image source for favorite ID: {$favorite->id}", [
                    'image_id' => $favorite->image_id,
                    'api_image_id' => $favorite->api_image_id,
                    'image_relation_loaded' => $favorite->image ? 'yes' : 'no',
                ]);
            }

            return [
                'id' => $favorite->id,
                'image_url' => $imageUrl,
                'type' => $type,
                'image_id' => $imageId, // Added for debug in Blade
            ];
        })->filter(function ($favorite) {
            return !is_null($favorite['image_url']);
        })->values();

        Log::info("Mapped favorites with URLs: " . json_encode($favorites->all()));

        return view('favorites', [
            'favorites' => $favorites->all(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'search' => $search,
        ]);
    }

    private static function fetchApiImage($apiId)
    {
        $response = Http::get("https://api.artic.edu/api/v1/artworks/{$apiId}", [
            'fields' => 'id,title,image_id,description',
        ]);
        return $response->successful() ? $response->json('data') : [];
    }

    private static function buildImageUrl(?string $imageId, int $width = 600): ?string
    {
        return $imageId ? "https://www.artic.edu/iiif/2/{$imageId}/full/{$width},/0/default.jpg" : null;
    }

    public function unfavorite($id)
    {
        $favorite = Favorite::where('user_id', Auth::id())->findOrFail($id);
        $favorite->delete();
        return redirect()->route('favorites.index')->with('success', 'Image unfavorited successfully.');
    }
}