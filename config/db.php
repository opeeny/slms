<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "slms_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed");
}
