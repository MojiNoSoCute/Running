<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการกลุ่มอายุ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-users-line me-2"></i>จัดการกลุ่มอายุ</span>
            <a href="admin_index.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>กลับหน้าหลัก
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Add/Edit Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0" id="form-title">เพิ่มกลุ่มอายุใหม่</h5>
            </div>
            <div class="card-body">
                <form id="age-group-form" action="crud_age_group.php" method="POST">
                    <input type="hidden" name="action" value="create" id="form-action">
                    <input type="hidden" name="age_group_id" id="age_group_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ประเภทการแข่งขัน</label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <option value="">-- เลือกประเภทการแข่งขัน --</option>
                                <?php
                                try {
                                    $cats = $pdo->query("SELECT category_id, name FROM RACE_CATEGORY ORDER BY name")->fetchAll();
                                    foreach ($cats as $c) {
                                        echo "<option value='{$c['category_id']}'>{$c['name']}</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>ไม่พบประเภทการแข่งขัน - กรุณาเพิ่มข้อมูลก่อน</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">เพศ</label>
                            <select name="gender" id="gender" class="form-select" required>
                                <option value="">-- เลือกเพศ --</option>
                                <option value="M">Male (ชาย)</option>
                                <option value="F">Female (หญิง)</option>
                                <option value="A">All (ทุกเพศ)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">อายุเริ่มต้น (Min Age)</label>
                            <input type="number" name="min_age" id="min_age" class="form-control" placeholder="เช่น 18" min="0" max="100" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">อายุสิ้นสุด (Max Age)</label>
                            <input type="number" name="max_age" id="max_age" class="form-control" placeholder="เช่น 29" min="0" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ชื่อกลุ่ม (Label)</label>
                            <input type="text" name="label" id="label" class="form-control" placeholder="เช่น 18-29 ปี" required>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-secondary" id="submit-btn">
                            <i class="fa-solid fa-save me-1"></i>บันทึก
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                            <i class="fa-solid fa-times me-1"></i>ยกเลิก
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">รายการกลุ่มอายุทั้งหมด</h5>
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
                                <th>ประเภทการแข่งขัน</th>
                                <th>เพศ</th>
                                <th>ช่วงอายุ</th>
                                <th>ชื่อกลุ่ม</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="age-groups-table">
                            <?php
                            try {
                                $ageGroups = $pdo->query("SELECT ag.*, rc.name as category_name 
                                                         FROM AGE_GROUP ag 
                                                         JOIN RACE_CATEGORY rc ON ag.category_id = rc.category_id 
                                                         ORDER BY rc.name, ag.min_age")->fetchAll();
                                foreach ($ageGroups as $ag) {
                                    $genderText = match($ag['gender']) {
                                        'M' => 'ชาย',
                                        'F' => 'หญิง',
                                        'A' => 'ทุกเพศ',
                                        default => $ag['gender']
                                    };
                                    echo "<tr>";
                                    echo "<td>{$ag['age_group_id']}</td>";
                                    echo "<td>{$ag['category_name']}</td>";
                                    echo "<td>{$genderText}</td>";
                                    echo "<td>{$ag['min_age']}-{$ag['max_age']} ปี</td>";
                                    echo "<td>{$ag['label']}</td>";
                                    echo "<td class='text-center'>";
                                    echo "<button class='btn btn-sm btn-warning me-1' onclick='editAgeGroup({$ag['age_group_id']})'>";
                                    echo "<i class='fa-solid fa-edit'></i></button>";
                                    echo "<button class='btn btn-sm btn-danger' onclick='deleteAgeGroup({$ag['age_group_id']})'>";
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
            document.getElementById('age-group-form').reset();
            document.getElementById('form-action').value = 'create';
            document.getElementById('age_group_id').value = '';
            document.getElementById('form-title').textContent = 'เพิ่มกลุ่มอายุใหม่';
            document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-save me-1"></i>บันทึก';
        }

        function editAgeGroup(id) {
            fetch(`crud_age_group.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ageGroup = data.data;
                        document.getElementById('form-action').value = 'update';
                        document.getElementById('age_group_id').value = ageGroup.age_group_id;
                        document.getElementById('category_id').value = ageGroup.category_id;
                        document.getElementById('gender').value = ageGroup.gender;
                        document.getElementById('min_age').value = ageGroup.min_age;
                        document.getElementById('max_age').value = ageGroup.max_age;
                        document.getElementById('label').value = ageGroup.label;
                        
                        document.getElementById('form-title').textContent = 'แก้ไขกลุ่มอายุ';
                        document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-save me-1"></i>อัปเดต';
                        
                        document.getElementById('age-group-form').scrollIntoView();
                    }
                });
        }

        function deleteAgeGroup(id) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะลบกลุ่มอายุนี้?')) {
                fetch('crud_age_group.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&age_group_id=${id}`
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

        // Search functionality
        document.getElementById('search-input').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#age-groups-table tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>

</html>