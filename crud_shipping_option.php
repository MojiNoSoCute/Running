<?php
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM SHIPPING_OPTION WHERE shipping_id = ?");
        $stmt->execute([$_GET['id']]);
        $shipping = $stmt->fetch();
        
        if ($shipping) {
            echo json_encode(['success' => true, 'data' => $shipping]);
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
                $sql = "INSERT INTO SHIPPING_OPTION (type, cost, detail) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['type'],
                    $_POST['cost'],
                    $_POST['detail'] ?: null
                ]);
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลสำเร็จ']);
                } else {
                    echo "<script>alert('เพิ่มข้อมูลสำเร็จ'); window.location='manage_shipping_options.php';</script>";
                }
                break;
                
            case 'update':
                $sql = "UPDATE SHIPPING_OPTION SET type=?, cost=?, detail=? WHERE shipping_id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['type'],
                    $_POST['cost'],
                    $_POST['detail'] ?: null,
                    $_POST['shipping_id']
                ]);
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
                } else {
                    echo "<script>alert('อัปเดตข้อมูลสำเร็จ'); window.location='manage_shipping_options.php';</script>";
                }
                break;
                
            case 'delete':
                // Check if shipping option is being used
                $check = $pdo->prepare("SELECT COUNT(*) FROM REGISTRATION WHERE shipping_id = ?");
                $check->execute([$_POST['shipping_id']]);
                
                if ($check->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบได้ เนื่องจากมีการใช้งานในการสมัครแล้ว']);
                    exit;
                }
                
                $stmt = $pdo->prepare("DELETE FROM SHIPPING_OPTION WHERE shipping_id = ?");
                $stmt->execute([$_POST['shipping_id']]);
                
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