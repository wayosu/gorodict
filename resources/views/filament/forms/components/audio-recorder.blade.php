<div x-data="audioRecorder()" class="space-y-3 p-3 bg-gray-50 rounded-lg">
    <!-- Recording Interface -->
    <div class="flex flex-col space-y-2">
        <!-- Visualizer dan Timer dalam satu baris -->
        <div class="flex items-center space-x-4">
            <!-- Visualizer Canvas - Ukuran lebih kecil -->
            <canvas x-ref="visualizer" class="w-32 h-16 bg-white rounded-lg shadow-inner"></canvas>

            <!-- Timer Display -->
            <div class="text-xl font-mono text-gray-600" x-text="formatTime(recordingTime)">00:00</div>
        </div>

        <!-- Volume Meter -->
        <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
            <div x-ref="volumeMeter" class="h-full bg-primary-500 transition-all duration-75" style="width: 0%"></div>
        </div>

        <!-- Controls -->
        <div class="flex items-center space-x-2">
            <!-- Record Button -->
            <button
                type="button"
                x-show="!isRecording && !audioUrl"
                @click="startRecording"
                class="flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-all"
            >
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="10" r="6" />
                </svg>
                Record
            </button>

            <!-- Stop Button -->
            <button
                type="button"
                x-show="isRecording"
                @click="stopRecording"
                class="flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all animate-pulse"
            >
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <rect x="6" y="6" width="8" height="8" />
                </svg>
                Stop
            </button>

            <!-- Record Again Button -->
            <button
                type="button"
                x-show="audioUrl && !isRecording"
                @click="startRecording"
                class="flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-all"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Record New
            </button>

            <!-- Delete Button -->
            <button
                type="button"
                x-show="audioUrl && !isRecording"
                @click="deleteRecording"
                class="flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-all"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
            </button>
        </div>
    </div>

    <!-- Audio Player -->
    <div x-show="audioUrl" class="mt-2">
        <audio
            x-ref="audioPlayer"
            :src="audioUrl"
            class="w-full h-8"
            controls
            @play="visualizePlayback"
        ></audio>
    </div>

    <!-- Tambahkan input hidden untuk Filament -->
    <input
        type="hidden"
        x-ref="audioInput"
        {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
    >

    <!-- Status Messages -->
    <div x-show="statusMessage" class="text-sm text-gray-600 mt-2" x-text="statusMessage"></div>
</div>

@push('scripts')
<script>
function audioRecorder() {
    return {
        isRecording: false,
        mediaRecorder: null,
        audioUrl: null,
        chunks: [],
        recordingTime: 0,
        timerInterval: null,
        audioContext: null,
        analyser: null,
        visualizerCanvas: null,
        canvasCtx: null,
        statusMessage: '',

        async init() {
            this.visualizerCanvas = this.$refs.visualizer;
            this.canvasCtx = this.visualizerCanvas.getContext('2d');
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        },

        formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        },

        async startRecording() {
            try {
                await this.init();
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.mediaRecorder = new MediaRecorder(stream);
                this.chunks = [];
                this.recordingTime = 0;
                this.statusMessage = 'Recording...';

                const audioSource = this.audioContext.createMediaStreamSource(stream);
                this.analyser = this.audioContext.createAnalyser();
                audioSource.connect(this.analyser);
                this.analyser.fftSize = 256;

                this.mediaRecorder.ondataavailable = (e) => this.chunks.push(e.data);
                this.mediaRecorder.onstop = () => this.processRecording();

                this.mediaRecorder.start();
                this.isRecording = true;
                this.startTimer();
                this.visualize();
            } catch (error) {
                this.statusMessage = 'Error: Could not access microphone';
                console.error('Error:', error);
            }
        },

        deleteRecording() {
            this.audioUrl = null;
            this.recordingTime = 0;
            this.$refs.audioInput.value = '';
            this.statusMessage = 'Recording deleted';
            setTimeout(() => this.statusMessage = '', 2000);
        },

        startTimer() {
            this.timerInterval = setInterval(() => {
                this.recordingTime++;
            }, 1000);
        },

        stopRecording() {
            this.mediaRecorder.stop();
            this.isRecording = false;
            clearInterval(this.timerInterval);
            this.mediaRecorder.stream.getTracks().forEach(track => track.stop());
            this.statusMessage = 'Recording stopped';
            setTimeout(() => this.statusMessage = '', 2000);
        },

        async processRecording() {
            const blob = new Blob(this.chunks, { type: 'audio/wav' });
            this.audioUrl = URL.createObjectURL(blob);

            // Buat file dari blob
            const fileName = `recording-${Date.now()}.wav`;
            const file = new File([blob], fileName, { type: 'audio/wav' });

            // Buat FormData untuk Livewire upload
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);

            // Update input file untuk Filament/Livewire
            const event = new Event('change', { bubbles: true });
            this.$refs.audioInput.files = dataTransfer.files;
            this.$refs.audioInput.dispatchEvent(event);

            // Trigger Livewire upload
            const uploadEvent = new CustomEvent('upload-file', { detail: { file } });
            this.$refs.audioInput.dispatchEvent(uploadEvent);
        },

        visualize() {
            if (!this.isRecording) return;

            const bufferLength = this.analyser.frequencyBinCount;
            const dataArray = new Uint8Array(bufferLength);
            const width = this.visualizerCanvas.width;
            const height = this.visualizerCanvas.height;

            const draw = () => {
                if (!this.isRecording) return;
                requestAnimationFrame(draw);

                this.analyser.getByteFrequencyData(dataArray);
                this.canvasCtx.fillStyle = 'rgb(255, 255, 255)';
                this.canvasCtx.fillRect(0, 0, width, height);

                const barWidth = (width / bufferLength) * 2.5;
                let barHeight;
                let x = 0;

                for (let i = 0; i < bufferLength; i++) {
                    barHeight = dataArray[i] / 2;
                    this.canvasCtx.fillStyle = `rgb(${barHeight + 100}, 50, 50)`;
                    this.canvasCtx.fillRect(x, height - barHeight, barWidth, barHeight);
                    x += barWidth + 1;
                }

                // Update volume meter
                const volume = dataArray.reduce((a, b) => a + b) / bufferLength;
                this.$refs.volumeMeter.style.width = `${(volume / 256) * 100}%`;
            };

            draw();
        },

        visualizePlayback() {
            // Implementasi visualisasi untuk playback
            // Mirip dengan visualize() tapi untuk audio player
        }
    }
}
</script>
@endpush
