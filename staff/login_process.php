<?php
session_start();
require_once "../config/db.php";

/* =========================
   BASIC VALIDATION
========================= */
if (empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['error'] = "Email and password are required.";
    header("Location: login.php");
    exit();
}

$email    = trim($_POST['email']);
$password = $_POST['password'];

/* =========================
   FETCH USER + ROLE
========================= */
$sql = "
SELECT 
    u.user_id,
    u.password,
    u.status,
    r.role_name
FROM users u
JOIN roles r ON u.role_id = r.role_id
WHERE u.email = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

/* =========================
   USER EXISTS CHECK
========================= */
if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

/* =========================
   STATUS CHECK
========================= */
if ($user['status'] !== 'ACTIVE') {
    $_SESSION['error'] = "Account is inactive.";
    header("Location: login.php");
    exit();
}

/* =========================
   ROLE CHECK (STAFF ONLY)
========================= */
if ($user['role_name'] !== 'STAFF') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: login.php");
    exit();
}

/* =========================
   PASSWORD VERIFY
========================= */
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login.php");
    exit();
}

/* =========================
   SUCCESS → SET SESSION
========================= */
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role']    = $user['role_name']; // STAFF

header("Location: dashboard.php");
exit();
