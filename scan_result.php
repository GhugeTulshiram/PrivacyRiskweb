<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

require_once "php/scanner/privacy_scanner.php";
require_once "php/scanner/risk_analyzer.php";

if (!isset($_SESSION["scan_result"])) {
    header("Location: scan.php");
    exit;
}

$result = $_SESSION["scan_result"];

$original_text = $result["original_text"] ?? "";
$masked_text = $result["masked_text"] ?? "";
$detected = $result["detected"] ?? [];

$risk_score = $result["risk_score"] ?? 0;
$risk_level = $result["risk_level"] ?? "SAFE";
$risk_message = $result["risk_message"] ?? "";
$solutions = $result["solutions"] ?? [];


/* --------------------------------
   CALCULATE PROTECTED RISK
-------------------------------- */

$protected_detected = detectSensitiveData($masked_text);

$protected_risk = calculateRisk($protected_detected);

$protected_score = $protected_risk["score"];
$protected_level = $protected_risk["level"];


/* Risk badge class */

function riskClass($level)
{
    switch ($level) {

        case "CRITICAL":
            return "critical";

        case "HIGH":
            return "high";

        case "MEDIUM":
            return "medium";

        case "LOW":
            return "low";

        default:
            return "safe";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Scan Result | PrivacyGuard</title>

<link rel="stylesheet"
      href="css/style.css">

<link rel="stylesheet"
      href="css/dashboard.css">

<style>

.result-page {
    padding: 35px 45px;
}

.result-header {
    margin-bottom: 28px;
}

.result-header h1 {
    font-size: 28px;
    margin-bottom: 8px;
}

.result-header p {
    color: #71869f;
    font-size: 13px;
}


/* BEFORE / AFTER */

.risk-comparison {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

.risk-card {
    background: rgba(7, 20, 38, 0.85);
    border: 1px solid rgba(96, 165, 250, 0.12);
    border-radius: 16px;
    padding: 25px;
}

.risk-card-title {
    color: #71869f;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 18px;
}

.risk-score {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 8px;
}

.risk-score span {
    font-size: 15px;
    color: #71869f;
}

.risk-message {
    color: #9aacbf;
    font-size: 13px;
    line-height: 1.6;
}


/* BADGES */

.risk-badge {
    display: inline-block;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.8px;
    margin-top: 12px;
}

.risk-badge.safe {
    color: #34d399;
    background: rgba(52, 211, 153, 0.10);
}

.risk-badge.low {
    color: #60a5fa;
    background: rgba(96, 165, 250, 0.10);
}

.risk-badge.medium {
    color: #fbbf24;
    background: rgba(251, 191, 36, 0.10);
}

.risk-badge.high {
    color: #fb923c;
    background: rgba(251, 146, 60, 0.10);
}

.risk-badge.critical {
    color: #f87171;
    background: rgba(248, 113, 113, 0.10);
}


/* CRITICAL ALERT */

.critical-alert {
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.25);
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 25px;
}

.critical-alert h3 {
    color: #f87171;
    font-size: 14px;
    margin-bottom: 6px;
}

.critical-alert p {
    color: #aebfd1;
    font-size: 12px;
    line-height: 1.6;
}


/* SOLUTIONS */

.solution-box {
    background: rgba(7, 20, 38, 0.85);
    border: 1px solid rgba(96, 165, 250, 0.12);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
}

.solution-box h2 {
    font-size: 18px;
    margin-bottom: 7px;
}

.solution-box > p {
    color: #71869f;
    font-size: 12px;
    margin-bottom: 20px;
}

.solution-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.solution-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    border-radius: 10px;
    background: rgba(37, 99, 235, 0.07);
    border: 1px solid rgba(96, 165, 250, 0.08);
}

.solution-icon {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: rgba(96, 165, 250, 0.12);
    color: #60a5fa;
    flex-shrink: 0;
}

.solution-item p {
    color: #b9c7d6;
    font-size: 12px;
    line-height: 1.6;
    margin: 0;
}


/* TEXT BOXES */

.text-section {
    background: rgba(7, 20, 38, 0.85);
    border: 1px solid rgba(96, 165, 250, 0.12);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 20px;
}

.text-section h2 {
    font-size: 17px;
    margin-bottom: 15px;
}

.text-box {
    background: #050d18;
    border: 1px solid rgba(96, 165, 250, 0.08);
    border-radius: 10px;
    padding: 18px;
    color: #aebfd1;
    font-size: 13px;
    line-height: 1.7;
    white-space: pre-wrap;
    word-break: break-word;
}

.protected-box {
    border-color: rgba(52, 211, 153, 0.15);
}


/* DETECTED DATA */

.detected-section {
    background: rgba(7, 20, 38, 0.85);
    border: 1px solid rgba(96, 165, 250, 0.12);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
}

.detected-section h2 {
    font-size: 18px;
    margin-bottom: 18px;
}

.detected-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.detected-card {
    background: #050d18;
    border-radius: 10px;
    padding: 15px;
    border: 1px solid rgba(96, 165, 250, 0.08);
}

.detected-type {
    color: #60a5fa;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 7px;
}

.detected-value {
    color: #e5edf5;
    font-size: 13px;
    word-break: break-all;
    margin-bottom: 8px;
}

.detected-reason {
    color: #71869f;
    font-size: 11px;
}


/* BUTTONS */

.result-actions {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.result-btn {
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 600;
}

.primary-btn {
    color: white;
    background: #2563eb;
}

.primary-btn:hover {
    background: #1d4ed8;
}

.secondary-btn {
    color: #9fb3c8;
    background: rgba(96, 165, 250, 0.08);
    border: 1px solid rgba(96, 165, 250, 0.12);
}


/* RESPONSIVE */

@media (max-width: 900px) {

    .risk-comparison {
        grid-template-columns: 1fr;
    }

    .detected-grid {
        grid-template-columns: 1fr;
    }

    .result-page {
        padding: 25px;
    }
}

</style>

</head>


<body>


<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="sidebar-brand">

        <div class="sidebar-logo">
            🛡
        </div>

        <div>
            <h2>PrivacyGuard</h2>
            <p>Privacy-Risk Assistant</p>
        </div>

    </div>


    <nav class="sidebar-nav">

        <div class="nav-title">
            MAIN
        </div>

        <a href="dashboard.php"
           class="nav-item">

            <span>⌂</span>
            Dashboard

        </a>


        <a href="scan.php"
           class="nav-item active">

            <span>⌕</span>
            Privacy Scanner

        </a>


        <a href="history.php"
           class="nav-item">

            <span>◷</span>
            Scan History

        </a>


        <div class="nav-title second-title">
            ANALYTICS
        </div>


        <a href="reports.php"
           class="nav-item">

            <span>▥</span>
            Reports

        </a>


        <a href="settings.php"
           class="nav-item">

            <span>⚙</span>
            Settings

        </a>

    </nav>


    <div class="sidebar-bottom">

        <a href="logout.php"
           class="logout-btn">

            <span>↪</span>
            Logout

        </a>

    </div>

</aside>



<!-- MAIN -->

<main class="dashboard-main">

<div class="result-page">


    <div class="result-header">

        <h1>Privacy Scan Result</h1>

        <p>
            Your submitted content has been analyzed for sensitive information.
        </p>

    </div>



    <?php if ($risk_level === "CRITICAL") : ?>

        <div class="critical-alert">

            <h3>⚠ Immediate Privacy Action Recommended</h3>

            <p>
                Critical-risk information was detected.
                Follow the protection recommendations below and use the
                protected version instead of sharing the original sensitive data.
            </p>

        </div>

    <?php endif; ?>



    <!-- BEFORE / AFTER -->

    <div class="risk-comparison">


        <!-- BEFORE -->

        <div class="risk-card">

            <div class="risk-card-title">
                Before Protection
            </div>

            <div class="risk-score">

                <?php echo $risk_score; ?>

                <span>/ 100</span>

            </div>

            <div class="risk-message">

                <?php echo htmlspecialchars($risk_message); ?>

            </div>

            <span class="risk-badge <?php echo riskClass($risk_level); ?>">

                <?php echo htmlspecialchars($risk_level); ?>

            </span>

        </div>



        <!-- AFTER -->

        <div class="risk-card">

            <div class="risk-card-title">
                After Protection
            </div>

            <div class="risk-score">

                <?php echo $protected_score; ?>

                <span>/ 100</span>

            </div>

            <div class="risk-message">

                <?php

                if ($protected_score == 0) {

                    echo "Sensitive information has been replaced with safe placeholders.";

                } elseif ($protected_score <= 20) {

                    echo "The protected version contains only limited low-risk information.";

                } else {

                    echo "Some information still requires review.";

                }

                ?>

            </div>

            <span class="risk-badge <?php echo riskClass($protected_level); ?>">

                <?php echo htmlspecialchars($protected_level); ?>

            </span>

        </div>

    </div>



    <!-- SOLUTIONS -->

    <div class="solution-box">

        <h2>🛡 How to Reduce This Risk</h2>

        <p>
            Follow these recommendations before sharing your information.
        </p>


        <div class="solution-list">

            <?php if (!empty($solutions)) : ?>

                <?php foreach ($solutions as $solution) : ?>

                    <div class="solution-item">

                        <div class="solution-icon">
                            ✓
                        </div>

                        <p>
                            <?php echo htmlspecialchars($solution); ?>
                        </p>

                    </div>

                <?php endforeach; ?>

            <?php else : ?>

                <div class="solution-item">

                    <div class="solution-icon">
                        ✓
                    </div>

                    <p>
                        Review your text before sharing and remove unnecessary
                        personal or confidential information.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>



    <!-- DETECTED INFORMATION -->

    <div class="detected-section">

        <h2>Detected Sensitive Information</h2>


        <?php if (!empty($detected)) : ?>

            <div class="detected-grid">

                <?php foreach ($detected as $item) : ?>

                    <div class="detected-card">

                        <div class="detected-type">

                            <?php echo htmlspecialchars($item["type"]); ?>

                        </div>


                        <div class="detected-value">

                            <?php echo htmlspecialchars($item["value"]); ?>

                        </div>


                        <span class="risk-badge <?php echo riskClass($item["level"]); ?>">

                            <?php echo htmlspecialchars($item["level"]); ?>

                        </span>


                        <div class="detected-reason">

                            <?php echo htmlspecialchars($item["reason"]); ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else : ?>

            <p style="color:#71869f;font-size:13px;">
                No sensitive information detected.
            </p>

        <?php endif; ?>

    </div>



    <!-- ORIGINAL TEXT -->

    <div class="text-section">

        <h2>Original Submitted Text</h2>

        <div class="text-box">

            <?php echo htmlspecialchars($original_text); ?>

        </div>

    </div>



    <!-- PROTECTED TEXT -->

    <div class="text-section">

        <h2>Protected / Masked Text</h2>

        <div class="text-box protected-box">

            <?php echo htmlspecialchars($masked_text); ?>

        </div>

    </div>



    <!-- ACTIONS -->

    <div class="result-actions">

        <a href="scan.php"
           class="result-btn primary-btn">

            + Scan Another Text

        </a>


        <a href="history.php"
           class="result-btn secondary-btn">

            View Scan History

        </a>

    </div>


</div>

</main>


</body>

</html>