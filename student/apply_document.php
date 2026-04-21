<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../include/config.php';

if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('กรุณาเข้าสู่ระบบก่อนดำเนินการกรอกข้อมูล'); window.location.href='index.php';</script>";
    exit();
}

$session_st_code = $_SESSION['student_id'];
$show_modal = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_to_update = $session_st_code;

    if (isset($_POST['advisor_name']) && !empty($_POST['advisor_name'])) {
        $advisor_name = mysqli_real_escape_string($connect1, $_POST['advisor_name']);
        $res_tc = mysqli_query($connect1, "SELECT tc_id FROM tb_teacher WHERE tc_name = '$advisor_name'");
        $row_tc = mysqli_fetch_assoc($res_tc);
        $tc_id = $row_tc['tc_id'] ?? 0;
        mysqli_query($connect1, "UPDATE tb_student SET id_teacher = '$tc_id' WHERE st_code = '$id_to_update'");
    }

    $upload_dir = "../images/student/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_fields = ['doc_std_card' => 'st_doc', 'doc_transcript' => 'st_doc1', 'doc_activity' => 'st_doc2'];

    foreach ($file_fields as $input_name => $db_column) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
            $extension = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));
            $file_size = $_FILES[$input_name]['size'];

            if ($extension === 'pdf' && $file_size <= 5242880) {
                $new_filename = time() . "_" . rand(1000, 9999) . "." . $extension;
                if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $upload_dir . $new_filename)) {
                    mysqli_query($connect1, "UPDATE tb_student SET $db_column = '$new_filename' WHERE st_code = '$id_to_update'");
                }
            }
        }
    }

    if (isset($_POST['target_page']) && !empty($_POST['target_page'])) {
        $target = $_POST['target_page'];
        header("Location: $target");
        exit();
    } elseif (isset($_POST['btn_next_step'])) {
        $show_modal = true;
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
$is_all_complete = false;
if (isset($connect1)) {
    $sql = "SELECT s.*, t.tc_name, p.g_program FROM tb_student s 
            LEFT JOIN tb_teacher t ON s.id_teacher = t.tc_id
            LEFT JOIN tb_program p ON s.st_program = p.g_id
            WHERE s.st_code = '$session_st_code'";
    $result = mysqli_query($connect1, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $student = $row;
        $student['prefix'] = ($row['st_sex'] == 1) ? 'นาย' : 'นางสาว';
        $student['firstname'] = $row['st_firstname'];
        $student['lastname'] = $row['st_lastname'];
        $student['major_name'] = $row['g_program'] ?? 'ไม่ระบุสาขา';
        $student['advisor'] = $row['tc_name'] ?? 'ยังไม่ได้ระบุ';
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

        if ($is_profile_complete && $is_family_complete && $is_reason_complete && $is_doc_complete) {
            $is_all_complete = true;
        }
    }
}

$is_submitted = ($student['st_confirm'] == 1);

$advisors = [];
if (isset($connect1)) {
    $sql_teachers = "SELECT tc_name FROM tb_teacher WHERE tc_type = 4 ORDER BY tc_name ASC";
    $result_teachers = mysqli_query($connect1, $sql_teachers);
    if ($result_teachers) {
        while ($row_t = mysqli_fetch_assoc($result_teachers)) {
            $advisors[] = $row_t['tc_name'];
        }
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
    <title>หนังสือรับรองและเอกสารแนบ - PSU E-Scholarship</title>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body.bg-light {
            position: relative;
            min-height: 100vh;
        }

        .inline-select-fill.is-invalid {
            border: 2px solid #dc3545 !important;
            background-color: #fff8f8;
        }

        .status-icon-complete {
            color: #28a745;
            margin-left: 5px;
        }

        .status-icon-incomplete {
            color: #dc3545;
            margin-left: 5px;
            opacity: 0.5;
        }

        .doc-upload-item.incomplete {
            border-left: 5px solid #e9ecef;
        }

        .doc-upload-item.complete {
            border-left: 5px solid #28a745;
        }

        #finalSubmit:disabled {
            background-color: #adb5bd !important;
            border-color: #adb5bd !important;
            color: #ffffff !important;
            cursor: not-allowed;
            opacity: 1;
        }

        #finalSubmit {
            transition: all 0.3s ease;
        }

        .btn-next-step:disabled {
            background: #ccc !important;
            cursor: not-allowed !important;
            transform: none !important;
            box-shadow: none !important;
        }

        .modal-custom-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            min-height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            display: none;
            padding-top: 50px;
            padding-bottom: 50px;
        }

        .modal-custom-card {
            background: white;
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            border-radius: 20px;
            position: relative;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="bg-light">

    <div class="sticky-header-wrapper">
        <?php include('../include/navbar.php'); ?>
        <?php include('../include/status_bar.php'); ?>
    </div>

    <button onclick="scrollToTop()" id="scrollTopBtn" class="scroll-top-btn"><i class="fa-solid fa-arrow-up"></i></button>

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
                <span><strong>เกรดเฉลี่ย:</strong> <?php echo htmlspecialchars($student['st_score']); ?></span>
                <span><strong>สาขาวิชา:</strong> <?php echo htmlspecialchars($student['major_name']); ?></span>
                <span><strong>อาจารย์ที่ปรึกษา:</strong> <?php echo htmlspecialchars($student['advisor']); ?></span>
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
                    <a href="apply_reasons.php" class="nav-link-custom inactive tab-save-link">
                        ระบุเหตุผลการขอทุน <?php echo $is_reason_complete ? '<i class="fa-solid fa-circle-check status-icon-complete"></i>' : '<i class="fa-regular fa-circle status-icon-incomplete"></i>'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="apply_document.php" class="nav-link-custom active">
                        หนังสือรับรองและเอกสารแนบ <?php echo $is_doc_complete ? '<i class="fa-solid fa-circle-check status-icon-complete"></i>' : '<i class="fa-regular fa-circle status-icon-incomplete"></i>'; ?>
                    </a>
                </li>
            </ul>

            <form action="" id="uploadForm" method="POST" enctype="multipart/form-data" class="px-2">
                <input type="hidden" name="target_page" id="target_page" value="">

                <div class="cert-fill-box shadow-sm mb-5">
                    <div class="cert-fill-title">หนังสือรับรองการขอรับทุนการศึกษาของคณะศิลปศาสตร์</div>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ข้าพเจ้า
                    <select name="advisor_name" id="advisor_name" class="inline-select-fill" style="width: 320px;" onchange="validateVisuals()">
                        <option value="" disabled <?php echo ($student['advisor'] == 'ยังไม่ได้ระบุ') ? 'selected' : ''; ?>>เลือกอาจารย์ที่ปรึกษา</option>
                        <?php foreach ($advisors as $adv): ?>
                            <option value="<?php echo $adv; ?>" <?php echo ($student['advisor'] == $adv) ? 'selected' : ''; ?>><?php echo $adv; ?></option>
                        <?php endforeach; ?>
                    </select>
                    ในฐานะอาจารย์ที่ปรึกษาของผู้ขอรับทุนการศึกษา ขอรับรองว่า
                    <span class="inline-readonly-field" style="width: 250px;"><?php echo $student['prefix'] . $student['firstname'] . " " . $student['lastname']; ?></span>
                    รหัสนักศึกษา
                    <span class="inline-readonly-field" style="width: 150px;"><?php echo $student['st_code']; ?></span>
                    สาขาวิชา
                    <span class="inline-readonly-field" style="min-width: 280px;"><?php echo $student['major_name']; ?></span>
                    ได้รับคะแนนเฉลี่ยสะสม
                    <span class="inline-readonly-field" style="width: 80px;"><?php echo $student['st_score']; ?></span>
                    เป็นผู้ที่มีความประพฤติดี ขาดแคลนทุนทรัพย์ ตามข้อมูลที่ได้แสดงไว้ในใบสมัครทุกประการ และเป็นบุคคลที่สมควรได้รับทุนการศึกษานี้
                </div>

                <div class="section-header-app"><i class="fa-solid fa-paperclip me-2"></i> อัปโหลดเอกสารแนบประกอบการพิจารณา</div>

                <?php
                $docs = [
                    ['label' => '1. สำเนาบัตรนักศึกษาเท่านั้น (ไฟล์ .pdf)', 'id' => 'doc_std_card', 'db' => $student['st_doc']],
                    ['label' => '2. สำเนาใบแสดงผลการศึกษา (ไฟล์ .pdf)', 'id' => 'doc_transcript', 'db' => $student['st_doc1']],
                    ['label' => '3. สำเนาใบแสดงผลการเข้าร่วมกิจกรรม (ไฟล์ .pdf)', 'id' => 'doc_activity', 'db' => $student['st_doc2']]
                ];
                foreach ($docs as $d):
                    $has_file = !empty($d['db']);
                ?>
                    <div class="doc-upload-item shadow-sm <?php echo $has_file ? 'complete' : 'incomplete'; ?>" id="box_<?php echo $d['id']; ?>">
                        <div class="flex-grow-1">
                            <span class="fw-bold text-dark"><?php echo $d['label']; ?></span>
                            <?php if ($has_file): ?>
                                <small class="file-status-tag text-success" id="status_<?php echo $d['id']; ?>"><i class="fa-solid fa-circle-check"></i> ไฟล์ปัจจุบัน: <?php echo htmlspecialchars($d['db']); ?></small>
                            <?php else: ?>
                                <small class="file-status-tag text-muted" id="status_<?php echo $d['id']; ?>"><i class="fa-solid fa-circle-info"></i> ยังไม่ได้อัปโหลดไฟล์</small>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="<?php echo $d['id']; ?>" class="regis-custom-file-btn border">Choose File</label>
                            <input type="file" name="<?php echo $d['id']; ?>" id="<?php echo $d['id']; ?>" accept="application/pdf" style="display:none;" onchange="validateAndDisplay(this); validateVisuals();">
                            <span id="name_<?php echo $d['id']; ?>" class="text-muted small">No file chosen</span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                    <a href="apply_reasons.php" class="btn-back-step-grey shadow-sm"><i class="fa-solid fa-chevron-left me-2"></i> ย้อนกลับ</a>
                    <button type="submit" name="btn_next_step" id="btn_submit" class="btn-next-step shadow-sm" <?php echo ($is_all_complete) ? '' : 'disabled'; ?>>ถัดไป <i class="fa-solid fa-chevron-right ms-2"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div id="confirmModal" class="modal-custom-overlay" style="<?php echo ($show_modal) ? 'display:block;' : ''; ?>">
        <div class="modal-custom-card">
            <span class="btn-close-modal" onclick="closeModal()">&times;</span>
            <h4 class="fw-bold text-center mb-4">ทำความเข้าใจรายละเอียดการส่งใบสมัคร</h4>

            <div class="modal-instruction-box">
                <span class="confirm-section-header">หมายเหตุ</span>
                <ul class="confirm-list-unstyled">
                    <li>1. นักศึกษาสั่งพิมพ์หนังสือรับรองการขอรับทุน เพื่อเสนอให้อาจารย์ที่ปรึกษาลงนาม และส่งคืนงานกิจการนักศึกษา
                        เพื่อยืนยันการสมัครในระบบต่อไป</li>
                    <li>2. นักศึกษาสามารถเข้าตรวจสอบสถานะการส่งใบสมัคร กรณีส่งเอกสารครบระบบจะแสดงผลเป็น "เป็นสีเขียว"
                        พร้อมสถานะ "ได้รับการยืนยันแล้ว รอการสัมภาษณ์"</li>
                    <li>3. นักศึกษาที่ขอรับทุนการศึกษา ติดตามการประกาศรายชื่อผู้มีสิทธิ์สอบสัมภาษณ์ได้ที่เว็บไซต์ งานกิจการนักศึกษา
                        (iw2.libarts.psu.ac.th/student) เว็บไซต์ระบบรับสมัครทุนฯ หรือเว็บไซต์หน่วยงาน</li>
                </ul>
                <span class="confirm-section-header mt-4">ข้อปฏิบัติการเข้าสัมภาษณ์</span>
                <ul class="confirm-list-unstyled">
                    <li>1. นักศึกษาต้องแต่งกายด้วยชุดนักศึกษาที่ถูกต้องตามระเบียบของมหาวิทยาลัยสงขลานครินทร์เท่านั้น</li>
                    <li>2. นักศึกษาต้องมาถึงสถานที่สอบสัมภาษณ์ก่อนเวลาอย่างน้อย 15 - 30 นาที</li>
                    <li>3. การเรียงลำดับก่อน - หลัง ผู้เข้าสัมภาษณ์ โดยการหยิบบัตรคิว</li>
                    <li>4. กรณีนักศึกษามาไม่ทันเวลาการสัมภาษณ์ และนักศึกษาคนอื่นสัมภาษณ์ครบทุกคนแล้ว
                        นักศึกษาจะถูกตัดสิทธิ์ทันทีไม่ว่าจะด้วยเหตุผลใดๆ</li>
                    <li>5. การพิจารณาของคณะกรรมการสอบสัมภาษณ์ถือเป็นสิ้นสุด</li>
                </ul>

                <div class="confirm-checkbox-group">
                    <input type="checkbox" id="accept_terms" class="form-check-input" <?php echo $is_submitted ? 'checked disabled' : ''; ?> onchange="document.getElementById('finalSubmit').disabled = !this.checked">
                    <label for="accept_terms" class="ms-2">ยอมรับเงื่อนไขทุกประการ</label>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-3 mt-5">
                <?php if (!$is_submitted): ?>
                    <button type="button" class="btn btn-danger rounded-pill px-5" onclick="closeModal()">ยกเลิก</button>
                    <form action="../admin/students/final_save.php" method="POST" class="m-0">
                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($session_st_code); ?>">
                        <button type="submit" id="finalSubmit" class="btn btn-success rounded-pill px-5" disabled>ยืนยัน</button>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn btn-app-waiting rounded-pill px-5">รอการยืนยัน</button>
                    <a href="print_scholarship.php?student_id=<?php echo $student['st_id']; ?>" target="_blank" class="btn btn-app-print rounded-pill px-5 shadow-sm text-decoration-none text-white">สั่งพิมพ์ใบสมัคร</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const prevPagesComplete = <?php echo ($is_profile_complete && $is_family_complete && $is_reason_complete) ? 'true' : 'false'; ?>;
        const hasDbDoc1 = <?php echo !empty($student['st_doc']) ? 'true' : 'false'; ?>;
        const hasDbDoc2 = <?php echo !empty($student['st_doc1']) ? 'true' : 'false'; ?>;
        const hasDbDoc3 = <?php echo !empty($student['st_doc2']) ? 'true' : 'false'; ?>;

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

        const form = document.getElementById('uploadForm');
        const targetInput = document.getElementById('target_page');
        const tabLinks = document.querySelectorAll('.tab-save-link');
        const advisorSelect = document.getElementById('advisor_name');
        const btnSubmit = document.getElementById('btn_submit');

        function validateAndDisplay(input) {
            const span = document.getElementById('name_' + input.id);
            const statusLabel = document.getElementById('status_' + input.id);
            const box = document.getElementById('box_' + input.id);
            const file = input.files[0];

            if (file) {
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                const fileSize = file.size;

                if (fileExt !== 'pdf') {
                    Swal.fire({
                        icon: 'error',
                        title: 'ชนิดไฟล์ไม่ถูกต้อง',
                        text: 'กรุณาแนบไฟล์ในรูปแบบ PDF (.pdf) เท่านั้นค่ะ',
                        confirmButtonColor: '#003b6f'
                    });
                    input.value = '';
                    span.textContent = 'No file chosen';
                } else if (fileSize > 5242880) {
                    Swal.fire({
                        icon: 'error',
                        title: 'ไฟล์มีขนาดใหญ่เกินไป',
                        text: 'กรุณาแนบไฟล์ที่มีขนาดไม่เกิน 5 MB ค่ะ',
                        confirmButtonColor: '#003b6f'
                    });
                    input.value = '';
                    span.textContent = 'No file chosen';
                } else {
                    span.textContent = fileName;
                    if (statusLabel) {
                        statusLabel.innerHTML = '<i class="fa-solid fa-file-pdf"></i> เลือกไฟล์แล้ว: ' + fileName;
                        statusLabel.classList.replace('text-muted', 'text-primary');
                        statusLabel.classList.replace('text-success', 'text-primary');
                    }
                    if (box) box.classList.replace('incomplete', 'complete');
                }
            } else {
                span.textContent = 'No file chosen';
            }
            checkAllRequirements();
        }

        function checkAllRequirements() {
            const advisorValid = advisorSelect.value !== "" && advisorSelect.value !== null;
            const file1Valid = hasDbDoc1 || document.getElementById('doc_std_card').files.length > 0;
            const file2Valid = hasDbDoc2 || document.getElementById('doc_transcript').files.length > 0;
            const file3Valid = hasDbDoc3 || document.getElementById('doc_activity').files.length > 0;

            const isCurrentPageComplete = advisorValid && file1Valid && file2Valid && file3Valid;

            if (btnSubmit) {
                btnSubmit.disabled = !(prevPagesComplete && isCurrentPageComplete);
            }
        }

        function validateVisuals() {
            const advisorValue = advisorSelect.value;
            if (advisorValue === "" || advisorValue === null) {
                if (advisorSelect.dataset.interacted === "true") {
                    advisorSelect.classList.add('is-invalid');
                }
            } else {
                advisorSelect.classList.remove('is-invalid');
                advisorSelect.classList.add('is-valid');
            }
            checkAllRequirements();
        }

        advisorSelect.addEventListener('change', () => {
            advisorSelect.dataset.interacted = "true";
            validateVisuals();
        });

        function openModal() {
            document.getElementById("confirmModal").style.display = "block";
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function closeModal() {
            document.getElementById("confirmModal").style.display = "none";
        }

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                targetInput.value = this.getAttribute('href');
                form.submit();
            });
        });

        window.onload = function() {
            checkAllRequirements();
            if (advisorSelect.value !== "" && advisorSelect.value !== null) {
                advisorSelect.classList.add('is-valid');
            }

            if (document.getElementById("confirmModal").style.display === "block") {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        };
    </script>
</body>

</html>