<?php
require_once __DIR__ . '/../config/db.php';

/* =====================
   TOTAL REGISTERED STAFF
===================== */
$totalStaffQuery = "
    SELECT COUNT(*) AS total
    FROM users u
    JOIN roles r ON u.role_id = r.role_id
    WHERE r.role_name = 'STAFF'
    AND u.status = 'ACTIVE'
";
$totalStaff = $conn->query($totalStaffQuery)->fetch_assoc()['total'];

/* =====================
   TOTAL PENDING LEAVE REQUESTS
===================== */
$totalLeaveQuery = "
    SELECT COUNT(*) AS total
    FROM leave_applications
    WHERE status = 'PENDING'
";
$totalLeave = $conn->query($totalLeaveQuery)->fetch_assoc()['total'];

/* =====================
   STAFF CURRENTLY ON LEAVE
===================== */
$stillOnLeaveQuery = "
    SELECT COUNT(DISTINCT user_id) AS total
    FROM leave_applications
    WHERE status = 'APPROVED'
    AND CURDATE() BETWEEN start_date AND end_date
";
$stillOnLeave = $conn->query($stillOnLeaveQuery)->fetch_assoc()['total'];

/* =====================
   ANNUAL LEAVE
===================== */
$annualLeaveQuery = "
    SELECT COUNT(DISTINCT la.user_id) AS total
    FROM leave_applications la
    JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id
    WHERE lt.leave_name = 'Annual Leave'
    AND la.status = 'APPROVED'
    AND CURDATE() BETWEEN la.start_date AND la.end_date
";
$annuaLeave = $conn->query($annualLeaveQuery)->fetch_assoc()['total'];

/* =====================
   PASS LEAVE
===================== */
$passLeaveQuery = "
    SELECT COUNT(DISTINCT la.user_id) AS total
    FROM leave_applications la
    JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id
    WHERE lt.leave_name = 'Pass Leave'
    AND la.status = 'APPROVED'
    AND CURDATE() BETWEEN la.start_date AND la.end_date
";
$passLeave = $conn->query($passLeaveQuery)->fetch_assoc()['total'];

/* =====================
   SICK LEAVE
===================== */
$sickLeaveQuery = "
    SELECT COUNT(DISTINCT la.user_id) AS total
    FROM leave_applications la
    JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id
    WHERE lt.leave_name = 'Sick Leave'
    AND la.status = 'APPROVED'
    AND CURDATE() BETWEEN la.start_date AND la.end_date
";
$sickLeave = $conn->query($sickLeaveQuery)->fetch_assoc()['total'];
