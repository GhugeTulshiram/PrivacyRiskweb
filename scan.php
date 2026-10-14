<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$userName = $_SESSION["user_name"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Privacy Scanner | PrivacyGuard</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/scan.css">

</head>

<body>


<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="sidebar-brand">

        <div class="sidebar-logo">
            🛡️
        </div>

        <div>

            <h2>
                Privacy<span>Guard</span>
            </h2>

            <p>
                Privacy Assistant
            </p>

        </div>

    </div>


    <nav class="sidebar-nav">

        <p class="nav-title">
            MAIN MENU
        </p>

        <a href="dashboard.php" class="nav-item">
            <span>⌂</span>
            Dashboard
        </a>

        <a href="scan.php" class="nav-item active">
            <span>⌕</span>
            Privacy Scanner
        </a>

        <a href="antivirus.php" class="nav-item">
            <span>⚡</span>
            Antivirus & Camera
        </a>

        <a href="history.php" class="nav-item">
            <span>◷</span>
            Scan History
        </a>

        <p class="nav-title second-title">
            ANALYTICS
        </p>

        <a href="reports.php" class="nav-item">
            <span>▥</span>
            Reports
        </a>

        <a href="settings.php" class="nav-item">
            <span>⚙</span>
            Settings
        </a>

    </nav>


    <div class="sidebar-bottom">

        <div class="privacy-status">

            <div class="status-icon">
                ✓
            </div>

            <div>

                <strong>
                    Protected
                </strong>

                <span>
                    Privacy monitoring active
                </span>

            </div>

        </div>


        <a href="logout.php" class="logout-btn">

            <span>↪</span>

            Logout

        </a>

    </div>

</aside>



<!-- MAIN -->

<main class="dashboard-main">


    <header class="dashboard-header">

        <div>

            <span class="page-label">
                PRIVACY CENTER
            </span>

            <h1>
                Privacy Scanner
            </h1>

            <p>
                Check your text for sensitive information before sharing it.
            </p>

        </div>


        <div class="user-area">

            <div class="user-info">

                <div class="user-avatar">

                    <?php
                    echo strtoupper(
                        substr($userName, 0, 1)
                    );
                    ?>

                </div>

                <div>

                    <strong>
                        <?php echo htmlspecialchars($userName); ?>
                    </strong>

                    <span>
                        Protected Account
                    </span>

                </div>

            </div>

        </div>

    </header>



    <!-- SCANNER CARD -->

    <section class="scanner-card">


        <div class="scanner-heading">

            <div class="scanner-icon">
                🔎
            </div>

            <div>

                <span>
                    TEXT ANALYSIS
                </span>

                <h2>
                    Check Before You Share
                </h2>

                <p>
                    Paste your message, prompt or text below.
                    PrivacyGuard will identify potentially sensitive information.
                </p>

            </div>

        </div>



        <form
            action="php/scanner/scan_handler.php"
            method="POST"
            id="scanForm"
        >


            <label for="scanText">
                Enter Text
            </label>


            <textarea
                id="scanText"
                name="scan_text"
                placeholder="Example: My name is Rahul. My email is rahul@gmail.com and my phone number is 9876543210..."
                required
            ></textarea>


            <div class="scanner-bottom">

                <div class="privacy-tip">

                    🛡️

                    <span>
                        Your text will be analyzed for privacy risks.
                    </span>

                </div>


                <button
                    type="submit"
                    class="analyze-btn"
                >

                    🔎

                    Analyze Privacy Risk

                    <span>
                        →
                    </span>

                </button>

            </div>

        </form>

    </section>



    <!-- INFORMATION CARDS -->

    <section class="detect-section">

        <div class="section-title">

            <span>
                WHAT WE CHECK
            </span>

            <h2>
                Sensitive Information Detection
            </h2>

        </div>


        <div class="detect-grid">


            <div class="detect-card">

                <div class="detect-icon">
                    ✉
                </div>

                <h3>
                    Email Address
                </h3>

                <p>
                    Detects personal email addresses.
                </p>

            </div>


            <div class="detect-card">

                <div class="detect-icon">
                    📱
                </div>

                <h3>
                    Phone Number
                </h3>

                <p>
                    Identifies mobile and phone numbers.
                </p>

            </div>


            <div class="detect-card">

                <div class="detect-icon">
                    🪪
                </div>

                <h3>
                    Government ID
                </h3>

                <p>
                    Detects Aadhaar and ID-like numbers.
                </p>

            </div>


            <div class="detect-card">

                <div class="detect-icon">
                    💳
                </div>

                <h3>
                    Credit Card
                </h3>

                <p>
                    Identifies card number patterns.
                </p>

            </div>


            <div class="detect-card">

                <div class="detect-icon">
                    🌐
                </div>

                <h3>
                    IP Address
                </h3>

                <p>
                    Detects IPv4 addresses.
                </p>

            </div>


            <div class="detect-card">

                <div class="detect-icon">
                    🔑
                </div>

                <h3>
                    Password / API Key
                </h3>

                <p>
                    Identifies possible credentials and keys.
                </p>

            </div>


        </div>

    </section>


</main>


<script src="js/scan.js"></script>

</body>

</html>