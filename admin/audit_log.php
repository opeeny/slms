<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

/* =====================================================
   AJAX: DATATABLES SERVER-SIDE
===================================================== */
if (isset($_POST['draw'])) {

    $columns = [
        0 => 'al.log_id',
        1 => 'u.full_name',
        2 => 'al.action',
        3 => 'al.details',
        4 => 'al.ip_address',
        5 => 'al.created_at'
    ];

    $limit  = intval($_POST['length']);
    $start  = intval($_POST['start']);
    $order  = $columns[$_POST['order'][0]['column']];
    $dir    = $_POST['order'][0]['dir'];
    $search = $_POST['search']['value'];

    $where = "WHERE 1=1";
    if ($search) {
        $search = "%{$search}%";
        $where .= " AND (
            u.full_name LIKE ?
            OR al.action LIKE ?
            OR al.details LIKE ?
            OR al.ip_address LIKE ?
        )";
    }

    $total = $conn->query("
        SELECT COUNT(*) total
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.user_id
    ")->fetch_assoc()['total'];

    $sql = "
        SELECT al.log_id, al.action, al.details, al.ip_address, al.created_at,
               u.full_name, u.profile_pic
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.user_id
        $where
        ORDER BY $order $dir
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    $search
        ? $stmt->bind_param("ssssii", $search, $search, $search, $search, $limit, $start)
        : $stmt->bind_param("ii", $limit, $start);

    $stmt->execute();
    $res = $stmt->get_result();

    $data = [];
    while ($row = $res->fetch_assoc()) {

        $pic = (!empty($row['profile_pic']) && file_exists("../uploads/profiles/".$row['profile_pic']))
            ? "../uploads/profiles/".$row['profile_pic']
            : "../assets/images/ui.jpg";

        $userName = $row['full_name'] ? htmlspecialchars($row['full_name']) : '<em>System</em>';

        $actionBadge = match(true) {
            str_contains($row['action'], 'LOGIN') => "<span class='badge bg-success'>{$row['action']}</span>",
            str_contains($row['action'], 'LOGOUT') => "<span class='badge bg-secondary'>{$row['action']}</span>",
            str_contains($row['action'], 'CREATED') || str_contains($row['action'], 'ADDED') => "<span class='badge bg-primary'>{$row['action']}</span>",
            str_contains($row['action'], 'UPDATED') || str_contains($row['action'], 'MODIFIED') => "<span class='badge bg-info'>{$row['action']}</span>",
            str_contains($row['action'], 'DELETED') || str_contains($row['action'], 'REMOVED') => "<span class='badge bg-danger'>{$row['action']}</span>",
            str_contains($row['action'], 'SUSPENDED') || str_contains($row['action'], 'CHANGE') => "<span class='badge bg-warning text-dark'>{$row['action']}</span>",
            default => "<span class='badge bg-secondary'>{$row['action']}</span>"
        };

        $data[] = [
            $row['log_id'],
            "<div class='d-flex align-items-center'>
                <img src='{$pic}' class='rounded-circle me-2' width='35' height='35'>
                {$userName}
             </div>",
            $actionBadge,
            htmlspecialchars($row['details']),
            htmlspecialchars($row['ip_address']),
            date("M d, Y h:i a", strtotime($row['created_at']))
        ];
    }

    echo json_encode([
        "draw" => intval($_POST['draw']),
        "recordsTotal" => $total,
        "recordsFiltered" => $total,
        "data" => $data
    ]);
    exit();
}

/* =====================================================
   FETCH ADMIN DETAILS
===================================================== */
$stmt = $conn->prepare("
    SELECT full_name, email, profile_pic
    FROM users WHERE user_id=?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

$profilePicPath = (!empty($admin['profile_pic']) && file_exists("../uploads/profiles/".$admin['profile_pic']))
    ? "../uploads/profiles/".$admin['profile_pic']
    : "../assets/images/ui.jpg";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Audit Log | SLMS</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        text-align: center;
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

    /* Card styling */
    .content-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 30px;
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

    /* DataTable styling */
    .dataTables_wrapper {
        padding: 20px 0;
    }

    table.dataTable thead th {
        background: #f8f9fa;
        color: #333;
        font-weight: 500;
        border-bottom: 2px solid #17a2b8;
    }

    table.dataTable tbody td {
        vertical-align: middle;
    }

    .table-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
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
        <div class="admin-name">Admin <?= htmlspecialchars($admin['full_name']) ?></div>
        <div class="admin-email">(<?= htmlspecialchars($admin['email']) ?>)</div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php">
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
            <a href="audit_log.php" class="active">
                <i class="bi bi-file-text"></i>
                <span class="menu-label">Audit Log</span>
            </a>
        </li>
        <li>
            <a href="logout.php">
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
        <h5>Audit Log</h5>
    </div>

    <div class="content-card">
        <table id="auditTable" class="table table-bordered table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP Address</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
        </table>
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function () {

    // Menu Toggle
    $('#menuToggle').click(function() {
        $('#sidebar').toggleClass('collapsed');
        $('#mainContent').toggleClass('expanded');
        $('#footer').toggleClass('expanded');
    });

    // Initialize DataTable
    const table = $('#auditTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { 
            url: 'audit_log.php', 
            type: 'POST' 
        },
        columns: [
            { data: 0, width: '8%' },
            { data: 1, width: '15%' },
            { data: 2, width: '12%' },
            { data: 3, width: '35%' },
            { data: 4, width: '12%' },
            { data: 5, width: '18%' }
        ],
        order: [[0, 'desc']], // Order by log_id descending (newest first)
        responsive: true,
        language: {
            search: "Search Logs:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ audit logs",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            processing: "Loading audit logs..."
        }
    });

});
</script>
</body>
</html>