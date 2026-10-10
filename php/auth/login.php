<?php

session_start();

require_once "../config/db.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../../index.php");

    exit;
}


$email = trim($_POST["email"] ?? "");

$password = $_POST["password"] ?? "";


if (empty($email) || empty($password)) {

    header(
        "Location: ../../index.php?error=Please enter email and password"
    );

    exit;
}


$stmt = $conn->prepare(
    "SELECT id, name, username, email, password
     FROM users
     WHERE email = ?"
);


$stmt->bind_param("s", $email);

$stmt->execute();


$result = $stmt->get_result();


if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();


    if (password_verify($password, $user["password"])) {


        $_SESSION["user_id"] = $user["id"];

        $_SESSION["user_name"] = $user["name"];

        $_SESSION["username"] = $user["username"];

        $_SESSION["user_email"] = $user["email"];


        header("Location: ../../dashboard.php");

        exit;

    }

}


/* Login failed */

header(
    "Location: ../../index.php?error=Invalid email or password"
);

exit;

?>