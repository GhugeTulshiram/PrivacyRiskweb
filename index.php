<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$success = $_GET["success"] ?? "";
$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PrivacyGuard | Secure Login</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">

    <style>
        body {
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
            background: #020817;
        }

        .background {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100vh;
            z-index: 0;

            background-image:
                linear-gradient(
                    90deg,
                    rgba(2, 8, 23, 0.88),
                    rgba(2, 8, 23, 0.58),
                    rgba(2, 8, 23, 0.88)
                ),
                url("images/login-bg.jpg");

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .background::before {
            content: "";
            position: absolute;
            inset: 0;

            background:
                radial-gradient(
                    circle at 18% 45%,
                    rgba(0, 180, 255, 0.20),
                    transparent 32%
                ),
                radial-gradient(
                    circle at 82% 45%,
                    rgba(0, 220, 255, 0.16),
                    transparent 32%
                );

            pointer-events: none;
        }

        .grid {
            position: absolute;
            inset: 0;

            background-image:
                linear-gradient(
                    rgba(255, 255, 255, 0.025) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255, 255, 255, 0.025) 1px,
                    transparent 1px
                );

            background-size: 50px 50px;
            pointer-events: none;
        }

        .glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
        }

        .glow-one {
            width: 350px;
            height: 350px;
            left: 5%;
            top: 15%;
            background: rgba(0, 174, 255, 0.10);
        }

        .glow-two {
            width: 400px;
            height: 400px;
            right: 5%;
            bottom: 10%;
            background: rgba(0, 120, 255, 0.08);
        }

        .login-container {
            position: relative;
            z-index: 2;
        }

        .login-card {
            background: rgba(5, 18, 38, 0.84);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);

            border: 1px solid rgba(100, 200, 255, 0.20);

            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.55),
                0 0 45px rgba(0, 170, 255, 0.08);
        }

        .input-box {
            background: rgba(2, 8, 23, 0.72);
        }

        /* ALERT MESSAGES */

        .custom-alert {
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 10px;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .custom-alert.success {
            color: #bbf7d0;
            background: rgba(22, 163, 74, 0.15);
            border: 1px solid rgba(74, 222, 128, 0.35);
        }

        .custom-alert.error {
            color: #fecaca;
            background: rgba(220, 38, 38, 0.15);
            border: 1px solid rgba(248, 113, 113, 0.35);
        }

        /* FACE LOGIN */

        .face-login-section {
            margin-top: 20px;
        }

        .face-login-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: #94a3b8;
            font-size: 12px;
        }

        .face-login-divider::before,
        .face-login-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(148, 163, 184, 0.25);
        }

        .face-login-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            box-sizing: border-box;
            padding: 14px 16px;

            color: #e2e8f0;
            text-decoration: none;

            background: rgba(14, 165, 233, 0.08);
            border: 1px solid rgba(56, 189, 248, 0.4);
            border-radius: 12px;

            transition: 0.25s ease;
        }

        .face-login-btn:hover {
            background: rgba(14, 165, 233, 0.18);
            border-color: #38bdf8;
            transform: translateY(-2px);
        }

        .face-icon {
            font-size: 26px;
        }

        .face-login-text {
            flex: 1;
        }

        .face-login-text strong,
        .face-login-text small {
            display: block;
        }

        .face-login-text strong {
            font-size: 14px;
        }

        .face-login-text small {
            margin-top: 4px;
            color: #94a3b8;
            font-size: 11px;
        }

        .face-arrow {
            font-size: 20px;
            color: #38bdf8;
        }

        @media (max-width: 850px) {
            .background {
                background-position: center;
            }
        }
    </style>
</head>

<body>

    <!-- BACKGROUND -->

    <div class="background">
        <div class="glow glow-one"></div>
        <div class="glow glow-two"></div>
        <div class="grid"></div>
    </div>

    <!-- MAIN CONTAINER -->

    <main class="login-container">

        <!-- LOGIN CARD -->

        <section class="login-card">

            <!-- BRAND -->

            <div class="brand">
                <div class="brand-icon">🛡️</div>

                <div>
                    <h1>Privacy<span>Guard</span></h1>
                    <p>Privacy-Risk Awareness Assistant</p>
                </div>
            </div>

            <!-- SUCCESS MESSAGE -->

            <?php if (!empty($success)): ?>
                <div class="custom-alert success">
                    <strong>Account Created Successfully!</strong><br>
                    Your account is ready. Please login to continue.
                </div>
            <?php endif; ?>

            <!-- ERROR MESSAGE -->

            <?php if (!empty($error)): ?>
                <div class="custom-alert error">
                    <strong>Login Failed</strong><br>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- WELCOME -->

            <div class="welcome">
                <span class="welcome-tag">SECURE ACCESS</span>

                <h2>Welcome Back</h2>

                <p>
                    Protect your personal information before
                    sharing it online.
                </p>
            </div>

            <!-- PASSWORD LOGIN -->

            <form action="php/auth/login.php" method="POST">

                <div class="input-group">
                    <label for="email">Email Address</label>

                    <div class="input-box">
                        <span>✉</span>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>

                    <div class="input-box">
                        <span>🔒</span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="show-password"
                            onclick="togglePassword()"
                            aria-label="Show or hide password"
                        >
                            👁
                        </button>
                    </div>
                </div>

                <!-- LOGIN OPTIONS -->

                <div class="login-options">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>

                    <a href="#">Forgot Password?</a>
                </div>

                <!-- PASSWORD LOGIN BUTTON -->

                <button type="submit" class="login-btn">
                    <span>Secure Login</span>
                    <span class="arrow">→</span>
                </button>

            </form>

            <!-- FACE LOGIN BUTTON -->

            <div class="face-login-section">

                <div class="face-login-divider">
                    <span>OR</span>
                </div>

                <a href="face_login.php" class="face-login-btn">

                    <span class="face-icon">📷</span>

                    <span class="face-login-text">
                        <strong>Login with Face</strong>
                        <small>Verify your identity using your camera</small>
                    </span>

                    <span class="face-arrow">→</span>

                </a>

            </div>

            <!-- REGISTER -->

            <div class="register-link">
                <span>Don't have an account?</span>

                <a href="register.php">Create Account</a>
            </div>

            <!-- SECURITY -->

            <div class="security">
                <span class="security-dot"></span>

                <span>Secure Access</span>

                <span class="separator">•</span>

                <span>Your data stays protected</span>
            </div>

            <div class="demo-login">
                <span>SECURE</span>
                Your privacy is our priority
            </div>

        </section>

        <!-- HERO SECTION -->

        <section class="hero-section">

            <div class="hero-content">

                <div class="hero-icon">🔐</div>

                <span class="hero-label">
                    YOUR PRIVACY. YOUR CONTROL.
                </span>

                <h2>
                    Know Your Risk
                    <br>
                    <span>Before You Share.</span>
                </h2>

                <p>
                    PrivacyGuard helps you identify sensitive
                    information before it reaches online services
                    or AI assistants.
                </p>

                <!-- FEATURES -->

                <div class="feature-list">

                    <div class="feature">
                        <div class="feature-icon">🔎</div>

                        <div>
                            <h3>Detect Sensitive Data</h3>

                            <p>
                                Identify emails, phone numbers,
                                IDs and other private information.
                            </p>
                        </div>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">📊</div>

                        <div>
                            <h3>Analyze Privacy Risk</h3>

                            <p>
                                Understand your privacy exposure
                                with a simple risk score.
                            </p>
                        </div>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">🛡️</div>

                        <div>
                            <h3>Protect Your Information</h3>

                            <p>
                                Mask sensitive information before
                                sharing your text.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- FLOATING CARD -->

            <div class="floating-card">

                <div class="mini-icon">✓</div>

                <div>
                    <strong>Privacy Check</strong>

                    <span>
                        Your information is under your control.
                    </span>
                </div>

            </div>

        </section>

    </main>

    <!-- EXISTING JAVASCRIPT -->

    <script src="js/login.js"></script>

</body>
</html>