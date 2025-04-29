<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/zoom-meeting.css') }}">
    </head>
    <div class="zoom-container">
        @if ($message)
            <div class="zoom-message">
                {{ $message }}
            </div>
        @endif

        @if ($zoomMeeting)
            <div class="zoom-header">
                <h2>"{{ $zoomMeeting->title_zoom }}"</h2>
                <p>Start Time: <span>{{ $zoomMeeting->start_time }}</span> - End
                    Time:<span> {{ $zoomMeeting->end_time }}</span>
                </p>
            </div>

            <div id="user-grid" class="user-grid">
                @if (!is_null($zoomCalls))
                    @foreach ($zoomCalls as $call)
                        <div class="user-tile" id="user-tile-{{ $call->user->id }}">
                            <p class="user-name">{{ $call->user->name }}: <span
                                    class="user-status">{{ $call->status }}</span></p>
                            <video id="video-{{ $call->user->id }}" autoplay playsinline muted
                                class="user-video"></video>
                            <div class="user-controls">
                                <span id="mic-status-{{ $call->user->id }}">Mic Off</span> |
                                <span id="cam-status-{{ $call->user->id }}">Cam Off</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endif
        <div class="zoom-buttons">
            <button onclick="toggleCamera()" class="btn camera-btn">Camera</button>
            <button onclick="toggleMic()" class="btn mic-btn">Mic</button>
            <button onclick="leaveCall()" class="btn leave-btn">End/Leave call</button>
        </div>
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
