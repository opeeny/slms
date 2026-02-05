<?php
session_start();

/* Unset all session variables */
$_SESSION = [];

/* Destroy session */
session_destroy();

/* Redirect to landing page */
header("Location: ../index.php");
exit();
