<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_name = $_SESSION["user_name"] ?? "User";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Government Services | PrivacyGuard</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at 80% 10%, rgba(37, 99, 235, 0.14), transparent 30%),
                radial-gradient(circle at 20% 90%, rgba(14, 165, 233, 0.08), transparent 30%),
                #050b14;
            color: #ffffff;
        }

        .government-layout {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 245px;
            min-height: 100vh;
            background: rgba(7, 18, 32, 0.96);
            border-right: 1px solid rgba(96, 165, 250, 0.10);
            padding: 25px 16px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px 30px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2563eb, #38bdf8);
            font-size: 21px;
            box-shadow: 0 0 25px rgba(37, 99, 235, 0.35);
        }

        .brand-name {
            font-size: 17px;
            font-weight: 700;
        }

        .brand-subtitle {
            font-size: 9px;
            color: #6f87a3;
            margin-top: 3px;
        }

        .nav-title {
            font-size: 9px;
            color: #50657d;
            letter-spacing: 1.4px;
            margin: 15px 12px 10px;
            text-transform: uppercase;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #7d91a8;
            padding: 12px 13px;
            margin: 5px 0;
            border-radius: 10px;
            font-size: 12px;
            transition: 0.25s;
        }

        .nav-item:hover {
            color: white;
            background: rgba(37, 99, 235, 0.10);
        }

        .nav-item.active {
            color: white;
            background: rgba(37, 99, 235, 0.16);
            border-left: 3px solid #60a5fa;
        }

        .nav-icon {
            width: 22px;
            text-align: center;
            font-size: 16px;
        }

        .sidebar-user {
            position: absolute;
            bottom: 22px;
            left: 16px;
            right: 16px;
            padding: 13px;
            border: 1px solid rgba(96, 165, 250, 0.10);
            background: rgba(15, 30, 50, 0.7);
            border-radius: 12px;
        }

        .sidebar-user small {
            color: #60758c;
            display: block;
            font-size: 9px;
        }

        .sidebar-user strong {
            display: block;
            margin-top: 4px;
            font-size: 12px;
        }

        /* MAIN */

        .main {
            margin-left: 245px;
            width: calc(100% - 245px);
            padding: 34px 42px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .topbar h1 {
            margin: 0;
            font-size: 25px;
        }

        .topbar p {
            margin: 7px 0 0;
            color: #71869f;
            font-size: 12px;
        }

        .secure-badge {
            padding: 9px 14px;
            border-radius: 20px;
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.20);
            color: #4ade80;
            font-size: 10px;
        }

        /* HERO */

        .government-hero {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            padding: 34px;
            margin-bottom: 25px;
            background:
                linear-gradient(
                    120deg,
                    rgba(12, 39, 72, 0.95),
                    rgba(8, 22, 40, 0.95)
                );
            border: 1px solid rgba(96, 165, 250, 0.15);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        }

        .government-hero::before {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            right: -80px;
            top: -100px;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.16);
            filter: blur(5px);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 700px;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            border-radius: 20px;
            background: rgba(96, 165, 250, 0.10);
            border: 1px solid rgba(96, 165, 250, 0.18);
            color: #93c5fd;
            font-size: 9px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .government-hero h2 {
            font-size: 30px;
            margin: 18px 0 10px;
        }

        .government-hero h2 span {
            color: #60a5fa;
        }

        .government-hero p {
            color: #91a5bb;
            line-height: 1.7;
            font-size: 12px;
            max-width: 650px;
        }

        /* SERVICES */

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 28px 0 15px;
        }

        .section-heading h3 {
            margin: 0;
            font-size: 15px;
        }

        .section-heading span {
            color: #536b84;
            font-size: 10px;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 17px;
        }

        .service-card {
            position: relative;
            overflow: hidden;
            min-height: 220px;
            padding: 23px;
            border-radius: 17px;
            text-decoration: none;
            color: white;
            background: linear-gradient(
                145deg,
                rgba(14, 31, 52, 0.96),
                rgba(8, 18, 31, 0.96)
            );
            border: 1px solid rgba(96, 165, 250, 0.10);
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            border-color: rgba(96, 165, 250, 0.30);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.30);
        }

        .service-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            background: rgba(37, 99, 235, 0.13);
            border: 1px solid rgba(96, 165, 250, 0.13);
            margin-bottom: 18px;
        }

        .service-card h4 {
            margin: 0 0 9px;
            font-size: 15px;
        }

        .service-card p {
            margin: 0;
            color: #71869f;
            font-size: 11px;
            line-height: 1.7;
        }

        .service-link {
            position: absolute;
            bottom: 20px;
            left: 23px;
            right: 23px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #60a5fa;
            font-size: 10px;
            padding-top: 13px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        /* STATUS */

        .status-panel {
            margin-top: 24px;
            padding: 20px 23px;
            border-radius: 16px;
            background: rgba(8, 20, 35, 0.8);
            border: 1px solid rgba(96, 165, 250, 0.10);
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .status-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(34, 197, 94, 0.10);
            border: 1px solid rgba(34, 197, 94, 0.20);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4ade80;
            font-size: 20px;
        }

        .status-panel h4 {
            margin: 0 0 5px;
            font-size: 12px;
        }

        .status-panel p {
            margin: 0;
            color: #71869f;
            font-size: 10px;
            line-height: 1.5;
        }

        /* NOTICE */

        .notice {
            margin-top: 18px;
            padding: 17px 20px;
            border-radius: 13px;
            background: rgba(245, 158, 11, 0.06);
            border: 1px solid rgba(245, 158, 11, 0.15);
            color: #b6a27a;
            font-size: 10px;
            line-height: 1.6;
        }

        .notice strong {
            color: #fbbf24;
        }

        @media(max-width: 1000px) {

            .services-grid {
                grid-template-columns: 1fr;
            }

        }

        @media(max-width: 700px) {

            .sidebar {
                width: 70px;
                padding: 20px 8px;
            }

            .brand-name,
            .brand-subtitle,
            .nav-title,
            .nav-item span:not(.nav-icon),
            .sidebar-user {
                display: none;
            }

            .brand {
                justify-content: center;
                padding-bottom: 25px;
            }

            .nav-item {
                justify-content: center;
            }

            .main {
                margin-left: 70px;
                width: calc(100% - 70px);
                padding: 25px 18px;
            }

            .topbar {
                align-items: flex-start;
                gap: 10px;
            }

            .government-hero {
                padding: 25px;
            }

            .government-hero h2 {
                font-size: 24px;
            }

        }

    </style>

</head>

<body>

<div class="government-layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="brand">

            <div class="brand-logo">
                🛡️
            </div>

            <div>
                <div class="brand-name">
                    PrivacyGuard
                </div>

                <div class="brand-subtitle">
                    PRIVACY-RISK AWARENESS
                </div>
            </div>

        </div>


        <div class="nav-title">
            Main Menu
        </div>

        <a href="dashboard.php" class="nav-item">

            <span class="nav-icon">⌂</span>

            <span>
                Dashboard
            </span>

        </a>


        <a href="scan.php" class="nav-item">

            <span class="nav-icon">⌕</span>

            <span>
                Privacy Scanner
            </span>

        </a>


        <a href="history.php" class="nav-item">

            <span class="nav-icon">◷</span>

            <span>
                Scan History
            </span>

        </a>


        <a href="reports.php" class="nav-item">

            <span class="nav-icon">▥</span>

            <span>
                Privacy Reports
            </span>

        </a>


        <div class="nav-title">
            Services
        </div>


        <a href="government.php" class="nav-item active">

            <span class="nav-icon">🏛️</span>

            <span>
                Government Services
            </span>

        </a>


        <a href="settings.php" class="nav-item">

            <span class="nav-icon">⚙</span>

            <span>
                Settings
            </span>

        </a>


        <div class="sidebar-user">

            <small>
                LOGGED IN AS
            </small>

            <strong>
                <?php echo htmlspecialchars($user_name); ?>
            </strong>

        </div>

    </aside>


    <!-- MAIN -->

    <main class="main">

        <div class="topbar">

            <div>

                <h1>
                    Government Services
                </h1>

                <p>
                    Access official privacy, identity and cyber-security resources.
                </p>

            </div>

            <div class="secure-badge">
                ● OFFICIAL RESOURCES
            </div>

        </div>


        <!-- HERO -->

        <section class="government-hero">

            <div class="hero-content">

                <div class="hero-tag">
                    🏛️ Government Connected
                </div>

                <h2>
                    Protect Your Privacy with
                    <span>Official Services</span>
                </h2>

                <p>
                    PrivacyGuard helps you identify sensitive information before
                    sharing it and provides quick access to official Government
                    of India privacy, identity and cyber-crime resources.
                </p>

            </div>

        </section>


        <!-- SERVICES -->

        <div class="section-heading">

            <h3>
                Official Government Resources
            </h3>

            <span>
                GOVERNMENT OF INDIA
            </span>

        </div>


        <div class="services-grid">


            <!-- PAN -->

            <a
                href="https://www.incometax.gov.in/"
                target="_blank"
                rel="noopener noreferrer"
                class="service-card"
            >

                <div class="service-icon">
                    🪪
                </div>

                <h4>
                    PAN & Income Tax
                </h4>

                <p>
                    Access the official Income Tax Department portal for
                    PAN-related services, tax services and taxpayer resources.
                </p>

                <div class="service-link">

                    <span>
                        Income Tax Portal
                    </span>

                    <span>
                        ↗
                    </span>

                </div>

            </a>


            <!-- AADHAAR -->

            <a
                href="https://uidai.gov.in/"
                target="_blank"
                rel="noopener noreferrer"
                class="service-card"
            >

                <div class="service-icon">
                    🆔
                </div>

                <h4>
                    Aadhaar Services
                </h4>

                <p>
                    Visit the official UIDAI portal for Aadhaar services,
                    updates, verification-related information and resources.
                </p>

                <div class="service-link">

                    <span>
                        UIDAI Portal
                    </span>

                    <span>
                        ↗
                    </span>

                </div>

            </a>


            <!-- CYBER CRIME -->

            <a
                href="cybercrime.php"
                class="service-card"
            >

                <div class="service-icon">
                    🛡️
                </div>

                <h4>
                    Cyber Crime Reporting
                </h4>

                <p>
                    Report cybercrime incidents and access the official
                    National Cyber Crime Reporting Portal.
                </p>

                <div class="service-link">

                    <span>
                        Cyber Crime Portal
                    </span>

                    <span>
                        ↗
                    </span>

                </div>

            </a>


        </div>


        <!-- STATUS -->

        <div class="status-panel">

            <div class="status-icon">
                ✓
            </div>

            <div>

                <h4>
                    Official Resource Access
                </h4>

                <p>
                    These links connect you to official government websites.
                    PrivacyGuard does not perform government verification
                    unless an authorized official service is integrated.
                </p>

            </div>

        </div>


        <!-- NOTICE -->

        <div class="notice">

            <strong>Important:</strong>
            Never share Aadhaar numbers, PAN numbers, passwords,
            OTPs, card details or other sensitive information unnecessarily.
            Always verify that you are using the official government website
            before entering personal information.

        </div>

    </main>

</div>

</body>

</html>