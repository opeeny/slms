<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

// TEMP demo values
$adminName  = "Admin admin";
$adminEmail = "admin@gmail.com";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Staff | SLMS</title>

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
                <a class="nav-link" href="dashboard.php">
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

            <!-- STAFF DROPDOWN -->
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center active"
                   data-bs-toggle="collapse" href="#staffMenu">
                    <span><i class="bi bi-people"></i> Staff</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>

                <ul class="nav flex-column ms-3 collapse show" id="staffMenu">
                    <li class="nav-item">
                        <a class="nav-link active" href="addstaff.php">
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

        <!-- SIDEBAR FOOTER -->
        <div class="sidebar-footer">
            <p>SLMS &copy; <?=date('Y');?> </p>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content p-4 w-100">

        <div class="bg-white p-4 rounded shadow-sm mx-auto" style="max-width: 900px;">
            <h5 class="text-center text-primary mb-4">Add Staff Details</h5>

            <!-- ✅ SUCCESS MESSAGE (ONLY ADDITION) -->
            <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?= $_SESSION['flash_success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= $_SESSION['flash_error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>


            <form method="POST" action="addstaff_process.php" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Profile Pic</label>
                    <input type="file" name="profile_pic" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Service Number</label>
                    <input type="text" name="service_number" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rank</label>
                    <input type="text" name="rank" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success px-4">
                    ADD STAFF
                </button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
