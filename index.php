<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBM RUN 2026 - หน้าหลัก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1533560916823-4246e27a6756?auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .category-card {
            border: none;
            border-radius: 15px;
            transition: 0.3s;
        }

        .category-card:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-register {
            border-radius: 30px;
            padding: 10px 30px;
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fa-solid fa-person-running text-warning me-2"></i>CBM RUN 2026
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">หน้าแรก</a></li>
                    <li class="nav-item"><a class="nav-link" href="registration_form.php">สมัครวิ่ง</a></li>
                    <li class="nav-item"><a class="nav-link" href="payment_form.php">แจ้งชำระเงิน</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="admin_index.php"><i
                                class="fa-solid fa-lock me-1"></i>สำหรับเจ้าหน้าที่</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container">
            <h1 class="display-3 fw-bold">ก้าวข้ามขีดจำกัดไปกับเรา</h1>
            <p class="lead mb-4">เปิดรับสมัครแล้ววันนี้! งานวิ่งเพื่อสุขภาพที่ใหญ่ที่สุดแห่งปี 2026</p>
            <a href="registration_form.php" class="btn btn-warning btn-lg btn-register">สมัครเลยตอนนี้ <i
                    class="fa-solid fa-arrow-right ms-2"></i></a>
        </div>
    </header>

    <div class="container my-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">ประเภทการแข่งขัน</h2>
            <div class="mx-auto bg-warning" style="height: 4px; width: 60px;"></div>
        </div>

        <div class="row g-4">
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM RACE_CATEGORY");
                while ($row = $stmt->fetch()) {
                    // กำหนดไอคอนตามระยะทาง
                    $icon = "fa-person-walking";
                    if ($row['distance_km'] > 20)
                        $icon = "fa-fire";
                    else if ($row['distance_km'] > 10)
                        $icon = "fa-bolt";
                    ?>
                    <div class="col-md-4">
                        <div class="card h-100 category-card shadow-sm text-center">
                            <div class="card-body py-5">
                                <div class="display-4 text-warning mb-3">
                                    <i class="fa-solid <?php echo $icon; ?>"></i>
                                </div>
                                <h3 class="card-title fw-bold"><?php echo $row['name']; ?></h3>
                                <h4 class="text-primary fw-bold"><?php echo number_format($row['distance_km'], 1); ?> KM</h4>
                                <ul class="list-unstyled mt-3 text-muted">
                                    <li><i class="fa-regular fa-clock me-2"></i>ปล่อยตัว:
                                        <?php echo substr($row['start_time'], 0, 5); ?> น.
                                    </li>
                                    <li><i class="fa-solid fa-hourglass-half me-2"></i>Cut-off:
                                        <?php echo substr($row['time_limit'], 0, 5); ?> ชม.
                                    </li>
                                    <li><i class="fa-solid fa-shirt me-2"></i>ของที่ระลึก: <?php echo $row['giveaway_type']; ?>
                                    </li>
                                </ul>
                                <a href="registration_form.php?cat=<?php echo $row['category_id']; ?>"
                                    class="btn btn-outline-dark btn-register mt-3">เลือกสมัครประเภทนี้</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } catch (PDOException $e) {
                echo "<p class='text-center text-danger'>กรุณาติดตั้งข้อมูล RACE_CATEGORY ในระบบหลังบ้านก่อน</p>";
            }
            ?>
        </div>
    </div>

    <div class="bg-white py-5 shadow-sm">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-md-4">
                    <div class="p-3">
                        <i class="fa-solid fa-user-edit mb-3 text-primary fa-2x"></i>
                        <h5>1. กรอกข้อมูล</h5>
                        <p class="text-muted small">บันทึกข้อมูลส่วนตัวและเลือกไซส์เสื้อ</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3">
                        <i class="fa-solid fa-credit-card mb-3 text-primary fa-2x"></i>
                        <h5>2. ชำระเงิน</h5>
                        <p class="text-muted small">โอนเงินผ่านบัญชีธนาคารหรือ QR Code</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3">
                        <i class="fa-solid fa-check-circle mb-3 text-primary fa-2x"></i>
                        <h5>3. รอรับ BIB</h5>
                        <p class="text-muted small">ตรวจสอบสถานะและรอรับเบอร์วิ่งทางไปรษณีย์</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 CBM RUN 2026 - All Rights Reserved</p>
            <small class="text-muted">Powered by PHP PDO & Bootstrap 5</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>