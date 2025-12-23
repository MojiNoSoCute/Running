<?php
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM RACE_CATEGORY WHERE category_id = ?");
        $stmt->execute([$_GET['id']]);
        $category = $stmt->fetch();
        
        if ($category) {
            echo json_encode(['success' => true, 'data' => $category]);
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
                $sql = "INSERT INTO RACE_CATEGORY (name, distance_km, start_time, time_limit, giveaway_type) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['name'],
                    $_POST['distance_km'],
                    $_POST['start_time'],
                    $_POST['time_limit'] ?? null,
                    $_POST['giveaway_type'] ?? null
                ]);
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลสำเร็จ']);
                } else {
                    echo "<script>alert('เพิ่มข้อมูลสำเร็จ'); window.location='manage_categories.php';</script>";
                }
                break;
                
            case 'update':
                $sql = "UPDATE RACE_CATEGORY SET name=?, distance_km=?, start_time=?, time_limit=?, giveaway_type=? WHERE category_id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['name'],
                    $_POST['distance_km'],
                    $_POST['start_time'],
                    $_POST['time_limit'] ?? null,
                    $_POST['giveaway_type'] ?? null,
                    $_POST['category_id']
                ]);
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
                } else {
                    echo "<script>alert('อัปเดตข้อมูลสำเร็จ'); window.location='manage_categories.php';</script>";
                }
                break;
                
            case 'delete':
                // Check if category has registrations
                $check = $pdo->prepare("SELECT COUNT(*) FROM REGISTRATION WHERE category_id = ?");
                $check->execute([$_POST['category_id']]);
                
                if ($check->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบได้ เนื่องจากมีการสมัครในประเภทนี้แล้ว']);
                    exit;
                }
                
                $stmt = $pdo->prepare("DELETE FROM RACE_CATEGORY WHERE category_id = ?");
                $stmt->execute([$_POST['category_id']]);
                
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