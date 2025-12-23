<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการชำระเงิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-receipt me-2"></i>จัดการการชำระเงิน</span>
            <a href="admin_index.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>กลับหน้าหลัก
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4>
                            <?php
                            $total = $pdo->query("SELECT SUM(total_amount) FROM PAYMENT WHERE status = 'Success'")->fetchColumn();
                            echo number_format($total ?? 0, 2);
                            ?>
                        </h4>
                        <small>รายได้รวม (บาท)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM PAYMENT WHERE status = 'Success'")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h4>
                        <small>ชำระสำเร็จ</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h4>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM PAYMENT WHERE status = 'Pending'")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h4>
                        <small>รอตรวจสอบ</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h4>
                            <?php
                            $count = $pdo->query("SELECT COUNT(*) FROM PAYMENT WHERE status = 'Failed'")->fetchColumn();
                            echo number_format($count);
                            ?>
                        </h4>
                        <small>ไม่สำเร็จ</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">สถานะ</label>
                        <select class="form-select" id="status-filter">
                            <option value="">ทั้งหมด</option>
                            <option value="Success">สำเร็จ</option>
                            <option value="Pending">รอตรวจสอบ</option>
                            <option value="Failed">ไม่สำเร็จ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">วิธีการชำระ</label>
                        <select class="form-select" id="method-filter">
                            <option value="">ทั้งหมด</option>
                            <option value="Bank Transfer">โอนเงิน</option>
                            <option value="QR Code">QR Code</option>
                            <option value="Cash">เงินสด</option>
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
            <div class="card-header bg-light">
                <h5 class="mb-0">รายการการชำระเงินทั้งหมด</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Payment ID</th>
                                <th>Reg ID</th>
                                <th>นักวิ่ง</th>
                                <th>จำนวนเงิน</th>
                                <th>วิธีการชำระ</th>
                                <th>วันเวลา</th>
                                <th>สถานะ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="payments-table">
                            <?php
                            try {
                                $sql = "SELECT p.*, r.reg_id, ru.first_name, ru.last_name
                                       FROM PAYMENT p 
                                       JOIN REGISTRATION r ON p.reg_id = r.reg_id
                                       JOIN RUNNER ru ON r.runner_id = ru.runner_id
                                       ORDER BY p.payment_id DESC";
                                $payments = $pdo->query($sql)->fetchAll();
                                
                                foreach ($payments as $payment) {
                                    $statusClass = match($payment['status']) {
                                        'Success' => 'bg-success',
                                        'Failed' => 'bg-danger',
                                        default => 'bg-warning'
                                    };
                                    
                                    echo "<tr data-status='{$payment['status']}' data-method='{$payment['payment_method']}'>";
                                    echo "<td>#{$payment['payment_id']}</td>";
                                    echo "<td>#{$payment['reg_id']}</td>";
                                    echo "<td>{$payment['first_name']} {$payment['last_name']}</td>";
                                    echo "<td>" . number_format($payment['total_amount'], 2) . " บาท</td>";
                                    echo "<td>{$payment['payment_method']}</td>";
                                    echo "<td>" . date('d/m/Y H:i', strtotime($payment['payment_time'])) . "</td>";
                                    echo "<td><span class='badge {$statusClass}'>{$payment['status']}</span></td>";
                                    echo "<td class='text-center'>";
                                    echo "<div class='btn-group' role='group'>";
                                    echo "<button class='btn btn-sm btn-warning' onclick='editPayment({$payment['payment_id']})'>";
                                    echo "<i class='fa-solid fa-edit'></i></button>";
                                    if ($payment['status'] === 'Pending') {
                                        echo "<button class='btn btn-sm btn-success' onclick='approvePayment({$payment['payment_id']})'>";
                                        echo "<i class='fa-solid fa-check'></i></button>";
                                        echo "<button class='btn btn-sm btn-danger' onclick='rejectPayment({$payment['payment_id']})'>";
                                        echo "<i class='fa-solid fa-times'></i></button>";
                                    }
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='8' class='text-center text-muted'>ไม่มีข้อมูล</td></tr>";
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
                    <h5 class="modal-title">แก้ไขการชำระเงิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-form">
                        <input type="hidden" id="edit-payment-id">
                        <div class="mb-3">
                            <label class="form-label">จำนวนเงิน</label>
                            <input type="number" step="0.01" class="form-control" id="edit-amount">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">วิธีการชำระ</label>
                            <select class="form-select" id="edit-method">
                                <option value="Bank Transfer">โอนเงิน</option>
                                <option value="QR Code">QR Code</option>
                                <option value="Cash">เงินสด</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สถานะ</label>
                            <select class="form-select" id="edit-status">
                                <option value="Pending">รอตรวจสอบ</option>
                                <option value="Success">สำเร็จ</option>
                                <option value="Failed">ไม่สำเร็จ</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หมายเลขอ้างอิง</label>
                            <input type="text" class="form-control" id="edit-ref" placeholder="Transaction Reference">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="savePaymentChanges()">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function applyFilters() {
            const status = document.getElementById('status-filter').value;
            const method = document.getElementById('method-filter').value;
            const search = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('#payments-table tr');
            
            rows.forEach(row => {
                let show = true;
                
                if (status && row.dataset.status !== status) show = false;
                if (method && row.dataset.method !== method) show = false;
                if (search && !row.textContent.toLowerCase().includes(search)) show = false;
                
                row.style.display = show ? '' : 'none';
            });
        }

        function editPayment(paymentId) {
            fetch(`crud_payment.php?action=get&id=${paymentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const payment = data.data;
                        document.getElementById('edit-payment-id').value = payment.payment_id;
                        document.getElementById('edit-amount').value = payment.total_amount;
                        document.getElementById('edit-method').value = payment.payment_method;
                        document.getElementById('edit-status').value = payment.status;
                        document.getElementById('edit-ref').value = payment.transaction_ref || '';
                        
                        new bootstrap.Modal(document.getElementById('editModal')).show();
                    }
                });
        }

        function savePaymentChanges() {
            const paymentId = document.getElementById('edit-payment-id').value;
            const amount = document.getElementById('edit-amount').value;
            const method = document.getElementById('edit-method').value;
            const status = document.getElementById('edit-status').value;
            const ref = document.getElementById('edit-ref').value;
            
            fetch('crud_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&payment_id=${paymentId}&total_amount=${amount}&payment_method=${method}&status=${status}&transaction_ref=${ref}`
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

        function approvePayment(paymentId) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะอนุมัติการชำระเงินนี้?')) {
                updatePaymentStatus(paymentId, 'Success');
            }
        }

        function rejectPayment(paymentId) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะปฏิเสธการชำระเงินนี้?')) {
                updatePaymentStatus(paymentId, 'Failed');
            }
        }

        function updatePaymentStatus(paymentId, status) {
            fetch('crud_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_status&payment_id=${paymentId}&status=${status}`
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

        // Auto-apply filters
        document.getElementById('status-filter').addEventListener('change', applyFilters);
        document.getElementById('method-filter').addEventListener('change', applyFilters);
        document.getElementById('search-input').addEventListener('keyup', applyFilters);
    </script>
</body>

</html>