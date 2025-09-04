<?php

namespace App\Http\Controllers;

use App\Models\UserImage;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MyImagesController extends Controller
{
    public function index()
    {
        $images = UserImage::where('user_id', Auth::id())->get();
        return view('filament.pages.myimages', compact('images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('uploads', 'public');

        UserImage::create([
            'user_id' => Auth::id(),
            'path' => $path,
        ]);

        return redirect()->route('my-images.index')->with('success', 'Image ajoutée.');
    }

    public function destroy(UserImage $image)
    {
        if ($image->user_id !== Auth::id()) {
            abort(403);
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success', 'Image supprimée.');
    }

    public function download(UserImage $image)
    {
        if ($image->user_id !== Auth::id()) {
            abort(403);
        }

        return Storage::disk('public')->download($image->path, basename($image->path));
    }

    public function toggleFavorite(Request $request, $id)
    {
        $userId = Auth::id();
        Log::info("Attempting to toggle favorite for user_id: $userId, image_id: $id");

        // Verify image exists and belongs to the user
        $image = UserImage::where('id', $id)->where('user_id', $userId)->first();
        if (!$image) {
            Log::error("Image with ID $id not found for user $userId.");
            return back()->with('error', "Image with ID $id not found for user $userId.");
        }

        // Double-check existence in user_images
        if (!UserImage::where('id', $id)->exists()) {
            Log::error("Image ID $id does not exist in user_images.");
            return back()->with('error', "Image ID $id does not exist.");
        }

        $favorite = Favorite::where('user_id', $userId)->where('image_id', $id)->first();
        if ($favorite) {
            $favorite->delete();
            Log::info("Favorite removed for image_id: $id");
        } else {
            try {
                Favorite::create([
                    'user_id' => $userId,
                    'image_id' => $id,
                ]);
                Log::info("Favorite created for image_id: $id");
            } catch (\Exception $e) {
                Log::error('Favorite creation failed for image_id: ' . $e->getMessage());
                return back()->with('error', 'Failed to favorite image. Error: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Favoris mis à jour.');
    }
}