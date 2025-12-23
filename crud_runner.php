<?php
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM RUNNER WHERE runner_id = ?");
        $stmt->execute([$_GET['id']]);
        $runner = $stmt->fetch();
        
        if ($runner) {
            echo json_encode(['success' => true, 'data' => $runner]);
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
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลนักวิ่งสำเร็จ']);
                } else {
                    echo "<script src='assets/js/alert-popup.js'></script>";
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showSuccess('เพิ่มข้อมูลนักวิ่งสำเร็จ', 'สำเร็จ', () => window.location='manage_runners.php');
                        });
                    </script>";
                }
                break;
                
            case 'update':
                $sql = "UPDATE RUNNER SET first_name=?, last_name=?, date_of_birth=?, gender=?, citizen_id=?, phone=?, email=?, address=?, is_disabled=? WHERE runner_id=?";
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
                    isset($_POST['is_disabled']) ? 1 : 0,
                    $_POST['runner_id']
                ]);
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลนักวิ่งสำเร็จ']);
                } else {
                    echo "<script src='assets/js/alert-popup.js'></script>";
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showSuccess('อัปเดตข้อมูลนักวิ่งสำเร็จ', 'สำเร็จ', () => window.location='manage_runners.php');
                        });
                    </script>";
                }
                break;
                
            case 'delete':
                // Check if runner has registrations
                $check = $pdo->prepare("SELECT COUNT(*) FROM REGISTRATION WHERE runner_id = ?");
                $check->execute([$_POST['runner_id']]);
                
                if ($check->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบได้ เนื่องจากนักวิ่งคนนี้มีการสมัครแข่งขันแล้ว']);
                    exit;
                }
                
                $stmt = $pdo->prepare("DELETE FROM RUNNER WHERE runner_id = ?");
                $stmt->execute([$_POST['runner_id']]);
                
                echo json_encode(['success' => true, 'message' => 'ลบข้อมูลนักวิ่งสำเร็จ']);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'การดำเนินการไม่ถูกต้อง']);
        }
    } catch (PDOException $e) {
        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        } else {
            echo "<script src='assets/js/alert-popup.js'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showError('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "', 'ข้อผิดพลาด', () => window.history.back());
                });
            </script>";
        }
    }
}
?>