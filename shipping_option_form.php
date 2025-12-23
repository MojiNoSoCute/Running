<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Shipping Options</title>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-header bg-white">เพิ่มตัวเลือกการส่ง/รับของ</div>
            <div class="card-body">
                <form action="save_shipping.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">รูปแบบการรับ</label>
                            <select name="type" class="form-select" required>
                                <option value="">-- เลือกรูปแบบการรับ --</option>
                                <option value="EMS">จัดส่งทางไปรษณีย์ (EMS)</option>
                                <option value="Pickup">รับด้วยตัวเอง (Pickup)</option>
                                <option value="Kerry">Kerry Express</option>
                                <option value="Flash">Flash Express</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ค่าบริการจัดส่ง (บาท)</label>
                            <input type="number" step="0.01" name="cost" class="form-control" value="0.00" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">รายละเอียดเพิ่มเติม (ที่อยู่จัดส่ง/วันเวลานัดรับ)</label>
                            <textarea name="detail" class="form-control" rows="2" placeholder="ระบุรายละเอียดเพิ่มเติม เช่น สถานที่รับ วันเวลา หรือเงื่อนไขพิเศษ"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3 px-4">บันทึกตัวเลือกการส่ง</button>
                </form>
            </div>
        </div>

        <!-- แสดงรายการตัวเลือกการจัดส่งที่มีอยู่ -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">รายการตัวเลือกการจัดส่งที่มีอยู่</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>รูปแบบการรับ</th>
                                <th>ค่าบริการ (บาท)</th>
                                <th>รายละเอียด</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $shippings = $pdo->query("SELECT * FROM SHIPPING_OPTION ORDER BY cost")->fetchAll();
                                foreach ($shippings as $ship) {
                                    echo "<tr>";
                                    echo "<td>{$ship['shipping_id']}</td>";
                                    echo "<td>{$ship['type']}</td>";
                                    echo "<td>" . number_format($ship['cost'], 2) . "</td>";
                                    echo "<td>" . ($ship['detail'] ?? '-') . "</td>";
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