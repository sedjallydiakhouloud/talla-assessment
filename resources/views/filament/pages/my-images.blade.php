<x-filament-panels::page>

    @if(session()->has('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded shadow">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded shadow">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search Form -->
    <form wire:submit.prevent="performSearch" class="mb-6 flex justify-center items-center space-x-2">
        <input type="text"
               placeholder="Search by title..."
               wire:model="search"
               class="w-full sm:w-1/3 p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
        <button type="submit" class="btn btn-primary rounded-lg hover:bg-blue-600 transition duration-300">Search</button>
    </form>

    <!-- Upload Form -->
    <form wire:submit.prevent="addImage" class="mb-8 space-y-4 bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold mb-2">Add a new image</h2>

        <input type="text" wire:model="imageTitle" placeholder="Title"
               class="input input-bordered w-full rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none" />
        @error('imageTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <textarea wire:model="description" placeholder="Description"
                  class="textarea textarea-bordered w-full rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"
                  rows="3"></textarea>
        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <input type="file" wire:model="file" accept="image/*" class="file-input file-input-bordered w-full rounded-lg" />
        @error('file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <button type="submit" class="btn btn-primary rounded-lg hover:bg-blue-600 transition duration-300">Upload</button>
        @if($file)
            <button type="button" wire:click="clearPreview" class="btn btn-outline btn-error rounded-lg hover:bg-red-600 hover:text-white mt-2 ml-2">Clear Preview</button>
            <button type="button" wire:click="togglePreviewFavorite" title="Toggle Favorite for Upload" class="btn btn-outline btn-info ml-2 {{ $previewFavorited ? 'bg-yellow-500 text-white' : '' }}">
                {{ $previewFavorited ? 'Favorited for Upload' : 'Favorite for Upload' }}
            </button>
        @endif
    </form>

    <!-- Preview Section -->
    @if($file)
        <div class="mb-6 border rounded-xl shadow overflow-hidden bg-white">
            <img src="{{ $file->temporaryUrl() }}" class="w-full h-64 object-cover rounded-t-xl" alt="{{ $imageTitle ?: 'Preview' }}">
            <div class="p-3">
                <h3 class="font-semibold truncate">{{ $imageTitle ?: 'Title not defined' }}</h3>
                <p class="text-sm text-gray-600 truncate">{{ $description ?: 'Description not defined' }}</p>
                <div class="flex justify-end space-x-2 mt-2">
                    <button type="button" wire:click="togglePreviewFavorite" title="Like / Dislike" class="text-2xl">
                        @if($previewFavorited)
                            <span class="text-red-500">❤️</span>
                        @else
                            <span class="text-gray-500 hover:text-red-500">🤍</span>
                        @endif
                    </button>
                    <a href="{{ $file->temporaryUrl() }}" download="{{ $imageTitle ?: 'preview_image' }}" class="text-2xl text-gray-500 hover:text-blue-400" title="Download preview">⬇️</a>
                    <button type="button" wire:click="clearPreview" title="Cancel upload" class="text-2xl text-gray-500 hover:text-red-600">🗑️</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Uploaded Images Grid -->
    @if($this->images->count() > 0)
        <div class="gallery-grid">
            @foreach($this->images as $img)
                <div class="gallery-item" data-id="{{ $img->id }}">
                    @if($img->file_path && Storage::disk('public')->exists($img->file_path))
                        <img src="{{ Storage::url($img->file_path) }}" alt="{{ $img->title }}" class="w-full h-64 object-cover rounded-t-xl">
                        <div class="icon-bar">
                            <button type="button" wire:click="toggleFavorite({{ $img->id }})" title="Like / Dislike" class="text-2xl">
                                @if($this->isFavorited($img->id))
                                    <span class="text-red-500">❤️</span>
                                @else
                                    <span class="text-white hover:text-red-500">🤍</span>
                                @endif
                            </button>
                            <button type="button" wire:click="viewImage({{ $img->id }})" title="View Details" class="text-2xl text-white hover:text-green-500">👁️</button>
                            <a href="{{ Storage::url($img->file_path) }}" download="{{ $img->title }}" class="text-2xl text-white hover:text-blue-400" title="Download">⬇️</a>
                            <button type="button" wire:click="deleteImage({{ $img->id }})" title="Delete" class="text-2xl text-white hover:text-red-600">🗑️</button>
                        </div>
                        <div class="p-3 bg-white">
                            <h3 class="font-semibold truncate" title="{{ $img->title }}">{{ $img->title }}</h3>
                        </div>
                    @else
                        <div class="placeholder w-full h-64 flex items-center justify-center rounded-t-xl">
                            <p class="text-red-600 text-lg">Image not available</p>
                        </div>
                        <div class="p-3 bg-white">
                            <h3 class="font-semibold truncate">Untitled</h3>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Per Page Selector -->
        <div class="mb-4 flex justify-end items-center space-x-2">
            <label class="text-gray-700">Per page</label>
            <select wire:model="perPage" class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="5">5</option>
                <option value="9">9</option>
                <option value="15">15</option>
                <option value="30">30</option>
            </select>
        </div>

        <!-- Pagination at the bottom -->
        <div class="mt-6 text-center">
            {{ $this->images->links() }}
        </div>
    @else
        <p class="text-center text-gray-500 text-2xl mt-10">No images found.</p>
    @endif

    <!-- View Image Modal -->
    @if($selectedImageId)
        <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50" wire:click.self="closeModal">
            @php
                $image = \App\Models\UserImage::find($selectedImageId);
            @endphp
            @if($image)
                <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full h-auto" wire:click.stop>
                    <button wire:click="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">✕</button>
                    <img src="{{ Storage::url($image->file_path) }}" alt="{{ $image->title }}" class="w-full h-64 object-contain mb-2" alt="{{ $image->title }}">
                    <h3 class="text-lg font-semibold truncate">{{ $image->title }}</h3>
                    <p class="text-gray-600 text-sm">{{ $image->description ?: 'No description provided.' }}</p>
                </div>
            @endif
        </div>
    @endif

    <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            padding: 10px;
        }
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.4s ease, opacity 0.4s ease;
        }
        .gallery-item img {
            transition: transform 0.4s ease;
        }
        .gallery-item:hover img {
            transform: scale(1.05);
        }
        .icon-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: space-around;
            padding: 8px 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .gallery-item:hover .icon-bar {
            opacity: 1;
        }
        .gallery-item .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 0, 0, 0.8);
            color: white;
            border: none;
            padding: 6px;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
            font-weight: bold;
        }
        .gallery-item:hover .remove-btn {
            opacity: 1;
        }
        .gallery-item.removing {
            opacity: 0;
            transform: scale(0.95);
        }
        .placeholder {
            background: #e5e7eb;
            border-radius: 8px 8px 0 0;
        }
    </style>
</x-filament-panels::page>