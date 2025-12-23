<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Payment Notification</title>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white">แจ้งหลักฐานการโอนเงิน</div>
                    <div class="card-body">
                        <form action="save_payment.php" method="POST">
                            <div class="mb-3">
                                <label>หมายเลขการสมัคร (Reg ID)</label>
                                <select name="reg_id" class="form-select" required>
                                    <option value="">-- เลือกการสมัคร --</option>
                                    <?php
                                    $regs = $pdo->query("SELECT r.reg_id, ru.first_name, ru.last_name, rc.name as category_name, r.status, pr.amount
                                                        FROM REGISTRATION r 
                                                        JOIN RUNNER ru ON r.runner_id = ru.runner_id 
                                                        JOIN RACE_CATEGORY rc ON r.category_id = rc.category_id 
                                                        JOIN PRICE_RATE pr ON r.price_id = pr.price_id
                                                        WHERE r.status = 'Pending'")->fetchAll();
                                    foreach ($regs as $reg) {
                                        echo "<option value='{$reg['reg_id']}'>#{$reg['reg_id']} - {$reg['first_name']} {$reg['last_name']} ({$reg['category_name']}) - {$reg['amount']} บาท</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>จำนวนเงินที่ชำระ</label>
                                <input type="number" step="0.01" name="total_amount" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>วันเวลาที่โอน</label>
                                <input type="datetime-local" name="payment_time" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>วิธีการชำระเงิน</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="Bank Transfer">โอนเงินผ่านธนาคาร</option>
                                    <option value="QR Code">QR Code</option>
                                    <option value="Cash">เงินสด</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">แจ้งชำระเงิน</button>
                        </form>
                    </div>
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