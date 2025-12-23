<?php
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM AGE_GROUP WHERE age_group_id = ?");
        $stmt->execute([$_GET['id']]);
        $ageGroup = $stmt->fetch();
        
        if ($ageGroup) {
            echo json_encode(['success' => true, 'data' => $ageGroup]);
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
                $sql = "INSERT INTO AGE_GROUP (category_id, gender, min_age, max_age, label) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['category_id'],
                    $_POST['gender'],
                    $_POST['min_age'],
                    $_POST['max_age'],
                    $_POST['label']
                ]);
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลสำเร็จ']);
                } else {
                    echo "<script>alert('เพิ่มข้อมูลสำเร็จ'); window.location='manage_age_groups.php';</script>";
                }
                break;
                
            case 'update':
                $sql = "UPDATE AGE_GROUP SET category_id=?, gender=?, min_age=?, max_age=?, label=? WHERE age_group_id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['category_id'],
                    $_POST['gender'],
                    $_POST['min_age'],
                    $_POST['max_age'],
                    $_POST['label'],
                    $_POST['age_group_id']
                ]);
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
                } else {
                    echo "<script>alert('อัปเดตข้อมูลสำเร็จ'); window.location='manage_age_groups.php';</script>";
                }
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM AGE_GROUP WHERE age_group_id = ?");
                $stmt->execute([$_POST['age_group_id']]);
                
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