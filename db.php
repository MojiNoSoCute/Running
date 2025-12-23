<?php
$host = 'localhost';
$db = 'running';
$user = 'root'; // เปลี่ยนตาม username ของคุณ
$pass = '';     // เปลี่ยนตาม password ของคุณ
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "เชื่อมต่อฐานข้อมูลสำเร็จ!"; 
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int) $e->getCode());
}
?>