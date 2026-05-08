<?php
$host = "localhost";
$user = "DB_USER";
$pass = "DB_PASSWORD";
$db_name = "DB_NAME";

$conn = new mysqli($host, $user, $pass, $db_name);

if (!$conn) {
    die("연결 실패: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
