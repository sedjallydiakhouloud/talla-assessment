<?php

namespace App\Filament\Pages;

use App\Models\UserImage;
use App\Models\Favorite;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MyImages extends Page
{
    use WithPagination, WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static string $view = 'filament.pages.my-images';

    public $search = '';
    public $imageTitle;
    public $description;
    public $file;
    public $previewFavorited = false;
    public $selectedImageId = null;
    public $refreshKey;
    public $perPage = 9;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->refreshKey = uniqid();
        // Log database and model configuration
        Log::info('Initializing MyImages page', [
            'users_table_exists' => Schema::hasTable('users'),
            'user_images_table_exists' => Schema::hasTable('user_images'),
            'favorites_table_exists' => Schema::hasTable('favorites'),
            'foreign_keys_enabled' => DB::select('PRAGMA foreign_keys')[0]->foreign_keys,
            'user_image_table' => (new UserImage)->getTable(),
            'favorite_table' => (new Favorite)->getTable(),
        ]);
    }

    public function performSearch()
    {
        $this->resetPage();
    }

    public function getImagesProperty()
    {
        return UserImage::where('user_id', Auth::id())
            ->when($this->search, fn($query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate($this->perPage);
    }

    public function addImage()
    {
        $this->validate([
            'imageTitle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|image|max:6144', // Updated to 6 MB (6144 KB)
        ]);

        $path = $this->file->store('uploads', 'public');
        $userId = Auth::id();

        try {
            // Verify schema and model configuration
            if (!Schema::hasTable('users') || !Schema::hasTable('user_images') || !Schema::hasTable('favorites')) {
                throw new \Exception('Required tables (users, user_images, or favorites) are missing.');
            }

            if ((new UserImage)->getTable() !== 'user_images' || (new Favorite)->getTable() !== 'favorites') {
                throw new \Exception('Model table configuration mismatch: UserImage=' . (new UserImage)->getTable() . ', Favorite=' . (new Favorite)->getTable());
            }

            // Verify user exists
            if (!DB::table('users')->where('id', $userId)->exists()) {
                throw new \Exception("User ID {$userId} does not exist in users table.");
            }

            // Create UserImage
            $image = UserImage::create([
                'user_id' => $userId,
                'title' => $this->imageTitle,
                'description' => $this->description,
                'file_path' => $path,
            ]);

            Log::info("Image created with ID: {$image->id}", ['image' => $image->toArray()]);

            // Verify image exists
            $image = UserImage::find($image->id);
            if (!$image) {
                throw new \Exception("Image ID {$image->id} does not exist after creation.");
            }

            if ($this->previewFavorited) {
                // Check for existing favorite
                if (Favorite::where('user_id', $userId)->where('image_id', $image->id)->exists()) {
                    Log::warning("Favorite already exists for user_id: {$userId}, image_id: {$image->id}");
                } else {
                    try {
                        // Temporarily disable foreign keys for debugging (remove in production)
                        DB::statement('PRAGMA foreign_keys = OFF;');
                        Favorite::create([
                            'user_id' => $userId,
                            'image_id' => $image->id,
                        ]);
                        DB::statement('PRAGMA foreign_keys = ON;');
                        Log::info("Favorite created for image_id: {$image->id}");
                    } catch (\Exception $e) {
                        DB::statement('PRAGMA foreign_keys = ON;');
                        throw $e;
                    }
                }
            }

            session()->flash('success', 'Image uploaded successfully!');
        } catch (\Exception $e) {
            Log::error('Error during addImage: ' . $e->getMessage(), [
                'user_id' => $userId,
                'image_id' => $image->id ?? 'N/A',
                'trace' => $e->getTraceAsString(),
                'tables' => [
                    'users' => Schema::hasTable('users'),
                    'user_images' => Schema::hasTable('user_images'),
                    'favorites' => Schema::hasTable('favorites'),
                ],
                'model_tables' => [
                    'user_image' => (new UserImage)->getTable(),
                    'favorite' => (new Favorite)->getTable(),
                ],
            ]);
            session()->flash('error', 'Image upload failed or favoriting issue. Please try again later.');
        }

        $this->reset(['imageTitle', 'description', 'file', 'previewFavorited']);
        $this->refreshKey = uniqid();
        $this->dispatch('refresh');
    }

    public function deleteImage($id)
    {
        $img = UserImage::where('user_id', Auth::id())->findOrFail($id);
        $img->delete();
        session()->flash('success', 'Image deleted.');
        $this->refreshKey = uniqid();
    }

    public function toggleFavorite($id)
    {
        $userId = Auth::id();
        Log::info("Attempting to toggle favorite for user_id: {$userId}, image_id: {$id}");

        try {
            // Verify schema and model configuration
            if (!Schema::hasTable('users') || !Schema::hasTable('user_images') || !Schema::hasTable('favorites')) {
                throw new \Exception('Required tables (users, user_images, or favorites) are missing.');
            }

            if ((new UserImage)->getTable() !== 'user_images' || (new Favorite)->getTable() !== 'favorites') {
                throw new \Exception('Model table configuration mismatch: UserImage=' . (new UserImage)->getTable() . ', Favorite=' . (new Favorite)->getTable());
            }

            // Verify image exists
            $image = UserImage::find($id);
            if (!$image) {
                throw new \Exception("Image with ID {$id} not found.");
            }

            // Verify user exists
            if (!DB::table('users')->where('id', $userId)->exists()) {
                throw new \Exception("User ID {$userId} does not exist in users table.");
            }

            $favorite = Favorite::where('user_id', $userId)->where('image_id', $id)->first();
            if ($favorite) {
                $favorite->delete();
                Log::info("Favorite removed for image_id: {$id}");
                session()->flash('success', 'Image unfavorited successfully.');
            } else {
                try {
                    // Temporarily disable foreign keys for debugging (remove in production)
                    DB::statement('PRAGMA foreign_keys = OFF;');
                    Favorite::create([
                        'user_id' => $userId,
                        'image_id' => $id,
                    ]);
                    DB::statement('PRAGMA foreign_keys = ON;');
                    Log::info("Favorite created for image_id: {$id}");
                    session()->flash('success', 'Image favorited successfully.');
                } catch (\Exception $e) {
                    DB::statement('PRAGMA foreign_keys = ON;');
                    throw $e;
                }
            }
        } catch (\Exception $e) {
            Log::error('Favorite creation failed for image_id: ' . $id . '. Error: ' . $e->getMessage(), [
                'user_id' => $userId,
                'image_id' => $id,
                'trace' => $e->getTraceAsString(),
                'tables' => [
                    'users' => Schema::hasTable('users'),
                    'user_images' => Schema::hasTable('user_images'),
                    'favorites' => Schema::hasTable('favorites'),
                ],
                'model_tables' => [
                    'user_image' => (new UserImage)->getTable(),
                    'favorite' => (new Favorite)->getTable(),
                ],
            ]);
            session()->flash('error', 'Failed to favorite image. Please try again later.');
            return;
        }

        $this->refreshKey = uniqid();
        $this->dispatch('refresh');
    }

    public function isFavorited($id)
    {
        return Favorite::where('user_id', Auth::id())->where('image_id', $id)->exists();
    }

    public function clearPreview()
    {
        $this->reset(['file', 'imageTitle', 'description', 'previewFavorited']);
    }

    public function togglePreviewFavorite()
    {
        $this->previewFavorited = !$this->previewFavorited;
    }

    public function viewImage($id)
    {
        $this->selectedImageId = $id;
    }

    public function closeModal()
    {
        $this->selectedImageId = null;
    }
}
