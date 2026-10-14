<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . "/php/config/db.php";

$user_id = $_SESSION["user_id"];

$user_name = $_SESSION["user_name"] ?? "User";
$user_email = $_SESSION["user_email"] ?? "";

$user_initial = strtoupper(
    substr(trim($user_name), 0, 1)
);


/* =========================================================
   TOTAL SCANS
   ========================================================= */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM scans
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_scans = (int)($row["total"] ?? 0);

$stmt->close();


/* =========================================================
   SAFE SCANS
   ========================================================= */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM scans
     WHERE user_id = ?
     AND risk_level = 'SAFE'"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$safe_scans = (int)($row["total"] ?? 0);

$stmt->close();


/* =========================================================
   HIGH RISK
   ========================================================= */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM scans
     WHERE user_id = ?
     AND risk_level = 'HIGH'"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$high_risk = (int)($row["total"] ?? 0);

$stmt->close();


/* =========================================================
   MEDIUM RISK
   ========================================================= */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM scans
     WHERE user_id = ?
     AND risk_level = 'MEDIUM'"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$medium_risk = (int)($row["total"] ?? 0);

$stmt->close();


/* =========================================================
   LOW RISK
   ========================================================= */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM scans
     WHERE user_id = ?
     AND risk_level = 'LOW'"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$low_risk = (int)($row["total"] ?? 0);

$stmt->close();


/* =========================================================
   PRIVACY SCORE
   ========================================================= */

if ($total_scans > 0) {

    $stmt = $conn->prepare(
        "SELECT AVG(risk_score) AS avg_risk
         FROM scans
         WHERE user_id = ?"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $avg_risk = (float)($row["avg_risk"] ?? 0);

    $privacy_score = round(100 - $avg_risk);

    if ($privacy_score < 0) {
        $privacy_score = 0;
    }

    if ($privacy_score > 100) {
        $privacy_score = 100;
    }

    $stmt->close();

} else {

    $privacy_score = 100;
}


/* =========================================================
   PRIVACY STATUS
   ========================================================= */

if ($privacy_score >= 80) {

    $privacy_status = "Good";
    $privacy_message = "Your data is well protected";

} elseif ($privacy_score >= 60) {

    $privacy_status = "Moderate";
    $privacy_message = "Some privacy risks need attention";

} else {

    $privacy_status = "Attention";
    $privacy_message = "Your privacy needs immediate attention";
}


/* =========================================================
   RECENT ACTIVITY
   ========================================================= */

$stmt = $conn->prepare(
    "SELECT
        id,
        original_text,
        risk_score,
        risk_level,
        created_at
     FROM scans
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 5"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$recent_scans = $stmt->get_result();


/* =========================================================
   RISK TOTAL
   ========================================================= */

$risk_total =
    $high_risk +
    $medium_risk +
    $low_risk;


/* =========================================================
   SAFE PERCENTAGE
   ========================================================= */

if ($total_scans > 0) {

    $safe_percentage =
        round(
            ($safe_scans / $total_scans) * 100
        );

} else {

    $safe_percentage = 0;
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        PrivacyGuard | Dashboard
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <style>


        /* =====================================================
           GLOBAL
           ===================================================== */

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            min-height: 100vh;

            background:
                radial-gradient(
                    circle at 70% 10%,
                    rgba(0, 110, 255, 0.10),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 30% 90%,
                    rgba(0, 180, 255, 0.06),
                    transparent 25%
                ),
                #020817;

            color: #ffffff;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        /* =====================================================
           SIDEBAR
           ===================================================== */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;
            bottom: 0;

            width: 260px;

            padding: 25px 18px;

            background:
                linear-gradient(
                    180deg,
                    #06152b,
                    #031022
                );

            border-right:
                1px solid
                rgba(59, 130, 246, 0.25);

            z-index: 1000;

            display: flex;

            flex-direction: column;

        }


        .brand {

            display: flex;

            align-items: center;

            gap: 12px;

            padding:
                0 8px
                25px;

            border-bottom:
                1px solid
                rgba(96, 165, 250, 0.10);

            margin-bottom: 25px;

        }


        .brand-logo {

            width: 44px;
            height: 44px;

            border-radius: 13px;

            display: flex;

            align-items: center;
            justify-content: center;

            background:
                linear-gradient(
                    135deg,
                    #0ea5e9,
                    #2563eb
                );

            font-size: 23px;

            box-shadow:
                0 0 25px
                rgba(14, 165, 233, 0.30);

        }


        .brand-name {

            margin: 0;

            font-size: 20px;

            font-weight: 700;

        }


        .brand-name span {
            color: #22d3ee;
        }


        .brand-subtitle {

            margin: 3px 0 0;

            color: #7890aa;

            font-size: 9px;

        }


        /* =====================================================
           NAVIGATION
           ===================================================== */

        .nav-title {

            padding:
                0 12px;

            margin-bottom: 10px;

            color: #4e6b88;

            font-size: 9px;

            font-weight: bold;

            letter-spacing: 1.5px;

        }


        .nav-item {

            width: 100%;

            height: 48px;

            display: flex;

            align-items: center;

            gap: 14px;

            padding:
                0 14px;

            margin-bottom: 6px;

            color: #9bb0c7;

            text-decoration: none;

            border-radius: 12px;

            font-size: 13px;

            transition: 0.2s;

        }


        .nav-icon {

            width: 25px;

            text-align: center;

            font-size: 18px;

        }


        .nav-item:hover {

            color: #ffffff;

            background:
                rgba(37, 99, 235, 0.10);

        }


        .nav-item.active {

            color: #ffffff;

            background:
                linear-gradient(
                    90deg,
                    rgba(37, 99, 235, 0.80),
                    rgba(37, 99, 235, 0.25)
                );

            border:
                1px solid
                rgba(96, 165, 250, 0.20);

            box-shadow:
                0 8px 20px
                rgba(37, 99, 235, 0.15);

        }


        /* =====================================================
           SIDEBAR BOTTOM
           ===================================================== */

        .sidebar-bottom {

            margin-top: auto;

        }


        .privacy-status {

            padding: 15px;

            display: flex;

            align-items: center;

            gap: 11px;

            border-radius: 13px;

            background:
                rgba(16, 185, 129, 0.08);

            border:
                1px solid
                rgba(16, 185, 129, 0.12);

            margin-bottom: 12px;

        }


        .status-circle {

            width: 35px;
            height: 35px;

            flex-shrink: 0;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                rgba(16, 185, 129, 0.15);

            color: #34d399;

        }


        .privacy-status strong {

            display: block;

            font-size: 11px;

        }


        .privacy-status span {

            display: block;

            margin-top: 4px;

            color: #7189a1;

            font-size: 8px;

        }


        .logout {

            display: flex;

            align-items: center;

            gap: 12px;

            height: 43px;

            padding: 0 14px;

            color: #fb7185;

            text-decoration: none;

            border-radius: 10px;

            font-size: 12px;

        }


        .logout:hover {

            background:
                rgba(239, 68, 68, 0.08);

        }


        /* =====================================================
           MAIN
           ===================================================== */

        .main {

            margin-left: 260px;

            min-height: 100vh;

            padding:
                28px 38px 40px;

        }


        /* =====================================================
           TOP BAR
           ===================================================== */

        .topbar {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 28px;

        }


        .welcome-label {

            color: #60a5fa;

            font-size: 10px;

            letter-spacing: 1.5px;

            font-weight: bold;

        }


        .page-title {

            margin:
                6px 0;

            font-size: 29px;

        }


        .page-description {

            margin: 0;

            color: #7189a1;

            font-size: 11px;

        }


        .profile-area {

            display: flex;

            align-items: center;

            gap: 15px;

        }


        .notification {

            width: 40px;
            height: 40px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 11px;

            background: #071a30;

            border:
                1px solid
                rgba(96, 165, 250, 0.12);

            font-size: 18px;

        }


        .profile {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .avatar {

            width: 40px;
            height: 40px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #0ea5e9,
                    #2563eb
                );

            font-weight: bold;

        }


        .profile strong {

            display: block;

            font-size: 11px;

        }


        .profile span {

            display: block;

            margin-top: 3px;

            color: #627b95;

            font-size: 8px;

        }


        /* =====================================================
           HERO
           ===================================================== */

        .hero {

            position: relative;

            min-height: 245px;

            overflow: hidden;

            padding:
                35px;

            margin-bottom: 20px;

            border-radius: 20px;

            border:
                1px solid
                rgba(37, 150, 255, 0.22);

            background:
                linear-gradient(
                    90deg,
                    rgba(2, 12, 30, 0.96),
                    rgba(2, 20, 43, 0.75),
                    rgba(2, 8, 24, 0.40)
                ),
                url("images/privacy-bg.jpg");

            background-size: cover;

            background-position: center;

            box-shadow:
                0 20px 50px
                rgba(0, 0, 0, 0.25);

        }


        .hero-content {

            position: relative;

            z-index: 2;

            max-width: 580px;

        }


        .hero-small {

            color: #60a5fa;

            font-size: 11px;

            letter-spacing: 1.5px;

            font-weight: bold;

        }


        .hero h2 {

            margin:
                10px 0;

            font-size: 31px;

        }


        .hero h2 span {

            color: #22d3ee;

        }


        .hero p {

            max-width: 520px;

            margin: 0;

            color: #a4b7ca;

            font-size: 12px;

            line-height: 1.7;

        }


        .hero-button {

            display: inline-flex;

            align-items: center;

            gap: 10px;

            margin-top: 20px;

            padding:
                11px 17px;

            border-radius: 9px;

            color: white;

            text-decoration: none;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #06b6d4
                );

            font-size: 11px;

            font-weight: bold;

            box-shadow:
                0 10px 25px
                rgba(37, 99, 235, 0.25);

        }


        .hero-shield {

            position: absolute;

            right: 90px;
            top: 20px;

            width: 220px;
            height: 220px;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 105px;

            opacity: 0.85;

            filter:
                drop-shadow(
                    0 0 30px
                    rgba(14, 165, 233, 0.40)
                );

        }


        /* =====================================================
           STATS
           ===================================================== */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 18px;

        }


        .stat {

            padding: 19px;

            border-radius: 15px;

            background:
                linear-gradient(
                    145deg,
                    #071b35,
                    #061426
                );

            border:
                1px solid
                rgba(96, 165, 250, 0.13);

        }


        .stat-top {

            display: flex;

            align-items: center;

            justify-content: space-between;

        }


        .stat-icon {

            width: 37px;
            height: 37px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 10px;

            font-size: 17px;

        }


        .blue {
            background: rgba(37, 99, 235, 0.20);
        }


        .red {
            background: rgba(239, 68, 68, 0.18);
        }


        .green {
            background: rgba(16, 185, 129, 0.18);
        }


        .purple {
            background: rgba(139, 92, 246, 0.18);
        }


        .stat-change {

            color: #34d399;

            font-size: 8px;

        }


        .stat h3 {

            margin:
                16px 0 5px;

            font-size: 27px;

        }


        .stat p {

            margin: 0;

            color: #6d849c;

            font-size: 10px;

        }


        /* =====================================================
           TWO COLUMN
           ===================================================== */

        .two-column {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 18px;

            margin-bottom: 18px;

        }


        .card {

            padding: 22px;

            border-radius: 16px;

            background:
                linear-gradient(
                    145deg,
                    #071a31,
                    #061426
                );

            border:
                1px solid
                rgba(96, 165, 250, 0.11);

        }


        .card-title {

            display: flex;

            align-items: center;

            gap: 10px;

            font-size: 14px;

            font-weight: bold;

        }


        .card-title-icon {

            color: #22d3ee;

            font-size: 18px;

        }


        /* =====================================================
           PRIVACY SCORE
           ===================================================== */

        .score-content {

            display: flex;

            align-items: center;

            gap: 35px;

            margin-top: 25px;

        }


        .score-ring {

            width: 145px;
            height: 145px;

            flex-shrink: 0;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                conic-gradient(
                    #22d3ee
                    <?php echo $privacy_score; ?>%,
                    #12304d
                    <?php echo $privacy_score; ?>%
                );

            position: relative;

        }


        .score-ring::before {

            content: "";

            position: absolute;

            width: 112px;
            height: 112px;

            border-radius: 50%;

            background: #061426;

        }


        .score-number {

            position: relative;

            z-index: 2;

            text-align: center;

        }


        .score-number strong {

            display: block;

            font-size: 29px;

        }


        .score-number span {

            color: #34d399;

            font-size: 10px;

            font-weight: bold;

        }


        .score-info h3 {

            margin:
                0 0 10px;

            color: #22d3ee;

            font-size: 17px;

        }


        .score-info p {

            max-width: 260px;

            margin:
                0 0 18px;

            color: #8196ab;

            font-size: 10px;

            line-height: 1.6;

        }


        /* =====================================================
           RISK OVERVIEW
           ===================================================== */

        .risk-content {

            display: flex;

            align-items: center;

            justify-content: space-around;

            margin-top: 20px;

        }


        .risk-ring {

            width: 145px;
            height: 145px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                conic-gradient(
                    #ef476f
                    0deg 40deg,
                    #f59e0b
                    40deg 80deg,
                    #2196f3
                    80deg 120deg,
                    #16c784
                    120deg 360deg
                );

            position: relative;

        }


        .risk-ring::before {

            content: "";

            position: absolute;

            width: 105px;
            height: 105px;

            border-radius: 50%;

            background: #061426;

        }


        .risk-center {

            position: relative;

            z-index: 2;

            text-align: center;

        }


        .risk-center strong {

            display: block;

            font-size: 25px;

        }


        .risk-center span {

            color: #8499ad;

            font-size: 9px;

        }


        .legend div {

            display: flex;

            align-items: center;

            gap: 9px;

            margin: 10px 0;

            color: #a9bacb;

            font-size: 10px;

        }


        .dot {

            width: 11px;
            height: 11px;

            border-radius: 50%;

        }


        .dot-high {
            background: #ef476f;
        }


        .dot-medium {
            background: #f59e0b;
        }


        .dot-low {
            background: #2196f3;
        }


        .dot-safe {
            background: #16c784;
        }


        /* =====================================================
           BOTTOM GRID
           ===================================================== */

        .bottom-grid {

            display: grid;

            grid-template-columns:
                1.25fr 0.85fr 0.75fr;

            gap: 18px;

        }


        /* =====================================================
           RECENT ACTIVITY
           ===================================================== */

        .activity-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

        }


        .view-all {

            color: #22d3ee;

            text-decoration: none;

            font-size: 9px;

        }


        .activity {

            margin-top: 16px;

        }


        .activity-item {

            display: flex;

            align-items: center;

            gap: 10px;

            padding:
                11px 0;

            border-bottom:
                1px solid
                rgba(96, 165, 250, 0.06);

        }


        .activity-icon {

            width: 32px;
            height: 32px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                rgba(16, 185, 129, 0.12);

        }


        .activity-text {

            flex: 1;

            min-width: 0;

        }


        .activity-text strong {

            display: block;

            overflow: hidden;

            white-space: nowrap;

            text-overflow: ellipsis;

            font-size: 9px;

        }


        .activity-text span {

            display: block;

            margin-top: 3px;

            color: #5f7892;

            font-size: 8px;

        }


        .activity-time {

            color: #607991;

            font-size: 8px;

        }


        .activity-badge {

            padding:
                5px 8px;

            border-radius: 20px;

            font-size: 7px;

            font-weight: bold;

        }


        .badge-safe {

            color: #34d399;

            background:
                rgba(16, 185, 129, 0.13);

        }


        .badge-high {

            color: #fb7185;

            background:
                rgba(239, 68, 68, 0.13);

        }


        .badge-medium {

            color: #fbbf24;

            background:
                rgba(245, 158, 11, 0.13);

        }


        /* =====================================================
           QUICK ACTIONS
           ===================================================== */

        .quick-list {

            margin-top: 15px;

        }


        .quick-item {

            display: flex;

            align-items: center;

            gap: 10px;

            padding:
                10px;

            margin-bottom: 6px;

            border-radius: 10px;

            color: white;

            text-decoration: none;

            background:
                rgba(37, 99, 235, 0.08);

            transition: 0.2s;

        }


        .quick-item:hover {

            background:
                rgba(37, 99, 235, 0.16);

            transform: translateX(2px);

        }


        .quick-item-icon {

            width: 32px;
            height: 32px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background:
                rgba(37, 99, 235, 0.16);

        }


        .quick-item-text {

            flex: 1;

        }


        .quick-item-text strong {

            display: block;

            font-size: 9px;

        }


        .quick-item-text span {

            display: block;

            margin-top: 3px;

            color: #607a94;

            font-size: 7px;

        }


        .arrow {

            color: #7893ad;

        }


        /* =====================================================
           FUTURE CARD
           ===================================================== */

        .future-card {

            min-height: 260px;

            display: flex;

            flex-direction: column;

            justify-content: flex-end;

            overflow: hidden;

            position: relative;

            background:
                linear-gradient(
                    180deg,
                    rgba(4, 18, 39, 0.35),
                    rgba(3, 15, 32, 0.95)
                ),
                url("images/privacy-bg.jpg");

            background-size: cover;

            background-position: center;

        }


        .future-card::before {

            content: "";

            position: absolute;

            inset: 0;

            background:
                linear-gradient(
                    transparent,
                    rgba(2, 8, 20, 0.95)
                );

        }


        .future-content {

            position: relative;

            z-index: 2;

        }


        .future-content h2 {

            margin: 0;

            font-size: 20px;

        }


        .future-content h2 span {

            color: #22d3ee;

        }


        .future-content p {

            color: #9aafc4;

            font-size: 9px;

            line-height: 1.5;

        }


        /* =====================================================
           EMPTY ACTIVITY
           ===================================================== */

        .empty {

            text-align: center;

            padding: 30px 10px;

        }


        .empty-icon {

            font-size: 28px;

            margin-bottom: 10px;

        }


        .empty h4 {

            margin: 0 0 5px;

            font-size: 11px;

        }


        .empty p {

            margin: 0;

            color: #607991;

            font-size: 9px;

        }


        /* =====================================================
           RESPONSIVE
           ===================================================== */

        @media (max-width: 1200px) {

            .stats {

                grid-template-columns:
                    repeat(2, 1fr);

            }

            .bottom-grid {

                grid-template-columns:
                    1fr 1fr;

            }

            .future-card {

                display: none;

            }

        }


        @media (max-width: 900px) {

            .sidebar {

                width: 75px;

                padding:
                    20px 10px;

            }

            .brand-name,
            .brand-subtitle,
            .nav-title,
            .nav-text,
            .privacy-status,
            .logout-text {

                display: none;

            }

            .brand {

                justify-content: center;

                border: none;

            }

            .nav-item {

                justify-content: center;

                padding: 0;

            }

            .main {

                margin-left: 75px;

                padding:
                    25px 20px;

            }

            .two-column {

                grid-template-columns:
                    1fr;

            }

        }


        @media (max-width: 650px) {

            .main {

                padding:
                    20px 12px;

            }

            .topbar {

                align-items: flex-start;

            }

            .profile span,
            .profile strong {

                display: none;

            }

            .hero {

                padding: 25px;

            }

            .hero h2 {

                font-size: 24px;

            }

            .hero-shield {

                right: -45px;

                opacity: 0.25;

            }

            .stats {

                grid-template-columns:
                    1fr 1fr;

            }

            .score-content,
            .risk-content {

                flex-direction: column;

            }

            .bottom-grid {

                grid-template-columns:
                    1fr;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
     ========================================================= -->

<aside class="sidebar">


    <div class="brand">

        <div class="brand-logo">
            🛡️
        </div>

        <div>

            <h2 class="brand-name">
                Privacy<span>Guard</span>
            </h2>

            <p class="brand-subtitle">
                PRIVACY ASSISTANT
            </p>

        </div>

    </div>


    <div class="nav-title">
        MAIN MENU
    </div>


    <a
        href="dashboard.php"
        class="nav-item active"
    >

        <span class="nav-icon">
            🏠
        </span>

        <span class="nav-text">
            Dashboard
        </span>

    </a>


    <a
        href="scan.php"
        class="nav-item"
    >

        <span class="nav-icon">
            🔎
        </span>

        <span class="nav-text">
            Privacy Scanner
        </span>

    </a>


    <a
        href="history.php"
        class="nav-item"
    >

        <span class="nav-icon">
            🕘
        </span>

        <span class="nav-text">
            Scan History
        </span>

    </a>


    <div
        class="nav-title"
        style="margin-top:25px;"
    >
        ANALYTICS
    </div>


    <a
        href="reports.php"
        class="nav-item"
    >

        <span class="nav-icon">
            📊
        </span>

        <span class="nav-text">
            Reports
        </span>

    </a>
    <a href="awareness.php" class="quick-item">

    <div class="quick-item-icon">
        🛡️
    </div>

    <div class="quick-item-text">

        <strong>
            Privacy Awareness
        </strong>

        <span>
            Learn how to protect your personal information
        </span>

    </div>

    <span class="arrow">
        →
    </span>

</a>
    <a href="government.php" class="quick-item">

    <div class="quick-item-icon">
        🏛️
    </div>

    <div class="quick-item-text">

        <strong>
            Government Helper
        </strong>

        <span>
            Official privacy & cyber services
        </span>

    </div>

    <span class="arrow">
        →
    </span>

</a>


    <a
        href="settings.php"
        class="nav-item"
    >

        <span class="nav-icon">
            ⚙️
        </span>

        <span class="nav-text">
            Settings
        </span>

    </a>


    <div class="sidebar-bottom">


        <div class="privacy-status">

            <div class="status-circle">
                ✓
            </div>

            <div>

                <strong>
                    Privacy Status
                </strong>

                <span>
                    <?php
                    echo htmlspecialchars($privacy_status);
                    ?>
                    <br>
                    <?php
                    echo htmlspecialchars($privacy_message);
                    ?>
                </span>

            </div>

        </div>


        <a
            href="logout.php"
            class="logout"
        >

            <span>
                ⏻
            </span>

            <span class="logout-text">
                Logout
            </span>

        </a>


    </div>


</aside>



<!-- =========================================================
     MAIN CONTENT
     ========================================================= -->

<main class="main">


    <!-- =====================================================
         TOP BAR
         ===================================================== -->

    <div class="topbar">


        <div>

            <div class="welcome-label">
                WELCOME BACK
            </div>

            <h1 class="page-title">
                Dashboard
            </h1>

            <p class="page-description">
                Monitor and protect your personal information.
            </p>

        </div>


        <div class="profile-area">


            <div class="notification">
                🔔
            </div>


            <div class="profile">

                <div class="avatar">
                    <?php
                    echo htmlspecialchars($user_initial);
                    ?>
                </div>

                <div>

                    <strong>
                        <?php
                        echo htmlspecialchars($user_name);
                        ?>
                    </strong>

                    <span>
                        User
                    </span>

                </div>

            </div>


        </div>


    </div>



    <!-- =====================================================
         HERO
         ===================================================== -->

    <section class="hero">


        <div class="hero-content">


            <div class="hero-small">
                PRIVACY ASSISTANT
            </div>


            <h2>
                Stay Safe,
                <span>Stay Private</span>
            </h2>


            <p>
                Your privacy matters. Scan, detect and stay
                ahead of online risks with PrivacyGuard.
                Protect your sensitive information before
                sharing it online.
            </p>


            <a
                href="scan.php"
                class="hero-button"
            >

                🔎

                Run New Scan

                →

            </a>


        </div>


        <div class="hero-shield">
            🛡️
        </div>


    </section>



    <!-- =====================================================
         STATISTICS
         ===================================================== -->

    <section class="stats">


        <div class="stat">


            <div class="stat-top">

                <div class="stat-icon blue">
                    🛡️
                </div>

                <span class="stat-change">
                    ✓ Active
                </span>

            </div>


            <h3>
                <?php
                echo $total_scans;
                ?>
            </h3>


            <p>
                Total Scans
            </p>


        </div>



        <div class="stat">


            <div class="stat-top">

                <div class="stat-icon red">
                    ⚠️
                </div>

                <span
                    class="stat-change"
                    style="color:#fb7185;"
                >
                    Attention
                </span>

            </div>


            <h3>
                <?php
                echo $high_risk;
                ?>
            </h3>


            <p>
                High Risk Found
            </p>


        </div>



        <div class="stat">


            <div class="stat-top">

                <div class="stat-icon green">
                    🔒
                </div>

                <span class="stat-change">
                    Protected
                </span>

            </div>


            <h3>
                <?php
                echo $safe_scans;
                ?>
            </h3>


            <p>
                Safe Scans
            </p>


        </div>



        <div class="stat">


            <div class="stat-top">

                <div class="stat-icon purple">
                    📄
                </div>

                <span class="stat-change">
                    Reports
                </span>

            </div>


            <h3>
                <?php
                echo $privacy_score;
                ?>%
            </h3>


            <p>
                Privacy Score
            </p>


        </div>


    </section>



    <!-- =====================================================
         PRIVACY + RISK
         ===================================================== -->

    <section class="two-column">


        <!-- PRIVACY SCORE -->

        <div class="card">


            <div class="card-title">

                <span class="card-title-icon">
                    🛡️
                </span>

                Privacy Score

            </div>


            <div class="score-content">


                <div class="score-ring">


                    <div class="score-number">

                        <strong>
                            <?php
                            echo $privacy_score;
                            ?>%
                        </strong>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $privacy_status
                            );
                            ?>
                        </span>

                    </div>


                </div>


                <div class="score-info">


                    <h3>
                        <?php
                        if ($privacy_score >= 80) {
                            echo "Your Privacy is Good!";
                        } elseif ($privacy_score >= 60) {
                            echo "Your Privacy is Moderate";
                        } else {
                            echo "Privacy Needs Attention";
                        }
                        ?>
                    </h3>


                    <p>

                        <?php
                        echo htmlspecialchars(
                            $privacy_message
                        );
                        ?>

                        Keep scanning regularly to
                        maintain your privacy and security.

                    </p>


                    <a
                        href="scan.php"
                        class="hero-button"
                        style="
                            margin-top:0;
                            padding:9px 13px;
                            font-size:9px;
                        "
                    >

                        Run New Scan →

                    </a>


                </div>


            </div>


        </div>



        <!-- RISK OVERVIEW -->

        <div class="card">


            <div class="card-title">

                <span class="card-title-icon">
                    📊
                </span>

                Risk Overview

            </div>


            <div class="risk-content">


                <div class="risk-ring">


                    <div class="risk-center">

                        <strong>
                            <?php
                            echo $risk_total;
                            ?>
                        </strong>

                        <span>
                            Risks
                        </span>

                    </div>


                </div>


                <div class="legend">


                    <div>

                        <span class="dot dot-high"></span>

                        High Risk

                        <b>
                            <?php
                            echo $high_risk;
                            ?>
                        </b>

                    </div>


                    <div>

                        <span class="dot dot-medium"></span>

                        Medium Risk

                        <b>
                            <?php
                            echo $medium_risk;
                            ?>
                        </b>

                    </div>


                    <div>

                        <span class="dot dot-low"></span>

                        Low Risk

                        <b>
                            <?php
                            echo $low_risk;
                            ?>
                        </b>

                    </div>


                    <div>

                        <span class="dot dot-safe"></span>

                        Safe

                        <b>
                            <?php
                            echo $safe_scans;
                            ?>
                        </b>

                    </div>


                </div>


            </div>


        </div>


    </section>



    <!-- =====================================================
         BOTTOM SECTION
         ===================================================== -->

    <section class="bottom-grid">


        <!-- =================================================
             RECENT ACTIVITY
             ================================================= -->

        <div class="card">


            <div class="activity-header">


                <div class="card-title">

                    <span class="card-title-icon">
                        ◷
                    </span>

                    Recent Activity

                </div>


                <a
                    href="history.php"
                    class="view-all"
                >
                    View All
                </a>


            </div>



            <div class="activity">


                <?php if ($recent_scans->num_rows > 0): ?>


                    <?php while (
                        $scan =
                        $recent_scans->fetch_assoc()
                    ): ?>


                        <?php

                        $risk =
                            strtoupper(
                                $scan["risk_level"]
                            );

                        $badge_class =
                            "badge-safe";

                        if ($risk === "HIGH") {

                            $badge_class =
                                "badge-high";

                        } elseif (
                            $risk === "MEDIUM"
                        ) {

                            $badge_class =
                                "badge-medium";
                        }

                        ?>


                        <div class="activity-item">


                            <div class="activity-icon">

                                <?php

                                if ($risk === "HIGH") {

                                    echo "⚠️";

                                } else {

                                    echo "🛡️";

                                }

                                ?>

                            </div>


                            <div class="activity-text">


                                <strong>

                                    <?php

                                    $activity_text =
                                        $scan["original_text"];

                                    if (
                                        strlen(
                                            $activity_text
                                        ) > 35
                                    ) {

                                        $activity_text =
                                            substr(
                                                $activity_text,
                                                0,
                                                35
                                            ) . "...";

                                    }

                                    echo htmlspecialchars(
                                        $activity_text
                                    );

                                    ?>

                                </strong>


                                <span>
                                    Privacy Scan
                                </span>


                            </div>


                            <span class="activity-time">

                                <?php

                                echo date(
                                    "d M",
                                    strtotime(
                                        $scan["created_at"]
                                    )
                                );

                                ?>

                            </span>


                            <span
                                class="
                                activity-badge
                                <?php
                                echo $badge_class;
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $risk
                                );
                                ?>

                            </span>


                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="empty">

                        <div class="empty-icon">
                            🔎
                        </div>

                        <h4>
                            No Activity Yet
                        </h4>

                        <p>
                            Start your first privacy scan.
                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </div>



        <!-- =================================================
             QUICK ACTIONS
             ================================================= -->

        <div class="card">


            <div class="card-title">

                <span class="card-title-icon">
                    ⚡
                </span>

                Quick Actions

            </div>


            <div class="quick-list">


                <a
                    href="scan.php"
                    class="quick-item"
                >

                    <div class="quick-item-icon">
                        🔎
                    </div>

                    <div class="quick-item-text">

                        <strong>
                            Scan Now
                        </strong>

                        <span>
                            Check for privacy risks
                        </span>

                    </div>

                    <span class="arrow">
                        ›
                    </span>

                </a>



                <a
                    href="reports.php"
                    class="quick-item"
                >

                    <div class="quick-item-icon">
                        📄
                    </div>

                    <div class="quick-item-text">

                        <strong>
                            View Reports
                        </strong>

                        <span>
                            See detailed analysis
                        </span>

                    </div>

                    <span class="arrow">
                        ›
                    </span>

                </a>



                <a
                    href="history.php"
                    class="quick-item"
                >

                    <div class="quick-item-icon">
                        🕘
                    </div>

                    <div class="quick-item-text">

                        <strong>
                            Scan History
                        </strong>

                        <span>
                            Check past scans
                        </span>

                    </div>

                    <span class="arrow">
                        ›
                    </span>

                </a>



                <a
                    href="settings.php"
                    class="quick-item"
                >

                    <div class="quick-item-icon">
                        ⚙️
                    </div>

                    <div class="quick-item-text">

                        <strong>
                            Settings
                        </strong>

                        <span>
                            Manage your preferences
                        </span>

                    </div>

                    <span class="arrow">
                        ›
                    </span>

                </a>


            </div>


        </div>



        <!-- =================================================
             FUTURE CARD
             ================================================= -->

        <div class="card future-card">


            <div class="future-content">


                <h2>

                    Better Privacy

                    <br>

                    <span>
                        Brighter Future
                    </span>

                </h2>


                <p>

                    Stay informed.
                    Stay secure.
                    Stay in control.

                </p>


                <div style="font-size:45px;">
                    🛡️
                </div>


            </div>


        </div>


    </section>


</main>


</body>

</html>