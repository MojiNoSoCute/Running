<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลนักวิ่ง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/alert-popup.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-users me-2"></i>จัดการข้อมูลนักวิ่ง</span>
            <a href="admin_index.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>กลับหน้าหลัก
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Add/Edit Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0" id="form-title">เพิ่มนักวิ่งใหม่</h5>
            </div>
            <div class="card-body">
                <form id="runner-form" action="crud_runner.php" method="POST">
                    <input type="hidden" name="action" value="create" id="form-action">
                    <input type="hidden" name="runner_id" id="runner_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ชื่อ</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">นามสกุล</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">วันเกิด</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">เพศ</label>
                            <select name="gender" id="gender" class="form-select" required>
                                <option value="">-- เลือกเพศ --</option>
                                <option value="Male">ชาย</option>
                                <option value="Female">หญิง</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">อีเมล</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">เลขบัตรประชาชน</label>
                            <input type="text" name="citizen_id" id="citizen_id" class="form-control" pattern="[0-9]{13}" maxlength="13" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" id="phone" class="form-control" pattern="[0-9]{10}" maxlength="10" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">ที่อยู่</label>
                            <textarea name="address" id="address" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_disabled" id="is_disabled">
                                <label class="form-check-label" for="is_disabled">
                                    ผู้พิการ
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <i class="fa-solid fa-save me-1"></i>บันทึก
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fa-solid fa-times me-1"></i>ยกเลิก
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">รายการนักวิ่งทั้งหมด</h5>
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control" placeholder="ค้นหา..." id="search-input">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>เพศ</th>
                                <th>อีเมล</th>
                                <th>เบอร์โทร</th>
                                <th>สถานะ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="runners-table">
                            <?php
                            try {
                                $runners = $pdo->query("SELECT * FROM RUNNER ORDER BY runner_id DESC")->fetchAll();
                                foreach ($runners as $runner) {
                                    echo "<tr>";
                                    echo "<td>{$runner['runner_id']}</td>";
                                    echo "<td>{$runner['first_name']} {$runner['last_name']}</td>";
                                    echo "<td>" . ($runner['gender'] == 'Male' ? 'ชาย' : 'หญิง') . "</td>";
                                    echo "<td>{$runner['email']}</td>";
                                    echo "<td>{$runner['phone']}</td>";
                                    echo "<td>" . ($runner['is_disabled'] ? '<span class="badge bg-info">ผู้พิการ</span>' : '<span class="badge bg-success">ปกติ</span>') . "</td>";
                                    echo "<td class='text-center'>";
                                    echo "<button class='btn btn-sm btn-warning me-1' onclick='editRunner({$runner['runner_id']})'>";
                                    echo "<i class='fa-solid fa-edit'></i></button>";
                                    echo "<button class='btn btn-sm btn-danger' onclick='deleteRunner({$runner['runner_id']})'>";
                                    echo "<i class='fa-solid fa-trash'></i></button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='7' class='text-center text-muted'>ไม่มีข้อมูล</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/alert-popup.js"></script>
    <script>
        function resetForm() {
            document.getElementById('runner-form').reset();
            document.getElementById('form-action').value = 'create';
            document.getElementById('runner_id').value = '';
            document.getElementById('form-title').textContent = 'เพิ่มนักวิ่งใหม่';
            document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-save me-1"></i>บันทึก';
        }

        function editRunner(id) {
            fetch(`crud_runner.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const runner = data.data;
                        document.getElementById('form-action').value = 'update';
                        document.getElementById('runner_id').value = runner.runner_id;
                        document.getElementById('first_name').value = runner.first_name;
                        document.getElementById('last_name').value = runner.last_name;
                        document.getElementById('date_of_birth').value = runner.date_of_birth;
                        document.getElementById('gender').value = runner.gender;
                        document.getElementById('email').value = runner.email;
                        document.getElementById('citizen_id').value = runner.citizen_id;
                        document.getElementById('phone').value = runner.phone;
                        document.getElementById('address').value = runner.address;
                        document.getElementById('is_disabled').checked = runner.is_disabled == 1;
                        
                        document.getElementById('form-title').textContent = 'แก้ไขข้อมูลนักวิ่ง';
                        document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-save me-1"></i>อัปเดต';
                        
                        document.getElementById('runner-form').scrollIntoView();
                    }
                });
        }

        function deleteRunner(id) {
            showConfirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลนักวิ่งนี้?', 'ยืนยันการลบ', () => {
                fetch('crud_runner.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&runner_id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(data.message, 'สำเร็จ', () => location.reload());
                    } else {
                        showError(data.message, 'เกิดข้อผิดพลาด');
                    }
                });
            });
        }

        // Search functionality
        document.getElementById('search-input').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#runners-table tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>

</html>