<x-filament-panels::page>

    {{-- Search bar --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:justify-between items-center gap-4">
        <input type="text"
               placeholder="Search artworks..."
               wire:model.debounce.500ms="search"
               class="input input-bordered w-full max-w-md">
        <span class="text-sm text-gray-500" wire:loading>Searching...</span>
    </div>

    {{-- Images grid --}}
    @if(count($images))
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($images as $image)
                @if($image['image_url'])
                    <div class="image-card border rounded-lg shadow hover:shadow-lg transition duration-300 overflow-hidden relative" x-data="{ zoomed: false }">
                        {{-- Image avec taille uniformisée --}}
                        <img src="{{ $image['image_url'] }}"
                             alt="{{ $image['title'] }}"
                             class="w-full h-64 object-cover cursor-pointer transition-transform duration-300"
                             x-bind:class="zoomed ? 'scale-110' : ''">

                        {{-- Icon bar (visible on hover) --}}
                        <div class="icon-bar absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 flex justify-around items-center p-2 opacity-0 transition-opacity duration-300">
                            <button x-on:click.prevent="$wire.toggleFavorite({{ $image['api_id'] }})"
                                    class="text-xl cursor-pointer"
                                    :class="{'text-red-500': {{ $image['is_favorite'] ? 'true' : 'false' }}}"
                                    title="Favorite">
                                <span x-text="{{ $image['is_favorite'] ? '\'❤️\'' : '\'🤍\'' }}"></span>
                            </button>
                            <button x-on:click="zoomed = true"
                                    class="text-xl cursor-pointer text-white hover:text-green-500"
                                    title="View Image">👁️</button>
                            <a href="{{ route('image.download', $image['api_id']) }}"
                               class="text-xl cursor-pointer text-white hover:text-blue-400"
                               title="Download Image">⬇️</a>
                        </div>

                        {{-- Modal Zoom with fixed size and title only --}}
                        <div x-show="zoomed" x-cloak
                             class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50"
                             x-on:click.away="zoomed = false">
                            <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full h-auto" x-on:click.stop>
                                <button x-on:click="zoomed = false" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">✕</button>
                                <img src="{{ $image['image_url'] }}" class="w-full h-96 object-contain mb-2" alt="{{ $image['title'] }}">
                                <h3 class="text-lg font-semibold truncate">{{ $image['title'] }}</h3>
                            </div>
                        </div>

                        {{-- Title + Actions (moved outside icon bar to keep title visible) --}}
                        <div class="p-2 bg-white">
                            <h3 class="text-sm font-semibold truncate">{{ $image['title'] }}</h3>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="text-center text-gray-500 mt-8">
            No images found.
        </div>
    @endif

    {{-- Pagination --}}
    <div class="mt-6 flex justify-between items-center">
        <button wire:click="prevPage" class="btn btn-sm" @disabled($page <= 1)>Prev</button>
        <span>Page {{ $page }} / {{ $totalPages }}</span>
        <button wire:click="nextPage" class="btn btn-sm" @disabled($page >= $totalPages)>Next</button>
    </div>

    <style>
        .image-card:hover .icon-bar {
            opacity: 1;
        }
        .image-card {
            position: relative;
        }
        /* Assure que l'image conserve un aspect ratio cohérent */
        .image-card img {
            height: 256px; /* h-64 en pixels pour consistance */
            object-fit: cover;
            width: 100%;
        }
    </style>

</x-filament-panels::page>