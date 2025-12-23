<?php
require 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "INSERT INTO RACE_CATEGORY (name, distance_km, start_time, time_limit, giveaway_type) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['name'], 
            $_POST['distance_km'], 
            $_POST['start_time'],
            $_POST['time_limit'] ?? null,
            $_POST['giveaway_type'] ?? null
        ]);
        echo "<script>alert('เพิ่มประเภทการแข่งขันสำเร็จ'); window.location='race_category_form.php';</script>";
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>