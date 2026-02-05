<?php
session_start();
require_once "../config/db.php";

/* ======================
   BASIC VALIDATION
====================== */
if (empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['error'] = "Email and password are required.";
    header("Location: login.php");
    exit();
}

$email    = trim($_POST['email']);
$password = $_POST['password'];

/* ======================
   ADMIN LOGIN QUERY (RBAC)
====================== */
$sql = "
    SELECT u.user_id, u.password, r.role_name
    FROM users u
    INNER JOIN roles r ON u.role_id = r.role_id
    WHERE u.email = ?
      AND r.role_name = 'ADMIN'
      AND u.status = 'ACTIVE'
    LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Invalid email or account not active.";
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

/* ======================
   PASSWORD VERIFY
====================== */
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Incorrect password.";
    header("Location: login.php");
    exit();
}

/* ======================
   LOGIN SUCCESS
====================== */
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role']    = $user['role_name'];

header("Location: dashboard.php");
exit();
