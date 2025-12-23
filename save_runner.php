<?php
require 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "INSERT INTO RUNNER (first_name, last_name, date_of_birth, gender, citizen_id, phone, email, address, is_disabled) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['date_of_birth'],
            $_POST['gender'],
            $_POST['citizen_id'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['address'],
            isset($_POST['is_disabled']) ? 1 : 0
        ]);
        echo "<script>alert('บันทึกข้อมูลนักวิ่งสำเร็จ'); window.location='runner_form.php';</script>";
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>