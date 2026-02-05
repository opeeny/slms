<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}
#require_once "../includes/dashboard_stats.php";

// TEMP demo values (to be replaced later with DB queries)
$adminName  = "Admin";
$adminEmail = "admin@gmail.com";
$totalStaff = 84;
$totalLeave = 8;
$stillOnLeave=6;
$sickLeave=3;
$passLeave=2;
$annuaLeave=4;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | SLMS</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- TOP BAR -->
<div class="topbar d-flex align-items-center justify-content-between px-4">
    <span class="fw-semibold text-white">STAFF LEAVE MANAGEMENT SYSTEM</span>

    <a href="../auth/logout.php" class="text-white" title="Logout">
        <i class="bi bi-box-arrow-right fs-5"></i>
    </a>
</div>

<!-- MAIN WRAPPER -->
<div class="d-flex">

    <!-- SIDEBAR -->
    <div class="sidebar p-3">

        <div class="profile text-center mb-4">
            <img src="../assets/images/ui.jpg" class="profile-img mb-2" alt="Admin">
            <div class="fw-semibold"><?= $adminName ?></div>
            <small class="text-muted">(<?= $adminEmail ?>)</small>
        </div>

        <ul class="nav flex-column sidebar-menu">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-person"></i> My Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-lock"></i> Change Password
                </a>
            </li>

            <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#staffMenu">
                <span><i class="bi bi-people"></i> Staff</span>
                <i class="bi bi-chevron-down small"></i>
            </a>

            <ul class="nav flex-column ms-3 collapse" id="staffMenu">
                <li class="nav-item">
                    <a class="nav-link" href="addstaff.php">
                        <i class="bi bi-person-plus"></i> Add Staff
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="managestaff.php">
                        <i class="bi bi-list-check"></i> Manage Staff
                    </a>
                </li>
            </ul>
            </li>
        <!-- staff --->

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-calendar-check"></i> Staff Leave
                </a>
            </li>

            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="../auth/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </a>
            </li>
        </ul>

        <!-- ✅ SIDEBAR FOOTER (CORRECT POSITION) -->
        <div class="sidebar-footer">
            <p>SLMS &copy; <?= date('Y'); ?></p>
        </div>

    </div>

    <!-- CONTENT -->
    <div class="content p-4 w-100">
        <h4 class="mb-4 text-primary">Welcome <?= $adminName ?>!</h4>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-title">TOTAL REGISTERED STAFF</div>
                    <div class="stat-value"><?= $totalStaff ?></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-title">TOTAL LEAVE RECIEVED/REQUESTS(PENDING)</div>
                    <div class="stat-value"><?= $totalLeave ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-title">TOTAL NUMBER OF STAFF ON LEAVE </div>
                    <div class="stat-value"><?= $stillOnLeave ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-title">TOTAL NUMBER ON ANNUAL-LEAVE </div>
                    <div class="stat-value"><?= $annuaLeave ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-title">TOTAL NUMBER  ON PASS-LEAVE </div>
                    <div class="stat-value"><?= $passLeave ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-title">TOTAL NUMBER  ON SICK-LEAVE </div>
                    <div class="stat-value"><?= $sickLeave ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
