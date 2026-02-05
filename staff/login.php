<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Login | Staff Leave Management System</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-wrapper">

    <div class="auth-card shadow">

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger text-center">
                <?= $_SESSION['error']; ?>
            </div>
        <?php unset($_SESSION['error']); endif; ?>

        <h4 class="text-center mb-4">Staff Login</h4>

        <form method="POST" action="login_process.php">
            <input type="email"
                   name="email"
                   class="form-control mb-3"
                   placeholder="Email"
                   required>

            <input type="password"
                   name="password"
                   class="form-control mb-3"
                   placeholder="Password"
                   required>

            <button class="btn btn-success w-100">Login</button>
        </form>
    </div>

</div>

</body>
</html>
