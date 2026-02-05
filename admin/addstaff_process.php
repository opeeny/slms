<?php
session_start();
require_once "../config/db.php";

/* ======================
   AUTH CHECK
====================== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: addstaff.php");
    exit();
}

/* ======================
   COLLECT INPUT
====================== */
$serviceNumber = trim($_POST['service_number']);
$rank          = trim($_POST['rank']);
$firstName     = trim($_POST['first_name']);
$lastName      = trim($_POST['last_name']);
$email         = trim($_POST['email']);
$password      = $_POST['password'];

$fullName = $firstName . ' ' . $lastName;

/* ======================
   VALIDATION
====================== */
if (!$serviceNumber || !$rank || !$firstName || !$lastName || !$email || !$password) {
    die("All fields are required.");
}

/* ======================
   PROFILE PIC (OPTIONAL, SAFE)
====================== */
$profilePic = null;

if (!empty($_FILES['profile_pic']['name'])) {
    $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png'];

    if (!in_array($ext, $allowed)) {
        die("Invalid image type.");
    }

    $dir = "../uploads/profiles/";
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $profilePic = uniqid("staff_", true) . "." . $ext;
    move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dir . $profilePic);
}

/* ======================
   GET STAFF ROLE ID
====================== */
$roleStmt = $conn->prepare("SELECT role_id FROM roles WHERE role_name='STAFF' LIMIT 1");
$roleStmt->execute();
$role = $roleStmt->get_result()->fetch_assoc();

if (!$role) die("STAFF role missing.");

$roleId = $role['role_id'];

/* ======================
   HASH PASSWORD
====================== */
$hashed = password_hash($password, PASSWORD_DEFAULT);

/* ======================
   INSERT STAFF
====================== */
$sql = "
INSERT INTO users
(service_number, profile_pic, first_name, last_name, full_name, email, password, role_id, rank, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE')
";

$stmt = $conn->prepare($sql);
if (!$stmt) die($conn->error);

$stmt->bind_param(
    "sssssssis",
    $serviceNumber,
    $profilePic,
    $firstName,
    $lastName,
    $fullName,
    $email,
    $hashed,
    $roleId,
    $rank
);

/* ======================
   EXECUTE INSERT
====================== */
if ($stmt->execute()) {
    $_SESSION['flash_success'] = "Staff successfully added.";
} else {
    $_SESSION['flash_error'] = "Failed to add staff. Please try again.";
}

header("Location: addstaff.php");
exit();
