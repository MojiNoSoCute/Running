<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการสมัคร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/alert-popup.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-file-signature me-2"></i>จัดการการสมัคร</span>
            <a href="admin_index.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>กลับหน้าหลัก
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">สถานะ</label>
                        <select class="form-select" id="status-filter">
                            <option value="">ทั้งหมด</option>
                            <option value="Pending">รอชำระเงิน</option>
                            <option value="Paid">ชำระแล้ว</option>
                            <option value="Cancelled">ยกเลิก</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ประเภทการแข่งขัน</label>
                        <select class="form-select" id="category-filter">
                            <option value="">ทั้งหมด</option>
                            <?php
                            $categories = $pdo->query("SELECT * FROM RACE_CATEGORY ORDER BY name")->fetchAll();
                            foreach ($categories as $cat) {
                                echo "<option value='{$cat['category_id']}'>{$cat['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ค้นหา</label>
                        <input type="text" class="form-control" placeholder="ชื่อนักวิ่ง..." id="search-input">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" onclick="applyFilters()">
                            <i class="fa-solid fa-search me-1"></i>ค้นหา
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">รายการการสมัครทั้งหมด</h5>
                <div>
                    <button class="btn btn-success btn-sm" onclick="exportData()">
                        <i class="fa-solid fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Reg ID</th>
                                <th>นักวิ่ง</th>
                                <th>ประเภท</th>
                                <th>ไซส์เสื้อ</th>
                                <th>วันที่สมัคร</th>
                                <th>ค่าสมัคร</th>
                                <th>สถานะ</th>
                                <th>BIB</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="registrations-table">
                            <?php
                            try {
                                $sql = "SELECT r.*, ru.first_name, ru.last_name, rc.name as category_name, pr.amount
                                       FROM REGISTRATION r 
                                       JOIN RUNNER ru ON r.runner_id = ru.runner_id 
                                       JOIN RACE_CATEGORY rc ON r.category_id = rc.category_id 
                                       JOIN PRICE_RATE pr ON r.price_id = pr.price_id
                                       ORDER BY r.reg_id DESC";
                                $registrations = $pdo->query($sql)->fetchAll();
                                
                                foreach ($registrations as $reg) {
                                    $statusClass = match($reg['status']) {
                                        'Paid' => 'bg-success',
                                        'Cancelled' => 'bg-danger',
                                        default => 'bg-warning'
                                    };
                                    
                                    echo "<tr data-status='{$reg['status']}' data-category='{$reg['category_id']}'>";
                                    echo "<td>#{$reg['reg_id']}</td>";
                                    echo "<td>{$reg['first_name']} {$reg['last_name']}</td>";
                                    echo "<td>{$reg['category_name']}</td>";
                                    echo "<td>{$reg['shirt_size']}</td>";
                                    echo "<td>{$reg['reg_date']}</td>";
                                    echo "<td>" . number_format($reg['amount'], 2) . " บาท</td>";
                                    echo "<td><span class='badge {$statusClass}'>{$reg['status']}</span></td>";
                                    echo "<td>" . ($reg['bib_number'] ?? '-') . "</td>";
                                    echo "<td class='text-center'>";
                                    echo "<div class='btn-group' role='group'>";
                                    echo "<button class='btn btn-sm btn-info' onclick='viewDetails({$reg['reg_id']})'>";
                                    echo "<i class='fa-solid fa-eye'></i></button>";
                                    echo "<button class='btn btn-sm btn-warning' onclick='editRegistration({$reg['reg_id']})'>";
                                    echo "<i class='fa-solid fa-edit'></i></button>";
                                    if ($reg['status'] !== 'Cancelled') {
                                        echo "<button class='btn btn-sm btn-danger' onclick='cancelRegistration({$reg['reg_id']})'>";
                                        echo "<i class='fa-solid fa-times'></i></button>";
                                    }
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='9' class='text-center text-muted'>ไม่มีข้อมูล</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขการสมัคร</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-form">
                        <input type="hidden" id="edit-reg-id">
                        <div class="mb-3">
                            <label class="form-label">ไซส์เสื้อ</label>
                            <select class="form-select" id="edit-shirt-size">
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หมายเลข BIB</label>
                            <input type="text" class="form-control" id="edit-bib-number" placeholder="เช่น A001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สถานะ</label>
                            <select class="form-select" id="edit-status">
                                <option value="Pending">รอชำระเงิน</option>
                                <option value="Paid">ชำระแล้ว</option>
                                <option value="Cancelled">ยกเลิก</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="saveChanges()">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/alert-popup.js"></script>
    <script>
        function applyFilters() {
            const status = document.getElementById('status-filter').value;
            const category = document.getElementById('category-filter').value;
            const search = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('#registrations-table tr');
            
            rows.forEach(row => {
                let show = true;
                
                if (status && row.dataset.status !== status) show = false;
                if (category && row.dataset.category !== category) show = false;
                if (search && !row.textContent.toLowerCase().includes(search)) show = false;
                
                row.style.display = show ? '' : 'none';
            });
        }

        function viewDetails(regId) {
            // Implementation for viewing registration details
            alert('View details for registration #' + regId);
        }

        function editRegistration(regId) {
            fetch(`crud_registration.php?action=get&id=${regId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const reg = data.data;
                        document.getElementById('edit-reg-id').value = reg.reg_id;
                        document.getElementById('edit-shirt-size').value = reg.shirt_size;
                        document.getElementById('edit-bib-number').value = reg.bib_number || '';
                        document.getElementById('edit-status').value = reg.status;
                        
                        new bootstrap.Modal(document.getElementById('editModal')).show();
                    }
                });
        }

        function saveChanges() {
            const regId = document.getElementById('edit-reg-id').value;
            const shirtSize = document.getElementById('edit-shirt-size').value;
            const bibNumber = document.getElementById('edit-bib-number').value;
            const status = document.getElementById('edit-status').value;
            
            fetch('crud_registration.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&reg_id=${regId}&shirt_size=${shirtSize}&bib_number=${bibNumber}&status=${status}`
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

        function cancelRegistration(regId) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะยกเลิกการสมัครนี้?')) {
                fetch('crud_registration.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=cancel&reg_id=${regId}`
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

        function exportData() {
            window.open('export_registrations.php', '_blank');
        }

        // Auto-apply filters on change
        document.getElementById('status-filter').addEventListener('change', applyFilters);
        document.getElementById('category-filter').addEventListener('change', applyFilters);
        document.getElementById('search-input').addEventListener('keyup', applyFilters);
    </script>
</body>

</html>