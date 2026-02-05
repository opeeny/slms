<?php
session_start();
require_once "../config/db.php";

/* =========================
   Basic Input Validation
========================= */
if (empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['error'] = "Email and password are required.";
    header("Location: login.php");
    exit();
}

$email    = trim($_POST['email']);
$password = $_POST['password'];

/* =========================
   Fetch Admin User
========================= */
$sql = "SELECT * FROM users 
        WHERE email = ? 
        AND role = 'STAFF' 
        AND status = 'ACTIVE'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

/* =========================
   User Exists Check
========================= */
if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Invalid email or account not active.";
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

/* =========================
   Password Verification
========================= */
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Incorrect password.";
    header("Location: login.php");
    exit();
}

/* =========================
   Successful Login
========================= */
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];

header("Location: dashboard.php");
exit();
