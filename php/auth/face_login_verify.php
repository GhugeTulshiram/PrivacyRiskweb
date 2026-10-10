<?php

session_start();

require_once __DIR__ . "/../config/db.php";

/*
|--------------------------------------------------------------------------
| Redirect helper
|--------------------------------------------------------------------------
*/

function redirectWithError($message)
{
    header(
        "Location: ../../face_login.php?error=" .
        urlencode($message)
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Only POST requests are allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Read submitted data
|--------------------------------------------------------------------------
*/

$email = trim($_POST["email"] ?? "");
$descriptorJson = $_POST["descriptor"] ?? "";

if ($email === "" || $descriptorJson === "") {
    redirectWithError("Email and face verification data are required.");
}

/*
|--------------------------------------------------------------------------
| Validate descriptor JSON
|--------------------------------------------------------------------------
*/

$submittedDescriptor = json_decode($descriptorJson, true);

if (
    !is_array($submittedDescriptor) ||
    count($submittedDescriptor) !== 128
) {
    redirectWithError("Invalid face data. Please try again.");
}

foreach ($submittedDescriptor as $value) {
    if (!is_numeric($value) || !is_finite((float)$value)) {
        redirectWithError("Invalid face descriptor.");
    }
}

/*
|--------------------------------------------------------------------------
| Get user by email and retrieve registered face descriptor
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        u.id,
        u.name,
        u.username,
        u.email,
        f.face_encoding
    FROM users u
    INNER JOIN face_auth f ON f.user_id = u.id
    WHERE u.email = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    redirectWithError("Unable to verify face at this time.");
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    redirectWithError("No registered face found for this email.");
}

$user = $result->fetch_assoc();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Decode registered descriptor
|--------------------------------------------------------------------------
*/

$storedDescriptor = json_decode(
    $user["face_encoding"],
    true
);

if (
    !is_array($storedDescriptor) ||
    count($storedDescriptor) !== 128
) {
    redirectWithError("Registered face data is invalid. Register again.");
}

foreach ($storedDescriptor as $value) {
    if (!is_numeric($value) || !is_finite((float)$value)) {
        redirectWithError("Registered face data is invalid.");
    }
}

/*
|--------------------------------------------------------------------------
| Calculate Euclidean distance
|--------------------------------------------------------------------------
*/

$sum = 0.0;

for ($i = 0; $i < 128; $i++) {
    $difference =
        (float)$submittedDescriptor[$i] -
        (float)$storedDescriptor[$i];

    $sum += $difference * $difference;
}

$distance = sqrt($sum);

/*
|--------------------------------------------------------------------------
| Compare descriptors
|--------------------------------------------------------------------------
|
| This threshold is a starting point for a classroom prototype.
| It must be tested with your camera, model, and registered users.
|
*/

$threshold = 0.50;

if ($distance > $threshold) {
    redirectWithError("Face did not match. Please try again.");
}

/*
|--------------------------------------------------------------------------
| Successful face verification
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["username"] = $user["username"];
$_SESSION["user_email"] = $user["email"];

/*
|--------------------------------------------------------------------------
| Redirect to dashboard
|--------------------------------------------------------------------------
*/

header("Location: ../../dashboard.php");
exit;

?>
