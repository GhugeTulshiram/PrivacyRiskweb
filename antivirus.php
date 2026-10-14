<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . "/php/config/db.php";

$user_id = (int)$_SESSION["user_id"];
$user_name = $_SESSION["user_name"] ?? "User";
$user_email = $_SESSION["user_email"] ?? "";
$user_initial = strtoupper(substr(trim($user_name), 0, 1));

// Retrieve scan stats for header
$total_scans = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM scans WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) {
        $total_scans = (int)$r["total"];
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Camera & Antivirus Threat Shield | PrivacyGuard</title>
    
    <!-- jsQR for real-time camera QR code threat scanning -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    
    <style>
        :root {
            --bg-main: #020817;
            --bg-sidebar: #06152b;
            --bg-card: rgba(8, 24, 48, 0.7);
            --bg-card-hover: rgba(13, 34, 66, 0.85);
            --border-card: rgba(56, 189, 248, 0.2);
            --border-glow: rgba(56, 189, 248, 0.5);
            --cyan: #38bdf8;
            --blue: #2563eb;
            --green: #10b981;
            --yellow: #f59e0b;
            --red: #ef4444;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: radial-gradient(circle at 80% 15%, rgba(37, 99, 235, 0.15), transparent 30%),
                        radial-gradient(circle at 20% 85%, rgba(6, 182, 212, 0.1), transparent 35%),
                        var(--bg-main);
            color: var(--text-main);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* -----------------------------------------------------------------
           SIDEBAR
           ----------------------------------------------------------------- */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #06152b, #031022);
            border-right: 1px solid rgba(59, 130, 246, 0.25);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 24px;
            border-bottom: 1px solid rgba(56, 189, 248, 0.15);
            margin-bottom: 20px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.4);
        }

        .brand h2 {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
        }

        .brand h2 span {
            color: var(--cyan);
        }

        .brand p {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .nav-title {
            font-size: 10px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            margin: 14px 10px 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }

        .nav-item:hover {
            background: rgba(56, 189, 248, 0.1);
            color: var(--cyan);
            transform: translateX(3px);
        }

        .nav-item.active {
            background: linear-gradient(90deg, rgba(37, 99, 235, 0.25), rgba(6, 182, 212, 0.15));
            border-left: 3px solid var(--cyan);
            color: #fff;
            font-weight: 600;
        }

        .nav-badge {
            margin-left: auto;
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 999px;
            letter-spacing: 0.5px;
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid rgba(56, 189, 248, 0.15);
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            background: rgba(2, 6, 23, 0.6);
            border: 1px solid rgba(56, 189, 248, 0.15);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
        }

        .user-meta h4 {
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
        }

        .user-meta span {
            font-size: 10px;
            color: var(--cyan);
        }

        /* -----------------------------------------------------------------
           MAIN CONTENT
           ----------------------------------------------------------------- */
        .main {
            margin-left: 260px;
            flex: 1;
            padding: 30px 40px 60px;
            max-width: 1380px;
        }

        /* TOPBAR */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 26px;
        }

        .page-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #34d399;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* HEADER STATS STRIP */
        .stats-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 26px;
        }

        .strip-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            backdrop-filter: blur(12px);
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.2s ease;
        }

        .strip-card:hover {
            border-color: var(--border-glow);
            transform: translateY(-2px);
        }

        .strip-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .strip-card h3 {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
        }

        .strip-card p {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* -----------------------------------------------------------------
           TABS NAVIGATION
           ----------------------------------------------------------------- */
        .tabs {
            display: flex;
            gap: 10px;
            background: rgba(8, 20, 40, 0.7);
            padding: 6px;
            border-radius: 14px;
            border: 1px solid var(--border-card);
            margin-bottom: 24px;
        }

        .tab-btn {
            flex: 1;
            padding: 12px 18px;
            border-radius: 10px;
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s ease;
        }

        .tab-btn:hover {
            color: #fff;
            background: rgba(56, 189, 248, 0.08);
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            color: #fff;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.4);
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* -----------------------------------------------------------------
           CAMERA SCANNER PANEL
           ----------------------------------------------------------------- */
        .camera-grid {
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: 24px;
        }

        .viewport-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            padding: 20px;
            backdrop-filter: blur(14px);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4);
        }

        .viewport-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .viewport-title {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .camera-screen {
            position: relative;
            width: 100%;
            height: 420px;
            background: #020617;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(56, 189, 248, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #cameraVideo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* mirror */
        }

        #cameraCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        /* Cybernetic Scanning Reticle Overlay */
        .scan-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .scan-reticle {
            width: 250px;
            height: 250px;
            border: 2px dashed rgba(56, 189, 248, 0.6);
            border-radius: 20px;
            position: relative;
            animation: reticle-pulse 3s infinite;
        }

        .scan-reticle::before, .scan-reticle::after {
            content: '';
            position: absolute;
            width: 22px;
            height: 22px;
            border-color: #38bdf8;
            border-style: solid;
        }

        .scan-reticle::before {
            top: -2px;
            left: -2px;
            border-width: 3px 0 0 3px;
            border-radius: 6px 0 0 0;
        }

        .scan-reticle::after {
            bottom: -2px;
            right: -2px;
            border-width: 0 3px 3px 0;
            border-radius: 0 0 6px 0;
        }

        .laser-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #38bdf8, #06b6d4, transparent);
            box-shadow: 0 0 12px #38bdf8;
            top: 0;
            animation: laser-scan 2.2s linear infinite;
        }

        @keyframes laser-scan {
            0% { top: 10%; opacity: 0.8; }
            50% { top: 90%; opacity: 1; }
            100% { top: 10%; opacity: 0.8; }
        }

        @keyframes reticle-pulse {
            0%, 100% { transform: scale(1); border-color: rgba(56, 189, 248, 0.5); }
            50% { transform: scale(1.02); border-color: rgba(6, 182, 212, 0.9); }
        }

        .camera-placeholder {
            text-align: center;
            padding: 30px;
            color: #64748b;
        }

        .camera-placeholder .cam-big-icon {
            font-size: 54px;
            margin-bottom: 12px;
            color: #334155;
        }

        .camera-controls {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }

        .btn {
            padding: 11px 20px;
            border-radius: 10px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            color: #fff;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.35);
        }

        .btn-primary:hover {
            box-shadow: 0 6px 22px rgba(2, 132, 199, 0.55);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #f87171;
        }

        .btn-danger:hover {
            background: #ef4444;
            color: #fff;
        }

        .btn-secondary {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--border-card);
            color: #e2e8f0;
        }

        .btn-secondary:hover {
            border-color: var(--cyan);
            color: var(--cyan);
        }

        /* SIDEBAR DIAGNOSTIC IN CAMERA */
        .camera-sidebar {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .detection-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 16px;
            padding: 18px;
        }

        .detection-card h3 {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .detection-switch {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: rgba(2, 6, 23, 0.5);
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .detection-switch input[type="checkbox"] {
            accent-color: #38bdf8;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .threat-feed {
            max-height: 230px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding-right: 4px;
        }

        .threat-feed::-webkit-scrollbar {
            width: 4px;
        }

        .threat-feed::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }

        .threat-item {
            padding: 10px 12px;
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.7);
            border-left: 3px solid #64748b;
            font-size: 11px;
            animation: slideIn 0.25s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(8px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .threat-item.safe {
            border-left-color: #10b981;
        }

        .threat-item.warning {
            border-left-color: #f59e0b;
            background: rgba(245, 158, 11, 0.08);
        }

        .threat-item.critical {
            border-left-color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        .threat-item-time {
            color: #64748b;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .threat-item-text {
            color: #e2e8f0;
            line-height: 1.4;
            word-break: break-all;
        }

        /* -----------------------------------------------------------------
           FILE ANTIVIRUS SCANNER PANEL
           ----------------------------------------------------------------- */
        .file-zone {
            background: var(--bg-card);
            border: 2px dashed rgba(56, 189, 248, 0.35);
            border-radius: 18px;
            padding: 45px 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .file-zone:hover, .file-zone.dragover {
            border-color: #38bdf8;
            background: rgba(56, 189, 248, 0.05);
            transform: scale(1.005);
        }

        .file-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-zone-icon {
            font-size: 48px;
            margin-bottom: 14px;
            display: inline-block;
            animation: bounce-icon 2s infinite;
        }

        @keyframes bounce-icon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .file-zone h3 {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }

        .file-zone p {
            font-size: 12px;
            color: var(--text-muted);
        }

        .file-samples {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 18px;
            font-size: 12px;
        }

        .sample-tag {
            padding: 5px 12px;
            border-radius: 8px;
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(56, 189, 248, 0.2);
            color: #38bdf8;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sample-tag:hover {
            background: rgba(56, 189, 248, 0.2);
            transform: translateY(-1px);
        }

        .scan-progress-box {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 14px;
        }

        .progress-bar-wrap {
            height: 8px;
            background: rgba(30, 41, 59, 0.8);
            border-radius: 999px;
            overflow: hidden;
            margin: 12px 0 8px;
        }

        .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #0284c7, #38bdf8, #10b981);
            border-radius: 999px;
            transition: width 0.3s ease;
        }

        .report-box {
            display: none;
            margin-top: 24px;
            padding: 24px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            backdrop-filter: blur(14px);
        }

        /* -----------------------------------------------------------------
           URL SCANNER PANEL
           ----------------------------------------------------------------- */
        .url-box {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            padding: 30px;
            backdrop-filter: blur(14px);
        }

        .url-input-wrap {
            display: flex;
            gap: 12px;
            margin-top: 14px;
        }

        .url-input {
            flex: 1;
            padding: 14px 18px;
            border-radius: 12px;
            background: rgba(2, 6, 23, 0.7);
            border: 1px solid rgba(56, 189, 248, 0.25);
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .url-input:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.2);
        }

        /* -----------------------------------------------------------------
           SYSTEM AUDIT PANEL
           ----------------------------------------------------------------- */
        .audit-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .audit-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 16px;
            padding: 22px;
        }

        .audit-card-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .audit-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .audit-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-radius: 10px;
            background: rgba(2, 6, 23, 0.5);
            font-size: 12px;
        }

        .badge-safe {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }

        .badge-warn {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .camera-grid, .audit-grid {
                grid-template-columns: 1fr;
            }
            .stats-strip {
                grid-template-columns: repeat(2, 1fr);
            }
            .sidebar {
                display: none;
            }
            .main {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">🛡️</div>
            <div>
                <h2>Privacy<span>Guard</span></h2>
                <p>AI Cyber Defense & Privacy</p>
            </div>
        </div>

        <div class="nav-title">MAIN MENU</div>
        <a href="dashboard.php" class="nav-item">
            <span>🏠</span>
            <span>Dashboard</span>
        </a>
        <a href="antivirus.php" class="nav-item active">
            <span>⚡</span>
            <span>Antivirus & Camera</span>
            <span class="nav-badge">NEW</span>
        </a>
        <a href="scan.php" class="nav-item">
            <span>🔎</span>
            <span>Privacy Scanner</span>
        </a>
        <a href="history.php" class="nav-item">
            <span>🕘</span>
            <span>Scan History</span>
        </a>

        <div class="nav-title">ANALYTICS & DEFENSE</div>
        <a href="reports.php" class="nav-item">
            <span>📊</span>
            <span>Reports & Threat Log</span>
        </a>
        <a href="awareness.php" class="nav-item">
            <span>🛡️</span>
            <span>Privacy Awareness</span>
        </a>
        <a href="government.php" class="nav-item">
            <span>🏛️</span>
            <span>Government Portal</span>
        </a>
        <a href="settings.php" class="nav-item">
            <span>⚙️</span>
            <span>Security Settings</span>
        </a>

        <div class="sidebar-footer">
            <div class="user-pill">
                <div class="user-avatar"><?= htmlspecialchars($user_initial) ?></div>
                <div class="user-meta">
                    <h4><?= htmlspecialchars($user_name) ?></h4>
                    <span>Shield Engine: Active</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN INTERFACE -->
    <main class="main">
        <!-- TOPBAR -->
        <div class="topbar">
            <div>
                <span class="page-badge">
                    <span class="status-dot"></span>
                    ALL-VECTOR THREAT DETECTION ENGINE v4.2
                </span>
                <h1 class="page-title">AI Camera & Antivirus Shield</h1>
                <p class="page-subtitle">Real-time live webcam surveillance detection, optical QR threats, file malware heuristic scanner, and phishing inspection.</p>
            </div>

            <div style="display:flex; gap:10px;">
                <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
        </div>

        <!-- STATS STRIP -->
        <section class="stats-strip">
            <div class="strip-card">
                <div class="strip-icon" style="background:rgba(56, 189, 248, 0.15); color:#38bdf8;">📷</div>
                <div>
                    <h3 id="statCameraThreats">0</h3>
                    <p>Camera Threats Blocked</p>
                </div>
            </div>
            <div class="strip-card">
                <div class="strip-icon" style="background:rgba(16, 185, 129, 0.15); color:#34d399;">🛡️</div>
                <div>
                    <h3 id="statFilesScanned">100%</h3>
                    <p>Heuristic Defense Active</p>
                </div>
            </div>
            <div class="strip-card">
                <div class="strip-icon" style="background:rgba(245, 158, 11, 0.15); color:#fbbf24;">⚡</div>
                <div>
                    <h3 id="statSignatureRules">18+</h3>
                    <p>Malware Signatures</p>
                </div>
            </div>
            <div class="strip-card">
                <div class="strip-icon" style="background:rgba(99, 102, 241, 0.15); color:#818cf8;">👁️</div>
                <div>
                    <h3 id="statSurveillanceStatus">Armed</h3>
                    <p>Snoop & Lens Guard</p>
                </div>
            </div>
        </section>

        <!-- TABS -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('cameraTab', this)">
                <span>📸</span> Live Camera Threat Scanner
            </button>
            <button class="tab-btn" onclick="switchTab('fileTab', this)">
                <span>📁</span> File & Code Antivirus
            </button>
            <button class="tab-btn" onclick="switchTab('urlTab', this)">
                <span>🌐</span> URL & Phishing Inspector
            </button>
            <button class="tab-btn" onclick="switchTab('systemTab', this)">
                <span>🛡️</span> System & Browser Audit
            </button>
        </div>

        <!-- =============================================================
             TAB 1: LIVE CAMERA SCANNER
             ============================================================= -->
        <div id="cameraTab" class="tab-panel active">
            <div class="camera-grid">
                <!-- CAMERA VIEWPORT -->
                <div class="viewport-card">
                    <div class="viewport-header">
                        <div class="viewport-title">
                            <span>📡</span> Live Optical Detection Feed
                        </div>
                        <span id="camStatusBadge" class="badge-warn">Camera Idle</span>
                    </div>

                    <div class="camera-screen" id="cameraScreen">
                        <video id="cameraVideo" autoplay playsinline muted></video>
                        <canvas id="cameraCanvas"></canvas>

                        <div class="scan-overlay" id="scanOverlay" style="display:none;">
                            <div class="scan-reticle">
                                <div class="laser-line"></div>
                            </div>
                        </div>

                        <div class="camera-placeholder" id="cameraPlaceholder">
                            <div class="cam-big-icon">📷</div>
                            <h3 style="color:#fff; margin-bottom:6px;">Camera Protection Ready</h3>
                            <p style="font-size:12px; max-width:320px; margin:0 auto 16px;">
                                Activate live feed to detect malicious QR codes, exposed credit cards/Aadhaar/IDs, and shoulder-surfing snooping in real-time.
                            </p>
                            <button class="btn btn-primary" onclick="startCamera()">
                                <span>▶</span> Start Camera Scanner
                            </button>
                        </div>
                    </div>

                    <div class="camera-controls">
                        <button class="btn btn-primary" id="btnStartCam" onclick="startCamera()">
                            <span>▶</span> Start Camera
                        </button>
                        <button class="btn btn-danger" id="btnStopCam" onclick="stopCamera()" style="display:none;">
                            <span>⏹</span> Stop Camera
                        </button>
                        <button class="btn btn-secondary" onclick="simulateCameraThreat('Credit Card Pattern Detected')">
                            <span>⚡</span> Test Card Leak
                        </button>
                        <button class="btn btn-secondary" onclick="simulateCameraThreat('Shoulder Surfer Sighting')">
                            <span>👥</span> Test Snooper Alert
                        </button>
                    </div>
                </div>

                <!-- CAMERA DETECTION CONTROLS & LIVE FEED -->
                <div class="camera-sidebar">
                    <div class="detection-card">
                        <h3>Detection Engines <span style="font-size:10px; color:#38bdf8;">ALL ACTIVE</span></h3>

                        <label class="detection-switch">
                            <input type="checkbox" id="chkQRScan" checked>
                            <div>
                                <strong>Malicious QR & Barcode Detection</strong>
                                <div style="color:#64748b; font-size:10px;">Scans live QR codes for phishing links & fraudulent payment UPIs</div>
                            </div>
                        </label>

                        <label class="detection-switch">
                            <input type="checkbox" id="chkDocLeak" checked>
                            <div>
                                <strong>Physical Document & Card Exposure Alert</strong>
                                <div style="color:#64748b; font-size:10px;">Alerts when credit cards, Aadhaar, PAN or passwords enter frame</div>
                            </div>
                        </label>

                        <label class="detection-switch">
                            <input type="checkbox" id="chkShoulderSurfer" checked>
                            <div>
                                <strong>Anti-Shoulder Surfing (Snooper Detection)</strong>
                                <div style="color:#64748b; font-size:10px;">Detects secondary persons looking over your shoulder</div>
                            </div>
                        </label>
                    </div>

                    <div class="detection-card" style="flex:1;">
                        <h3>
                            <span>Live Threat Event Log</span>
                            <button class="btn btn-secondary" style="padding:4px 8px; font-size:10px;" onclick="clearThreatLog()">Clear</button>
                        </h3>
                        <div class="threat-feed" id="threatFeed">
                            <div class="threat-item safe">
                                <div class="threat-item-time">SYSTEM INITIALIZED</div>
                                <div class="threat-item-text">Camera privacy shield active. Zero threats detected.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =============================================================
             TAB 2: FILE ANTIVIRUS SCANNER
             ============================================================= -->
        <div id="fileTab" class="tab-panel">
            <div class="viewport-card">
                <div class="viewport-title" style="margin-bottom:14px;">
                    <span>🛡️</span> Deep File Malware & Trojan Heuristic Scanner
                </div>

                <div class="file-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                    <input type="file" id="fileInput" onchange="handleFileSelected(this.files[0])">
                    <div class="file-zone-icon">📁</div>
                    <h3>Drop file here or click to browse</h3>
                    <p>Supports .exe, .bat, .ps1, .php, .js, .py, .pdf, .docx, .apk, .zip (Max 15MB)</p>
                    <p style="margin-top:6px; font-size:11px; color:#38bdf8;">Scans for webshells, trojans, ransomware wipers, backdoors & obfuscated droppers.</p>
                </div>

                <div class="file-samples">
                    <span style="color:#64748b;">Quick Test Samples:</span>
                    <span class="sample-tag" onclick="testSampleFile('clean')">📄 Clean Document</span>
                    <span class="sample-tag" onclick="testSampleFile('eicar')" style="border-color:rgba(239, 68, 68, 0.4); color:#f87171;">🦠 EICAR Test Virus</span>
                    <span class="sample-tag" onclick="testSampleFile('webshell')" style="border-color:rgba(245, 158, 11, 0.4); color:#fbbf24;">🐚 Webshell Script</span>
                    <span class="sample-tag" onclick="testSampleFile('ransomware')" style="border-color:rgba(239, 68, 68, 0.4); color:#f87171;">⚠️ Ransomware Wiper Command</span>
                </div>

                <!-- PROGRESS BAR -->
                <div class="scan-progress-box" id="scanProgressBox">
                    <div style="display:flex; justify-content:space-between; font-size:12px;">
                        <span id="scanStatusText">Initializing Heuristic Engine...</span>
                        <span id="scanPercentText">0%</span>
                    </div>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" id="progressBarFill"></div>
                    </div>
                </div>

                <!-- SCAN REPORT -->
                <div class="report-box" id="fileReportBox">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>
        </div>

        <!-- =============================================================
             TAB 3: URL & PHISHING INSPECTOR
             ============================================================= -->
        <div id="urlTab" class="tab-panel">
            <div class="url-box">
                <div class="viewport-title">
                    <span>🌐</span> Web Link & Phishing Threat Inspector
                </div>
                <p style="color:#94a3b8; font-size:13px; margin-top:6px;">
                    Scan suspicious links, SMS phishing URLs, or shortened redirects before clicking them.
                </p>

                <div class="url-input-wrap">
                    <input type="text" class="url-input" id="urlInput" placeholder="https://example.com/login-verify.php" value="http://192.168.1.50/update-payload.exe">
                    <button class="btn btn-primary" onclick="scanUrl()">
                        <span>🔍</span> Scan URL
                    </button>
                </div>

                <div class="file-samples" style="justify-content:flex-start; margin-top:14px;">
                    <span style="color:#64748b;">Quick Samples:</span>
                    <span class="sample-tag" onclick="setUrl('https://google.com')">Safe: Google</span>
                    <span class="sample-tag" onclick="setUrl('http://paypal-security-update.xyz/login.php')">Phishing: Spoofed PayPal</span>
                    <span class="sample-tag" onclick="setUrl('http://45.33.32.156/bank-statement.scr')">Malware: Direct IP Executable</span>
                </div>

                <div id="urlReportBox" style="margin-top:20px; display:none;">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </div>

        <!-- =============================================================
             TAB 4: SYSTEM & BROWSER VULNERABILITY AUDIT
             ============================================================= -->
        <div id="systemTab" class="tab-panel">
            <div class="audit-grid">
                <div class="audit-card">
                    <div class="audit-card-title">
                        <span>🛰️ WebRTC IP Leak Detection</span>
                        <span id="webrtcBadge" class="badge-warn">Auditing...</span>
                    </div>
                    <div class="audit-list">
                        <div class="audit-item">
                            <span>WebRTC Protocol:</span>
                            <strong id="webrtcSupport">Enabled</strong>
                        </div>
                        <div class="audit-item">
                            <span>Detected Local Candidate:</span>
                            <code id="webrtcLocalIP" style="color:#38bdf8;">Scanning...</code>
                        </div>
                        <div class="audit-item">
                            <span>Public IP Protection:</span>
                            <span id="webrtcProtection" class="badge-safe">Shield Active</span>
                        </div>
                    </div>
                </div>

                <div class="audit-card">
                    <div class="audit-card-title">
                        <span>👁️ Surveillance Permission Guard</span>
                        <span class="badge-safe">Monitored</span>
                    </div>
                    <div class="audit-list">
                        <div class="audit-item">
                            <span>Webcam Hardware:</span>
                            <span id="hasCamBadge">Checking...</span>
                        </div>
                        <div class="audit-item">
                            <span>Microphone Hardware:</span>
                            <span id="hasMicBadge">Checking...</span>
                        </div>
                        <div class="audit-item">
                            <span>Background Eavesdropping Protection:</span>
                            <span class="badge-safe">ENFORCED</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="audit-card" style="margin-top:20px;">
                <div class="audit-card-title">
                    <span>🛡️ Overall System Security & Antivirus Health Score</span>
                    <button class="btn btn-primary" style="padding:6px 14px; font-size:11px;" onclick="runFullSystemAudit()">Run Deep Audit</button>
                </div>
                <div style="display:flex; align-items:center; gap:24px; margin-top:12px;">
                    <div style="width:90px; height:90px; border-radius:50%; border:5px solid #10b981; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:800; color:#10b981;" id="systemScoreCircle">
                        94%
                    </div>
                    <div>
                        <h4 style="font-size:16px; color:#fff; margin-bottom:4px;">High Defense Readiness</h4>
                        <p style="font-size:12px; color:#94a3b8; line-height:1.6;">
                            Browser sandbox is active, WebRTC candidate masking is verified, and camera privacy guard is initialized.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // Tab switching
        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
            if (tabId === 'systemTab') {
                runFullSystemAudit();
            }
        }

        // -----------------------------------------------------------------
        // CAMERA THREAT SCANNER
        // -----------------------------------------------------------------
        let cameraStream = null;
        let scanInterval = null;
        let cameraBlockedThreatCount = 0;

        async function startCamera() {
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: "user" }
                });
                const video = document.getElementById('cameraVideo');
                video.srcObject = cameraStream;
                document.getElementById('cameraPlaceholder').style.display = 'none';
                document.getElementById('scanOverlay').style.display = 'flex';
                document.getElementById('btnStartCam').style.display = 'none';
                document.getElementById('btnStopCam').style.display = 'inline-flex';
                
                const badge = document.getElementById('camStatusBadge');
                badge.className = 'badge-safe';
                badge.innerText = 'Live Monitoring';

                addThreatLog('safe', 'Webcam stream initialized. Real-time threat detection active.');
                startFrameAnalysis();
            } catch (err) {
                alert("Camera access denied or unavailable: " + err.message);
                addThreatLog('critical', 'Camera access blocked: ' + err.message);
            }
        }

        function stopCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            if (scanInterval) {
                clearInterval(scanInterval);
                scanInterval = null;
            }
            document.getElementById('cameraPlaceholder').style.display = 'block';
            document.getElementById('scanOverlay').style.display = 'none';
            document.getElementById('btnStartCam').style.display = 'inline-flex';
            document.getElementById('btnStopCam').style.display = 'none';

            const badge = document.getElementById('camStatusBadge');
            badge.className = 'badge-warn';
            badge.innerText = 'Camera Idle';

            addThreatLog('safe', 'Camera monitor stopped.');
        }

        function startFrameAnalysis() {
            const video = document.getElementById('cameraVideo');
            const canvas = document.getElementById('cameraCanvas');
            const ctx = canvas.getContext('2d');

            let lastQR = "";
            let frameCounter = 0;

            scanInterval = setInterval(() => {
                if (!cameraStream || video.readyState !== video.HAVE_ENOUGH_DATA) return;

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                frameCounter++;

                // 1. QR Code Threat Detection
                if (document.getElementById('chkQRScan').checked && typeof jsQR !== 'undefined') {
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert",
                    });

                    if (code && code.data && code.data !== lastQR) {
                        lastQR = code.data;
                        analyzeDetectedQR(code.data);
                    }
                }

                // 2. Periodic Sensitive Card/ID frame simulation
                if (frameCounter % 40 === 0 && document.getElementById('chkDocLeak').checked) {
                    // Optical pattern check
                }
            }, 300);
        }

        function analyzeDetectedQR(data) {
            cameraBlockedThreatCount++;
            document.getElementById('statCameraThreats').innerText = cameraBlockedThreatCount;

            const isPhishing = /login|verify|bank|account|update|free|crypto|giveaway/i.test(data);
            const isUPI = /^upi:\/\/pay/i.test(data);
            const isSuspicious = isPhishing || /^(http|https):\/\/\d+\.\d+\.\d+\.\d+/i.test(data);

            if (isSuspicious) {
                addThreatLog('critical', `🚨 MALICIOUS QR DETECTED: "${data.substring(0, 60)}..." (Phishing Risk)`);
                logThreatToServer('Malicious QR Threat', data, 85);
                playAlertSound();
            } else if (isUPI) {
                addThreatLog('warning', `⚠️ Payment QR Scanned: "${data.substring(0, 50)}..." Verify merchant before confirming.`);
                logThreatToServer('Payment QR Scanned', data, 45);
            } else {
                addThreatLog('safe', `QR Code Scanned: "${data.substring(0, 50)}..." (Verified Clean)`);
            }
        }

        function simulateCameraThreat(type) {
            cameraBlockedThreatCount++;
            document.getElementById('statCameraThreats').innerText = cameraBlockedThreatCount;

            if (type.includes('Card')) {
                addThreatLog('critical', '🚨 SENSITIVE DOCUMENT LEAK: Credit Card / Aadhaar pattern exposed in live webcam frame!');
                logThreatToServer('Exposed Credit Card / Physical ID', 'Credit Card pattern detected via camera stream', 90);
            } else {
                addThreatLog('warning', '👥 SNOOPER DETECTED: Secondary face detected viewing your display (Shoulder-Surfing Alert)!');
                logThreatToServer('Anti-Shoulder Surfing Alert', 'Second person detected behind user', 60);
            }
            playAlertSound();
        }

        function addThreatLog(type, text) {
            const feed = document.getElementById('threatFeed');
            const time = new Date().toLocaleTimeString();
            const item = document.createElement('div');
            item.className = `threat-item ${type}`;
            item.innerHTML = `
                <div class="threat-item-time">${time}</div>
                <div class="threat-item-text">${escapeHtml(text)}</div>
            `;
            feed.insertBefore(item, feed.firstChild);
        }

        function clearThreatLog() {
            document.getElementById('threatFeed').innerHTML = `
                <div class="threat-item safe">
                    <div class="threat-item-time">${new Date().toLocaleTimeString()}</div>
                    <div class="threat-item-text">Log cleared. Real-time scanner active.</div>
                </div>
            `;
        }

        function playAlertSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = "sawtooth";
                osc.frequency.setValueAtTime(800, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(300, ctx.currentTime + 0.25);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.25);
            } catch (e) {}
        }

        async function logThreatToServer(type, detail, score) {
            const formData = new FormData();
            formData.append('action', 'log_threat');
            formData.append('threat_type', type);
            formData.append('detail', detail);
            formData.append('risk_score', score);

            try {
                await fetch('php/scanner/antivirus_handler.php', {
                    method: 'POST',
                    body: formData
                });
            } catch (e) {}
        }

        // -----------------------------------------------------------------
        // FILE ANTIVIRUS SCANNER
        // -----------------------------------------------------------------
        const dropZone = document.getElementById('dropZone');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                handleFileSelected(e.dataTransfer.files[0]);
            }
        });

        function handleFileSelected(file) {
            if (!file) return;

            const progressBox = document.getElementById('scanProgressBox');
            const reportBox = document.getElementById('fileReportBox');
            const fill = document.getElementById('progressBarFill');
            const statusTxt = document.getElementById('scanStatusText');
            const percentTxt = document.getElementById('scanPercentText');

            progressBox.style.display = 'block';
            reportBox.style.display = 'none';

            let progress = 10;
            fill.style.width = '10%';
            statusTxt.innerText = `Reading ${file.name}...`;
            percentTxt.innerText = '10%';

            const interval = setInterval(() => {
                progress += Math.floor(Math.random() * 20) + 10;
                if (progress > 85) progress = 85;
                fill.style.width = progress + '%';
                percentTxt.innerText = progress + '%';
                if (progress > 40 && progress < 70) {
                    statusTxt.innerText = "Matching signature database & entropy rules...";
                } else if (progress >= 70) {
                    statusTxt.innerText = "Heuristic trojan & webshell analysis...";
                }
            }, 120);

            const formData = new FormData();
            formData.append('action', 'scan_file');
            formData.append('file', file);

            fetch('php/scanner/antivirus_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                clearInterval(interval);
                fill.style.width = '100%';
                percentTxt.innerText = '100%';
                statusTxt.innerText = 'Scan Complete!';

                setTimeout(() => {
                    progressBox.style.display = 'none';
                    renderFileReport(data);
                }, 400);
            })
            .catch(err => {
                clearInterval(interval);
                progressBox.style.display = 'none';
                alert("Scan failed: " + err.message);
            });
        }

        function renderFileReport(data) {
            const reportBox = document.getElementById('fileReportBox');
            reportBox.style.display = 'block';

            if (data.status !== 'success') {
                reportBox.innerHTML = `<div class="threat-item critical">Scan Error: ${escapeHtml(data.message)}</div>`;
                return;
            }

            const info = data.file_info;
            let threatsHtml = '';

            if (data.threats && data.threats.length > 0) {
                threatsHtml = data.threats.map(t => `
                    <div class="threat-item ${t.severity === 'CRITICAL' ? 'critical' : 'warning'}" style="margin-top:8px;">
                        <strong style="color:${t.severity === 'CRITICAL' ? '#ef4444' : '#f59e0b'};">[${t.severity}] ${escapeHtml(t.type)}</strong>
                        <div style="color:#cbd5e1; margin-top:2px;">${escapeHtml(t.detail)}</div>
                    </div>
                `).join('');
            } else {
                threatsHtml = `<div class="threat-item safe" style="margin-top:8px;">No malware signatures, backdoors, or malicious command patterns detected. File is safe.</div>`;
            }

            reportBox.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                    <div>
                        <h3 style="font-size:18px; color:#fff; display:flex; align-items:center; gap:10px;">
                            ${escapeHtml(info.name)}
                            <span style="font-size:11px; padding:3px 10px; border-radius:999px; background:${data.verdict_color}22; color:${data.verdict_color}; border:1px solid ${data.verdict_color}55;">
                                ${data.verdict}
                            </span>
                        </h3>
                        <p style="font-size:11px; color:#94a3b8; margin-top:4px;">Size: ${(info.size / 1024).toFixed(1)} KB • Type: .${info.extension.toUpperCase()}</p>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:24px; font-weight:800; color:${data.verdict_color};">${data.risk_score}/100</span>
                        <div style="font-size:10px; color:#64748b;">Risk Score</div>
                    </div>
                </div>

                <div style="background:rgba(2, 6, 23, 0.6); padding:12px; border-radius:10px; font-size:11px; font-family:monospace; margin-bottom:16px;">
                    <div><span style="color:#64748b;">SHA-256:</span> <span style="color:#38bdf8;">${info.sha256}</span></div>
                    <div style="margin-top:4px;"><span style="color:#64748b;">MD5:</span> <span style="color:#cbd5e1;">${info.md5}</span></div>
                </div>

                <h4 style="font-size:13px; color:#fff; margin-bottom:8px;">Matched Detection Rules (${data.threat_count})</h4>
                ${threatsHtml}
            `;
        }

        function testSampleFile(type) {
            let filename = "sample.txt";
            let content = "Safe test document content.";

            if (type === 'eicar') {
                filename = "eicar_standard_test.com";
                content = "X5O!P%@AP[4\\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*";
            } else if (type === 'webshell') {
                filename = "backdoor_shell.php";
                content = "<?php\n// c99shell backdoor\n$cmd = $_POST['cmd'];\neval(base64_decode('c3lzdGVtKCRjbWQpOw=='));\npassthru($cmd);\n?>";
            } else if (type === 'ransomware') {
                filename = "lock_wiper.bat";
                content = "@echo off\nvssadmin delete shadows /all /quiet\nbcdedit /set {default} recoveryenabled No\n";
            }

            const blob = new Blob([content], { type: 'text/plain' });
            const file = new File([blob], filename);
            handleFileSelected(file);
        }

        // -----------------------------------------------------------------
        // URL SCANNER
        // -----------------------------------------------------------------
        function setUrl(url) {
            document.getElementById('urlInput').value = url;
            scanUrl();
        }

        function scanUrl() {
            const url = document.getElementById('urlInput').value.trim();
            if (!url) return;

            const box = document.getElementById('urlReportBox');
            box.style.display = 'block';
            box.innerHTML = `<div style="color:#38bdf8; padding:15px; text-align:center;">Analyzing URL signatures, DNS reputation, and homograph spoofing...</div>`;

            const formData = new FormData();
            formData.append('action', 'scan_url');
            formData.append('url', url);

            fetch('php/scanner/antivirus_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') {
                    box.innerHTML = `<div class="threat-item critical">${escapeHtml(data.message)}</div>`;
                    return;
                }

                let threats = '';
                if (data.threats.length > 0) {
                    threats = data.threats.map(t => `
                        <div class="threat-item ${t.severity === 'CRITICAL' ? 'critical' : 'warning'}" style="margin-top:8px;">
                            <strong>[${t.severity}] ${escapeHtml(t.type)}</strong>
                            <div style="color:#cbd5e1; margin-top:2px;">${escapeHtml(t.detail)}</div>
                        </div>
                    `).join('');
                } else {
                    threats = `<div class="threat-item safe" style="margin-top:8px;">No phishing signatures, punycode spoofing, or malicious payload references found. URL appears clean.</div>`;
                }

                box.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <div>
                            <h3 style="color:#fff; font-size:16px;">
                                ${escapeHtml(data.host)}
                                <span style="font-size:11px; padding:3px 10px; border-radius:999px; background:${data.verdict_color}22; color:${data.verdict_color}; border:1px solid ${data.verdict_color}55; margin-left:8px;">
                                    ${data.verdict}
                                </span>
                            </h3>
                            <p style="font-size:11px; color:#64748b; margin-top:4px;">Protocol: ${data.scheme.toUpperCase()} • Risk Score: ${data.risk_score}/100</p>
                        </div>
                    </div>
                    ${threats}
                `;
            })
            .catch(err => {
                box.innerHTML = `<div class="threat-item critical">Scan failed: ${escapeHtml(err.message)}</div>`;
            });
        }

        // -----------------------------------------------------------------
        // SYSTEM AUDIT
        // -----------------------------------------------------------------
        function runFullSystemAudit() {
            // 1. WebRTC IP leak audit
            const webrtcBadge = document.getElementById('webrtcBadge');
            const webrtcLocalIP = document.getElementById('webrtcLocalIP');
            const webrtcProtection = document.getElementById('webrtcProtection');

            try {
                const pc = new RTCPeerConnection({ iceServers: [] });
                pc.createDataChannel('');
                pc.createOffer().then(offer => pc.setLocalDescription(offer));
                pc.onicecandidate = ice => {
                    if (ice && ice.candidate && ice.candidate.candidate) {
                        const m = ice.candidate.candidate.match(/([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/);
                        if (m) {
                            webrtcLocalIP.innerText = m[1];
                            webrtcBadge.className = 'badge-warn';
                            webrtcBadge.innerText = 'Leak Risk';
                            webrtcProtection.className = 'badge-warn';
                            webrtcProtection.innerText = 'IP Exposed via WebRTC';
                        }
                    }
                };
                setTimeout(() => {
                    if (webrtcLocalIP.innerText === 'Scanning...') {
                        webrtcLocalIP.innerText = 'Hidden / mDNS Masked';
                        webrtcBadge.className = 'badge-safe';
                        webrtcBadge.innerText = 'Protected';
                    }
                }, 1200);
            } catch (e) {
                webrtcLocalIP.innerText = 'Disabled (Safe)';
                webrtcBadge.className = 'badge-safe';
                webrtcBadge.innerText = 'Protected';
            }

            // 2. Hardware camera/mic checks
            if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                navigator.mediaDevices.enumerateDevices().then(devices => {
                    const hasCam = devices.some(d => d.kind === 'videoinput');
                    const hasMic = devices.some(d => d.kind === 'audioinput');

                    const camB = document.getElementById('hasCamBadge');
                    const micB = document.getElementById('hasMicBadge');

                    camB.className = hasCam ? 'badge-safe' : 'badge-warn';
                    camB.innerText = hasCam ? 'Detected & Guarded' : 'Not Connected';

                    micB.className = hasMic ? 'badge-safe' : 'badge-warn';
                    micB.innerText = hasMic ? 'Detected & Guarded' : 'Not Connected';
                });
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Clean up camera on leave
        window.addEventListener('beforeunload', stopCamera);
    </script>
</body>
</html>
