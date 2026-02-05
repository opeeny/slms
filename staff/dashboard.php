<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'STAFF') {
    header("Location: login.php");
    exit();
}

// TEMP demo values (replace later with DB queries)
$staffName  = "Dungu Fahad";
$staffEmail = "dungu@gmail.com";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard | SLMS</title>

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
            <img src="../assets/images/ui.jpg" class="profile-img mb-2" alt="Staff">
            <div class="fw-semibold"><?= $staffName ?></div>
            <small class="text-muted">(<?= $staffEmail ?>)</small>
        </div>

        <ul class="nav flex-column sidebar-menu">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="apply_leave.php">
                    <i class="bi bi-calendar-plus"></i> Apply For Leave
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="leave_history.php">
                    <i class="bi bi-clock-history"></i> Apply Leave History
                </a>
            </li>

            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="../auth/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </a>
            </li>
        </ul>

        <!-- SIDEBAR FOOTER (same as admin) -->
        <div class="sidebar-footer">
            <p>SLMS &copy; v1.0</p>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content p-4 w-100">
        <h4 class="mb-4 text-primary">Welcome <?= $staffName ?>!</h4>

        <div class="row">
            <div class="col-md-12">
                <div class="stat-card">
                    <div class="stat-title">Staff Dashboard</div>
                    <p class="mb-0 text-muted">
                        Use the menu on the left to apply for leave and track your leave history.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
