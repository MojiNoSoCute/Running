<?php
require 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "INSERT INTO PRICE_RATE (category_id, runner_type, amount) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['category_id'], $_POST['runner_type'], $_POST['amount']]);
        echo "<script>alert('ตั้งค่าราคาสำเร็จ'); window.location='price_rate_form.php';</script>";
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>