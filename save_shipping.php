<?php
require 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "INSERT INTO SHIPPING_OPTION (type, cost, detail) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['type'], $_POST['cost'], $_POST['detail']]);
        echo "<script>alert('บันทึกตัวเลือกการจัดส่งสำเร็จ'); window.location='shipping_option_form.php';</script>";
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>