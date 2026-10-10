<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

require_once "php/config/db.php";

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"] ?? "User";
$user_email = $_SESSION["user_email"] ?? "";

$email_alerts = 1;
$auto_masking = 1;

$stmt = $conn->prepare(
    "SELECT email_alerts, auto_masking
     FROM settings
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $settings = $result->fetch_assoc();

    $email_alerts = (int)$settings["email_alerts"];
    $auto_masking = (int)$settings["auto_masking"];
}

$stmt->close();

$success = $_GET["success"] ?? "";
$error = $_GET["error"] ?? "";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Settings | PrivacyGuard</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">

    <style>

        .settings-page {
            max-width: 1050px;
        }

        .settings-header {
            margin-bottom: 28px;
        }

        .settings-header h1 {
            margin-bottom: 8px;
        }

        .settings-header p {
            color: #71869f;
            font-size: 13px;
        }

        .settings-alert {
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .success-alert {
            background: rgba(34, 197, 94, 0.10);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #86efac;
        }

        .error-alert {
            background: rgba(239, 68, 68, 0.10);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
        }

        .settings-card {
            background: rgba(7, 20, 38, 0.88);
            border: 1px solid rgba(96, 165, 250, 0.12);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.18);
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }

        .card-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(37, 99, 235, 0.12);
            font-size: 18px;
        }

        .card-title h2 {
            font-size: 16px;
            color: #ffffff;
            margin: 0;
        }

        .card-title p {
            margin: 4px 0 0;
            color: #71869f;
            font-size: 11px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .profile-item {
            background: rgba(15, 32, 54, 0.55);
            border: 1px solid rgba(96, 165, 250, 0.08);
            padding: 16px;
            border-radius: 10px;
        }

        .profile-label {
            display: block;
            color: #71869f;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin-bottom: 7px;
        }

        .profile-value {
            color: #e5edf7;
            font-size: 13px;
            font-weight: 600;
        }

        .setting-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 18px 0;
            border-bottom: 1px solid rgba(96, 165, 250, 0.08);
        }

        .setting-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .setting-info h3 {
            color: #e5edf7;
            font-size: 13px;
            margin: 0 0 5px;
        }

        .setting-info p {
            color: #71869f;
            font-size: 11px;
            margin: 0;
            line-height: 1.5;
        }

        .switch {
            position: relative;
            width: 48px;
            height: 26px;
            flex-shrink: 0;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #26384e;
            border-radius: 30px;
            transition: 0.25s;
        }

        .slider:before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            left: 3px;
            top: 3px;
            background: #ffffff;
            border-radius: 50%;
            transition: 0.25s;
        }

        .switch input:checked + .slider {
            background: #2563eb;
        }

        .switch input:checked + .slider:before {
            transform: translateX(22px);
        }

        .save-area {
            display: flex;
            justify-content: flex-end;
            margin-top: 22px;
        }

        .save-btn {
            border: none;
            border-radius: 9px;
            padding: 11px 22px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #ffffff;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.20);
            transition: 0.2s;
        }

        .save-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.30);
        }

        .security-box {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 17px;
            background: rgba(34, 197, 94, 0.06);
            border: 1px solid rgba(34, 197, 94, 0.12);
            border-radius: 11px;
        }

        .security-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(34, 197, 94, 0.10);
            color: #4ade80;
            font-size: 18px;
        }

        .security-box strong {
            display: block;
            color: #86efac;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .security-box span {
            color: #71869f;
            font-size: 10px;
        }

        @media (max-width: 700px) {

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .setting-row {
                align-items: flex-start;
            }

            .settings-card {
                padding: 18px;
            }

        }

    </style>

</head>

<body>

<div class="dashboard-layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="sidebar-brand">

            <div class="sidebar-logo">
                🛡️
            </div>

            <div>
                <h2>PrivacyGuard</h2>
                <p>Privacy Risk Assistant</p>
            </div>

        </div>

        <nav class="sidebar-nav">

            <div class="nav-title">
                MAIN MENU
            </div>

            <a href="dashboard.php" class="nav-item">
                <span>📊</span>
                Dashboard
            </a>

            <a href="scan.php" class="nav-item">
                <span>🔎</span>
                Privacy Scanner
            </a>

            <a href="history.php" class="nav-item">
                <span>🕘</span>
                Scan History
            </a>

            <div class="nav-title second-title">
                ANALYTICS
            </div>

            <a href="reports.php" class="nav-item">
                <span>📈</span>
                Reports
            </a>

            <a href="settings.php" class="nav-item active">
                <span>⚙️</span>
                Settings
            </a>

        </nav>

        <div class="sidebar-bottom">

            <div class="privacy-status">

                <div class="status-icon">
                    ✓
                </div>

                <div>
                    <strong>Privacy Protected</strong>
                    <span>Your session is secure</span>
                </div>

            </div>

            <a href="logout.php" class="logout-btn">
                <span>🚪</span>
                Logout
            </a>

        </div>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="dashboard-main">

        <div class="settings-page">

            <div class="settings-header">

                <h1>Settings</h1>

                <p>
                    Manage your PrivacyGuard account and privacy preferences.
                </p>

            </div>


            <?php if (!empty($success)): ?>

                <div class="settings-alert success-alert">
                    ✓ <?php echo htmlspecialchars($success); ?>
                </div>

            <?php endif; ?>


            <?php if (!empty($error)): ?>

                <div class="settings-alert error-alert">
                    ⚠ <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <!-- ACCOUNT INFORMATION -->

            <div class="settings-card">

                <div class="card-title">

                    <div class="card-icon">
                        👤
                    </div>

                    <div>
                        <h2>Account Information</h2>
                        <p>Your registered PrivacyGuard account details</p>
                    </div>

                </div>


                <div class="profile-grid">

                    <div class="profile-item">

                        <span class="profile-label">
                            Full Name
                        </span>

                        <div class="profile-value">
                            <?php echo htmlspecialchars($user_name); ?>
                        </div>

                    </div>


                    <div class="profile-item">

                        <span class="profile-label">
                            Email Address
                        </span>

                        <div class="profile-value">
                            <?php echo htmlspecialchars($user_email); ?>
                        </div>

                    </div>

                </div>

            </div>


            <!-- PRIVACY SETTINGS -->

            <form action="php/settings/settings_handler.php" method="POST">

                <div class="settings-card">

                    <div class="card-title">

                        <div class="card-icon">
                            🔐
                        </div>

                        <div>
                            <h2>Privacy Preferences</h2>
                            <p>Control how PrivacyGuard handles your scans</p>
                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-info">

                            <h3>Email Alerts</h3>

                            <p>
                                Receive notifications about important privacy
                                and security events.
                            </p>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="email_alerts"
                                <?php echo $email_alerts ? "checked" : ""; ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="setting-row">

                        <div class="setting-info">

                            <h3>Automatic Masking</h3>

                            <p>
                                Automatically replace detected sensitive
                                information with safe placeholders.
                            </p>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="auto_masking"
                                <?php echo $auto_masking ? "checked" : ""; ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="save-area">

                        <button type="submit" class="save-btn">
                            Save Preferences
                        </button>

                    </div>

                </div>

            </form>


            <!-- SECURITY STATUS -->

            <div class="settings-card">

                <div class="card-title">

                    <div class="card-icon">
                        🛡️
                    </div>

                    <div>
                        <h2>Security Status</h2>
                        <p>Your current PrivacyGuard security status</p>
                    </div>

                </div>


                <div class="security-box">

                    <div class="security-icon">
                        ✓
                    </div>

                    <div>

                        <strong>
                            Account Secure
                        </strong>

                        <span>
                            Your password is securely stored using password hashing.
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>

</body>

</html>