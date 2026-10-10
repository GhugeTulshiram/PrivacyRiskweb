<?php
session_start();

$message = "";
$messageType = "";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | PrivacyGuard</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">

    <style>
        .register-card {
            max-width: 500px;
            width: 100%;
        }

        .register-title {
            margin-bottom: 28px;
        }

        .register-title h2 {
            font-size: 32px;
            margin-top: 10px;
        }

        .register-title p {
            color: #8da1b8;
            font-size: 14px;
            margin-top: 8px;
        }

        .name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .register-link {
            text-align: center;
            margin-top: 22px;
            color: #71869f;
            font-size: 13px;
        }

        .register-link a {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 600;
        }

        .password-info {
            color: #52677f;
            font-size: 10px;
            margin-top: 6px;
        }

        @media (max-width: 550px) {
            .name-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>

<body>

<div class="background">

    <div class="glow glow-one"></div>
    <div class="glow glow-two"></div>

    <div class="grid"></div>

</div>


<main class="login-container">

    <section class="login-card register-card">

        <div class="brand">

            <div class="brand-icon">
                🛡️
            </div>

            <div>
                <h1>Privacy<span>Guard</span></h1>

                <p>
                    Privacy-Risk Awareness Assistant
                </p>
            </div>

        </div>


        <div class="register-title">

            <span class="welcome-tag">
                GET STARTED
            </span>

            <h2>Create Account</h2>

            <p>
                Create your secure account and take control
                of your privacy.
            </p>

        </div>


        <form action="php/auth/register.php" method="POST">

            <div class="name-row">

                <div class="input-group">

                    <label for="name">
                        Full Name
                    </label>

                    <div class="input-box">

                        <span>👤</span>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Your name"
                            required
                        >

                    </div>

                </div>


                <div class="input-group">

                    <label for="username">
                        Username
                    </label>

                    <div class="input-box">

                        <span>🪪</span>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Username"
                            required
                        >

                    </div>

                </div>

            </div>


            <div class="input-group">

                <label for="email">
                    Email Address
                </label>

                <div class="input-box">

                    <span>✉</span>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                </div>

            </div>


            <div class="input-group">

                <label for="password">
                    Password
                </label>

                <div class="input-box">

                    <span>🔒</span>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create password"
                        minlength="6"
                        required
                    >

                    <button
                        type="button"
                        class="show-password"
                        onclick="toggleRegisterPassword('password')"
                    >
                        👁
                    </button>

                </div>

                <div class="password-info">
                    Minimum 6 characters
                </div>

            </div>


            <div class="input-group">

                <label for="confirm_password">
                    Confirm Password
                </label>

                <div class="input-box">

                    <span>🔐</span>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        minlength="6"
                        required
                    >

                    <button
                        type="button"
                        class="show-password"
                        onclick="toggleRegisterPassword('confirm_password')"
                    >
                        👁
                    </button>

                </div>

            </div>


            <button type="submit" class="login-btn">

                <span>Create Secure Account</span>

                <span class="arrow">→</span>

            </button>

        </form>


        <div class="register-link">

            Already have an account?

            <a href="index.php">
                Login here
            </a>

        </div>


        <div class="security">

            <span class="security-dot"></span>

            Secure Account Registration

        </div>

    </section>

</main>


<script src="js/login.js"></script>

<script>

function toggleRegisterPassword(id) {

    const password =
        document.getElementById(id);

    if (password.type === "password") {

        password.type = "text";

    } else {

        password.type = "password";

    }
}

</script>

</body>
</html>