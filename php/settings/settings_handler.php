<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../settings.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$email_alerts = isset($_POST["email_alerts"]) ? 1 : 0;
$auto_masking = isset($_POST["auto_masking"]) ? 1 : 0;


/* Check whether settings already exist */

$check = $conn->prepare(
    "SELECT id FROM settings WHERE user_id = ?"
);

$check->bind_param("i", $user_id);
$check->execute();

$result = $check->get_result();


/* Update existing settings */

if ($result->num_rows > 0) {

    $stmt = $conn->prepare(
        "UPDATE settings
         SET email_alerts = ?, auto_masking = ?
         WHERE user_id = ?"
    );

    $stmt->bind_param(
        "iii",
        $email_alerts,
        $auto_masking,
        $user_id
    );

    if ($stmt->execute()) {

        header(
            "Location: ../../settings.php?success=Privacy preferences saved successfully"
        );

    } else {

        header(
            "Location: ../../settings.php?error=Unable to save preferences"
        );
    }

    $stmt->close();

}


/* Insert settings for first time */

else {

    $stmt = $conn->prepare(
        "INSERT INTO settings
        (user_id, email_alerts, auto_masking)
        VALUES (?, ?, ?)"
    );

    $stmt->bind_param(
        "iii",
        $user_id,
        $email_alerts,
        $auto_masking
    );

    if ($stmt->execute()) {

        header(
            "Location: ../../settings.php?success=Privacy preferences saved successfully"
        );

    } else {

        header(
            "Location: ../../settings.php?error=Unable to save preferences"
        );
    }

    $stmt->close();
}

$check->close();

exit;

?>