<x-app-layout>
    <div class="p-6">
        @if ($message)
            <p>{{ $message }}</p>
        @endif

        @if ($zoomMeeting)
            <h2>{{ $zoomMeeting->title_zoom }}</h2>
            <p>Start Time: {{ $zoomMeeting->start_time }}</p>
            <p>End Time: {{ $zoomMeeting->end_time }}</p>

            <div id="user-grid" class="grid grid-cols-2 gap-4">
                @if (!is_null($zoomCalls))
                    @foreach ($zoomCalls as $call)
                        <div class="border p-4 rounded shadow text-center" id="user-tile-{{ $call->user->id }}">
                            <p class="font-semibold">{{ $call->user->name }}: {{ $call->status }}</p>
                            <video id="video-{{ $call->user->id }}" autoplay playsinline
                                class="w-full h-40 bg-black rounded" muted></video>
                            <div class="mt-2">
                                <span id="mic-status-{{ $call->user->id }}" class="text-gray-500 text-sm">Mic Off</span>
                                |
                                <span id="cam-status-{{ $call->user->id }}" class="text-gray-500 text-sm">Cam Off</span>
                            </div>
                        </div>
                    @endforeach
            </div>
        @endif
        @endif
    </div>

    <div class="mt-6 space-x-2">
        <button onclick="toggleCamera()" class="bg-blue-500 text-white px-4 py-2 rounded">Toggle Camera</button>
        <button onclick="toggleMic()" class="bg-green-500 text-white px-4 py-2 rounded">Toggle Mic</button>
        <button onclick="leaveCall()" class="bg-red-500 text-white px-4 py-2 rounded">Leave Call</button>
    </div>


    <script>
        let localStream;
        let micOn = false;
        let camOn = false;

        async function toggleCamera() {
            if (!localStream) {
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
            }

            camOn = !camOn;
            const videoTrack = localStream.getVideoTracks()[0];
            if (videoTrack) videoTrack.enabled = camOn;

            document.getElementById('cam-status-{{ auth()->id() }}').textContent = camOn ? 'Cam On' : 'Cam Off';
            document.getElementById('video-{{ auth()->id() }}').srcObject = camOn ? localStream : null;
        }

        async function toggleMic() {
            if (!localStream) {
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
            }

            micOn = !micOn;
            const audioTrack = localStream.getAudioTracks()[0];
            if (audioTrack) audioTrack.enabled = micOn;

            document.getElementById('mic-status-{{ auth()->id() }}').textContent = micOn ? 'Mic On' : 'Mic Off';
        }

        function leaveCall() {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }

            window.location.href = '/tasks';
        }
    </script>
</x-app-layout>
