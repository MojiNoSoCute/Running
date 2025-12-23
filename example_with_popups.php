<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตัวอย่างการใช้ Popup Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/alert-popup.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-bell me-2"></i>ตัวอย่าง Popup Alert</span>
            <a href="admin_index.php" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>กลับหน้าหลัก
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">ทดสอบ Popup Alert System</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <button class="btn btn-success w-100" onclick="testSuccess()">
                                    <i class="fa-solid fa-check-circle me-2"></i>Success Alert
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-danger w-100" onclick="testError()">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>Error Alert
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-warning w-100" onclick="testWarning()">
                                    <i class="fa-solid fa-exclamation-circle me-2"></i>Warning Alert
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-info w-100" onclick="testInfo()">
                                    <i class="fa-solid fa-info-circle me-2"></i>Info Alert
                                </button>
                            </div>
                            <div class="col-md-12">
                                <button class="btn btn-secondary w-100" onclick="testConfirm()">
                                    <i class="fa-solid fa-question-circle me-2"></i>Confirm Dialog
                                </button>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6>การใช้งานในโค้ด:</h6>
                        <div class="bg-light p-3 rounded">
                            <code>
                                // Success Alert<br>
                                showSuccess('บันทึกข้อมูลสำเร็จ', 'สำเร็จ', () => location.reload());<br><br>
                                
                                // Error Alert<br>
                                showError('เกิดข้อผิดพลาดในการบันทึก', 'ข้อผิดพลาด');<br><br>
                                
                                // Confirm Dialog<br>
                                showConfirm('คุณแน่ใจหรือไม่?', 'ยืนยัน', () => deleteItem(), () => console.log('ยกเลิก'));
                            </code>
                        </div>
                    </div>
                </div>

                <!-- Example Form -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">ตัวอย่างฟอร์มที่ใช้ Popup</h5>
                    </div>
                    <div class="card-body">
                        <form id="example-form">
                            <div class="mb-3">
                                <label class="form-label">ชื่อ</label>
                                <input type="text" class="form-control" id="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">อีเมล</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save me-2"></i>บันทึก
                            </button>
                            <button type="button" class="btn btn-danger" onclick="testDelete()">
                                <i class="fa-solid fa-trash me-2"></i>ลบ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/alert-popup.js"></script>
    <script>
        function testSuccess() {
            showSuccess('การดำเนินการสำเร็จ!', 'สำเร็จ', () => {
                console.log('Success callback executed');
            });
        }

        function testError() {
            showError('เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง', 'เกิดข้อผิดพลาด');
        }

        function testWarning() {
            showWarning('กรุณาตรวจสอบข้อมูลให้ครบถ้วน', 'คำเตือน');
        }

        function testInfo() {
            showInfo('ระบบจะปิดปรับปรุงในวันอาทิตย์ที่ 25 ธันวาคม 2567', 'ประกาศ');
        }

        function testConfirm() {
            showConfirm(
                'คุณต้องการออกจากระบบหรือไม่?', 
                'ยืนยันการออกจากระบบ',
                () => {
                    showSuccess('ออกจากระบบสำเร็จ', 'สำเร็จ');
                },
                () => {
                    showInfo('ยกเลิกการออกจากระบบ', 'ยกเลิก');
                }
            );
        }

        function testDelete() {
            showConfirm(
                'คุณแน่ใจหรือไม่ที่จะลบข้อมูลนี้? การดำเนินการนี้ไม่สามารถย้อนกลับได้',
                'ยืนยันการลบ',
                () => {
                    // Simulate delete operation
                    setTimeout(() => {
                        showSuccess('ลบข้อมูลสำเร็จ', 'สำเร็จ');
                    }, 1000);
                }
            );
        }

        // Example form submission
        document.getElementById('example-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            
            if (!name || !email) {
                showWarning('กรุณากรอกข้อมูลให้ครบถ้วน', 'ข้อมูลไม่ครบ');
                return;
            }
            
            // Simulate form submission
            showSuccess(`บันทึกข้อมูลของ ${name} สำเร็จ`, 'บันทึกสำเร็จ', () => {
                this.reset();
            });
        });
    </script>
</body>

</html>