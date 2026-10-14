<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . "/php/config/db.php";

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"] ?? "User";


// TOTAL SCANS
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


// SAFE
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


// LOW
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


// MEDIUM
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


// HIGH
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


// AVERAGE RISK
$stmt = $conn->prepare(
    "SELECT AVG(risk_score) AS average_risk
     FROM scans
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$average_risk = round(
    (float)($row["average_risk"] ?? 0)
);

$stmt->close();


// PRIVACY SCORE
if ($total_scans > 0) {

    $privacy_score = 100 - $average_risk;

    if ($privacy_score < 0) {
        $privacy_score = 0;
    }

} else {

    $privacy_score = 100;
}


// SAFE PERCENTAGE
if ($total_scans > 0) {

    $safe_percentage = round(
        ($safe_scans / $total_scans) * 100
    );

} else {

    $safe_percentage = 0;
}


// ALL SCANS
$stmt = $conn->prepare(
    "SELECT
        id,
        original_text,
        risk_score,
        risk_level,
        created_at
     FROM scans
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$all_scans = $stmt->get_result();

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
PrivacyGuard | Reports
</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    min-height: 100vh;

    background:
        radial-gradient(
            circle at 80% 10%,
            rgba(14,165,233,.10),
            transparent 30%
        ),
        #020817;

    color: #fff;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

}


/* SIDEBAR */

.sidebar {

    position: fixed;

    top: 0;
    left: 0;
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
        rgba(59,130,246,.20);

    display: flex;

    flex-direction: column;

}


.brand {

    display: flex;

    align-items: center;

    gap: 12px;

    padding:
        0 8px 25px;

    margin-bottom: 25px;

    border-bottom:
        1px solid
        rgba(96,165,250,.10);

}


.brand-logo {

    width: 44px;
    height: 44px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #0ea5e9,
            #2563eb
        );

    font-size: 22px;

}


.brand h2 {

    margin: 0;

    font-size: 19px;

}


.brand h2 span {

    color: #22d3ee;

}


.brand p {

    margin: 3px 0 0;

    color: #7189a1;

    font-size: 8px;

}


.nav-title {

    margin:
        0 12px 10px;

    color: #4e6b88;

    font-size: 9px;

    letter-spacing: 1.5px;

}


.nav-item {

    width: 100%;

    height: 48px;

    display: flex;

    align-items: center;

    gap: 14px;

    padding: 0 14px;

    margin-bottom: 6px;

    color: #9bb0c7;

    text-decoration: none;

    border-radius: 12px;

    font-size: 13px;

    transition: .2s;

}


.nav-item:hover {

    color: white;

    background:
        rgba(37,99,235,.10);

}


.nav-item.active {

    color: white;

    background:
        linear-gradient(
            90deg,
            rgba(37,99,235,.80),
            rgba(37,99,235,.25)
        );

}


.nav-icon {

    width: 25px;

    text-align: center;

    font-size: 17px;

}


.sidebar-bottom {

    margin-top: auto;

}


.status {

    padding: 14px;

    border-radius: 12px;

    background:
        rgba(16,185,129,.07);

    border:
        1px solid
        rgba(16,185,129,.12);

    color: #34d399;

    font-size: 10px;

    margin-bottom: 10px;

}


.logout {

    height: 43px;

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 0 14px;

    color: #fb7185;

    text-decoration: none;

    border-radius: 10px;

    font-size: 12px;

}


/* MAIN */

.main {

    margin-left: 260px;

    padding: 35px 40px;

}


/* HEADER */

.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 28px;

}


.small-title {

    color: #60a5fa;

    font-size: 10px;

    letter-spacing: 1.5px;

    font-weight: bold;

}


.header h1 {

    margin: 7px 0;

    font-size: 30px;

}


.header p {

    margin: 0;

    color: #7189a1;

    font-size: 11px;

}


.user {

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


/* REPORT HERO */

.report-hero {

    padding: 30px;

    border-radius: 18px;

    margin-bottom: 20px;

    background:
        linear-gradient(
            100deg,
            rgba(8,30,60,.95),
            rgba(4,20,40,.70)
        ),
        url("images/privacy-bg.jpg");

    background-size: cover;

    background-position: center;

    border:
        1px solid
        rgba(96,165,250,.16);

}


.report-hero h2 {

    margin: 0 0 8px;

    font-size: 25px;

}


.report-hero h2 span {

    color: #22d3ee;

}


.report-hero p {

    margin: 0;

    color: #8ea4ba;

    font-size: 11px;

}


/* SUMMARY */

.summary {

    display: grid;

    grid-template-columns:
        repeat(4,1fr);

    gap: 15px;

    margin-bottom: 20px;

}


.summary-card {

    padding: 20px;

    border-radius: 15px;

    background:
        linear-gradient(
            145deg,
            #071b35,
            #061426
        );

    border:
        1px solid
        rgba(96,165,250,.11);

}


.summary-icon {

    width: 38px;
    height: 38px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 10px;

    font-size: 18px;

}


.summary-card h2 {

    margin:
        15px 0 5px;

    font-size: 27px;

}


.summary-card p {

    margin: 0;

    color: #7189a1;

    font-size: 10px;

}


/* REPORT GRID */

.report-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 18px;

    margin-bottom: 20px;

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
        rgba(96,165,250,.11);

}


.card-title {

    font-size: 14px;

    font-weight: bold;

    margin-bottom: 20px;

}


/* RISK BARS */

.risk-row {

    margin-bottom: 18px;

}


.risk-info {

    display: flex;

    justify-content: space-between;

    margin-bottom: 7px;

    font-size: 10px;

}


.risk-info span {

    color: #91a5b9;

}


.bar {

    width: 100%;

    height: 8px;

    border-radius: 10px;

    background: #102640;

    overflow: hidden;

}


.bar-fill {

    height: 100%;

    border-radius: 10px;

}


.high {
    background: #ef476f;
}


.medium {
    background: #f59e0b;
}


.low {
    background: #2196f3;
}


.safe {
    background: #16c784;
}


/* SCORE */

.score-box {

    display: flex;

    align-items: center;

    gap: 25px;

}


.score-circle {

    width: 135px;
    height: 135px;

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


.score-circle::before {

    content: "";

    position: absolute;

    width: 105px;
    height: 105px;

    border-radius: 50%;

    background: #061426;

}


.score-value {

    position: relative;

    z-index: 2;

    text-align: center;

}


.score-value strong {

    display: block;

    font-size: 27px;

}


.score-value span {

    color: #34d399;

    font-size: 9px;

}


.score-text h3 {

    margin: 0 0 8px;

    color: #22d3ee;

}


.score-text p {

    margin: 0;

    color: #8196ab;

    font-size: 10px;

    line-height: 1.6;

}


/* TABLE */

.table-card {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

}


th {

    padding: 13px;

    text-align: left;

    color: #5f7892;

    font-size: 9px;

    letter-spacing: .5px;

    border-bottom:
        1px solid
        rgba(96,165,250,.10);

}


td {

    padding: 14px 13px;

    color: #a9bacb;

    font-size: 10px;

    border-bottom:
        1px solid
        rgba(96,165,250,.06);

}


.scan-text {

    max-width: 320px;

    overflow: hidden;

    white-space: nowrap;

    text-overflow: ellipsis;

}


.badge {

    display: inline-block;

    padding:
        5px 9px;

    border-radius: 20px;

    font-size: 8px;

    font-weight: bold;

}


.badge-safe {

    color: #34d399;

    background:
        rgba(16,185,129,.12);

}


.badge-low {

    color: #60a5fa;

    background:
        rgba(37,99,235,.12);

}


.badge-medium {

    color: #fbbf24;

    background:
        rgba(245,158,11,.12);

}


.badge-high {

    color: #fb7185;

    background:
        rgba(239,68,68,.12);

}


/* EMPTY */

.empty {

    padding: 40px;

    text-align: center;

    color: #617b95;

}


.empty-icon {

    font-size: 35px;

    margin-bottom: 10px;

}


.empty p {

    font-size: 10px;

}


/* RESPONSIVE */

@media(max-width:1000px) {

    .summary {

        grid-template-columns:
            repeat(2,1fr);

    }

    .report-grid {

        grid-template-columns: 1fr;

    }

}


@media(max-width:750px) {

    .sidebar {

        width: 75px;

        padding:
            20px 10px;

    }

    .brand h2,
    .brand p,
    .nav-title,
    .nav-text,
    .status,
    .logout-text {

        display: none;

    }

    .brand {

        justify-content: center;

    }

    .nav-item {

        justify-content: center;

        padding: 0;

    }

    .main {

        margin-left: 75px;

        padding: 25px 15px;

    }

}


@media(max-width:550px) {

    .summary {

        grid-template-columns: 1fr;

    }

    .header {

        align-items: flex-start;

    }

    .user {

        display: none;

    }

    .score-box {

        flex-direction: column;

    }

}

</style>

</head>


<body>


<!-- SIDEBAR -->

<aside class="sidebar">


    <div class="brand">

        <div class="brand-logo">
            🛡️
        </div>

        <div>

            <h2>
                Privacy<span>Guard</span>
            </h2>

            <p>
                PRIVACY ASSISTANT
            </p>

        </div>

    </div>


    <div class="nav-title">
        MAIN MENU
    </div>


    <a
        href="dashboard.php"
        class="nav-item"
    >

        <span class="nav-icon">🏠</span>

        <span class="nav-text">
            Dashboard
        </span>

    </a>


    <a
        href="scan.php"
        class="nav-item"
    >

        <span class="nav-icon">🔎</span>

        <span class="nav-text">
            Privacy Scanner
        </span>

    </a>


    <a
        href="antivirus.php"
        class="nav-item"
    >

        <span class="nav-icon">⚡</span>

        <span class="nav-text">
            Antivirus & Camera
        </span>

    </a>


    <a
        href="history.php"
        class="nav-item"
    >

        <span class="nav-icon">🕘</span>

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
        class="nav-item active"
    >

        <span class="nav-icon">📊</span>

        <span class="nav-text">
            Reports
        </span>

    </a>


    <a
        href="settings.php"
        class="nav-item"
    >

        <span class="nav-icon">⚙️</span>

        <span class="nav-text">
            Settings
        </span>

    </a>


    <div class="sidebar-bottom">

        <div class="status">
            🛡️ Privacy Protected
        </div>

        <a
            href="logout.php"
            class="logout"
        >

            <span>⏻</span>

            <span class="logout-text">
                Logout
            </span>

        </a>

    </div>


</aside>



<!-- MAIN -->

<main class="main">


    <div class="header">

        <div>

            <div class="small-title">
                PRIVACY ANALYTICS
            </div>

            <h1>
                Reports
            </h1>

            <p>
                Detailed overview of your privacy scanning activity.
            </p>

        </div>


        <div class="user">

            <div class="avatar">

                <?php
                echo strtoupper(
                    substr(
                        trim($user_name),
                        0,
                        1
                    )
                );
                ?>

            </div>

            <span>
                <?php
                echo htmlspecialchars(
                    $user_name
                );
                ?>
            </span>

        </div>

    </div>



    <!-- HERO -->

    <section class="report-hero">

        <h2>
            Your Privacy
            <span>Security Report</span>
        </h2>

        <p>
            Review detected risks, scan statistics and
            your overall privacy protection score.
        </p>

    </section>



    <!-- SUMMARY -->

    <section class="summary">


        <div class="summary-card">

            <div
                class="summary-icon"
                style="
                    background:rgba(37,99,235,.16);
                "
            >
                🔎
            </div>

            <h2>
                <?php
                echo $total_scans;
                ?>
            </h2>

            <p>
                Total Scans
            </p>

        </div>


        <div class="summary-card">

            <div
                class="summary-icon"
                style="
                    background:rgba(16,185,129,.16);
                "
            >
                🛡️
            </div>

            <h2>
                <?php
                echo $safe_scans;
                ?>
            </h2>

            <p>
                Safe Scans
            </p>

        </div>


        <div class="summary-card">

            <div
                class="summary-icon"
                style="
                    background:rgba(239,68,68,.16);
                "
            >
                ⚠️
            </div>

            <h2>
                <?php
                echo $high_risk;
                ?>
            </h2>

            <p>
                High Risk
            </p>

        </div>


        <div class="summary-card">

            <div
                class="summary-icon"
                style="
                    background:rgba(139,92,246,.16);
                "
            >
                🔐
            </div>

            <h2>
                <?php
                echo $privacy_score;
                ?>%
            </h2>

            <p>
                Privacy Score
            </p>

        </div>


    </section>



    <!-- REPORT GRID -->

    <section class="report-grid">


        <!-- RISK DISTRIBUTION -->

        <div class="card">

            <div class="card-title">
                📊 Risk Distribution
            </div>


            <div class="risk-row">

                <div class="risk-info">

                    <span>
                        High Risk
                    </span>

                    <strong>
                        <?php
                        echo $high_risk;
                        ?>
                    </strong>

                </div>

                <div class="bar">

                    <div
                        class="bar-fill high"
                        style="
                            width:
                            <?php
                            echo $total_scans > 0
                                ? ($high_risk / $total_scans) * 100
                                : 0;
                            ?>%;
                        "
                    ></div>

                </div>

            </div>


            <div class="risk-row">

                <div class="risk-info">

                    <span>
                        Medium Risk
                    </span>

                    <strong>
                        <?php
                        echo $medium_risk;
                        ?>
                    </strong>

                </div>

                <div class="bar">

                    <div
                        class="bar-fill medium"
                        style="
                            width:
                            <?php
                            echo $total_scans > 0
                                ? ($medium_risk / $total_scans) * 100
                                : 0;
                            ?>%;
                        "
                    ></div>

                </div>

            </div>


            <div class="risk-row">

                <div class="risk-info">

                    <span>
                        Low Risk
                    </span>

                    <strong>
                        <?php
                        echo $low_risk;
                        ?>
                    </strong>

                </div>

                <div class="bar">

                    <div
                        class="bar-fill low"
                        style="
                            width:
                            <?php
                            echo $total_scans > 0
                                ? ($low_risk / $total_scans) * 100
                                : 0;
                            ?>%;
                        "
                    ></div>

                </div>

            </div>


            <div class="risk-row">

                <div class="risk-info">

                    <span>
                        Safe
                    </span>

                    <strong>
                        <?php
                        echo $safe_scans;
                        ?>
                    </strong>

                </div>

                <div class="bar">

                    <div
                        class="bar-fill safe"
                        style="
                            width:
                            <?php
                            echo $total_scans > 0
                                ? ($safe_scans / $total_scans) * 100
                                : 0;
                            ?>%;
                        "
                    ></div>

                </div>

            </div>


        </div>



        <!-- PRIVACY SCORE -->

        <div class="card">

            <div class="card-title">
                🛡️ Privacy Score
            </div>


            <div class="score-box">


                <div class="score-circle">

                    <div class="score-value">

                        <strong>
                            <?php
                            echo $privacy_score;
                            ?>%
                        </strong>

                        <span>
                            Protected
                        </span>

                    </div>

                </div>


                <div class="score-text">

                    <h3>

                        <?php

                        if (
                            $privacy_score >= 80
                        ) {

                            echo "Excellent";

                        } elseif (
                            $privacy_score >= 60
                        ) {

                            echo "Moderate";

                        } else {

                            echo "Needs Attention";

                        }

                        ?>

                    </h3>


                    <p>

                        Your privacy score is calculated
                        from the risk level of your previous
                        scans.

                    </p>

                </div>


            </div>


        </div>


    </section>



    <!-- TABLE -->

    <section class="card table-card">


        <div class="card-title">
            🕘 Scan Report History
        </div>


        <?php if ($all_scans->num_rows > 0): ?>


        <table>


            <thead>

                <tr>

                    <th>
                        #
                    </th>

                    <th>
                        SCANNED TEXT
                    </th>

                    <th>
                        SCORE
                    </th>

                    <th>
                        RISK LEVEL
                    </th>

                    <th>
                        DATE
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php

            $count = 1;

            while (
                $scan =
                $all_scans->fetch_assoc()
            ):

                $level =
                    strtoupper(
                        $scan["risk_level"]
                    );

                $badge =
                    "badge-safe";

                if ($level === "HIGH") {

                    $badge =
                        "badge-high";

                } elseif (
                    $level === "MEDIUM"
                ) {

                    $badge =
                        "badge-medium";

                } elseif (
                    $level === "LOW"
                ) {

                    $badge =
                        "badge-low";

                }

            ?>


                <tr>


                    <td>
                        <?php
                        echo $count++;
                        ?>
                    </td>


                    <td>

                        <div class="scan-text">

                            <?php

                            echo htmlspecialchars(
                                $scan["original_text"]
                            );

                            ?>

                        </div>

                    </td>


                    <td>

                        <?php
                        echo $scan["risk_score"];
                        ?>

                    </td>


                    <td>

                        <span
                            class="
                            badge
                            <?php
                            echo $badge;
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $level
                            );
                            ?>

                        </span>

                    </td>


                    <td>

                        <?php

                        echo date(
                            "d M Y, h:i A",
                            strtotime(
                                $scan["created_at"]
                            )
                        );

                        ?>

                    </td>


                </tr>


            <?php endwhile; ?>


            </tbody>


        </table>


        <?php else: ?>


            <div class="empty">

                <div class="empty-icon">
                    📊
                </div>

                <h3>
                    No Reports Available
                </h3>

                <p>
                    Complete your first privacy scan
                    to generate a report.
                </p>

            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>