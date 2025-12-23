<?php
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM PRICE_RATE WHERE price_id = ?");
        $stmt->execute([$_GET['id']]);
        $priceRate = $stmt->fetch();
        
        if ($priceRate) {
            echo json_encode(['success' => true, 'data' => $priceRate]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                // Check for duplicate
                $check = $pdo->prepare("SELECT COUNT(*) FROM PRICE_RATE WHERE category_id = ? AND runner_type = ?");
                $check->execute([$_POST['category_id'], $_POST['runner_type']]);
                
                if ($check->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'มีอัตราค่าสมัครสำหรับประเภทนี้แล้ว']);
                    exit;
                }
                
                $sql = "INSERT INTO PRICE_RATE (category_id, runner_type, amount) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['category_id'],
                    $_POST['runner_type'],
                    $_POST['amount']
                ]);
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลสำเร็จ']);
                } else {
                    echo "<script>alert('เพิ่มข้อมูลสำเร็จ'); window.location='manage_price_rates.php';</script>";
                }
                break;
                
            case 'update':
                // Check for duplicate (excluding current record)
                $check = $pdo->prepare("SELECT COUNT(*) FROM PRICE_RATE WHERE category_id = ? AND runner_type = ? AND price_id != ?");
                $check->execute([$_POST['category_id'], $_POST['runner_type'], $_POST['price_id']]);
                
                if ($check->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'มีอัตราค่าสมัครสำหรับประเภทนี้แล้ว']);
                    exit;
                }
                
                $sql = "UPDATE PRICE_RATE SET category_id=?, runner_type=?, amount=? WHERE price_id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['category_id'],
                    $_POST['runner_type'],
                    $_POST['amount'],
                    $_POST['price_id']
                ]);
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
                } else {
                    echo "<script>alert('อัปเดตข้อมูลสำเร็จ'); window.location='manage_price_rates.php';</script>";
                }
                break;
                
            case 'delete':
                // Check if price rate is being used
                $check = $pdo->prepare("SELECT COUNT(*) FROM REGISTRATION WHERE price_id = ?");
                $check->execute([$_POST['price_id']]);
                
                if ($check->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบได้ เนื่องจากมีการใช้งานในการสมัครแล้ว']);
                    exit;
                }
                
                $stmt = $pdo->prepare("DELETE FROM PRICE_RATE WHERE price_id = ?");
                $stmt->execute([$_POST['price_id']]);
                
                echo json_encode(['success' => true, 'message' => 'ลบข้อมูลสำเร็จ']);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (PDOException $e) {
        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } else {
            echo "<script>alert('เกิดข้อผิดพลาด: " . $e->getMessage() . "'); window.history.back();</script>";
        }
    }
}
?>