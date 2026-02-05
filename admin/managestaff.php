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
        0 => 'u.user_id',
        1 => 'u.profile_pic',
        2 => 'u.service_number', // ✅ ADDED
        3 => 'u.rank',
        4 => 'u.full_name',
        5 => 'u.email',
        6 => 'u.created_at'
    ];

    $limit  = intval($_POST['length']);
    $start  = intval($_POST['start']);
    $order  = $columns[$_POST['order'][0]['column']];
    $dir    = $_POST['order'][0]['dir'];

    $search = $_POST['search']['value'];

    $where = "WHERE r.role_name = 'STAFF' AND u.status = 'ACTIVE'";
    if (!empty($search)) {
        $search = "%{$search}%";
        $where .= " AND (
            u.full_name LIKE ?
            OR u.email LIKE ?
            OR u.rank LIKE ?
            OR u.service_number LIKE ?
        )";
    }

    /* TOTAL RECORDS */
    $totalSql = "
        SELECT COUNT(*) AS total
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE r.role_name = 'STAFF' AND u.status = 'ACTIVE'
    ";
    $total = $conn->query($totalSql)->fetch_assoc()['total'];

    /* FILTERED RECORDS */
    $filteredSql = "
        SELECT COUNT(*) AS total
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        $where
    ";
    $stmt = $conn->prepare($filteredSql);
    if (!empty($_POST['search']['value'])) {
        $stmt->bind_param("ssss", $search, $search, $search, $search);
    }
    $stmt->execute();
    $filtered = $stmt->get_result()->fetch_assoc()['total'];

    /* DATA QUERY */
    $dataSql = "
        SELECT 
            u.user_id,
            u.profile_pic,
            u.service_number,
            u.rank,
            u.full_name,
            u.email,
            u.created_at
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        $where
        ORDER BY $order $dir
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($dataSql);

    if (!empty($_POST['search']['value'])) {
        $stmt->bind_param("ssssii", $search, $search, $search, $search, $limit, $start);
    } else {
        $stmt->bind_param("ii", $limit, $start);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {

        $pic = (!empty($row['profile_pic']) && file_exists("../uploads/profiles/".$row['profile_pic']))
            ? "../uploads/profiles/".$row['profile_pic']
            : "../assets/images/ui.jpg";

        $data[] = [
            $row['user_id'],
            "<img src='{$pic}' class='rounded-circle' width='40'>",
            htmlspecialchars($row['service_number']), // ✅ NEW COLUMN
            htmlspecialchars($row['rank']),
            htmlspecialchars($row['full_name']),
            htmlspecialchars($row['email']),
            date("M d, Y h:i a", strtotime($row['created_at'])),
            "
            <a href='editstaff.php?id={$row['user_id']}' class='text-primary me-2'>
                <i class='bi bi-pencil-square'></i>
            </a>
            <a href='#' class='text-danger deleteStaff' data-id='{$row['user_id']}'>
                <i class='bi bi-trash'></i>
            </a>
            "
        ];
    }

    echo json_encode([
        "draw" => intval($_POST['draw']),
        "recordsTotal" => $total,
        "recordsFiltered" => $filtered,
        "data" => $data
    ]);
    exit();
}

/* =====================================================
   AJAX: SOFT DELETE
===================================================== */
if (isset($_POST['soft_delete'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE users SET status = 'INACTIVE' WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "OK";
    exit();
}

/* =====================================================
   FETCH ADMIN DETAILS (SAME AS DASHBOARD)
===================================================== */
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT full_name, email, profile_pic FROM users WHERE user_id = ? LIMIT 1"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$adminName  = $user['full_name'];
$adminEmail = $user['email'];

$profilePicPath = (!empty($user['profile_pic']) && file_exists("../uploads/profiles/".$user['profile_pic']))
    ? "../uploads/profiles/".$user['profile_pic']
    : "../assets/images/ui.jpg";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff | SLMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- TOP BAR -->
<div class="topbar d-flex align-items-center justify-content-between px-4">
    <span class="fw-semibold text-white">STAFF LEAVE MANAGEMENT SYSTEM</span>
    <a href="../auth/logout.php" class="text-white">
        <i class="bi bi-box-arrow-right fs-5"></i>
    </a>
</div>

<div class="d-flex">

<!-- SIDEBAR -->
<div class="sidebar p-3">
    <div class="profile text-center mb-4">
        <img src="<?= $profilePicPath ?>" class="profile-img mb-2">
        <div class="fw-semibold"><?= htmlspecialchars($adminName) ?></div>
        <small class="text-muted">(<?= htmlspecialchars($adminEmail) ?>)</small>
    </div>

    <ul class="nav flex-column sidebar-menu">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-person"></i> My Profile</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-lock"></i> Change Password</a>
        </li>

        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center active"
               data-bs-toggle="collapse" href="#staffMenu">
                <span><i class="bi bi-people"></i> Staff</span>
                <i class="bi bi-chevron-down small"></i>
            </a>

            <ul class="nav flex-column ms-3 collapse show" id="staffMenu">
                <li class="nav-item">
                    <a class="nav-link" href="addstaff.php">
                        <i class="bi bi-person-plus"></i> Add Staff
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="managestaff.php">
                        <i class="bi bi-list-check"></i> Manage Staff
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-calendar-check"></i> Staff Leave</a>
        </li>

        <li class="nav-item mt-3">
            <a class="nav-link text-danger" href="../auth/logout.php">
                <i class="bi bi-box-arrow-right"></i> Sign Out
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <p>SLMS &copy; <?= date('Y'); ?></p>
    </div>
</div>

<!-- CONTENT -->
<div class="content p-4 w-100">
    <div class="bg-white p-4 rounded shadow-sm">
        <h5 class="text-primary mb-3">Manage Staff Details</h5>

        <table id="staffTable" class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Profile Pic</th>
                    <th>Service Number</th>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function () {

    const table = $('#staffTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'managestaff.php',
            type: 'POST'
        }
    });

    $(document).on('click', '.deleteStaff', function () {
        if (!confirm('Deactivate this staff member?')) return;

        $.post('managestaff.php', {
            soft_delete: 1,
            id: $(this).data('id')
        }, function () {
            table.ajax.reload(null, false);
        });
    });

});
</script>
</body>
</html>
