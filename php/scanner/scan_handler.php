<?php

session_start();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/privacy_scanner.php";
require_once __DIR__ . "/risk_analyzer.php";
require_once __DIR__ . "/masking_service.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../scan.php");
    exit;
}

$text = trim($_POST["scan_text"] ?? "");

if (empty($text)) {
    header("Location: ../../scan.php?error=Please enter some text to scan");
    exit;
}


/* Detect sensitive information */

$detected = detectSensitiveData($text);


/* Calculate original risk */

$risk = calculateRisk($detected);

$risk_score = $risk["score"];
$risk_level = $risk["level"];
$risk_message = $risk["message"];
$solutions = $risk["solutions"];


/* Create protected version */

$masked_text = maskSensitiveData(
    $text,
    $detected
);


/* Save scan in database */

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "INSERT INTO scans
    (user_id, original_text, masked_text, risk_score, risk_level)
    VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "issis",
    $user_id,
    $text,
    $masked_text,
    $risk_score,
    $risk_level
);

$stmt->execute();

$stmt->close();


/* Store complete result in session */

$_SESSION["scan_result"] = [

    "original_text" => $text,

    "masked_text" => $masked_text,

    "detected" => $detected,

    "risk_score" => $risk_score,

    "risk_level" => $risk_level,

    "risk_message" => $risk_message,

    "solutions" => $solutions

];


header("Location: ../../scan_result.php");

exit;

?>