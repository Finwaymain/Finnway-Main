@extends('finance.layouts.base')
@section('title', 'Selfie Verification — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s16_validation', ['phone' => $phone]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card" style="padding: 24px 16px;">
    <h2 class="fw-heading" style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
        Agent Verification Required
    </h2>
    <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
        Please capture a joint verification selfie of the applicant with the verification agent.
    </p>
    
    <!-- Guidelines Box -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px;">
        <div style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
            Verification Guidelines:
        </div>
        <ul style="margin: 0; padding-left: 18px; font-size: 13px; color: #334155; line-height: 1.6;">
            <li>Applicant face must be clearly visible</li>
            <li>Agent face must be clearly visible in frame</li>
            <li>Good lighting, avoid glare or blur</li>
            <li>No sunglasses, caps, or face covering</li>
        </ul>
    </div>

    <!-- Selfie Capture / Preview Form -->
    <form id="selfieForm" method="POST" action="{{ route('finance.cash_loan.s17_selfie_agent_submit', ['phone' => $phone]) }}" enctype="multipart/form-data">
        @csrf
        <!-- Direct front-camera trigger (capture="user" launches FRONT selfie camera directly on Android/iOS) -->
        <input type="file" id="cameraFileInput" name="selfie" accept="image/*" capture="user" style="display: none;">
        <input type="hidden" name="selfie_base64" id="selfieBase64">

        <!-- Live Camera Stream Element (fallback/direct) -->
        <div id="liveStreamContainer" style="display: none; position: relative; width: 100%; max-width: 320px; margin: 0 auto 16px; border-radius: 14px; overflow: hidden; background: #000;">
            <video id="liveVideo" autoplay playsinline muted style="width: 100%; height: 260px; object-fit: cover; transform: scaleX(-1);"></video>
            <div style="position: absolute; bottom: 12px; left: 0; width: 100%; display: flex; justify-content: center; gap: 12px;">
                <button type="button" id="snapPhotoBtn" style="background: #ffffff; color: #0f172a; border: 4px solid #3b82f6; border-radius: 50%; width: 56px; height: 56px; font-size: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                    📸
                </button>
                <button type="button" id="cancelStreamBtn" style="background: rgba(0,0,0,0.6); color: #fff; border: 1px solid rgba(255,255,255,0.4); border-radius: 20px; padding: 6px 14px; font-size: 12px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>
        <canvas id="captureCanvas" style="display: none;"></canvas>

        <!-- Initial Placeholder State (No photo yet) -->
        <div id="placeholderBox" style="text-align: center; border: 2px dashed #cbd5e1; border-radius: 16px; padding: 30px 16px; margin-bottom: 20px; background: #fafafa;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 12px;">
                📷
            </div>
            <div style="font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 4px;">
                Front Camera Verification
            </div>
            <div style="font-size: 12px; color: #64748b; margin-bottom: 18px;">
                Tap below to open your front selfie camera directly
            </div>
            <button type="button" id="openCameraBtn" class="fw-btn fw-btn-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; max-width: 260px; margin: 0 auto;">
                <span>📷</span>
                <span>Open Front Camera</span>
            </button>
        </div>

        <!-- Captured Photo Preview State (Shows captured image + Retake option) -->
        <div id="previewBox" style="display: none; text-align: center; margin-bottom: 20px;">
            <div style="display: inline-block; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 20px; margin-bottom: 12px;">
                ✓ Selfie Captured Successfully
            </div>
            
            <div style="position: relative; width: 100%; max-width: 300px; height: 280px; margin: 0 auto 16px; border-radius: 14px; overflow: hidden; border: 2px solid #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.06); background: #000;">
                <img id="selfiePreviewImage" src="" alt="Captured Selfie" style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            <!-- Action buttons after photo is taken -->
            <div style="display: flex; gap: 10px; max-width: 320px; margin: 0 auto;">
                <button type="button" id="retakeBtn" class="fw-btn fw-btn-outline" style="flex: 1; border: 1px solid #cbd5e1; color: #0f172a; font-weight: 600;">
                    🔄 Retake
                </button>
                <button type="submit" id="submitSelfieBtn" class="fw-btn fw-btn-primary" style="flex: 2;">
                    Submit Verification →
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var cameraFileInput = document.getElementById('cameraFileInput');
    var selfieBase64Input = document.getElementById('selfieBase64');
    var openCameraBtn = document.getElementById('openCameraBtn');
    var placeholderBox = document.getElementById('placeholderBox');
    var previewBox = document.getElementById('previewBox');
    var selfiePreviewImage = document.getElementById('selfiePreviewImage');
    var retakeBtn = document.getElementById('retakeBtn');
    var liveStreamContainer = document.getElementById('liveStreamContainer');
    var liveVideo = document.getElementById('liveVideo');
    var captureCanvas = document.getElementById('captureCanvas');
    var snapPhotoBtn = document.getElementById('snapPhotoBtn');
    var cancelStreamBtn = document.getElementById('cancelStreamBtn');

    var streamActive = null;

    // Trigger Camera Action
    function triggerFrontCamera() {
        // Preferred Native capture="user" on mobile which opens front selfie camera directly
        if (/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            cameraFileInput.value = '';
            cameraFileInput.click();
            return;
        }

        // Desktop / Laptop: try live mediaDevices front camera
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 960 } },
                audio: false
            })
            .then(function (stream) {
                streamActive = stream;
                liveVideo.srcObject = stream;
                liveVideo.play();
                placeholderBox.style.display = 'none';
                previewBox.style.display = 'none';
                liveStreamContainer.style.display = 'block';
            })
            .catch(function (err) {
                console.log('Live camera stream not supported or denied, falling back to input:', err);
                cameraFileInput.value = '';
                cameraFileInput.click();
            });
        } else {
            cameraFileInput.value = '';
            cameraFileInput.click();
        }
    }

    function stopLiveStream() {
        if (streamActive) {
            streamActive.getTracks().forEach(function (track) { track.stop(); });
            streamActive = null;
        }
        liveStreamContainer.style.display = 'none';
    }

    // 1. When user taps Open Camera
    openCameraBtn.addEventListener('click', function () {
        triggerFrontCamera();
    });

    // 2. When photo taken via native camera file input
    cameraFileInput.addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (evt) {
                displayPhoto(evt.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // 3. When snapping photo from live video stream
    snapPhotoBtn.addEventListener('click', function () {
        if (!liveVideo.videoWidth) return;
        captureCanvas.width = liveVideo.videoWidth;
        captureCanvas.height = liveVideo.videoHeight;
        var ctx = captureCanvas.getContext('2d');
        // Flip horizontally to match mirror preview
        ctx.translate(captureCanvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(liveVideo, 0, 0, captureCanvas.width, captureCanvas.height);
        
        var dataUrl = captureCanvas.toDataURL('image/jpeg', 0.9);
        stopLiveStream();
        displayPhoto(dataUrl);
    });

    cancelStreamBtn.addEventListener('click', function () {
        stopLiveStream();
        placeholderBox.style.display = 'block';
    });

    // 4. Display Photo in Preview Box
    function displayPhoto(imageDataUri) {
        selfieBase64Input.value = imageDataUri;
        selfiePreviewImage.src = imageDataUri;
        placeholderBox.style.display = 'none';
        liveStreamContainer.style.display = 'none';
        previewBox.style.display = 'block';
    }

    // 5. When Retake Button is clicked
    retakeBtn.addEventListener('click', function () {
        selfieBase64Input.value = '';
        selfiePreviewImage.src = '';
        previewBox.style.display = 'none';
        triggerFrontCamera();
    });
});
</script>
@endsection
