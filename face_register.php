<?php
session_start();
require_once __DIR__ . '/php/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_face'])) {
    $face_encoding = trim($_POST['face_encoding'] ?? '');
    $encoding_data = json_decode($face_encoding, true);

    if (
        !is_array($encoding_data) ||
        count($encoding_data) !== 128
    ) {
        $message = "Invalid face data. Please detect your face again.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO face_auth (user_id, face_encoding)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE face_encoding = VALUES(face_encoding)"
        );

        if ($stmt) {
            $stmt->bind_param("is", $user_id, $face_encoding);

            if ($stmt->execute()) {
                $message = "Face descriptor registered successfully.";
                $message_type = "success";
            } else {
                $message = "Database error while saving face data.";
                $message_type = "error";
            }

            $stmt->close();
        } else {
            $message = "Could not prepare the database query.";
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Face Registration | Privacy Risk Awareness Assistant</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    padding: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: Arial, sans-serif;
    color: #eaf2ff;
    background:
        radial-gradient(circle at top left, #183a65, transparent 45%),
        linear-gradient(135deg, #07111f, #0c1930, #101d38);
}

.container {
    width: 100%;
    max-width: 760px;
    padding: 28px;
    background: #0e1c32;
    border: 1px solid #2c4364;
    border-radius: 20px;
    box-shadow: 0 20px 60px #0006;
}

.header {
    text-align: center;
    margin-bottom: 22px;
}

.icon {
    font-size: 44px;
}

h1 {
    margin: 10px 0;
    font-size: 27px;
}

.subtitle {
    color: #aabbd3;
    line-height: 1.6;
}

.camera-box {
    position: relative;
    width: 100%;
    max-width: 560px;
    margin: 20px auto;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    border-radius: 16px;
    background: #030914;
    border: 1px solid #304866;
}

video, canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

canvas {
    pointer-events: none;
}

.placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: #91a5c1;
    padding: 20px;
}

.status {
    padding: 13px;
    border-radius: 10px;
    margin: 16px 0;
    background: #14243a;
    color: #d4e2f7;
    text-align: center;
    overflow-wrap: anywhere;
}

.success {
    color: #86efac;
    background: #103323;
}

.error {
    color: #fca5a5;
    background: #3b171b;
}

.buttons {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 12px;
    margin-top: 18px;
}

button, .back-link {
    border: 0;
    border-radius: 10px;
    padding: 13px 18px;
    color: white;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.start {
    background: #2563eb;
}

.stop {
    background: #475569;
}

.register {
    background: #059669;
}

button:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.back-link {
    background: #253852;
}

.notice {
    font-size: 13px;
    color: #afc0d7;
    line-height: 1.7;
    margin-top: 20px;
    padding: 14px;
    background: #101e32;
    border-radius: 10px;
}

@media(max-width: 500px) {
    .container {
        padding: 18px;
    }

    .buttons button, .buttons a {
        width: 100%;
        text-align: center;
    }
}
</style>
</head>

<body>
<div class="container">

    <div class="header">
        <div class="icon">🔐</div>
        <h1>Face Registration</h1>
        <div class="subtitle">
            Privacy Risk Awareness Assistant<br>
            Register a facial descriptor for your account.
        </div>
    </div>

    <?php if ($message !== ""): ?>
        <div class="status <?= htmlspecialchars($message_type) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="camera-box">
        <div class="placeholder" id="placeholder">
            Camera is stopped.<br>Click Start Camera to begin.
        </div>

        <video id="cameraPreview" autoplay muted playsinline></video>
        <canvas id="faceCanvas"></canvas>
    </div>

    <div class="status" id="faceStatus" role="status" aria-live="polite">
        Ready. Click Start Camera.
    </div>

    <div class="buttons">
        <button type="button" class="start" id="startCamera">
            📷 Start Camera
        </button>

        <button type="button" class="stop" id="stopCamera">
            Stop Camera
        </button>
    </div>

    <form method="POST" id="faceForm">
        <input type="hidden" name="face_encoding" id="faceEncoding">

        <div class="buttons">
            <button
                type="submit"
                name="save_face"
                value="1"
                class="register"
                id="registerFace"
                disabled
            >
                🔒 Register Face
            </button>

            <a class="back-link" href="dashboard.php">
                Back to Dashboard
            </a>
        </div>
    </form>

    <div class="notice">
        <strong>Privacy notice:</strong>
        Face descriptors are sensitive biometric data. Register only with
        informed consent. This demo has no liveness detection and may be
        vulnerable to photo or video replay. Keep password login available.
    </div>
</div>

<!-- Face recognition library -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
const video = document.getElementById("cameraPreview");
const canvas = document.getElementById("faceCanvas");
const placeholder = document.getElementById("placeholder");
const faceStatus = document.getElementById("faceStatus");
const startButton = document.getElementById("startCamera");
const stopButton = document.getElementById("stopCamera");
const registerButton = document.getElementById("registerFace");
const faceEncodingInput = document.getElementById("faceEncoding");

let cameraStream = null;
let detectionTimer = null;
let latestDescriptor = null;
let modelsReady = false;
let detecting = false;

const MODEL_URL =
    "https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights";

function setStatus(message, type = "") {
    faceStatus.textContent = message;
    faceStatus.className = "status" + (type ? " " + type : "");
}

async function loadModels() {
    try {
        if (typeof faceapi === "undefined") {
            throw new Error("Face recognition library failed to load.");
        }

        setStatus("Loading face recognition models...");

        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

        modelsReady = true;
        setStatus("Models loaded. Camera is ready.", "success");

        if (cameraStream) {
            beginFaceDetection();
        }
    } catch (error) {
        console.error("Model loading error:", error);
        setStatus(
            "Models failed to load. Check internet and model URLs. Camera can still be started.",
            "error"
        );
    }
}

async function startCamera() {
    try {
        if (!navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia) {
            throw new Error(
                "Camera API unavailable. Open this page on localhost using Chrome or Edge."
            );
        }

        if (cameraStream) {
            setStatus("Camera is already running.");
            return;
        }

        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user" },
            audio: false
        });

        video.srcObject = cameraStream;
        await video.play();

        placeholder.style.display = "none";
        setStatus("Camera started.", "success");

        if (modelsReady) {
            beginFaceDetection();
        } else {
            setStatus(
                "Camera started. Face recognition models are still loading.",
                "error"
            );
        }
    } catch (error) {
        console.error("Camera error:", error);
        setStatus("Camera error: " + error.message, "error");
    }
}

function stopCamera() {
    if (detectionTimer) {
        clearInterval(detectionTimer);
        detectionTimer = null;
    }

    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }

    video.srcObject = null;
    placeholder.style.display = "flex";

    const ctx = canvas.getContext("2d");
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    latestDescriptor = null;
    faceEncodingInput.value = "";
    registerButton.disabled = true;
    detecting = false;

    setStatus("Camera stopped.");
}

function beginFaceDetection() {
    if (!modelsReady || !cameraStream || detectionTimer) {
        return;
    }

    detectionTimer = setInterval(async () => {
        if (detecting || video.readyState < 2) {
            return;
        }

        detecting = true;

        try {
            const result = await faceapi
                .detectSingleFace(
                    video,
                    new faceapi.TinyFaceDetectorOptions()
                )
                .withFaceLandmarks()
                .withFaceDescriptor();

            const width = video.videoWidth;
            const height = video.videoHeight;

            if (!width || !height) {
                return;
            }

            if (canvas.width !== width || canvas.height !== height) {
                canvas.width = width;
                canvas.height = height;
            }

            const ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (!result) {
                latestDescriptor = null;
                faceEncodingInput.value = "";
                registerButton.disabled = true;
                setStatus("No face detected. Look directly at the camera.");
                return;
            }

            faceapi.draw.drawDetections(canvas, result);
            faceapi.draw.drawFaceLandmarks(canvas, result);

            latestDescriptor = Array.from(result.descriptor);
            registerButton.disabled = false;

            setStatus(
                "Face detected. Click Register Face to save its descriptor.",
                "success"
            );
        } catch (error) {
            console.error("Face detection error:", error);
            setStatus("Face detection failed. Check model files.", "error");
        } finally {
            detecting = false;
        }
    }, 800);
}

document.getElementById("faceForm").addEventListener("submit", function(event) {
    if (!latestDescriptor || latestDescriptor.length !== 128) {
        event.preventDefault();
        setStatus("Detect a face before registering.", "error");
        return;
    }

    faceEncodingInput.value = JSON.stringify(latestDescriptor);
});

startButton.addEventListener("click", startCamera);
stopButton.addEventListener("click", stopCamera);

window.addEventListener("beforeunload", () => {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }
});

loadModels();
</script>
</body>
</html>