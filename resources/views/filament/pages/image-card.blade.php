@props(['image'])

<div class="image-card border rounded-lg shadow hover:shadow-lg transition duration-300 overflow-hidden" x-data="imageModal()">

    {{-- Image --}}
    @if($image['image_url'])
        <img src="{{ $image['image_url'] }}"
             alt="{{ $image['title'] }}"
             class="w-full h-64 object-cover cursor-pointer transition-transform duration-300 ease-in-out"
             @click="openModal()">

        {{-- Modal --}}
        <div x-show="isOpen" x-cloak
             class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 cursor-move"
             @click.self="closeModal()"
             @keydown.escape.window="closeModal()"
             @wheel="zoomWheel($event)">
             
            <div class="relative">
                <img :src="imageUrl"
                     :style="`transform: scale(${zoom}) translate(${posX}px, ${posY}px)`"
                     class="max-h-[90vh] max-w-[90vw] object-contain rounded-md shadow-lg transition-transform duration-200"
                     @mousedown="startDrag($event)"
                     @mouseup="stopDrag()"
                     @mousemove="dragImage($event)">
                
                {{-- Close Button --}}
                <button @click="closeModal()"
                        class="absolute top-2 right-2 bg-gray-800 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-gray-700">
                    ✕
                </button>

                {{-- Zoom Controls --}}
                <div class="absolute bottom-4 right-4 flex gap-2 bg-gray-800 bg-opacity-60 p-2 rounded">
                    <button @click="zoomIn()" class="text-white px-2 py-1 rounded hover:bg-gray-700">+</button>
                    <button @click="zoomOut()" class="text-white px-2 py-1 rounded hover:bg-gray-700">-</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Title + Actions --}}
    <div class="p-3 flex justify-between items-center">
        <h3 class="text-sm font-semibold truncate" title="{{ $image['title'] }}">{{ $image['title'] }}</h3>
        <div class="flex gap-3 items-center">
            {{-- Favorite --}}
            <button x-data
                    x-on:click.prevent="$wire.toggleFavorite({{ $image['api_id'] }})"
                    class="text-xl cursor-pointer transition-colors duration-200"
                    :class="{'text-red-500': {{ $image['is_favorite'] ? 'true' : 'false' }}}"
                    title="Favorite">
                <span x-text="{{ $image['is_favorite'] ? '\'❤️\'' : '\'🤍\'' }}"></span>
            </button>

            {{-- Download --}}
            <a href="{{ route('image.download', $image['api_id']) }}"
               class="text-xl cursor-pointer transition-colors duration-200 hover:text-gray-300"
               title="Download">⬇️</a>
        </div>
    </div>

</div>

<script>
function imageModal() {
    return {
        isOpen: false,
        imageUrl: @js($image['image_url']),
        zoom: 1,
        posX: 0,
        posY: 0,
        startX: 0,
        startY: 0,
        dragging: false,

        openModal() {
            this.isOpen = true;
            this.zoom = 1;
            this.posX = 0;
            this.posY = 0;
        },
        closeModal() {
            this.isOpen = false;
        },
        zoomIn() { this.zoom += 0.2; },
        zoomOut() { if(this.zoom > 0.2) this.zoom -= 0.2; },
        zoomWheel(event) {
            event.preventDefault();
            if(event.deltaY < 0) this.zoomIn(); 
            else this.zoomOut();
        },
        startDrag(event) {
            this.dragging = true;
            this.startX = event.clientX - this.posX;
            this.startY = event.clientY - this.posY;
        },
        stopDrag() {
            this.dragging = false;
        },
        dragImage(event) {
            if(this.dragging){
                this.posX = event.clientX - this.startX;
                this.posY = event.clientY - this.startY;
            }
        }
    }
}
</script>
