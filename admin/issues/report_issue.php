<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าสำหรับคณะกรรมการ - PSU E-Scholarship</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/bg/head_01.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../assets/css/global2.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/navigation.css">
    <link rel="stylesheet" href="../../assets/css/ui-elements.css">
    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets/css/tables.css">
    <link rel="stylesheet" href="../../assets/css/pages.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Prompt', sans-serif;
        }

        .sticky-header-wrapper {
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card-header-psu {
            background-color: #fff;
            border-bottom: 2px solid #eee;
            color: #333;
            padding: 20px;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .btn-psu {
            background-color: #003366;
            color: white;
            border-radius: 20px;
            padding: 8px 35px;
        }

        .btn-psu:hover {
            background-color: #002244;
            color: white;
        }

        .main-container {
            padding-top: 30px;
            padding-bottom: 50px;
        }

        .fade-out {
            transition: opacity 0.8s ease-out;
            opacity: 0;
        }
    </style>
</head>

<body>

    <div class="sticky-header-wrapper">
        <?php include('../../include/navbar.php'); ?>
    </div>

    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header-psu d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fa fa-edit me-2 text-primary"></i> แบบฟอร์มแจ้งปัญหาการใช้งาน
                        </div>
                        <button onclick="window.close();" class="btn btn-secondary rounded-pill px-4 btn-sm shadow-sm">
                            <i class="fa-solid fa-arrow-left me-2"></i> ย้อนกลับ
                        </button>
                    </div>
                    <div class="card-body p-4 p-md-5">

                        <?php if (isset($_GET['status'])): ?>
                            <div id="status-alert">
                                <?php if ($_GET['status'] == 'success'): ?>
                                    <div class="alert alert-success shadow-sm">บันทึกข้อมูลและส่งอีเมลเรียบร้อยแล้ว</div>
                                <?php elseif ($_GET['status'] == 'invalid_id'): ?>
                                    <div class="alert alert-danger shadow-sm">รหัสนักศึกษาไม่ถูกต้อง (ต้องอยู่ในรูปแบบ xx1111xxxx)</div>
                                <?php elseif ($_GET['status'] == 'mail_error'): ?>
                                    <div class="alert alert-warning shadow-sm">บันทึกแล้ว แต่ส่งเมลไม่สำเร็จ: <?php echo $_GET['msg']; ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form action="save_issue.php" method="POST">
                            <div class="row">
                                <div class="col-md-8 mb-4">
                                    <label class="form-label fw-bold">หัวข้อปัญหา <span class="text-danger">*</span></label>
                                    <input type="text" name="issue_topic" class="form-control" placeholder="ระบุหัวข้อที่ต้องการแจ้ง" required>
                                </div>

                                <div class="col-md-4 mb-4">
                                    <label class="form-label fw-bold">รหัสนักศึกษา <span class="text-danger">*</span></label>
                                    <input type="text" name="student_id" class="form-control" placeholder="xx1111xxxx" pattern="[0-9]{2}1111[0-9]{4}" title="รูปแบบต้องเป็น xx1111xxxx" maxlength="10" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">ผู้แจ้ง <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa fa-user text-muted"></i></span>
                                    <input type="text" name="reporter_name" class="form-control" placeholder="ระบุชื่อ-นามสกุล ผู้แจ้งปัญหา" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">รายละเอียดเพิ่มเติม <span class="text-danger">*</span></label>
                                <textarea name="issue_details" class="form-control" rows="6" placeholder="กรุณาระบุรายละเอียดปัญหา" required></textarea>
                            </div>

                            <div class="alert alert-info py-3 bg-light border-0">
                                <small><i class="fa fa-info-circle me-2 text-primary"></i> ข้อมูลนี้จะถูกส่งไปยังอีเมลของผู้ดูแลระบบเพื่อดำเนินการแก้ไขโดยเร็วที่สุด</small>
                            </div>

                            <div class="text-end mt-4 pt-3 border-top">
                                <button type="reset" class="btn btn-light border px-4 rounded-pill me-2">ล้างข้อมูล</button>
                                <button type="submit" name="save_issue" class="btn btn-psu shadow-sm">
                                    <i class="fa fa-save me-2"></i> บันทึกข้อมูลและส่งแจ้งเตือน
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alertBox = document.getElementById('status-alert');
            if (alertBox) {
                setTimeout(function() {
                    alertBox.classList.add('fade-out');

                    setTimeout(function() {
                        alertBox.style.display = 'none';
                    }, 800);
                }, 1000);
            }
        });
    </script>
</body>

</html>