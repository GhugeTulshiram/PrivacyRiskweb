<?php

session_start();

require_once __DIR__ . "/php/config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT id, original_text, masked_text, risk_score, risk_level, created_at
     FROM scans
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PrivacyGuard | Scan History</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">

    <style>

        .history-page {
            padding: 30px;
        }

        .history-header {
            margin-bottom: 25px;
        }

        .history-header h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .history-header p {
            color: #94a3b8;
        }

        .history-card {
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px;
            overflow: hidden;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            text-align: left;
            padding: 18px;
            background: rgba(255,255,255,0.04);
            color: #94a3b8;
            font-size: 13px;
        }

        .history-table td {
            padding: 18px;
            border-top: 1px solid rgba(255,255,255,0.06);
            color: #cbd5e1;
        }

        .scan-text {
            max-width: 350px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .risk-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .safe {
            background: rgba(34,197,94,0.12);
            color: #4ade80;
        }

        .low {
            background: rgba(132,204,22,0.12);
            color: #a3e635;
        }

        .medium {
            background: rgba(234,179,8,0.12);
            color: #facc15;
        }

        .high {
            background: rgba(239,68,68,0.12);
            color: #f87171;
        }

        .empty-history {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-history .icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .empty-history h3 {
            margin-bottom: 8px;
        }

        .empty-history p {
            color: #94a3b8;
            margin-bottom: 20px;
        }

        .scan-btn {
            display: inline-block;
            text-decoration: none;
            background: #2563eb;
            color: white;
            padding: 11px 18px;
            border-radius: 9px;
            font-weight: 600;
        }

        @media (max-width: 850px) {

            .history-page {
                padding: 20px;
            }

            .history-card {
                overflow-x: auto;
            }

            .history-table {
                min-width: 750px;
            }

        }

    </style>

</head>

<body>

<div class="dashboard-layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="sidebar-brand">

            <div class="brand-icon">
                🛡️
            </div>

            <div>
                <strong>PrivacyGuard</strong>
                <span>Privacy Assistant</span>
            </div>

        </div>

        <nav class="sidebar-nav">

            <a href="dashboard.php">
                📊 Dashboard
            </a>

            <a href="scan.php">
                🔎 Privacy Scanner
            </a>

            <a href="antivirus.php">
                ⚡ Antivirus & Camera
            </a>

            <a href="history.php" class="active">
                🕘 Scan History
            </a>

            <a href="reports.php">
                📈 Reports
            </a>

            <a href="settings.php">
                ⚙️ Settings
            </a>

        </nav>

        <div class="sidebar-bottom">

            <a href="logout.php">
                🚪 Logout
            </a>

        </div>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="dashboard-main">

        <div class="history-page">

            <div class="history-header">

                <h1>
                    Scan History
                </h1>

                <p>
                    View your previous privacy scans and risk levels.
                </p>

            </div>


            <div class="history-card">

                <?php if ($result->num_rows > 0): ?>

                    <table class="history-table">

                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Scanned Text
                                </th>

                                <th>
                                    Risk Score
                                </th>

                                <th>
                                    Risk Level
                                </th>

                                <th>
                                    Date
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $count = 1;

                            while ($row = $result->fetch_assoc()):

                                $level = strtolower(
                                    $row["risk_level"]
                                );

                            ?>

                                <tr>

                                    <td>
                                        <?php echo $count++; ?>
                                    </td>

                                    <td>

                                        <div class="scan-text">

                                            <?php

                                            echo htmlspecialchars(
                                                $row["original_text"]
                                            );

                                            ?>

                                        </div>

                                    </td>

                                    <td>

                                        <?php

                                        echo $row["risk_score"];

                                        ?>

                                        / 100

                                    </td>

                                    <td>

                                        <span
                                            class="risk-badge <?php echo $level; ?>"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $row["risk_level"]
                                            );

                                            ?>

                                        </span>

                                    </td>

                                    <td>

                                        <?php

                                        echo date(
                                            "d M Y, h:i A",
                                            strtotime(
                                                $row["created_at"]
                                            )
                                        );

                                        ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="empty-history">

                        <div class="icon">
                            🕘
                        </div>

                        <h3>
                            No Scan History
                        </h3>

                        <p>
                            You haven't performed any privacy scans yet.
                        </p>

                        <a
                            href="scan.php"
                            class="scan-btn"
                        >
                            🔎 Start Your First Scan
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </main>

</div>

</body>

</html>