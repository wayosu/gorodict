@if($getRecord()->audio_path)
    <div x-data="{
        playing: false,
        audio: new Audio('{{ Storage::url($getRecord()->audio_path) }}'),
        stopPropagation(event) {
            event.stopPropagation();
        }
    }" class="flex items-center space-x-2" @click="stopPropagation($event)">
        <!-- Play Button -->
        <button
            x-show="!playing"
            @click.stop.prevent="audio.play(); playing = true"
            type="button"
            class="p-2 text-primary-600 hover:text-primary-500 transition-colors"
        >
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
            </svg>
        </button>

        <!-- Stop Button -->
        <button
            x-show="playing"
            @click.stop.prevent="audio.pause(); audio.currentTime = 0; playing = false"
            type="button"
            class="p-2 text-red-600 hover:text-red-500 transition-colors"
        >
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M6 6h12v12H6z"/>
            </svg>
        </button>

        <!-- Event Listeners -->
        <div x-init="
            audio.addEventListener('ended', () => { playing = false });
            audio.addEventListener('pause', () => { playing = false });
            audio.addEventListener('play', () => { playing = true });
        "></div>
    </div>
@else
    <span class="text-gray-400 text-sm">No audio</span>
@endif
