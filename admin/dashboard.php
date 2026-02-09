<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

/* =========================
   FETCH ADMIN DETAILS
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

$adminName  = $user['full_name'];
$adminEmail = $user['email'];

$profilePic = $user['profile_pic'];
$profilePicPath = (!empty($profilePic) && file_exists("../uploads/profiles/" . $profilePic))
    ? "../uploads/profiles/" . $profilePic
    : "../assets/images/ui.jpg";

/* =========================
   FETCH DASHBOARD STATISTICS
========================= */

// Total Registered Staff (STAFF role only)
$totalStaffQuery = "
    SELECT COUNT(*) as total 
    FROM users u 
    JOIN roles r ON u.role_id = r.role_id 
    WHERE r.role_name = 'STAFF'
";
$totalStaffResult = $conn->query($totalStaffQuery);
$totalStaff = $totalStaffResult->fetch_assoc()['total'];

// Total Leave Received/Pending (assuming you have a leave_requests table)
// If table doesn't exist yet, will default to 0
$totalLeaveQuery = "
    SELECT COUNT(*) as total 
    FROM leave_requests 
    WHERE status IN ('PENDING', 'APPROVED')
";
if ($conn->query("SHOW TABLES LIKE 'leave_requests'")->num_rows > 0) {
    $totalLeaveResult = $conn->query($totalLeaveQuery);
    $totalLeave = $totalLeaveResult->fetch_assoc()['total'];
} else {
    $totalLeave = 0;
}

// Total Staff Currently on Leave
$stillOnLeaveQuery = "
    SELECT COUNT(*) as total 
    FROM leave_requests 
    WHERE status = 'APPROVED' 
    AND CURDATE() BETWEEN start_date AND end_date
";
if ($conn->query("SHOW TABLES LIKE 'leave_requests'")->num_rows > 0) {
    $stillOnLeaveResult = $conn->query($stillOnLeaveQuery);
    $stillOnLeave = $stillOnLeaveResult->fetch_assoc()['total'];
} else {
    $stillOnLeave = 0;
}

// Annual Leave
$annualLeaveQuery = "
    SELECT COUNT(*) as total 
    FROM leave_requests 
    WHERE leave_type = 'ANNUAL' 
    AND status = 'APPROVED'
    AND CURDATE() BETWEEN start_date AND end_date
";
if ($conn->query("SHOW TABLES LIKE 'leave_requests'")->num_rows > 0) {
    $annualLeaveResult = $conn->query($annualLeaveQuery);
    $annuaLeave = $annualLeaveResult->fetch_assoc()['total'];
} else {
    $annuaLeave = 0;
}

// Pass Leave
$passLeaveQuery = "
    SELECT COUNT(*) as total 
    FROM leave_requests 
    WHERE leave_type = 'PASS' 
    AND status = 'APPROVED'
    AND CURDATE() BETWEEN start_date AND end_date
";
if ($conn->query("SHOW TABLES LIKE 'leave_requests'")->num_rows > 0) {
    $passLeaveResult = $conn->query($passLeaveQuery);
    $passLeave = $passLeaveResult->fetch_assoc()['total'];
} else {
    $passLeave = 0;
}

// Sick Leave
$sickLeaveQuery = "
    SELECT COUNT(*) as total 
    FROM leave_requests 
    WHERE leave_type = 'SICK' 
    AND status = 'APPROVED'
    AND CURDATE() BETWEEN start_date AND end_date
";
if ($conn->query("SHOW TABLES LIKE 'leave_requests'")->num_rows > 0) {
    $sickLeaveResult = $conn->query($sickLeaveQuery);
    $sickLeave = $sickLeaveResult->fetch_assoc()['total'];
} else {
    $sickLeave = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | SLMS</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
        background-color: #f5f5f5;
    }

    /* Top Header */
    .top-header {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        height: 70px;
    }

    .top-header .logo-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .top-header .menu-toggle {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 5px 10px;
    }

    .top-header .system-title {
        font-size: 18px;
        font-weight: 400;
        letter-spacing: 0.5px;
    }

    .top-header .header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .notification-icon {
        position: relative;
        color: white;
        font-size: 20px;
        cursor: pointer;
    }

    /* Sidebar */
    .sidebar {
        position: fixed;
        left: 0;
        top: 70px;
        width: 250px;
        height: calc(100vh - 70px);
        background: white;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        padding: 20px 0 60px 0;
        overflow-y: auto;
        transition: transform 0.3s ease;
        z-index: 999;
    }

    .sidebar.collapsed {
        transform: translateX(-250px);
    }

    .sidebar-profile {
        text-align: center;
        padding: 20px;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 20px;
    }

    .sidebar-profile img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 10px;
        border: 3px solid #17a2b8;
    }

    .sidebar-profile .admin-name {
        font-weight: 400;
        color: #333;
        margin-bottom: 5px;
    }

    .sidebar-profile .admin-email {
        font-size: 12px;
        font-weight: 300;
        color: #666;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
    }

    .sidebar-menu li {
        margin: 0;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 15px 25px;
        color: #555;
        text-decoration: none;
        transition: all 0.3s;
        border-left: 3px solid transparent;
        font-weight: 400;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background: #f0f9fa;
        color: #17a2b8;
        border-left-color: #17a2b8;
    }

    .sidebar-menu a i {
        margin-right: 15px;
        font-size: 18px;
        width: 20px;
        text-align: center;
    }

    .sidebar-menu .menu-label {
        flex: 1;
    }

    .sidebar-menu .submenu-arrow {
        font-size: 12px;
        transition: transform 0.3s;
    }

    .sidebar-menu .has-submenu[aria-expanded="true"] .submenu-arrow {
        transform: rotate(180deg);
    }

    .submenu {
        list-style: none;
        padding: 0;
        background: #f8f9fa;
    }

    .submenu .submenu-item {
        padding-left: 60px !important;
        font-size: 14px;
        font-weight: 400;
    }

    .submenu .submenu-item:hover,
    .submenu .submenu-item.active {
        background: #e3f2fd;
        color: #17a2b8;
    }

    .sidebar-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px;
        text-align: center;
        border-top: 1px solid #e0e0e0;
        background: white;
        font-weight: 300;
        color: #999;
        font-size: 14px;
    }

    /* Main Content */
    .main-content {
        margin-left: 250px;
        margin-top: 70px;
        padding: 30px;
        min-height: calc(100vh - 70px);
        transition: margin-left 0.3s ease;
    }

    .main-content.expanded {
        margin-left: 0;
    }

    .content-header {
        margin-bottom: 30px;
    }

    .content-header h5 {
        color: #17a2b8;
        font-weight: 500;
        font-size: 24px;
    }

    /* Footer */
    .footer {
        background: white;
        padding: 15px 30px;
        text-align: center;
        border-top: 1px solid #e0e0e0;
        margin-left: 250px;
        transition: margin-left 0.3s ease;
        position: relative;
        bottom: 0;
    }

    .footer.expanded {
        margin-left: 0;
    }

    .footer-content {
        font-size: 14px;
        color: #666;
    }

    /* Dashboard Stats Cards */
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        text-decoration: none;
        color: inherit;
        display: block;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .stat-card.clickable {
        cursor: pointer;
    }

    .stat-title {
        font-size: 13px;
        color: #666;
        font-weight: 500;
        text-transform: uppercase;
        margin-bottom: 15px;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 600;
        color: #17a2b8;
    }

    .stat-icon {
        font-size: 40px;
        color: #17a2b8;
        opacity: 0.3;
        float: right;
        margin-top: -10px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-250px);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .main-content,
        .footer {
            margin-left: 0;
        }

        .top-header .system-title {
            font-size: 14px;
        }
    }
</style>
</head>
<body>

<!-- TOP HEADER -->
<div class="top-header">
    <div class="logo-section">
        <button class="menu-toggle" id="menuToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="system-title">STAFF LEAVE MANAGEMENT SYSTEM</div>
    </div>
    <div class="header-right">
        <div class="notification-icon">
            <i class="bi bi-bell"></i>
        </div>
    </div>
</div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-profile">
        <img src="<?= htmlspecialchars($profilePicPath) ?>" alt="Admin Profile">
        <div class="admin-name">Admin <?= htmlspecialchars($adminName) ?></div>
        <div class="admin-email">(<?= htmlspecialchars($adminEmail) ?>)</div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="active">
                <i class="bi bi-speedometer2"></i>
                <span class="menu-label">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="myprofile.php">
                <i class="bi bi-person"></i>
                <span class="menu-label">My Profiles</span>
            </a>
        </li>
        <li>
            <a href="changepassword.php">
                <i class="bi bi-key"></i>
                <span class="menu-label">Change Password</span>
            </a>
        </li>
        <li>
            <a href="#staffSubmenu" data-bs-toggle="collapse" class="has-submenu">
                <i class="bi bi-people"></i>
                <span class="menu-label">Staff</span>
                <i class="bi bi-chevron-down submenu-arrow"></i>
            </a>
            <ul class="collapse submenu" id="staffSubmenu">
                <li>
                    <a href="addstaff.php" class="submenu-item">
                        <i class="bi bi-person-plus"></i>
                        <span class="menu-label">Add Staff</span>
                    </a>
                </li>
                <li>
                    <a href="managestaff.php" class="submenu-item">
                        <i class="bi bi-people-fill"></i>
                        <span class="menu-label">Manage Staff</span>
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <a href="staffleave.php">
                <i class="bi bi-calendar-check"></i>
                <span class="menu-label">Staff Leave</span>
            </a>
        </li>
        <li>
            <a href="audit_log.php">
                <i class="bi bi-file-text"></i>
                <span class="menu-label">Audit Log</span>
            </a>
        </li>
        <li>
            <a href="../auth/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span class="menu-label">Sign Out</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        SLMS©
    </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content" id="mainContent">
    <div class="content-header">
        <h5>Welcome, <?= htmlspecialchars($adminName) ?>!</h5>
    </div>

    <div class="row g-4">
        <!-- Total Registered Staff -->
        <div class="col-md-4">
            <a href="managestaff.php" class="stat-card clickable">
                <i class="bi bi-people-fill stat-icon"></i>
                <div class="stat-title">Total Registered Staff</div>
                <div class="stat-value"><?= $totalStaff ?></div>
            </a>
        </div>

        <!-- Total Leave Received/Pending -->
        <div class="col-md-4">
            <a href="staffleave.php" class="stat-card clickable">
                <i class="bi bi-file-earmark-text stat-icon"></i>
                <div class="stat-title">Total Leave Received / Pending</div>
                <div class="stat-value"><?= $totalLeave ?></div>
            </a>
        </div>

        <!-- Total Staff on Leave -->
        <div class="col-md-4">
            <a href="staffleave.php" class="stat-card clickable">
                <i class="bi bi-person-x stat-icon"></i>
                <div class="stat-title">Total Staff on Leave</div>
                <div class="stat-value"><?= $stillOnLeave ?></div>
            </a>
        </div>

        <!-- Annual Leave -->
        <div class="col-md-4">
            <a href="staffleave.php?type=ANNUAL" class="stat-card clickable">
                <i class="bi bi-calendar-check stat-icon"></i>
                <div class="stat-title">Annual Leave</div>
                <div class="stat-value"><?= $annuaLeave ?></div>
            </a>
        </div>

        <!-- Pass Leave -->
        <div class="col-md-4">
            <a href="staffleave.php?type=PASS" class="stat-card clickable">
                <i class="bi bi-calendar-plus stat-icon"></i>
                <div class="stat-title">Pass Leave</div>
                <div class="stat-value"><?= $passLeave ?></div>
            </a>
        </div>

        <!-- Sick Leave -->
        <div class="col-md-4">
            <a href="staffleave.php?type=SICK" class="stat-card clickable">
                <i class="bi bi-heart-pulse stat-icon"></i>
                <div class="stat-title">Sick Leave</div>
                <div class="stat-value"><?= $sickLeave ?></div>
            </a>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer" id="footer">
    <div class="footer-content">
        <div>
            <span>&copy; 2024 Staff Leave Management System. All rights reserved.</span>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(function () {
    // Menu Toggle
    $('#menuToggle').click(function() {
        $('#sidebar').toggleClass('collapsed');
        $('#mainContent').toggleClass('expanded');
        $('#footer').toggleClass('expanded');
    });
});
</script>
</body>
</html>