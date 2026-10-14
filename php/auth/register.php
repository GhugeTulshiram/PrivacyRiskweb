<?php

require_once __DIR__ . "/../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../register.php");
    exit;
}

$name = trim($_POST["name"] ?? "");
$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";


/* Check empty fields */

if (
    empty($name) ||
    empty($username) ||
    empty($email) ||
    empty($password) ||
    empty($confirm_password)
) {
    header("Location: ../../register.php?error=Please fill all fields");
    exit;
}


/* Check email */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../../register.php?error=Invalid email address");
    exit;
}


/* Check password */

if ($password !== $confirm_password) {
    header("Location: ../../register.php?error=Passwords do not match");
    exit;
}


/* Check password length */

if (strlen($password) < 6) {
    header("Location: ../../register.php?error=Password must contain at least 6 characters");
    exit;
}


/* Check existing username or email */

$check = $conn->prepare(
    "SELECT id FROM users WHERE username = ? OR email = ?"
);

$check->bind_param("ss", $username, $email);

$check->execute();

$result = $check->get_result();


if ($result->num_rows > 0) {

    header(
        "Location: ../../register.php?error=Username or email already exists"
    );

    exit;
}


/* Encrypt password */

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* Insert user */

$stmt = $conn->prepare(
    "INSERT INTO users
    (name, username, email, password)
    VALUES (?, ?, ?, ?)"
);

$stmt->bind_param(
    "ssss",
    $name,
    $username,
    $email,
    $hashed_password
);


if ($stmt->execute()) {

    header(
        "Location: ../../index.php?success=Account Created Successfully"
    );

    exit;

} else {

    header(
        "Location: ../../register.php?error=Registration failed"
    );

    exit;
}

?>