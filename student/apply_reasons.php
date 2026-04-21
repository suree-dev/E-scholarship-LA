<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../include/config.php';

if (!isset($_SESSION['student_id'])) {
    echo "<script>
            alert('กรุณาเข้าสู่ระบบก่อนดำเนินการกรอกข้อมูล');
            window.location.href='index.php';
          </script>";
    exit();
}

$session_st_code = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $st_note = mysqli_real_escape_string($connect1, $_POST['scholarship_reason'] ?? '');

    $sql_update_note = "UPDATE tb_student SET st_note = ? WHERE st_code = ?";
    $stmt = $connect1->prepare($sql_update_note);
    $stmt->bind_param("ss", $st_note, $session_st_code);
    $stmt->execute();

    if (isset($_POST['target_page']) && !empty($_POST['target_page'])) {
        $target = $_POST['target_page'];
        echo "<script>window.location.href='$target';</script>";
        exit();
    } elseif (isset($_POST['btn_save_reasons'])) {
        echo "<script>window.location.href='apply_document.php';</script>";
        exit();
    } elseif (isset($_POST['btn_back'])) {
        echo "<script>window.location.href='apply_fam.php';</script>";
        exit();
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

$student = [];
$scholarship_reason = "";
if (isset($connect1)) {
    $sql = "SELECT s.*, t.tc_name, p.g_program 
            FROM tb_student s 
            LEFT JOIN tb_teacher t ON s.id_teacher = t.tc_id
            LEFT JOIN tb_program p ON s.st_program = p.g_id
            WHERE s.st_code = '$session_st_code'";

    $result = mysqli_query($connect1, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $student = $row;
        $student['st_id']     = $row['st_id'];
        $student['prefix']    = ($row['st_sex'] == 1) ? 'นาย' : 'นางสาว';
        $student['firstname'] = $row['st_firstname'];
        $student['lastname']  = $row['st_lastname'];
        $student['gpa']        = $row['st_score'];
        $student['major_name'] = $row['g_program'] ?? 'ไม่ระบุ';
        $student['advisor']    = $row['tc_name'] ?? 'ยังไม่ได้ระบุ';
        $student['scholarship_name'] = $scholarship_options[$row['st_type']] ?? 'ไม่ระบุประเภททุน';
        $scholarship_reason = $row['st_note'] ?? '';

        $is_profile_complete = (!empty($row['st_birthday']) && !empty($row['st_age']) && !empty($row['st_address1']) && !empty($row['st_tel1']));

        $recv_parts = explode('|-o-|', $row['st_received'] ?? '');
        $is_family_complete = (
            !empty($row['st_father']) &&
            !empty($row['st_mother']) &&
            !empty($row['st_family_status']) &&
            isset($recv_parts[5]) && (float)$recv_parts[5] > 0
        );

        $is_reason_complete = (mb_strlen(trim($scholarship_reason), 'UTF-8') >= 50);

        $is_doc_complete = ($row['id_teacher'] > 0 && !empty($row['st_doc']) && !empty($row['st_doc1']) && !empty($row['st_doc2']));
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
    <title>ระบุเหตุผลการขอทุน - PSU E-Scholarship</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/bg/head_01.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/global2.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
    <link rel="stylesheet" href="../assets/css/ui-elements.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
    <link rel="stylesheet" href="../assets/css/tables.css">
    <link rel="stylesheet" href="../assets/css/pages.css">

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
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

        .reason-textarea-fill.is-invalid {
            border: 2px solid #dc3545 !important;
            background-color: #fff8f8;
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
                <h5 class="fw-bold mb-1">ใบสมัครขอรับทุนการศึกษา<?php echo htmlspecialchars($student['scholarship_name'] ?? ''); ?></h5>
                <h5 class="fw-bold mb-1">คณะศิลปศาสตร์ มหาวิทยาลัยสงขลานครินทร์</h5>
                <h5 class="fw-bold mb-1">ประจำปีการศึกษา <?php echo $current_year_th; ?></h5>
                <p class="text-muted small mt-2">วันที่ <?php echo date("j"); ?> <?php echo $current_month_th; ?> <?php echo $current_year_th; ?> เวลา <?php echo date("H:i"); ?> น.</p>
            </div>

            <div class="student-info-highlight shadow-sm">
                <span><strong>ชื่อ:</strong> <?php echo htmlspecialchars(($student['prefix'] ?? '') . ($student['firstname'] ?? '') . " " . ($student['lastname'] ?? '')); ?></span>
                <span><strong>รหัสนักศึกษา:</strong> <?php echo htmlspecialchars($session_st_code); ?></span><br>
                <span><strong>เกรดเฉลี่ย:</strong> <?php echo htmlspecialchars($student['gpa'] ?? ''); ?></span>
                <span><strong>สาขาวิชา:</strong> <?php echo htmlspecialchars($student['major_name'] ?? ''); ?></span>
                <span><strong>อาจารย์ที่ปรึกษา:</strong> <?php echo htmlspecialchars($student['advisor'] ?? ''); ?></span>
            </div>

            <ul class="nav-tabs-app">
                <li class="nav-item">
                    <a href="apply_form.php" class="nav-link-custom inactive tab-save-link">
                        ข้อมูลส่วนตัว <?php echo $is_profile_complete ? '<i class="fa-solid fa-circle-check status-icon-complete"></i>' : '<i class="fa-regular fa-circle status-icon-incomplete"></i>'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="apply_fam.php" class="nav-link-custom inactive tab-save-link">
                        ข้อมูลครอบครัว <?php echo $is_family_complete ? '<i class="fa-solid fa-circle-check status-icon-complete"></i>' : '<i class="fa-regular fa-circle status-icon-incomplete"></i>'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="apply_reasons.php" class="nav-link-custom active">
                        ระบุเหตุผลการขอทุน <?php echo $is_reason_complete ? '<i class="fa-solid fa-circle-check status-icon-complete"></i>' : '<i class="fa-regular fa-circle status-icon-incomplete"></i>'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="apply_document.php" class="nav-link-custom inactive tab-save-link">
                        หนังสือรับรองและเอกสารแนบ <?php echo $is_doc_complete ? '<i class="fa-solid fa-circle-check status-icon-complete"></i>' : '<i class="fa-regular fa-circle status-icon-incomplete"></i>'; ?>
                    </a>
                </li>
            </ul>

            <form action="" method="POST" class="px-2" id="reasonForm">
                <input type="hidden" name="target_page" id="target_page" value="">

                <div class="d-flex align-items-center gap-2 mb-3 fw-bold" style="font-size: 1rem; color:#003b6f">
                    <i class="fa-solid fa-pen-to-square"></i>เพื่อให้ข้อมูลเป็นประโยชน์สำหรับการพิจารณาคัดเลือก กรุณาบรรยายโดยละเอียด :
                </div>

                <div class="position-relative">
                    <textarea name="scholarship_reason" id="scholarship_reason" class="reason-textarea-fill shadow-sm"
                        placeholder="กรุณาระบุเหตุผลความจำเป็นในการขอรับทุน และความคาดหวังหากได้รับทุนนี้..."><?php echo htmlspecialchars($scholarship_reason ?? ''); ?></textarea>
                    <div id="reason_error" class="text-danger small mt-2" style="display:none;">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>กรุณาระบุเหตุผลความจำเป็นในการขอรับทุนอย่างน้อย 100 ตัวอักษร เพื่อประกอบการพิจารณา (ปัจจุบัน: <span id="char_count">0</span> ตัวอักษร)
                    </div>
                </div>

                <div class="certification-warning-box shadow-sm mt-4">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <strong>คำรับรองข้อมูล:</strong> ข้าพเจ้าขอรับรองว่า ข้อความที่ได้กล่าวมาทั้งหมดในใบสมัครนี้เป็นความจริงทุกประการ หากตรวจสอบพบว่าข้อความข้างต้นไม่เป็นความจริง ข้าพเจ้ายินดีคืนทุนและงดสิทธิ์การรับทุนอื่นๆ ของคณะตลอดสภาพการเป็นนักศึกษา
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                    <button type="submit" name="btn_back" class="btn-back-step-grey shadow-sm"><i class="fa-solid fa-chevron-left me-2"></i> ย้อนกลับ</button>
                    <button type="submit" name="btn_save_reasons" id="btn_submit" class="btn-next-step shadow-sm">ถัดไป <i class="fa-solid fa-chevron-right ms-2"></i></button>
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

        const textarea = document.getElementById('scholarship_reason');
        const errorMsg = document.getElementById('reason_error');
        const charCountSpan = document.getElementById('char_count');
        const form = document.getElementById('reasonForm');
        const targetInput = document.getElementById('target_page');
        const tabLinks = document.querySelectorAll('.tab-save-link');

        function validateContent(showVisualError = true) {
            const textValue = textarea.value.trim();
            const textLength = textValue.length;
            const isValid = textLength >= 100;

            if (charCountSpan) charCountSpan.innerText = textLength;

            if (showVisualError && textLength > 0) {
                if (!isValid) {
                    textarea.classList.add('is-invalid');
                    textarea.classList.remove('is-valid');
                    errorMsg.style.display = 'block';
                } else {
                    textarea.classList.remove('is-invalid');
                    textarea.classList.add('is-valid');
                    errorMsg.style.display = 'none';
                }
            } else if (textLength === 0) {
                textarea.classList.remove('is-invalid');
                textarea.classList.remove('is-valid');
                errorMsg.style.display = 'none';
            }
        }

        textarea.addEventListener('input', () => {
            validateContent(true);
        });

        window.addEventListener('load', () => {
            validateContent(false);
        });

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const destination = this.getAttribute('href');
                targetInput.value = destination;
                form.submit();
            });
        });
    </script>

</body>

</html>