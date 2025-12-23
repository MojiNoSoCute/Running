<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Running 2026 - Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/alert-popup.css">
    <style>
        .menu-card {
            transition: transform 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .menu-card:hover {
            transform: translateY(-5px);
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }

        .stats-card-2 {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
        }

        .stats-card-3 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-radius: 15px;
        }

        .stats-card-4 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            border-radius: 15px;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-person-running me-2"></i> Running 2026 Admin</span>
            <div class="navbar-nav">
                <a class="nav-link text-warning" href="index.php"><i class="fa-solid fa-home me-1"></i>กลับหน้าหลัก</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- สถิติระบบ -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-users fa-2x mb-2"></i>
                        <h3 class="mb-0">
                            <?php
                            try {
                                $count = $pdo->query("SELECT COUNT(*) FROM RUNNER")->fetchColumn();
                                echo number_format($count);
                            } catch (PDOException $e) {
                                echo "0";
                            }
                            ?>
                        </h3>
                        <small>นักวิ่งทั้งหมด</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card-2 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-file-signature fa-2x mb-2"></i>
                        <h3 class="mb-0">
                            <?php
                            try {
                                $count = $pdo->query("SELECT COUNT(*) FROM REGISTRATION")->fetchColumn();
                                echo number_format($count);
                            } catch (PDOException $e) {
                                echo "0";
                            }
                            ?>
                        </h3>
                        <small>การสมัครทั้งหมด</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card-3 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-check-circle fa-2x mb-2"></i>
                        <h3 class="mb-0">
                            <?php
                            try {
                                $count = $pdo->query("SELECT COUNT(*) FROM REGISTRATION WHERE status = 'Paid'")->fetchColumn();
                                echo number_format($count);
                            } catch (PDOException $e) {
                                echo "0";
                            }
                            ?>
                        </h3>
                        <small>ชำระเงินแล้ว</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card-4 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-dollar-sign fa-2x mb-2"></i>
                        <h3 class="mb-0">
                            <?php
                            try {
                                $total = $pdo->query("SELECT SUM(p.amount) FROM REGISTRATION r JOIN PRICE_RATE p ON r.price_id = p.price_id WHERE r.status = 'Paid'")->fetchColumn();
                                echo number_format($total ?? 0);
                            } catch (PDOException $e) {
                                echo "0";
                            }
                            ?>
                        </h3>
                        <small>รายได้ (บาท)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4 mb-4">
            <div class="col-12 mb-3">
                <h4 class="border-start border-4 border-success ps-2">การเพิ่มข้อมูลด่วน</h4>
            </div>
            
            <div class="col-md-3 mb-3">
                <a href="runner_form.php" class="btn btn-outline-primary w-100 py-3">
                    <i class="fa-solid fa-user-plus d-block mb-2 fa-2x"></i>
                    เพิ่มนักวิ่งใหม่
                </a>
            </div>
            
            <div class="col-md-3 mb-3">
                <a href="registration_form.php" class="btn btn-outline-success w-100 py-3">
                    <i class="fa-solid fa-file-signature d-block mb-2 fa-2x"></i>
                    สมัครแข่งขัน
                </a>
            </div>
            
            <div class="col-md-3 mb-3">
                <a href="payment_form.php" class="btn btn-outline-warning w-100 py-3">
                    <i class="fa-solid fa-receipt d-block mb-2 fa-2x"></i>
                    แจ้งชำระเงิน
                </a>
            </div>
            
            <div class="col-md-3 mb-3">
                <a href="race_category_form.php" class="btn btn-outline-info w-100 py-3">
                    <i class="fa-solid fa-layer-group d-block mb-2 fa-2x"></i>
                    เพิ่มประเภทแข่งขัน
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-3">
                <h4 class="border-start border-4 border-primary ps-2">การจัดการข้อมูล (CRUD)</h4>
            </div>

            <div class="col-md-4 mb-4">
                <a href="manage_runners.php" class="card h-100 shadow-sm menu-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-primary text-white mx-auto">
                            <i class="fa-solid fa-user-plus fa-lg"></i>
                        </div>
                        <h5>จัดการนักวิ่ง</h5>
                        <p class="text-muted small">จัดการข้อมูลประวัติส่วนตัวนักวิ่ง (CRUD)</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4 mb-4">
                <a href="manage_registrations.php" class="card h-100 shadow-sm menu-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-success text-white mx-auto">
                            <i class="fa-solid fa-file-signature fa-lg"></i>
                        </div>
                        <h5>จัดการการสมัคร</h5>
                        <p class="text-muted small">จัดการใบสมัครการแข่งขัน (CRUD)</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4 mb-4">
                <a href="manage_payments.php" class="card h-100 shadow-sm menu-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-warning text-dark mx-auto">
                            <i class="fa-solid fa-receipt fa-lg"></i>
                        </div>
                        <h5>จัดการการชำระเงิน</h5>
                        <p class="text-muted small">จัดการหลักฐานการโอนเงิน (CRUD)</p>
                    </div>
                </a>
            </div>

            <div class="col-12 mt-4 mb-3">
                <h4 class="border-start border-4 border-secondary ps-2">การตั้งค่าระบบ (Master Data)</h4>
            </div>

            <div class="col-md-6 mb-4">
                <a href="manage_categories.php" class="card h-100 shadow-sm menu-card border-start border-info border-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <i class="fa-solid fa-layer-group text-info fa-3x"></i>
                            </div>
                            <div class="col-9">
                                <h6 class="mb-1">ประเภทการแข่งขัน</h6>
                                <small class="text-muted">จัดการประเภทและระยะทางการแข่งขัน</small>
                                <div class="mt-1">
                                    <span class="badge bg-info">
                                        <?php
                                        try {
                                            $count = $pdo->query("SELECT COUNT(*) FROM RACE_CATEGORY")->fetchColumn();
                                            echo "{$count} รายการ";
                                        } catch (PDOException $e) {
                                            echo "0 รายการ";
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 mb-4">
                <a href="manage_age_groups.php" class="card h-100 shadow-sm menu-card border-start border-secondary border-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <i class="fa-solid fa-users-line text-secondary fa-3x"></i>
                            </div>
                            <div class="col-9">
                                <h6 class="mb-1">กลุ่มอายุ (Age Group)</h6>
                                <small class="text-muted">จัดการการแบ่งกลุ่มอายุนักวิ่ง</small>
                                <div class="mt-1">
                                    <span class="badge bg-secondary">
                                        <?php
                                        try {
                                            $count = $pdo->query("SELECT COUNT(*) FROM AGE_GROUP")->fetchColumn();
                                            echo "{$count} รายการ";
                                        } catch (PDOException $e) {
                                            echo "0 รายการ";
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 mb-4">
                <a href="manage_price_rates.php" class="card h-100 shadow-sm menu-card border-start border-danger border-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <i class="fa-solid fa-tags text-danger fa-3x"></i>
                            </div>
                            <div class="col-9">
                                <h6 class="mb-1">อัตราค่าสมัคร (Price)</h6>
                                <small class="text-muted">จัดการราคาค่าสมัครแต่ละประเภท</small>
                                <div class="mt-1">
                                    <span class="badge bg-danger">
                                        <?php
                                        try {
                                            $count = $pdo->query("SELECT COUNT(*) FROM PRICE_RATE")->fetchColumn();
                                            echo "{$count} รายการ";
                                        } catch (PDOException $e) {
                                            echo "0 รายการ";
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 mb-4">
                <a href="manage_shipping_options.php" class="card h-100 shadow-sm menu-card border-start border-dark border-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <i class="fa-solid fa-truck-fast text-dark fa-3x"></i>
                            </div>
                            <div class="col-9">
                                <h6 class="mb-1">ตัวเลือกการจัดส่ง</h6>
                                <small class="text-muted">จัดการวิธีการส่งมอบ BIB และเสื้อ</small>
                                <div class="mt-1">
                                    <span class="badge bg-dark">
                                        <?php
                                        try {
                                            $count = $pdo->query("SELECT COUNT(*) FROM SHIPPING_OPTION")->fetchColumn();
                                            echo "{$count} รายการ";
                                        } catch (PDOException $e) {
                                            echo "0 รายการ";
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <!-- รายงานและข้อมูลสำคัญ -->
        <div class="row mt-5">
            <div class="col-12 mb-3">
                <h4 class="border-start border-4 border-warning ps-2">รายงานและข้อมูลสำคัญ</h4>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fa-solid fa-chart-bar me-2"></i>สถิติการสมัครตามประเภท</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ประเภท</th>
                                        <th class="text-center">จำนวน</th>
                                        <th class="text-center">ชำระแล้ว</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $stats = $pdo->query("SELECT rc.name, 
                                                            COUNT(r.reg_id) as total,
                                                            SUM(CASE WHEN r.status = 'Paid' THEN 1 ELSE 0 END) as paid
                                                            FROM RACE_CATEGORY rc 
                                                            LEFT JOIN REGISTRATION r ON rc.category_id = r.category_id 
                                                            GROUP BY rc.category_id, rc.name")->fetchAll();
                                        foreach ($stats as $stat) {
                                            echo "<tr>";
                                            echo "<td>{$stat['name']}</td>";
                                            echo "<td class='text-center'>{$stat['total']}</td>";
                                            echo "<td class='text-center'><span class='badge bg-success'>{$stat['paid']}</span></td>";
                                            echo "</tr>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='3' class='text-center text-muted'>ไม่มีข้อมูล</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fa-solid fa-clock me-2"></i>การสมัครล่าสุด</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>นักวิ่ง</th>
                                        <th>ประเภท</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $recent = $pdo->query("SELECT ru.first_name, ru.last_name, rc.name, r.status 
                                                             FROM REGISTRATION r 
                                                             JOIN RUNNER ru ON r.runner_id = ru.runner_id 
                                                             JOIN RACE_CATEGORY rc ON r.category_id = rc.category_id 
                                                             ORDER BY r.reg_id DESC LIMIT 5")->fetchAll();
                                        foreach ($recent as $reg) {
                                            $statusClass = $reg['status'] == 'Paid' ? 'bg-success' : 'bg-warning';
                                            echo "<tr>";
                                            echo "<td>{$reg['first_name']} {$reg['last_name']}</td>";
                                            echo "<td><small>{$reg['name']}</small></td>";
                                            echo "<td><span class='badge {$statusClass} small'>{$reg['status']}</span></td>";
                                            echo "</tr>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='3' class='text-center text-muted'>ไม่มีข้อมูล</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-info border-0 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="fa-solid fa-circle-info me-2"></i> คำแนะนำ:</strong>
                            ควรเริ่มจากการตั้งค่า "ประเภทการแข่งขัน" และ "อัตราค่าสมัคร"
                            ก่อนเป็นอันดับแรกเพื่อให้ระบบทำงานได้ครบถ้วน
                        </div>
                        <div>
                            <a href="example_with_popups.php" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-bell me-1"></i>ทดสอบ Popup Alert
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 text-muted mt-5">
        <small>&copy; 2025 Running System - Database: running</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/alert-popup.js"></script>
</body>

</html>