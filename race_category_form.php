<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Race Category Management</title>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">เพิ่มประเภทการแข่งขัน</div>
            <div class="card-body">
                <form action="save_category.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ชื่อประเภท (เช่น Marathon)</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">ระยะทาง (km)</label>
                            <input type="number" step="0.1" name="distance_km" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">เวลาปล่อยตัว</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">เวลาจำกัด (Cut-off)</label>
                            <input type="time" name="time_limit" class="form-control" required>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label class="form-label">ของที่ระลึก</label>
                            <input type="text" name="giveaway_type" class="form-control" placeholder="เช่น เสื้อ + เหรียญ" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">บันทึกประเภทการแข่งขัน</button>
                </form>
            </div>
        </div>

        <!-- แสดงรายการประเภทการแข่งขันที่มีอยู่ -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">รายการประเภทการแข่งขันที่มีอยู่</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ชื่อประเภท</th>
                                <th>ระยะทาง (km)</th>
                                <th>เวลาปล่อยตัว</th>
                                <th>Cut-off</th>
                                <th>ของที่ระลึก</th>
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
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='6' class='text-center text-muted'>ยังไม่มีข้อมูล</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4 mb-4">
        <a href="admin_index.php" class="btn btn-secondary me-2"><i class="fa-solid fa-arrow-left me-1"></i>กลับหน้าหลัก</a>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-home me-1"></i>หน้าเว็บไซต์</a>
    </div>
</body>

</html>