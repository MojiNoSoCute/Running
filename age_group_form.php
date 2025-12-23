<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Age Group Setting</title>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-secondary text-white">ตั้งค่ากลุ่มอายุ (Age Group)</div>
            <div class="card-body">
                <form action="save_age_group.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ประเภทการแข่งขัน</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- เลือกประเภทการแข่งขัน --</option>
                                <?php
                                try {
                                    $cats = $pdo->query("SELECT category_id, name FROM RACE_CATEGORY ORDER BY name")->fetchAll();
                                    foreach ($cats as $c) {
                                        echo "<option value='{$c['category_id']}'>{$c['name']}</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>ไม่พบประเภทการแข่งขัน - กรุณาเพิ่มข้อมูลก่อน</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">เพศ</label>
                            <select name="gender" class="form-select" required>
                                <option value="">-- เลือกเพศ --</option>
                                <option value="M">Male (ชาย)</option>
                                <option value="F">Female (หญิง)</option>
                                <option value="A">All (ทุกเพศ)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">อายุเริ่มต้น (Min Age)</label>
                            <input type="number" name="min_age" class="form-control" placeholder="เช่น 18" min="0" max="100" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">อายุสิ้นสุด (Max Age)</label>
                            <input type="number" name="max_age" class="form-control" placeholder="เช่น 29" min="0" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ชื่อกลุ่ม (Label)</label>
                            <input type="text" name="label" class="form-control" placeholder="เช่น 18-29 ปี" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-secondary mt-3">บันทึกกลุ่มอายุ</button>
                </form>
            </div>
        </div>

        <!-- แสดงรายการกลุ่มอายุที่มีอยู่ -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">รายการกลุ่มอายุที่มีอยู่</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ประเภทการแข่งขัน</th>
                                <th>เพศ</th>
                                <th>อายุ</th>
                                <th>ชื่อกลุ่ม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $ageGroups = $pdo->query("SELECT ag.*, rc.name as category_name 
                                                         FROM AGE_GROUP ag 
                                                         JOIN RACE_CATEGORY rc ON ag.category_id = rc.category_id 
                                                         ORDER BY rc.name, ag.min_age")->fetchAll();
                                foreach ($ageGroups as $ag) {
                                    $genderText = $ag['gender'] == 'M' ? 'ชาย' : ($ag['gender'] == 'F' ? 'หญิง' : 'ทุกเพศ');
                                    echo "<tr>";
                                    echo "<td>{$ag['age_group_id']}</td>";
                                    echo "<td>{$ag['category_name']}</td>";
                                    echo "<td>{$genderText}</td>";
                                    echo "<td>{$ag['min_age']}-{$ag['max_age']} ปี</td>";
                                    echo "<td>{$ag['label']}</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='5' class='text-center text-muted'>ยังไม่มีข้อมูล</td></tr>";
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