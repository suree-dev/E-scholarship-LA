<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../include/config.php';

if (!isset($_SESSION['student_id'])) {
    echo "<script>
            alert('กรุณาเข้าสู่ระบบก่อนดำเนินการกรอกข้อมูล');
            window.location.href='../root/index.php';
          </script>";
    exit();
}

$session_st_code = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $st_id       = $_POST['st_id'];
    $st_birthday = $_POST['st_birthday'];
    $st_age      = (empty($_POST['st_age'])) ? 0 : $_POST['st_age'];
    $st_address1 = $_POST['st_address1'];
    $st_address2 = $_POST['st_address2'];
    $st_tel1     = $_POST['st_tel1'];
    $st_tel2     = $_POST['st_tel2'];

    $sql_update = "UPDATE tb_student SET 
                    st_birthday = ?, 
                    st_age = ?, 
                    st_address1 = ?, 
                    st_address2 = ?, 
                    st_tel1 = ?, 
                    st_tel2 = ? 
                   WHERE st_id = ?";

    $stmt = $connect1->prepare($sql_update);
    $stmt->bind_param("sissssi", $st_birthday, $st_age, $st_address1, $st_address2, $st_tel1, $st_tel2, $st_id);

    if ($stmt->execute()) {
        if (isset($_POST['target_page']) && !empty($_POST['target_page'])) {
            $target = $_POST['target_page'];
            header("Location: $target");
            exit();
        } elseif (isset($_POST['btn_save'])) {
            header("Location: apply_fam.php");
            exit();
        }
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');</script>";
    }
}

$scholarship_options = [];
if (isset($connect1)) {
    $sql_types = "SELECT st_name_1, st_name_2, st_name_3 FROM tb_year WHERE y_id = 1";
    $result_types = mysqli_query($connect1, $sql_types);
    if ($result_types && mysqli_num_rows($result_types) > 0) {
        $data_types = mysqli_fetch_assoc($result_types);
        if (!empty($data_types['st_name_1'])) $scholarship_options[1] = $data_types['st_name_1'];
        if (!empty($data_types['st_name_2'])) $scholarship_options[2] = $data_types['st_name_2'];
        if (!empty($data_types['st_name_3'])) $scholarship_options[3] = $data_types['st_name_3'];
    }
}

$major_options = [];
if (isset($connect1)) {
    $sql_majors = "SELECT g_id, g_program FROM tb_program ORDER BY g_program ASC";
    $result_majors = mysqli_query($connect1, $sql_majors);
    if ($result_majors) {
        while ($row_m = mysqli_fetch_assoc($result_majors)) {
            $major_options[$row_m['g_id']] = $row_m['g_program'];
        }
    }
}

$student = [];
if (isset($connect1)) {
    $stmt = $connect1->prepare("SELECT s.*, t.tc_name FROM tb_student s LEFT JOIN tb_teacher t ON s.id_teacher = t.tc_id WHERE s.st_code = ?");
    $stmt->bind_param("s", $session_st_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $student = $row;
        $student['st_id']     = $row['st_id'];
        $student['prefix']    = ($row['st_sex'] == 1) ? 'นาย' : 'นางสาว';
        $student['firstname'] = $row['st_firstname'];
        $student['lastname']  = $row['st_lastname'];
        $student['gpa']        = $row['st_score'];
        $student['email']      = $row['st_email'];
        $student['advisor']    = $row['tc_name'] ?? 'ยังไม่ได้ระบุ';
        $student['dob']        = $row['st_birthday'];
        $student['age']        = ($row['st_age'] == 0) ? '' : $row['st_age'];
        $student['address1']   = $row['st_address1'];
        $student['address2']   = $row['st_address2'];
        $student['tel1']       = $row['st_tel1'];
        $student['major_name'] = $major_options[$row['st_program']] ?? 'ไม่ระบุ';
        $student['scholarship_name'] = $scholarship_options[$row['st_type']] ?? 'ไม่ระบุประเภททุน';

        $is_profile_complete = (!empty($row['st_birthday']) && !empty($row['st_age']) && !empty($row['st_address1']) && (preg_match('/^0\d{9}$/', $row['st_tel1'])));

        $received_parts = explode('|-o-|', $row['st_received'] ?? '');
        $is_family_complete = (
            !empty($row['st_father']) &&
            !empty($row['st_mother']) &&
            !empty($row['st_family_status']) &&
            isset($received_parts[5]) && (float)$received_parts[5] > 0
        );

        $is_reason_complete = (mb_strlen(trim($row['st_note'] ?? ''), 'UTF-8') >= 50);

        $is_doc_complete = ($row['id_teacher'] > 0 && !empty($row['st_doc']) && !empty($row['st_doc1']) && !empty($row['st_doc2']));
    } else {
        session_destroy();
        echo "<script>alert('ไม่พบข้อมูลนักศึกษา กรุณาเข้าสู่ระบบอีกครั้ง'); window.location.href='index.php';</script>";
        exit();
    }
}

$current_month_th = [1 => "มกราคม", 2 => "กุมภาพันธ์", 3 => "มีนาคม", 4 => "เมษายน", 5 => "พฤษภาคม", 6 => "มิถุนายน", 7 => "กรกฎาคม", 8 => "สิงหาคม", 9 => "กันยายน", 10 => "ตุลาคม", 11 => "พฤศจิกายน", 12 => "ธันวาคม"][(int)date("n")];
$current_year_th = date("Y") + 543;
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลส่วนตัว - PSU E-Scholarship</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/bg/head_01.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/global2.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
    <link rel="stylesheet" href="../assets/css/ui-elements.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
    <link rel="stylesheet" href="../assets/css/tables.css">
    <link rel="stylesheet" href="../assets/css/pages.css">

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .status-icon-complete {
            color: #28a745;
            margin-left: 5px;
        }

        .status-icon-incomplete {
            color: #dc3545;
            margin-left: 5px;
            opacity: 0.5;
        }

        .invalid-feedback-custom {
            color: #dc3545;
            font-size: 0.8rem;
            display: none;
            margin-top: 4px;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff8f8 !important;
        }
    </style>
</head>

<body class="bg-light">

    <div class="sticky-header-wrapper">
        <?php include('../include/navbar.php'); ?>
        <?php include('../include/status_bar.php'); ?>
    </div>

    <button onclick="scrollToTop()" id="scrollTopBtn" class="scroll-top-btn" title="เลื่อนขึ้นข้างบน">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <div class="container py-4">

        <div class="app-form-container mx-auto">
            <div class="header-content text-center mb-4">
                <h5 class="fw-bold mb-1">ใบสมัครขอรับทุนการศึกษา<?php echo htmlspecialchars($student['scholarship_name']); ?></h5>
                <h5 class="fw-bold mb-1">คณะศิลปศาสตร์ มหาวิทยาลัยสงขลานครินทร์</h5>
                <h5 class="fw-bold mb-1">ประจำปีการศึกษา <?php echo $current_year_th; ?></h5>
                <p class="text-muted small mt-2">วันที่ <?php echo date("j"); ?> <?php echo $current_month_th; ?> <?php echo $current_year_th; ?> เวลา <?php echo date("H:i"); ?> น.</p>
            </div>

            <div class="student-info-highlight shadow-sm">
                <span><strong>ชื่อ:</strong> <?php echo htmlspecialchars($student['prefix'] . $student['firstname'] . " " . $student['lastname']); ?></span>
                <span><strong>รหัสนักศึกษา:</strong> <?php echo htmlspecialchars($session_st_code); ?></span><br>
                <span><strong>เกรดเฉลี่ย:</strong> <?php echo htmlspecialchars($student['gpa']); ?></span>
                <span><strong>สาขาวิชา:</strong> <?php echo htmlspecialchars($student['major_name']); ?></span>
                <span><strong>อาจารย์ที่ปรึกษา:</strong> <?php echo htmlspecialchars($student['advisor']); ?></span>
            </div>

            <ul class="nav-tabs-app">
                <li class="nav-item">
                    <a href="apply_form.php" class="nav-link-custom active">
                        ข้อมูลส่วนตัว <?php echo $is_profile_complete ? '<i class="fa-solid fa-circle-check status-icon-complete"></i>' : '<i class="fa-regular fa-circle status-icon-incomplete"></i>'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="apply_fam.php" class="nav-link-custom inactive tab-save-link">
                        ข้อมูลครอบครัว <?php echo $is_family_complete ? '<i class="fa-solid fa-circle-check status-icon-complete"></i>' : '<i class="fa-regular fa-circle status-icon-incomplete"></i>'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="apply_reasons.php" class="nav-link-custom inactive tab-save-link">
                        ระบุเหตุผลการขอทุน <?php echo $is_reason_complete ? '<i class="fa-solid fa-circle-check status-icon-complete"></i>' : '<i class="fa-regular fa-circle status-icon-incomplete"></i>'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="apply_document.php" class="nav-link-custom inactive tab-save-link">
                        หนังสือรับรองและเอกสารแนบ <?php echo $is_doc_complete ? '<i class="fa-solid fa-circle-check status-icon-complete"></i>' : '<i class="fa-regular fa-circle status-icon-incomplete"></i>'; ?>
                    </a>
                </li>
            </ul>

            <form action="" method="POST" id="personalForm" class="px-2">
                <input type="hidden" name="st_id" value="<?php echo $student['st_id']; ?>">
                <input type="hidden" name="st_tel2" value="<?php echo htmlspecialchars($student['email']); ?>">
                <input type="hidden" name="target_page" id="target_page" value="">

                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 fw-medium">วัน/เดือน/ปี เกิด:</label>
                    <div class="col-sm-9">
                        <input type="date" name="st_birthday" id="st_birthday" class="form-control check-input" value="<?php echo $student['dob']; ?>" onclick="this.showPicker()">
                        <div class="invalid-feedback-custom">กรุณาระบุวันเดือนปีเกิด</div>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 fw-medium">อายุ:</label>
                    <div class="col-sm-9">
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" name="st_age" id="st_age" class="form-control check-input" placeholder="อายุ" style="max-width: 100px; " value="<?php echo $student['age']; ?>">
                            <span>ปี</span>
                        </div>
                        <div class="invalid-feedback-custom">กรุณาระบุอายุ</div>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 fw-medium">ที่อยู่ปัจจุบัน:</label>
                    <div class="col-sm-9">
                        <input type="text" name="st_address1" id="st_address1" class="form-control check-input" placeholder="ที่อยู่ปัจจุบัน" value="<?php echo htmlspecialchars($student['address1']); ?>">
                        <div class="invalid-feedback-custom">กรุณาระบุที่อยู่ปัจจุบัน</div>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 fw-medium">ภูมิลำเนา (ตามทะเบียนบ้าน):</label>
                    <div class="col-sm-9">
                        <input type="text" name="st_address2" id="st_address2" class="form-control check-input" placeholder="ภูมิลำเนา" value="<?php echo htmlspecialchars($student['address2']); ?>">
                        <div class="invalid-feedback-custom">กรุณาระบุภูมิลำเนา</div>
                    </div>
                </div>

                <div class="row mb-3 align-items-start">
                    <label class="col-sm-3 fw-medium pt-2">หมายเลขโทรศัพท์มือถือ:</label>
                    <div class="col-sm-9">
                        <input type="text" name="st_tel1" id="st_tel1" class="form-control check-input" placeholder="หมายเลขโทรศัพท์" value="<?php echo htmlspecialchars($student['tel1']); ?>" maxlength="10">
                        <div id="tel-error" class="invalid-feedback-custom">กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง</div>
                    </div>
                </div>

                <div class="row mb-4 align-items-center">
                    <label class="col-sm-3 fw-medium">อีเมล:</label>
                    <div class="col-sm-9">
                        <input type="email" name="st_email" class="form-control bg-light" placeholder="อีเมล" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                    <button type="submit" name="btn_save" id="btn_submit" class="btn-next-step shadow-sm">
                        ถัดไป <i class="fa-solid fa-chevron-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        window.onscroll = function() {
            let btn = document.getElementById("scrollTopBtn");
            if (btn) btn.style.display = (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) ? "block" : "none";
        };

        const form = document.getElementById('personalForm');
        const telInput = document.getElementById('st_tel1');
        const submitBtn = document.getElementById('btn_submit');
        const targetInput = document.getElementById('target_page');
        const tabLinks = document.querySelectorAll('.tab-save-link');
        const checkInputs = document.querySelectorAll('.check-input');

        function validateSingleInput(input) {
            const value = input.value.trim();
            let isInvalid = false;
            let errorMsg = input.closest('.col-sm-9').querySelector('.invalid-feedback-custom');

            if (value === "" || value === "0") {
                isInvalid = true;
            } else if (input.id === 'st_tel1') {
                const telPattern = /^0\d{9}$/;
                if (!telPattern.test(value)) isInvalid = true;
            }

            if (isInvalid) {
                input.classList.add('is-invalid');
                if (errorMsg) errorMsg.style.display = 'block';
            } else {
                input.classList.remove('is-invalid');
                if (errorMsg) errorMsg.style.display = 'none';
            }
            return !isInvalid;
        }

        checkInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                validateSingleInput(e.target);
            });
            input.addEventListener('change', function(e) {
                validateSingleInput(e.target);
            });
        });

        form.addEventListener('submit', function(e) {
            checkInputs.forEach(input => validateSingleInput(input));
        });

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                targetInput.value = this.getAttribute('href');
                form.submit();
            });
        });
    </script>
</body>

</html>