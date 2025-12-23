<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Event Registration</title>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">ลงสมัครกิจกรรมวิ่ง</div>
            <div class="card-body">
                <form action="save_registration.php" method="POST">
                    <div class="mb-3">
                        <label>เลือกนักวิ่ง</label>
                        <select name="runner_id" class="form-select" required>
                            <option value="">-- เลือกนักวิ่ง --</option>
                            <?php
                            try {
                                $runners = $pdo->query("SELECT runner_id, first_name, last_name, citizen_id FROM RUNNER ORDER BY first_name")->fetchAll();
                                foreach ($runners as $r) {
                                    echo "<option value='{$r['runner_id']}'>{$r['first_name']} {$r['last_name']} (ID: {$r['citizen_id']})</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>ไม่พบข้อมูลนักวิ่ง - กรุณาเพิ่มข้อมูลนักวิ่งก่อน</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>ประเภทระยะทาง</label>
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
                            <label>ไซส์เสื้อ</label>
                            <select name="shirt_size" class="form-select" required>
                                <option value="">-- เลือกไซส์ --</option>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>รูปแบบการรับ BIB/เสื้อ</label>
                        <select name="shipping_id" class="form-select" required>
                            <option value="">-- เลือกวิธีการรับ --</option>
                            <?php
                            try {
                                $ships = $pdo->query("SELECT shipping_id, type, cost FROM SHIPPING_OPTION ORDER BY cost")->fetchAll();
                                foreach ($ships as $s) {
                                    echo "<option value='{$s['shipping_id']}'>{$s['type']} (+{$s['cost']} บาท)</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>ไม่พบตัวเลือกการจัดส่ง - กรุณาเพิ่มข้อมูลก่อน</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success w-100">ยืนยันการสมัคร</button>
                </form>
            </div>
        </div>

        <!-- แสดงรายการการสมัครล่าสุด -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">รายการการสมัครล่าสุด</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Reg ID</th>
                                <th>นักวิ่ง</th>
                                <th>ประเภท</th>
                                <th>วันที่สมัคร</th>
                                <th>สถานะ</th>
                                <th>ค่าสมัคร</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $regs = $pdo->query("SELECT r.reg_id, ru.first_name, ru.last_name, rc.name as category_name, r.reg_date, r.status, pr.amount
                                                    FROM REGISTRATION r 
                                                    JOIN RUNNER ru ON r.runner_id = ru.runner_id 
                                                    JOIN RACE_CATEGORY rc ON r.category_id = rc.category_id 
                                                    JOIN PRICE_RATE pr ON r.price_id = pr.price_id
                                                    ORDER BY r.reg_id DESC LIMIT 10")->fetchAll();
                                foreach ($regs as $reg) {
                                    $statusClass = $reg['status'] == 'Paid' ? 'bg-success' : 'bg-warning';
                                    echo "<tr>";
                                    echo "<td>#{$reg['reg_id']}</td>";
                                    echo "<td>{$reg['first_name']} {$reg['last_name']}</td>";
                                    echo "<td>{$reg['category_name']}</td>";
                                    echo "<td>{$reg['reg_date']}</td>";
                                    echo "<td><span class='badge {$statusClass}'>{$reg['status']}</span></td>";
                                    echo "<td>" . number_format($reg['amount'], 2) . " บาท</td>";
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

    <script>
        // No JavaScript needed since price rate section was removed
    </script>
</body>

</html>