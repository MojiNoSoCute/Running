<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Runner Registration</title>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white">ข้อมูลส่วนตัวนักวิ่ง</div>
            <div class="card-body">
                <form action="save_runner.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6"><label>ชื่อ</label><input type="text" name="first_name"
                                class="form-control" required></div>
                        <div class="col-md-6"><label>นามสกุล</label><input type="text" name="last_name"
                                class="form-control" required></div>
                        <div class="col-md-4"><label>วันเกิด</label><input type="date" name="date_of_birth"
                                class="form-control" required></div>
                        <div class="col-md-4"><label>เพศ</label>
                            <select name="gender" class="form-select" required>
                                <option value="">-- เลือกเพศ --</option>
                                <option value="Male">ชาย</option>
                                <option value="Female">หญิง</option>
                            </select>
                        </div>
                        <div class="col-md-4"><label>อีเมล</label><input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">เลขบัตรประชาชน</label>
                            <input type="text" name="citizen_id" class="form-control" pattern="[0-9]{13}" maxlength="13" required>
                            <small class="text-muted">กรอก 13 หลัก</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control" pattern="[0-9]{10}" maxlength="10" required>
                            <small class="text-muted">กรอก 10 หลัก</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">ที่อยู่</label>
                            <textarea name="address" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_disabled" id="is_disabled">
                                <label class="form-check-label" for="is_disabled">
                                    ผู้พิการ (สำหรับส่วนลดค่าสมัคร)
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-info text-white mt-3">ลงทะเบียนข้อมูลส่วนตัว</button>
                </form>
            </div>
        </div>

        <!-- แสดงรายการนักวิ่งที่ลงทะเบียนแล้ว -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">รายการนักวิ่งที่ลงทะเบียนแล้ว</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>เพศ</th>
                                <th>อีเมล</th>
                                <th>เบอร์โทร</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $runners = $pdo->query("SELECT * FROM RUNNER ORDER BY runner_id DESC LIMIT 10")->fetchAll();
                                foreach ($runners as $runner) {
                                    echo "<tr>";
                                    echo "<td>{$runner['runner_id']}</td>";
                                    echo "<td>{$runner['first_name']} {$runner['last_name']}</td>";
                                    echo "<td>" . ($runner['gender'] == 'Male' ? 'ชาย' : 'หญิง') . "</td>";
                                    echo "<td>{$runner['email']}</td>";
                                    echo "<td>{$runner['phone']}</td>";
                                    echo "<td>" . ($runner['is_disabled'] ? '<span class="badge bg-info">ผู้พิการ</span>' : '<span class="badge bg-success">ปกติ</span>') . "</td>";
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