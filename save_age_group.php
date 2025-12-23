<?php
require 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "INSERT INTO AGE_GROUP (category_id, gender, min_age, max_age, label) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['category_id'],
            $_POST['gender'],
            $_POST['min_age'],
            $_POST['max_age'],
            $_POST['label']
        ]);
        echo "<script>alert('ตั้งค่ากลุ่มอายุสำเร็จ'); window.location='age_group_form.php';</script>";
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>