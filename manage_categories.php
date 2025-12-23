<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการประเภทการแข่งขัน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/alert-popup.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-layer-group me-2"></i>จัดการประเภทการแข่งขัน</span>
            <a href="admin_index.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>กลับหน้าหลัก
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Add/Edit Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0" id="form-title">เพิ่มประเภทการแข่งขันใหม่</h5>
            </div>
            <div class="card-body">
                <form id="category-form" action="crud_category.php" method="POST">
                    <input type="hidden" name="action" value="create" id="form-action">
                    <input type="hidden" name="category_id" id="category_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ชื่อประเภท</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="เช่น Marathon" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ระยะทาง (km)</label>
                            <input type="number" step="0.1" name="distance_km" id="distance_km" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">เวลาปล่อยตัว</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">เวลาจำกัด (Cut-off)</label>
                            <input type="time" name="time_limit" id="time_limit" class="form-control" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">ของที่ระลึก</label>
                            <input type="text" name="giveaway_type" id="giveaway_type" class="form-control" placeholder="เช่น เสื้อ + เหรียญ" required>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <i class="fa-solid fa-save me-1"></i>บันทึก
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fa-solid fa-times me-1"></i>ยกเลิก
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">รายการประเภทการแข่งขันทั้งหมด</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>ชื่อประเภท</th>
                                <th>ระยะทาง (km)</th>
                                <th>เวลาปล่อยตัว</th>
                                <th>Cut-off</th>
                                <th>ของที่ระลึก</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $categories = $pdo->query("SELECT * FROM RACE_CATEGORY ORDER BY distance_km")->fetchAll();
                                foreach ($categories as $cat) {
                                    echo "<tr>";
                                    echo "<td>{$cat['category_id']}</td>";
                                    echo "<td>{$cat['name']}</td>";
                                    echo "<td>" . number_format($cat['distance_km'], 1) . "</td>";
                                    echo "<td>" . substr($cat['start_time'], 0, 5) . "</td>";
                                    echo "<td>" . (isset($cat['time_limit']) ? substr($cat['time_limit'], 0, 5) : '-') . "</td>";
                                    echo "<td>" . ($cat['giveaway_type'] ?? '-') . "</td>";
                                    echo "<td class='text-center'>";
                                    echo "<button class='btn btn-sm btn-warning me-1' onclick='editCategory({$cat['category_id']})'>";
                                    echo "<i class='fa-solid fa-edit'></i></button>";
                                    echo "<button class='btn btn-sm btn-danger' onclick='deleteCategory({$cat['category_id']})'>";
                                    echo "<i class='fa-solid fa-trash'></i></button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='7' class='text-center text-muted'>ไม่มีข้อมูล</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/alert-popup.js"></script>
    <script>
        function resetForm() {
            document.getElementById('category-form').reset();
            document.getElementById('form-action').value = 'create';
            document.getElementById('category_id').value = '';
            document.getElementById('form-title').textContent = 'เพิ่มประเภทการแข่งขันใหม่';
            document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-save me-1"></i>บันทึก';
        }

        function editCategory(id) {
            fetch(`crud_category.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const category = data.data;
                        document.getElementById('form-action').value = 'update';
                        document.getElementById('category_id').value = category.category_id;
                        document.getElementById('name').value = category.name;
                        document.getElementById('distance_km').value = category.distance_km;
                        document.getElementById('start_time').value = category.start_time;
                        document.getElementById('time_limit').value = category.time_limit;
                        document.getElementById('giveaway_type').value = category.giveaway_type;
                        
                        document.getElementById('form-title').textContent = 'แก้ไขประเภทการแข่งขัน';
                        document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-save me-1"></i>อัปเดต';
                        
                        document.getElementById('category-form').scrollIntoView();
                    }
                });
        }

        function deleteCategory(id) {
            showConfirm('คุณแน่ใจหรือไม่ที่จะลบประเภทการแข่งขันนี้?', 'ยืนยันการลบ', () => {
                fetch('crud_category.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&category_id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(data.message, 'สำเร็จ', () => location.reload());
                    } else {
                        showError(data.message, 'เกิดข้อผิดพลาด');
                    }
                });
            });
        }
    </script>
</body>

</html>