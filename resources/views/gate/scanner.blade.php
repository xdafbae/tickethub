@extends('layouts.app')

@section('title', 'Gate Scanner')
@section('page-title', 'Gate Entry — QR Scanner')

@section('additional-css')
<style>
    .scanner-card { border:1px solid var(--admin-border); border-radius:14px; background: rgba(255,255,255,0.05); padding:12px; }
    .video-wrap { position:relative; border-radius:10px; overflow:hidden; background:#000; }
    video { width: 100%; height: auto; }
    canvas { display:none; }
    .toolbar { display:flex; gap:8px; margin-top:10px; }
    .status { margin-top:10px; padding:10px 12px; border-radius:10px; }
    .status.valid { background:#0d2b11; border:1px solid #22c55e; color:#0f5132; }
    .status.used { background:#242305; border:1px solid #f59e0b; color:#7a6400; }
    .status.refunded { background:#2b0d0d; border:1px solid #ef4444; color:#842029; }
    .muted { color:#9ca3af; font-size:12px; }
</style>
@endsection

@section('content')
<div class="admin-content-section">
    <div class="scanner-card">
        <div class="video-wrap">
            <video id="video" playsinline></video>
        </div>
        <div class="toolbar">
            <button id="startBtn" class="btn btn-primary">Mulai Kamera</button>
            <button id="stopBtn" class="btn">Hentikan</button>
            <button id="torchBtn" class="btn">Torch</button>
        </div>
        <div id="hint" class="muted" style="margin-top:8px;">Arahkan kamera ke QR pada e‑ticket.</div>

        <div id="result" class="status" style="display:none;"></div>
        <canvas id="canvas"></canvas>
    </div>
</div>
@endsection

@section('additional-js')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script src="https://unpkg.com/@zxing/library@0.19.1/umd/index.min.js"></script>
<script>
(function(){
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    const startBtn = document.getElementById('startBtn');
    const stopBtn  = document.getElementById('stopBtn');
    const torchBtn = document.getElementById('torchBtn');
    const resultEl = document.getElementById('result');
    const hintEl = document.getElementById('hint');
    let stream = null;
    let scanning = false;
    let track = null;
    let torchOn = false;
    let fallbackReader = null;
    let jsqrFailCount = 0;

    function showResult(type, text) {
        resultEl.style.display = 'block';
        resultEl.className = 'status ' + (type || '');
        resultEl.textContent = text;
    }

    async function startCamera() {
        stopCamera();
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: false
            });
            video.srcObject = stream;
            await video.play();
            track = stream.getVideoTracks()[0] || null;
            hintEl.textContent = 'Kamera aktif. Arahkan ke QR.';
            scanning = true;
            jsqrFailCount = 0;
            scanFrame();
        } catch (e) {
            hintEl.textContent = 'Tidak dapat mengakses kamera: ' + e.message;
        }
    }

    function stopCamera() {
        scanning = false;
        if (fallbackReader) { try { fallbackReader.reset(); } catch {} fallbackReader = null; }
        if (track) { track.stop(); track = null; }
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            video.srcObject = null;
            stream = null;
        }
        hintEl.textContent = 'Kamera dimatikan.';
    }

    async function toggleTorch() {
        if (!track) return;
        const cap = track.getCapabilities ? track.getCapabilities() : {};
        if (!cap.torch) {
            hintEl.textContent = 'Torch tidak didukung di perangkat ini.';
            return;
        }
        torchOn = !torchOn;
        try {
            await track.applyConstraints({ advanced: [{ torch: torchOn }] });
            hintEl.textContent = torchOn ? 'Torch ON' : 'Torch OFF';
        } catch (e) {
            hintEl.textContent = 'Gagal ubah torch: ' + e.message;
        }
    }

    async function validate(qrText) {
        try {
            const res = await fetch("{{ route('gate.validate') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ qr: qrText })
            });
            let data;
            try {
                data = await res.json();
            } catch {
                showResult('refunded', 'Respons tidak valid. Pastikan akun punya role gate_staff atau admin.');
                return;
            }
            if (data.status === 'valid') {
                showResult('valid', `VALID • Order #${data.order_id} • ${data.event} • ${data.buyer}`);
            } else if (data.status === 'used') {
                showResult('used', `SUDAH DIGUNAKAN • Order #${data.order_id} • ${data.event} • ${data.buyer}`);
            } else if (data.status === 'refunded') {
                showResult('refunded', `REFUND • Order #${data.order_id} • ${data.event} • ${data.buyer}`);
            } else {
                showResult('refunded', `TIDAK VALID • ${data.error || 'QR tidak dikenali'}`);
            }
        } catch (e) {
            showResult('refunded', 'Kesalahan jaringan: ' + e.message);
        } finally {
            setTimeout(() => { scanning = true; }, 900);
        }
    }

    function scanFrame() {
        if (!scanning) return;
        requestAnimationFrame(scanFrame);
        if (video.readyState !== video.HAVE_ENOUGH_DATA) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const cropW = Math.floor(canvas.width * 0.7);
        const cropH = Math.floor(canvas.height * 0.7);
        const cropX = Math.floor((canvas.width - cropW) / 2);
        const cropY = Math.floor((canvas.height - cropH) / 2);
        const imageData = ctx.getImageData(cropX, cropY, cropW, cropH);

        const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: "attemptBoth" });
        if (code && code.data) {
            scanning = false;
            jsqrFailCount = 0;
            validate(code.data);
        } else {
            jsqrFailCount++;
            if (jsqrFailCount === 30 && !fallbackReader) {
                hintEl.textContent = 'Mencoba metode pemindaian alternatif (ZXing)...';
                try {
                    fallbackReader = new ZXing.BrowserMultiFormatReader();
                    fallbackReader.decodeFromVideoDevice(null, video, (result, err) => {
                        if (result && result.text) {
                            scanning = false;
                            validate(result.text);
                        }
                    });
                } catch (e) {
                    console.warn('ZXing init failed:', e);
                }
            }
        }
    }

    startBtn.addEventListener('click', startCamera);
    stopBtn.addEventListener('click', stopCamera);
    torchBtn.addEventListener('click', toggleTorch);

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        startCamera();
    } else {
        hintEl.textContent = 'Perangkat tidak mendukung kamera.';
    }
})();
</script>
@endsection