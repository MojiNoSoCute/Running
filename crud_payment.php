<?php
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM PAYMENT WHERE payment_id = ?");
        $stmt->execute([$_GET['id']]);
        $payment = $stmt->fetch();
        
        if ($payment) {
            echo json_encode(['success' => true, 'data' => $payment]);
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
                $sql = "UPDATE PAYMENT SET total_amount=?, payment_method=?, status=?, transaction_ref=? WHERE payment_id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['total_amount'],
                    $_POST['payment_method'],
                    $_POST['status'],
                    $_POST['transaction_ref'] ?: null,
                    $_POST['payment_id']
                ]);
                
                // Update registration status if payment is successful
                if ($_POST['status'] === 'Success') {
                    $regStmt = $pdo->prepare("UPDATE REGISTRATION SET status='Paid' WHERE reg_id = (SELECT reg_id FROM PAYMENT WHERE payment_id = ?)");
                    $regStmt->execute([$_POST['payment_id']]);
                }
                
                echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
                break;
                
            case 'update_status':
                $pdo->beginTransaction();
                
                // Update payment status
                $stmt = $pdo->prepare("UPDATE PAYMENT SET status=? WHERE payment_id=?");
                $stmt->execute([$_POST['status'], $_POST['payment_id']]);
                
                // Update registration status
                if ($_POST['status'] === 'Success') {
                    $regStmt = $pdo->prepare("UPDATE REGISTRATION SET status='Paid' WHERE reg_id = (SELECT reg_id FROM PAYMENT WHERE payment_id = ?)");
                    $regStmt->execute([$_POST['payment_id']]);
                } elseif ($_POST['status'] === 'Failed') {
                    $regStmt = $pdo->prepare("UPDATE REGISTRATION SET status='Pending' WHERE reg_id = (SELECT reg_id FROM PAYMENT WHERE payment_id = ?)");
                    $regStmt->execute([$_POST['payment_id']]);
                }
                
                $pdo->commit();
                
                echo json_encode(['success' => true, 'message' => 'อัปเดตสถานะสำเร็จ']);
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM PAYMENT WHERE payment_id = ?");
                $stmt->execute([$_POST['payment_id']]);
                
                echo json_encode(['success' => true, 'message' => 'ลบข้อมูลสำเร็จ']);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>