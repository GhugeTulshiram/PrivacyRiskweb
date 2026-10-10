<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PrivacyGuard | Face Login</title>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            color: #e2e8f0;
            background:
                radial-gradient(circle at top, #12345a, transparent 45%),
                #020817;
        }

        .card {
            width: 100%;
            max-width: 520px;
            padding: 30px;
            text-align: center;
            background: rgba(5, 18, 38, 0.95);
            border: 1px solid rgba(56, 189, 248, 0.35);
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
        }

        h1 {
            margin-top: 0;
            color: #38bdf8;
        }

        .subtitle {
            color: #94a3b8;
            line-height: 1.6;
        }

        label {
            display: block;
            text-align: left;
            margin: 20px 0 8px;
        }

        input {
            width: 100%;
            padding: 13px;
            color: white;
            background: #07152b;
            border: 1px solid #334155;
            border-radius: 9px;
            outline: none;
        }

        input:focus {
            border-color: #38bdf8;
        }

        .camera-box {
            position: relative;
            margin: 20px auto;
            width: 100%;
            max-width: 400px;
            overflow: hidden;
            border: 1px solid #334155;
            border-radius: 14px;
            background: #000;
        }

        video {
            display: block;
            width: 100%;
            min-height: 220px;
            object-fit: cover;
            transform: scaleX(-1);
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 12px;
            border: 0;
            border-radius: 10px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .start-btn {
            color: #02111f;
            background: #38bdf8;
        }

        .verify-btn {
            color: white;
            background: #2563eb;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        #status {
            min-height: 45px;
            margin: 15px 0;
            padding: 12px;
            color: #bae6fd;
            background: rgba(14, 165, 233, 0.10);
            border-radius: 9px;
            overflow-wrap: anywhere;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #7dd3fc;
            text-decoration: none;
        }

        .error {
            margin: 12px 0;
            padding: 12px;
            color: #fecaca;
            background: rgba(220, 38, 38, 0.15);
            border-radius: 9px;
        }

        .notice {
            color: #fcd34d;
            font-size: 12px;
            line-height: 1.6;
        }
    </style>
</head>

<body>

<div class="card">

    <h1>🛡️ PrivacyGuard</h1>

    <h2>Login with Face</h2>

    <p class="subtitle">
        Enter your registered email and verify your face.
    </p>

    <?php if ($error !== ''): ?>
        <div class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form id="faceLoginForm" action="php/auth/face_login_verify.php" method="POST">

        <label for="email">Registered Email Address</label>

        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your registered email"
            autocomplete="email"
            required
        >

        <input
            type="hidden"
            id="descriptor"
            name="descriptor"
        >

        <div class="camera-box">
            <video id="video" autoplay muted playsinline></video>
        </div>

        <button type="button" class="start-btn" id="startBtn">
            Start Camera
        </button>

        <button type="submit" class="verify-btn" id="verifyBtn" disabled>
            Verify Face & Login
        </button>

    </form>

    <div id="status">Loading face recognition models...</div>

    <p class="notice">
        Camera access is required. Face matching alone does not prevent
        photo or video spoofing. This is an educational prototype.
    </p>

    <a class="back-link" href="index.php">← Back to Password Login</a>

</div>

<script>
const video = document.getElementById("video");
const statusBox = document.getElementById("status");
const startBtn = document.getElementById("startBtn");
const verifyBtn = document.getElementById("verifyBtn");
const descriptorInput = document.getElementById("descriptor");
const form = document.getElementById("faceLoginForm");

const MODEL_URL =
    "https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights";

let modelsLoaded = false;
let cameraStream = null;

async function loadModels() {
    try {
        statusBox.textContent = "Loading face recognition models...";

        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

        modelsLoaded = true;
        statusBox.textContent = "Models loaded. Click Start Camera.";
        startBtn.disabled = false;
    } catch (error) {
        console.error(error);
        statusBox.textContent =
            "Could not load models. Check your internet connection and refresh.";
        startBtn.disabled = true;
    }
}

startBtn.disabled = true;
verifyBtn.disabled = true;

startBtn.addEventListener("click", async () => {
    if (!modelsLoaded) {
        statusBox.textContent = "Please wait for models to load.";
        return;
    }

    try {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
        }

        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: "user"
            },
            audio: false
        });

        video.srcObject = cameraStream;

        await video.play();

        statusBox.textContent =
            "Camera ready. Position your face in the center.";

        verifyBtn.disabled = false;

    } catch (error) {
        console.error(error);

        statusBox.textContent =
            "Camera unavailable. Allow camera permission and try again.";

        verifyBtn.disabled = true;
    }
});

form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();

    if (!email) {
        statusBox.textContent = "Enter your registered email.";
        return;
    }

    if (!cameraStream || video.readyState < 2) {
        statusBox.textContent = "Start the camera first.";
        return;
    }

    verifyBtn.disabled = true;
    startBtn.disabled = true;

    try {
        statusBox.textContent = "Detecting your face...";

        const result = await faceapi
            .detectSingleFace(
                video,
                new faceapi.TinyFaceDetectorOptions({
                    inputSize: 320,
                    scoreThreshold: 0.5
                })
            )
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!result) {
            statusBox.textContent =
                "No face detected. Improve lighting and try again.";

            return;
        }

        const descriptor = Array.from(result.descriptor);

        if (descriptor.length !== 128) {
            statusBox.textContent =
                "Could not create a valid face descriptor.";

            return;
        }

        descriptorInput.value = JSON.stringify(descriptor);

        statusBox.textContent = "Submitting face for verification...";

        // Server performs the actual descriptor comparison.
        form.submit();

    } catch (error) {
        console.error(error);

        statusBox.textContent =
            "Face detection failed. Please try again.";

    } finally {
        if (!descriptorInput.value) {
            verifyBtn.disabled = false;
            startBtn.disabled = false;
        }
    }
});

loadModels();
</script>

</body>
</html>