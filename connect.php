<?php

$servername = "localhost";
$username = "root"; // Thay 'username' bằng tên người dùng MySQL của bạn
$password = ""; // Thay 'password' bằng mật khẩu MySQL của bạn
$dbname = "knotreastore"; // Thay 'database' bằng tên cơ sở dữ liệu bạn muốn kết nối

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Thiết lập chế độ lỗi PDO để báo lỗi trực tiếp
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
}