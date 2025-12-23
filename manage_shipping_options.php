<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการตัวเลือกการจัดส่ง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-truck-fast me-2"></i>จัดการตัวเลือกการจัดส่ง</span>
            <a href="admin_index.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>กลับหน้าหลัก
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Add/Edit Form -->
        <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
            <div class="card-header bg-white">
                <h5 class="mb-0" id="form-title">เพิ่มตัวเลือกการจัดส่งใหม่</h5>
            </div>
            <div class="card-body">
                <form id="shipping-form" action="crud_shipping_option.php" method="POST">
                    <input type="hidden" name="action" value="create" id="form-action">
                    <input type="hidden" name="shipping_id" id="shipping_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">รูปแบบการรับ</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">-- เลือกรูปแบบการรับ --</option>
                                <option value="EMS">จัดส่งทางไปรษณีย์ (EMS)</option>
                                <option value="Pickup">รับด้วยตัวเอง (Pickup)</option>
                                <option value="Kerry">Kerry Express</option>
                                <option value="Flash">Flash Express</option>
                                <option value="J&T">J&T Express</option>
                                <option value="DHL">DHL</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ค่าบริการจัดส่ง (บาท)</label>
                            <input type="number" step="0.01" name="cost" id="cost" class="form-control" value="0.00" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">รายละเอียดเพิ่มเติม</label>
                            <textarea name="detail" id="detail" class="form-control" rows="3" placeholder="ระบุรายละเอียดเพิ่มเติม เช่น สถานที่รับ วันเวลา หรือเงื่อนไขพิเศษ"></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <i class="fa-solid fa-save me-1"></i>บันทึก
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="resetForm()">
                            <i class="fa-solid fa-times me-1"></i>ยกเลิก
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM SHIPPING_OPTION WHERE cost = 0")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h5>
                        <small>ฟรี</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM SHIPPING_OPTION WHERE cost > 0 AND cost <= 50")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h5>
                        <small>1-50 บาท</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h5>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM SHIPPING_OPTION WHERE cost > 50 AND cost <= 100")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h5>
                        <small>51-100 บาท</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h5>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM SHIPPING_OPTION WHERE cost > 100")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h5>
                        <small>100+ บาท</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">รายการตัวเลือกการจัดส่งทั้งหมด</h5>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="cost-filter" style="width: 150px;">
                        <option value="">ทุกราคา</option>
                        <option value="free">ฟรี</option>
                        <option value="low">1-50 บาท</option>
                        <option value="medium">51-100 บาท</option>
                        <option value="high">100+ บาท</option>
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
                                <th>รูปแบบการรับ</th>
                                <th>ค่าบริการ (บาท)</th>
                                <th>รายละเอียด</th>
                                <th>วันที่สร้าง</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="shipping-table">
                            <?php
                            try {
                                $shippings = $pdo->query("SELECT * FROM SHIPPING_OPTION ORDER BY cost, type")->fetchAll();
                                foreach ($shippings as $ship) {
                                    $costClass = match(true) {
                                        $ship['cost'] == 0 => 'bg-success',
                                        $ship['cost'] <= 50 => 'bg-info',
                                        $ship['cost'] <= 100 => 'bg-warning text-dark',
                                        default => 'bg-danger'
                                    };
                                    
                                    $costCategory = match(true) {
                                        $ship['cost'] == 0 => 'free',
                                        $ship['cost'] <= 50 => 'low',
                                        $ship['cost'] <= 100 => 'medium',
                                        default => 'high'
                                    };
                                    
                                    echo "<tr data-cost-category='{$costCategory}'>";
                                    echo "<td>{$ship['shipping_id']}</td>";
                                    echo "<td>";
                                    echo "<i class='fa-solid fa-truck me-2'></i>";
                                    echo "{$ship['type']}";
                                    echo "</td>";
                                    echo "<td><span class='badge {$costClass}'>" . number_format($ship['cost'], 2) . "</span></td>";
                                    echo "<td>" . (strlen($ship['detail']) > 50 ? substr($ship['detail'], 0, 50) . '...' : ($ship['detail'] ?? '-')) . "</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($ship['created_at'])) . "</td>";
                                    echo "<td class='text-center'>";
                                    echo "<button class='btn btn-sm btn-warning me-1' onclick='editShipping({$ship['shipping_id']})'>";
                                    echo "<i class='fa-solid fa-edit'></i></button>";
                                    echo "<button class='btn btn-sm btn-danger' onclick='deleteShipping({$ship['shipping_id']})'>";
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
            document.getElementById('shipping-form').reset();
            document.getElementById('form-action').value = 'create';
            document.getElementById('shipping_id').value = '';
            document.getElementById('form-title').textContent = 'เพิ่มตัวเลือกการจัดส่งใหม่';
            document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-save me-1"></i>บันทึก';
            document.getElementById('cost').value = '0.00';
        }

        function editShipping(id) {
            fetch(`crud_shipping_option.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const shipping = data.data;
                        document.getElementById('form-action').value = 'update';
                        document.getElementById('shipping_id').value = shipping.shipping_id;
                        document.getElementById('type').value = shipping.type;
                        document.getElementById('cost').value = shipping.cost;
                        document.getElementById('detail').value = shipping.detail || '';
                        
                        document.getElementById('form-title').textContent = 'แก้ไขตัวเลือกการจัดส่ง';
                        document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-save me-1"></i>อัปเดต';
                        
                        document.getElementById('shipping-form').scrollIntoView();
                    }
                });
        }

        function deleteShipping(id) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะลบตัวเลือกการจัดส่งนี้?')) {
                fetch('crud_shipping_option.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&shipping_id=${id}`
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
            const costFilter = document.getElementById('cost-filter').value;
            const search = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('#shipping-table tr');
            
            rows.forEach(row => {
                let show = true;
                
                if (costFilter && row.dataset.costCategory !== costFilter) show = false;
                if (search && !row.textContent.toLowerCase().includes(search)) show = false;
                
                row.style.display = show ? '' : 'none';
            });
        }

        // Auto-apply filters
        document.getElementById('cost-filter').addEventListener('change', applyFilters);
        document.getElementById('search-input').addEventListener('keyup', applyFilters);
    </script>
</body>

</html>