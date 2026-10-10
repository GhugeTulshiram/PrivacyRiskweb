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

    <title>Privacy Awareness | PrivacyGuard</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at 80% 10%, rgba(37, 99, 235, .13), transparent 30%),
                #050b14;
            color: #fff;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 245px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            padding: 25px 16px;
            background: rgba(7, 18, 32, .97);
            border-right: 1px solid rgba(96, 165, 250, .1);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px 28px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2563eb, #38bdf8);
            font-size: 20px;
        }

        .brand-name {
            font-size: 17px;
            font-weight: 700;
        }

        .brand-subtitle {
            color: #61758d;
            font-size: 9px;
            margin-top: 3px;
        }

        .nav-title {
            color: #50657d;
            font-size: 9px;
            letter-spacing: 1.3px;
            margin: 15px 12px 9px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 13px;
            margin: 5px 0;
            border-radius: 10px;
            color: #7d91a8;
            text-decoration: none;
            font-size: 12px;
            transition: .25s;
        }

        .nav-item:hover {
            color: #fff;
            background: rgba(37, 99, 235, .1);
        }

        .nav-item.active {
            color: #fff;
            background: rgba(37, 99, 235, .16);
            border-left: 3px solid #60a5fa;
        }

        .nav-icon {
            width: 22px;
            text-align: center;
            font-size: 16px;
        }

        /* MAIN */

        .main {
            margin-left: 245px;
            width: calc(100% - 245px);
            padding: 34px 42px;
        }

        .topbar {
            margin-bottom: 25px;
        }

        .topbar h1 {
            margin: 0;
            font-size: 26px;
        }

        .topbar p {
            color: #71869f;
            font-size: 12px;
            margin-top: 8px;
        }

        /* HERO */

        .hero {
            padding: 30px;
            border-radius: 19px;
            background: linear-gradient(
                135deg,
                rgba(14, 45, 78, .95),
                rgba(8, 20, 35, .95)
            );
            border: 1px solid rgba(96, 165, 250, .13);
            margin-bottom: 25px;
        }

        .hero-badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            color: #93c5fd;
            background: rgba(96, 165, 250, .1);
            font-size: 9px;
            letter-spacing: 1px;
        }

        .hero h2 {
            margin: 17px 0 10px;
            font-size: 27px;
        }

        .hero h2 span {
            color: #60a5fa;
        }

        .hero p {
            max-width: 720px;
            color: #8da1b7;
            line-height: 1.7;
            font-size: 12px;
        }

        /* TIPS */

        .section-title {
            margin: 28px 0 15px;
            font-size: 15px;
        }

        .tips-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .tip-card {
            padding: 22px;
            min-height: 190px;
            border-radius: 16px;
            background: rgba(9, 22, 38, .9);
            border: 1px solid rgba(96, 165, 250, .09);
            transition: .25s;
        }

        .tip-card:hover {
            transform: translateY(-4px);
            border-color: rgba(96, 165, 250, .25);
        }

        .tip-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(37, 99, 235, .12);
            font-size: 20px;
            margin-bottom: 15px;
        }

        .tip-card h3 {
            margin: 0 0 9px;
            font-size: 14px;
        }

        .tip-card p {
            margin: 0;
            color: #71869f;
            line-height: 1.65;
            font-size: 11px;
        }

        /* AI WARNING */

        .ai-box {
            margin-top: 24px;
            padding: 23px;
            border-radius: 16px;
            background: rgba(37, 99, 235, .07);
            border: 1px solid rgba(96, 165, 250, .15);
        }

        .ai-box h3 {
            margin: 0 0 10px;
            font-size: 14px;
        }

        .ai-box p {
            color: #8195aa;
            font-size: 11px;
            line-height: 1.7;
        }

        .avoid-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 9px;
            margin-top: 15px;
        }

        .avoid-item {
            padding: 11px 13px;
            border-radius: 9px;
            background: rgba(239, 68, 68, .06);
            border: 1px solid rgba(239, 68, 68, .1);
            color: #b8a0a0;
            font-size: 10px;
        }

        .avoid-item span {
            color: #f87171;
            margin-right: 7px;
        }

        /* FOOTER NOTICE */

        .notice {
            margin-top: 20px;
            padding: 15px 18px;
            border-radius: 11px;
            background: rgba(34, 197, 94, .05);
            border: 1px solid rgba(34, 197, 94, .12);
            color: #819b8c;
            font-size: 10px;
            line-height: 1.6;
        }

        .notice strong {
            color: #4ade80;
        }

        @media(max-width: 1000px) {

            .tips-grid {
                grid-template-columns: 1fr 1fr;
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
            .nav-item span:not(.nav-icon) {
                display: none;
            }

            .brand {
                justify-content: center;
            }

            .nav-item {
                justify-content: center;
            }

            .main {
                margin-left: 70px;
                width: calc(100% - 70px);
                padding: 25px 18px;
            }

            .tips-grid,
            .avoid-list {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="layout">

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
            <span>Dashboard</span>
        </a>


        <a href="scan.php" class="nav-item">
            <span class="nav-icon">⌕</span>
            <span>Privacy Scanner</span>
        </a>


        <a href="history.php" class="nav-item">
            <span class="nav-icon">◷</span>
            <span>Scan History</span>
        </a>


        <a href="reports.php" class="nav-item">
            <span class="nav-icon">▥</span>
            <span>Privacy Reports</span>
        </a>


        <div class="nav-title">
            Services
        </div>


        <a href="awareness.php" class="nav-item active">
            <span class="nav-icon">🛡️</span>
            <span>Privacy Awareness</span>
        </a>


        <a href="government.php" class="nav-item">
            <span class="nav-icon">🏛️</span>
            <span>Government Services</span>
        </a>


        <a href="settings.php" class="nav-item">
            <span class="nav-icon">⚙</span>
            <span>Settings</span>
        </a>

    </aside>


    <!-- MAIN -->

    <main class="main">

        <div class="topbar">

            <h1>
                Privacy Awareness
            </h1>

            <p>
                Learn what information should be protected before sharing online.
            </p>

        </div>


        <!-- HERO -->

        <section class="hero">

            <div class="hero-badge">
                🛡️ PRIVACY EDUCATION
            </div>

            <h2>
                Think Before You <span>Share</span>
            </h2>

            <p>
                Personal information can be misused when shared with websites,
                online services or AI assistants. Understanding what information
                is sensitive can help you make safer privacy decisions.
            </p>

        </section>


        <!-- TIPS -->

        <h2 class="section-title">
            Essential Privacy Tips
        </h2>


        <div class="tips-grid">


            <div class="tip-card">

                <div class="tip-icon">
                    🪪
                </div>

                <h3>
                    Protect Identity Data
                </h3>

                <p>
                    Avoid unnecessarily sharing Aadhaar, PAN, passport numbers
                    or other government identification details.
                </p>

            </div>


            <div class="tip-card">

                <div class="tip-icon">
                    🔐
                </div>

                <h3>
                    Keep Credentials Private
                </h3>

                <p>
                    Never share passwords, OTPs, API keys or authentication
                    tokens with other people or online services.
                </p>

            </div>


            <div class="tip-card">

                <div class="tip-icon">
                    💳
                </div>

                <h3>
                    Protect Financial Data
                </h3>

                <p>
                    Avoid exposing complete card numbers, banking credentials,
                    UPI details or other sensitive financial information.
                </p>

            </div>


            <div class="tip-card">

                <div class="tip-icon">
                    📍
                </div>

                <h3>
                    Limit Location Sharing
                </h3>

                <p>
                    Be careful when sharing your exact address, PIN code,
                    live location or other information that reveals where you are.
                </p>

            </div>


            <div class="tip-card">

                <div class="tip-icon">
                    📧
                </div>

                <h3>
                    Review Personal Messages
                </h3>

                <p>
                    Check messages before submitting them to websites or
                    assistants for emails, phone numbers and private details.
                </p>

            </div>


            <div class="tip-card">

                <div class="tip-icon">
                    🔎
                </div>

                <h3>
                    Verify Before You Submit
                </h3>

                <p>
                    Use the Privacy Scanner to identify sensitive information
                    before sending text to an online service.
                </p>

            </div>


        </div>


        <!-- AI SECTION -->

        <div class="ai-box">

            <h3>
                🤖 Before Using an AI Assistant
            </h3>

            <p>
                AI assistants can process the information you provide.
                Before submitting a prompt, review it and remove information
                that is unnecessary for the task.
            </p>


            <div class="avoid-list">

                <div class="avoid-item">
                    <span>✕</span>
                    Passwords and OTPs
                </div>

                <div class="avoid-item">
                    <span>✕</span>
                    Aadhaar and PAN numbers
                </div>

                <div class="avoid-item">
                    <span>✕</span>
                    Credit / Debit card details
                </div>

                <div class="avoid-item">
                    <span>✕</span>
                    API keys and access tokens
                </div>

                <div class="avoid-item">
                    <span>✕</span>
                    Private personal documents
                </div>

                <div class="avoid-item">
                    <span>✕</span>
                    Unnecessary confidential information
                </div>

            </div>

        </div>


        <div class="notice">

            <strong>PrivacyGuard Tip:</strong>
            If information is not necessary for the task, consider removing
            it before submitting your text to an online service.

        </div>

    </main>

</div>

</body>

</html>