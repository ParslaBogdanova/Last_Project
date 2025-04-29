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

    <main class="issues-zoom-call">
        <div class="container">
            <div class="issues">
                <h2>Having issues with your camera? Is it glitching?</h2>
                <div class="issues-info">
                    <br>Don't worry, it happened to me too. One reason could be that you didn't turn off the camera
                    before leaving the Zoom meeting.
                    <div class="bottom-text">
                        Don't forget to turn off the camera before leaving the zoom call.
                    </div>
                </div>
            </div>
            <div class="issues-solving">
                <h2>How to solve problems that i used:</h2>
                <div class="problem-solving-info">
                    Open <strong>Settings &#8594; Privacy & Security &#8594; Camera</strong>. Make sure "Camera access"
                    is
                    turned on. Then check "Camera device settings" - if no device appears under "Connected
                    cameras", the camera may be glitching. <br><br>

                    Search for <strong>Device Manager</strong>. If the camera appears and disappears repeatedly,
                    right-click it under "Cameras" and select "Update driver" or "Uninstall device". After
                    uninstalling, restart your PC to reinstall it automatically.<br><br>

                    Press <strong>Win + R</strong>, type <code>services.msc</code>, then press Enter. In the
                    list, find <strong>Windows Camera Frame Server</strong> and <strong>Windows Camera Frame Server
                        Monitor</strong>. Right-click each and choose "Restart".<br><br>

                    If you're still having issues, open <strong>Command Prompt as Administrator</strong> and run
                    the following command:<br>
                    <code>sfc /scannow</code><br>
                    You can also try: <code>chkdsk /f /r</code> (this will scan and fix drive issues after a
                    reboot).
                </div>
            </div>
        </div>
    </main>




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
