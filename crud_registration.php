<?php
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM REGISTRATION WHERE reg_id = ?");
        $stmt->execute([$_GET['id']]);
        $registration = $stmt->fetch();
        
        if ($registration) {
            echo json_encode(['success' => true, 'data' => $registration]);
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
            case 'update':
                $sql = "UPDATE REGISTRATION SET shirt_size=?, bib_number=?, status=? WHERE reg_id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['shirt_size'],
                    $_POST['bib_number'] ?: null,
                    $_POST['status'],
                    $_POST['reg_id']
                ]);
                
                echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
                break;
                
            case 'cancel':
                $stmt = $pdo->prepare("UPDATE REGISTRATION SET status='Cancelled' WHERE reg_id=?");
                $stmt->execute([$_POST['reg_id']]);
                
                echo json_encode(['success' => true, 'message' => 'ยกเลิกการสมัครสำเร็จ']);
                break;
                
            case 'delete':
                // Check if there are payments
                $check = $pdo->prepare("SELECT COUNT(*) FROM PAYMENT WHERE reg_id = ?");
                $check->execute([$_POST['reg_id']]);
                
                if ($check->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบได้ เนื่องจากมีการชำระเงินแล้ว']);
                    exit;
                }
                
                $stmt = $pdo->prepare("DELETE FROM REGISTRATION WHERE reg_id = ?");
                $stmt->execute([$_POST['reg_id']]);
                
                echo json_encode(['success' => true, 'message' => 'ลบข้อมูลสำเร็จ']);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>