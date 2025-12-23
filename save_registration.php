<?php
require 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get default price for Standard runner type based on selected category
        $stmt_price = $pdo->prepare("SELECT price_id FROM PRICE_RATE WHERE category_id = ? AND runner_type = 'Standard' LIMIT 1");
        $stmt_price->execute([$_POST['category_id']]);
        $price_result = $stmt_price->fetch();
        
        if (!$price_result) {
            die("ข้อผิดพลาด: ไม่พบข้อมูลราคาสำหรับประเภทการแข่งขันนี้ กรุณาติดต่อเจ้าหน้าที่");
        }

        $sql = "INSERT INTO REGISTRATION (runner_id, category_id, price_id, shipping_id, reg_date, shirt_size, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['runner_id'],
            $_POST['category_id'],
            $price_result['price_id'], // Use default Standard price
            $_POST['shipping_id'],
            date('Y-m-d'),
            $_POST['shirt_size']
        ]);

        echo "<script>alert('ลงสมัครสำเร็จ!'); window.location='registration_form.php';</script>";
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>