<?php
session_start();
require_once "../config/db.php";

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'STAFF') {
    header("Location: login.php");
    exit();
}

/* =========================
   FETCH STAFF DETAILS
========================= */
$userId = $_SESSION['user_id'];

$sql = "SELECT full_name, email, profile_pic FROM users WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

$staffName  = $user['full_name'];
$staffEmail = $user['email'];
$profilePic = $user['profile_pic'];

$profilePicPath = (!empty($profilePic) && file_exists("../uploads/profiles/" . $profilePic))
    ? "../uploads/profiles/" . $profilePic
    : "../assets/images/ui.jpg";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply For Leave | SLMS</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f5f5f5;
    margin: 0;
}

/* Top Header */
.top-header {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 70px;
    z-index: 1000;
}

.top-header .menu-toggle {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
}

/* Sidebar */
.sidebar {
    position: fixed;
    top: 70px;
    left: 0;
    width: 250px;
    height: calc(100vh - 70px);
    background: #fff;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    transition: transform .3s ease;
}

.sidebar.collapsed {
    transform: translateX(-250px);
}

.sidebar-profile {
    text-align: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.sidebar-profile img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid #17a2b8;
    object-fit: cover;
}

.sidebar-menu {
    flex: 1;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 15px 25px;
    color: #555;
    text-decoration: none;
}

.sidebar-menu a.active,
.sidebar-menu a:hover {
    background: #f0f9fa;
    color: #17a2b8;
    border-left: 3px solid #17a2b8;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 15px;
    text-align: center;
    font-size: 13px;
    color: #777;
    border-top: 1px solid #eee;
}

/* Main Content */
.main-content {
    margin-left: 250px;
    margin-top: 70px;
    padding: 30px;
    min-height: calc(100vh - 140px);
    transition: margin-left .3s ease;
}

.main-content.expanded {
    margin-left: 0;
}

/* Footer */
.footer {
    position: fixed;
    bottom: 0;
    left: 250px;
    right: 0;
    background: white;
    padding: 15px;
    border-top: 1px solid #ddd;
    text-align: center;
    transition: left .3s ease;
}

.footer.expanded {
    left: 0;
}

/* Form Card */
.form-card {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
    max-width: 800px;
    margin: 0 auto; /* ✅ CENTER CARD */
}
</style>
</head>

<body>

<!-- TOP HEADER -->
<div class="top-header">
    <div class="d-flex align-items-center gap-3">
        <button class="menu-toggle" id="menuToggle">
            <i class="bi bi-list"></i>
        </button>
        <span>STAFF LEAVE MANAGEMENT SYSTEM</span>
    </div>

    <div class="header-right">
        <i class="bi bi-bell fs-5" title="Notifications"></i>
        <a href="../auth/logout.php" class="text-white">
            <i class="bi bi-box-arrow-right fs-5"></i>
        </a>
    </div>
</div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-profile">
        <img src="<?= htmlspecialchars($profilePicPath) ?>" alt="Staff">
        <div class="fw-semibold mt-2"><?= htmlspecialchars($staffName) ?></div>
        <small class="text-muted">(<?= htmlspecialchars($staffEmail) ?>)</small>
    </div>

    <ul class="sidebar-menu list-unstyled">
        <li><a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
        <li><a href="apply_leave.php" class="active"><i class="bi bi-calendar-plus me-2"></i> Apply For Leave</a></li>
        <li><a href="leave_history.php"><i class="bi bi-clock-history me-2"></i> Leave History</a></li>
        <li><a href="../auth/logout.php" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
    </ul>

    <div class="sidebar-footer">SLMS©</div>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content" id="mainContent">

    <div class="form-card">
        <h5 class="text-primary mb-4 text-center">Apply For Leave</h5>

        <form method="POST" action="apply_leave_process.php">

            <div class="mb-3">
                <label class="form-label">Leave Type</label>
                <select name="leave_type" class="form-select" required>
                    <option value="">-- Select Leave Type --</option>
                    <option value="ANNUAL">Annual Leave</option>
                    <option value="SICK">Sick Leave</option>
                    <option value="PASS">Pass Leave</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">From Date</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">To Date</label>
                <input type="date" name="end_date" class="form-control" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="4"></textarea>
            </div>

            <!-- ✅ CENTERED BUTTON -->
            <div class="text-center">
                <button type="submit" class="btn btn-info text-white px-5">
                    APPLY LEAVE
                </button>
            </div>

        </form>
    </div>

</div>

<!-- FOOTER -->
<footer class="footer" id="footer">
    <small>&copy; <?= date('Y') ?> Staff Leave Management System. All rights reserved.</small>
</footer>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$('#menuToggle').on('click', function () {
    $('#sidebar').toggleClass('collapsed');
    $('#mainContent').toggleClass('expanded');
    $('#footer').toggleClass('expanded');
});
</script>

</body>
</html>
