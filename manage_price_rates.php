<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการอัตราค่าสมัคร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-tags me-2"></i>จัดการอัตราค่าสมัคร</span>
            <a href="admin_index.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>กลับหน้าหลัก
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Add/Edit Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0" id="form-title">เพิ่มอัตราค่าสมัครใหม่</h5>
            </div>
            <div class="card-body">
                <form id="price-rate-form" action="crud_price_rate.php" method="POST">
                    <input type="hidden" name="action" value="create" id="form-action">
                    <input type="hidden" name="price_id" id="price_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ประเภทการแข่งขัน</label>
                            <select name="category_id" id="category_id" class="form-select" required>
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
                            <select name="runner_type" id="runner_type" class="form-select" required>
                                <option value="">-- เลือกประเภทนักวิ่ง --</option>
                                <option value="Standard">Standard (ทั่วไป)</option>
                                <option value="Senior 70+">Senior 70+ (ผู้สูงอายุ)</option>
                                <option value="Disabled">Disabled (ผู้พิการ)</option>
                                <option value="Student">Student (นักเรียน/นักศึกษา)</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">ค่าสมัคร (บาท)</label>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                                placeholder="เช่น 900.00" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning" id="submit-btn">
                            <i class="fa-solid fa-save me-1"></i>บันทึก
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                            <i class="fa-solid fa-times me-1"></i>ยกเลิก
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM PRICE_RATE WHERE runner_type = 'Standard'")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h5>
                        <small>Standard</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM PRICE_RATE WHERE runner_type = 'Senior 70+'")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h5>
                        <small>Senior 70+</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM PRICE_RATE WHERE runner_type = 'Disabled'")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h5>
                        <small>Disabled</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h5>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM PRICE_RATE WHERE runner_type = 'Student'")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h5>
                        <small>Student</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">รายการอัตราค่าสมัครทั้งหมด</h5>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="type-filter" style="width: 150px;">
                        <option value="">ทุกประเภท</option>
                        <option value="Standard">Standard</option>
                        <option value="Senior 70+">Senior 70+</option>
                        <option value="Disabled">Disabled</option>
                        <option value="Student">Student</option>
                    </select>
                    <input type="text" class="form-control form-control-sm" placeholder="ค้นหา..." id="search-input" style="width: 200px;">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>ประเภทการแข่งขัน</th>
                                <th>ประเภทนักวิ่ง</th>
                                <th>ค่าสมัคร (บาท)</th>
                                <th>วันที่สร้าง</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="price-rates-table">
                            <?php
                            try {
                                $prices = $pdo->query("SELECT pr.*, rc.name as category_name 
                                                      FROM PRICE_RATE pr 
                                                      JOIN RACE_CATEGORY rc ON pr.category_id = rc.category_id 
                                                      ORDER BY rc.name, pr.runner_type")->fetchAll();
                                foreach ($prices as $price) {
                                    $badgeClass = match($price['runner_type']) {
                                        'Standard' => 'bg-primary',
                                        'Senior 70+' => 'bg-info',
                                        'Disabled' => 'bg-success',
                                        'Student' => 'bg-secondary',
                                        default => 'bg-dark'
                                    };
                                    
                                    echo "<tr data-type='{$price['runner_type']}'>";
                                    echo "<td>{$price['price_id']}</td>";
                                    echo "<td>{$price['category_name']}</td>";
                                    echo "<td><span class='badge {$badgeClass}'>{$price['runner_type']}</span></td>";
                                    echo "<td>" . number_format($price['amount'], 2) . "</td>";
                                    echo "<td>" . (isset($price['created_at']) ? date('d/m/Y', strtotime($price['created_at'])) : '-') . "</td>";
                                    echo "<td class='text-center'>";
                                    echo "<button class='btn btn-sm btn-warning me-1' onclick='editPriceRate({$price['price_id']})'>";
                                    echo "<i class='fa-solid fa-edit'></i></button>";
                                    echo "<button class='btn btn-sm btn-danger' onclick='deletePriceRate({$price['price_id']})'>";
                                    echo "<i class='fa-solid fa-trash'></i></button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='6' class='text-center text-muted'>ไม่มีข้อมูล</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('price-rate-form').reset();
            document.getElementById('form-action').value = 'create';
            document.getElementById('price_id').value = '';
            document.getElementById('form-title').textContent = 'เพิ่มอัตราค่าสมัครใหม่';
            document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-save me-1"></i>บันทึก';
        }

        function editPriceRate(id) {
            fetch(`crud_price_rate.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const priceRate = data.data;
                        document.getElementById('form-action').value = 'update';
                        document.getElementById('price_id').value = priceRate.price_id;
                        document.getElementById('category_id').value = priceRate.category_id;
                        document.getElementById('runner_type').value = priceRate.runner_type;
                        document.getElementById('amount').value = priceRate.amount;
                        
                        document.getElementById('form-title').textContent = 'แก้ไขอัตราค่าสมัคร';
                        document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-save me-1"></i>อัปเดต';
                        
                        document.getElementById('price-rate-form').scrollIntoView();
                    }
                });
        }

        function deletePriceRate(id) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะลบอัตราค่าสมัครนี้?')) {
                fetch('crud_price_rate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&price_id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + data.message);
                    }
                });
            }
        }

        function applyFilters() {
            const type = document.getElementById('type-filter').value;
            const search = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('#price-rates-table tr');
            
            rows.forEach(row => {
                let show = true;
                
                if (type && row.dataset.type !== type) show = false;
                if (search && !row.textContent.toLowerCase().includes(search)) show = false;
                
                row.style.display = show ? '' : 'none';
            });
        }

        // Auto-apply filters
        document.getElementById('type-filter').addEventListener('change', applyFilters);
        document.getElementById('search-input').addEventListener('keyup', applyFilters);
    </script>
</body>

</html>