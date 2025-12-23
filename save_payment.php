<?php
require 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();

        // 1. Insert ข้อมูลจ่ายเงิน
        $stmt1 = $pdo->prepare("INSERT INTO PAYMENT (reg_id, total_amount, payment_time, payment_method, status) VALUES (?, ?, ?, ?, 'Success')");
        $stmt1->execute([$_POST['reg_id'], $_POST['total_amount'], $_POST['payment_time'], $_POST['payment_method']]);

        // 2. Update สถานะใบสมัคร
        $stmt2 = $pdo->prepare("UPDATE REGISTRATION SET status = 'Paid' WHERE reg_id = ?");
        $stmt2->execute([$_POST['reg_id']]);

        $pdo->commit();
        echo "<script>alert('แจ้งชำระเงินสำเร็จ'); window.location='payment_form.php';</script>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>