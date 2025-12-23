<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Price Rate Setting</title>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark">ตั้งค่าอัตราค่าสมัคร (Price Rate)</div>
            <div class="card-body">
                <form action="save_price_rate.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ประเภทการแข่งขัน</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- เลือกประเภทการแข่งขัน --</option>
                                <?php
                                try {
                                    $cats = $pdo->query("SELECT category_id, name, distance_km FROM RACE_CATEGORY ORDER BY distance_km")->fetchAll();
                                    foreach ($cats as $c) {
                                        echo "<option value='{$c['category_id']}'>{$c['name']} ({$c['distance_km']} km)</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>ไม่พบประเภทการแข่งขัน - กรุณาเพิ่มข้อมูลก่อน</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ประเภทนักวิ่ง</label>
                            <select name="runner_type" class="form-select" required>
                                <option value="">-- เลือกประเภทนักวิ่ง --</option>
                                <option value="Standard">Standard (ทั่วไป)</option>
                                <option value="Senior 70+">Senior 70+ (ผู้สูงอายุ)</option>
                                <option value="Disabled">Disabled (ผู้พิการ)</option>
                                <option value="Student">Student (นักเรียน/นักศึกษา)</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">ค่าสมัคร (บาท)</label>
                            <input type="number" step="0.01" name="amount" class="form-control"
                                placeholder="เช่น 900.00" min="0" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning mt-3">บันทึกอัตราค่าสมัคร</button>
                </form>
            </div>
        </div>

        <!-- แสดงรายการอัตราค่าสมัครที่มีอยู่ -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">รายการอัตราค่าสมัครที่มีอยู่</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ประเภทการแข่งขัน</th>
                                <th>ประเภทนักวิ่ง</th>
                                <th>ค่าสมัคร (บาท)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $prices = $pdo->query("SELECT pr.*, rc.name as category_name 
                                                      FROM PRICE_RATE pr 
                                                      JOIN RACE_CATEGORY rc ON pr.category_id = rc.category_id 
                                                      ORDER BY rc.name, pr.runner_type")->fetchAll();
                                foreach ($prices as $price) {
                                    echo "<tr>";
                                    echo "<td>{$price['price_id']}</td>";
                                    echo "<td>{$price['category_name']}</td>";
                                    echo "<td>{$price['runner_type']}</td>";
                                    echo "<td>" . number_format($price['amount'], 2) . "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='4' class='text-center text-muted'>ยังไม่มีข้อมูล</td></tr>";
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