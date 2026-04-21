<?php
session_start();
include '../include/config.php';

if (!isset($_SESSION['student_id'])) {
    echo "<script>
            alert('กรุณาเข้าสู่ระบบก่อนดำเนินการกรอกข้อมูล');
            window.location.href='../root/index.php';
        </script>";
    exit();
}

$current_student_code = $_SESSION['student_id'];
$today = date('Y-m-d');

$correct_info = null;
$st_activate_status = 0;
$st_type_db = 0;
$st_confirm_db = 0;
if (isset($connect1)) {
    $sql_get_info = "SELECT st_firstname, st_lastname, st_code, st_program, st_email, st_sex, st_activate, st_type, st_confirm FROM tb_student WHERE st_code = '$current_student_code' LIMIT 1";
    $result_info = mysqli_query($connect1, $sql_get_info);
    if ($result_info) {
        $correct_info = mysqli_fetch_assoc($result_info);
        if ($correct_info) {
            $st_activate_status = (int)$correct_info['st_activate'];
            $st_type_db = (int)$correct_info['st_type'];
            $st_confirm_db = (int)$correct_info['st_confirm'];
        }
    }

    $sql_check_ban = "SELECT * FROM tb_ban 
                    WHERE code_student = '$current_student_code' 
                    AND '$today' BETWEEN date_start AND date_end";
    $result_ban = mysqli_query($connect1, $sql_check_ban);

    if (mysqli_num_rows($result_ban) > 0) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Prompt&display=swap' rel='stylesheet'>
            <style>body { font-family: \"Prompt\", sans-serif; }</style>
        </head>
        <body>
            <script>
                setTimeout(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'สิทธิ์ของคุณถูกระงับ',
                        text: 'ขออภัย รายชื่อนักศึกษาของคุณถูกระงับสิทธิ์การสมัครทุนในขณะนี้',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'ตกลง'
                    }).then((result) => {
                        window.location.href = '../root/index.php';
                    });
                }, 100);
            </script>
        </body>
        </html>";
        exit();
    }
}

$already_applied_type2 = false;
$existing_member_data = null;
$existing_schedule = [];

if (isset($connect1) && $correct_info) {
    $fname_check = mysqli_real_escape_string($connect1, $correct_info['st_firstname']);
    $lname_check = mysqli_real_escape_string($connect1, $correct_info['st_lastname']);
    $sql_check_member = "SELECT * FROM tb_member WHERE name_mem = '$fname_check' AND sur_mem = '$lname_check' LIMIT 1";
    $res_member = mysqli_query($connect1, $sql_check_member);
    if ($res_member && mysqli_num_rows($res_member) > 0) {
        $already_applied_type2 = true;
        $existing_member_data = mysqli_fetch_assoc($res_member);

        $mem_id = $existing_member_data['id_mem'];
        $sql_get_date = "SELECT * FROM tb_mem_date WHERE id_mem = '$mem_id'";
        $res_date = mysqli_query($connect1, $sql_get_date);
        while ($row_date = mysqli_fetch_assoc($res_date)) {
            $existing_schedule[$row_date['date_date']] = $row_date['date_time'];
        }
    }
}

$scholarship_options = [];
if (isset($connect1)) {
    $sql_types = "SELECT st_name_1, st_1, st_name_2, st_2, st_name_3, st_3 FROM tb_year WHERE y_id = 1";
    $result_types = mysqli_query($connect1, $sql_types);
    if ($result_types && mysqli_num_rows($result_types) > 0) {
        $data_types = mysqli_fetch_assoc($result_types);
        if (!empty($data_types['st_name_1']) && $data_types['st_1'] == 0) $scholarship_options[1] = $data_types['st_name_1'];
        if (!empty($data_types['st_name_2']) && $data_types['st_2'] == 0) $scholarship_options[2] = $data_types['st_name_2'];
        if (!empty($data_types['st_name_3']) && $data_types['st_3'] == 0) $scholarship_options[3] = $data_types['st_name_3'];
    }
}

$major_options = [];
if (isset($connect1)) {
    $sql_majors = "SELECT g_id, g_program FROM tb_program ORDER BY g_program ASC";
    $result_majors = mysqli_query($connect1, $sql_majors);
    if ($result_majors) while ($row = mysqli_fetch_assoc($result_majors)) $major_options[$row['g_id']] = $row['g_program'];
}

$teacher_options = [];
if (isset($connect1)) {
    $sql_teachers = "SELECT tc_id, tc_name FROM tb_teacher ORDER BY tc_name ASC";
    $result_teachers = mysqli_query($connect1, $sql_teachers);
    if ($result_teachers) while ($row = mysqli_fetch_assoc($result_teachers)) $teacher_options[$row['tc_id']] = $row['tc_name'];
}

$time_steps = ["08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "12:00", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30"];
$days = [2 => "จันทร์", 3 => "อังคาร", 4 => "พุธ", 5 => "พฤหัสบดี", 6 => "ศุกร์"];

$st_title_db = "";
if ($correct_info) {
    if ($correct_info['st_sex'] == 1) $st_title_db = "นาย";
    else if ($correct_info['st_sex'] == 2) $st_title_db = "นางสาว";
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครทุนการศึกษา - คณะศิลปศาสตร์</title>
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
        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content-area {
            flex: 1 0 auto;
            background-color: #fff;
        }

        .dynamic-section {
            display: none;
        }

        .dynamic-section.active {
            display: block;
        }

        .skill-grid {
            display: grid;
            grid-template-columns: 180px 1fr;
            align-items: center;
            margin-bottom: 12px;
            padding: 5px;
            border-radius: 5px;
        }

        .skill-grid.is-invalid-grid {
            border: 1px solid #dc3545;
            background-color: #fff8f8;
        }

        .radio-group {
            display: flex;
            gap: 15px;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .schedule-table th {
            background: #f8f9fa;
            text-align: center;
            font-size: 14px;
        }

        .schedule-table td {
            vertical-align: middle;
        }

        .has-error .form-control,
        .has-error .regis-custom-select-trigger {
            border-color: #dc3545 !important;
        }

        .btn-regis-submit {
            background-color: #003c71;
            color: white;
            border-radius: 50px;
            padding: 10px 35px;
            border: 0;
            transition: opacity 0.3s;
        }

        .btn-regis-cancel {
            background-color: #6c757d;
            color: white;
            border-radius: 50px;
            padding: 10px 35px;
        }

        #default-scholarship-selector {
            padding: 40px 0;
            text-align: center;
        }

        .main-choice-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 500px;
            margin: 0 auto;
        }

        .main-choice-card {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .main-choice-card:hover {
            border-color: #003c71;
            background-color: #f8fafc;
            transform: scale(1.02);
        }

        .main-choice-card h4 {
            margin: 0;
            font-weight: 600;
            color: #1e293b;
            font-size: 18px;
            text-align: left;
        }

        .main-choice-icon {
            width: 45px;
            height: 45px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #003c71;
            font-size: 20px;
        }

        #registration-form-content {
            display: none;
        }

        .form-check-input[type=checkbox] {
            border-color: black
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff8f8 !important;
        }

        .regis-custom-select-wrapper.is-invalid .regis-custom-select-trigger {
            border-color: #dc3545 !important;
        }

        .is-invalid-file-label {
            border: 1px solid #dc3545 !important;
            background-color: #fff8f8;
        }

        .is-invalid-checkbox-label {
            color: #dc3545;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .regis-container-card {
                padding: 25px 15px !important;
                margin: 0 10px;
            }

            #scholarship-title-header {
                font-size: 18px !important;
            }

            .skill-grid {
                grid-template-columns: 1fr;
                gap: 5px;
                margin-bottom: 20px;
            }

            .radio-group {
                flex-wrap: wrap;
                gap: 10px;
            }

            .radio-item {
                font-size: 13px;
                background: #f8f9fa;
                padding: 5px 10px;
                border-radius: 5px;
            }

            .main-choice-card {
                padding: 15px;
            }

            .main-choice-card h4 {
                font-size: 15px;
            }

            .btn-regis-submit,
            .btn-regis-cancel {
                width: 100%;
                padding: 12px;
            }

            .d-flex.justify-content-center.gap-3 {
                flex-direction: column;
                gap: 10px !important;
            }
        }
    </style>
</head>

<body>
    <div class="sticky-header-wrapper">
        <?php include('../include/navbar.php'); ?>
        <?php include('../include/status_bar.php'); ?>
    </div>

    <div class="main-content-area">
        <div class="container py-5">
            <div class="regis-container-card mx-auto shadow border p-4 p-md-5" style="max-width: 850px; background: #fff; border-radius: 15px;">
                <div class="text-center mb-5" id="header-container">
                    <h1 id="scholarship-title-header" class="fw-bold" style="font-size: 24px; color: #333;">กรุณาเลือกประเภททุนการศึกษาที่ต้องการสมัคร</h1>
                </div>

                <div id="default-scholarship-selector">
                    <div class="main-choice-container">
                        <?php if (!empty($scholarship_options)): ?>
                            <?php foreach ($scholarship_options as $id => $name): ?>
                                <?php
                                $isDisabled = false;
                                $cardOnClick = "selectScholarshipMain('" . $id . "')";
                                $cardStyle = "";
                                ?>
                                <div class="main-choice-card" onclick="<?= $cardOnClick ?>" style="<?= $cardStyle ?>">
                                    <div>
                                        <h4><?= htmlspecialchars($name) ?></h4>
                                        <?php if ($id == 2 && $already_applied_type2): ?>
                                            <div class="text-success mt-2" style="font-size: 13px; font-weight: 500; text-align: left;">สถานะ : ส่งใบสมัครแล้ว</div>
                                        <?php elseif (($id == 1 || $id == 3) && $st_type_db == $id): ?>
                                            <?php
                                            if ($st_activate_status == 0) {
                                                $status_label = "ยังกรอกข้อมูลไม่ครบถ้วน";
                                                $status_style = "color: #dc3545;"; // สีแดง
                                            } else {
                                                if ($st_confirm_db == 1) {
                                                    $status_label = "ได้รับการพิจารณา";
                                                    $status_style = "color: #198754;"; // สีเขียว
                                                } else if ($st_confirm_db == 2) {
                                                    $status_label = "ไม่ผ่านการพิจารณา";
                                                    $status_style = "color: #dc3545;"; // สีแดง
                                                } else {
                                                    $status_label = "รอพิจารณา";
                                                    $status_style = "color: #ffaa00;"; // สีเหลืองเข้ม/ส้ม
                                                }
                                            }
                                            ?>
                                            <div class="mt-2" style="font-size: 13px; font-weight: 500; text-align: left; <?= $status_style ?>">สถานะ : <?= $status_label ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="main-choice-icon"><i class="fa-solid fa-chevron-right"></i></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-warning py-4">ขออภัย ขณะนี้ยังไม่มีทุนที่เปิดรับสมัคร</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="registration-form-content">
                    <form id="regisForm" action="../admin/students/submit_regis.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()" novalidate>
                        <input type="hidden" name="scholarship_type" id="hidden-scholarship-type" value="">

                        <div class="row mb-4" id="group-name">
                            <div class="col-md-2 pt-2">
                                <label class="fw-bold regis-label">ชื่อ-สกุล <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <div class="row g-2">
                                    <div class="col-sm-3">
                                        <div class="regis-custom-select-wrapper" id="wrap-title">
                                            <select name="title" id="title" style="display: none;">
                                                <option value="" disabled <?php echo ($st_title_db == "") ? "selected" : ""; ?>>คำนำหน้า</option>
                                                <option value="นาย" <?php echo ($st_title_db == "นาย") ? "selected" : ""; ?>>นาย</option>
                                                <option value="นางสาว" <?php echo ($st_title_db == "นางสาว") ? "selected" : ""; ?>>นางสาว</option>
                                                <option value="นาง" <?php echo ($st_title_db == "นาง") ? "selected" : ""; ?>>นาง</option>
                                            </select>
                                            <div class="regis-custom-select-trigger">
                                                <span><?php echo ($st_title_db != "") ? $st_title_db : "คำนำหน้า"; ?></span>
                                                <i class="fa-solid fa-chevron-down ms-2"></i>
                                            </div>
                                            <div class="regis-custom-options">
                                                <div class="regis-custom-option" data-value="นาย">นาย</div>
                                                <div class="regis-custom-option" data-value="นางสาว">นางสาว</div>
                                                <div class="regis-custom-option" data-value="นาง">นาง</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <input type="text" id="firstname" name="firstname" class="form-control bg-light" placeholder="ชื่อ" value="<?php echo $correct_info['st_firstname'] ?? ''; ?>" readonly>
                                    </div>
                                    <div class="col-sm">
                                        <input type="text" id="lastname" name="lastname" class="form-control bg-light" placeholder="นามสกุล" value="<?php echo $correct_info['st_lastname'] ?? ''; ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="fields-type-1" class="dynamic-section">
                            <div class="mb-4" id="group-gpa">
                                <label class="fw-bold regis-label mb-2">เกรดเฉลี่ย <span class="text-danger">*</span></label>
                                <input type="text" name="gpa" id="gpa" class="form-control" placeholder="เกรดเฉลี่ยสะสม (เช่น 3.50)" oninput="checkInput(this)">
                            </div>
                            <div class="mb-4" id="group-student-id-t1">
                                <label class="fw-bold regis-label mb-2">รหัสนักศึกษา <span class="text-danger">*</span></label>
                                <input type="text" name="student_id_t1" id="student-id-t1" class="form-control" placeholder="กรอกรหัสนักศึกษา" value="<?php echo $correct_info['st_code'] ?? ''; ?>" oninput="checkInput(this)">
                            </div>
                            <div class="mb-4" id="group-major-t1">
                                <label class="fw-bold regis-label mb-2">สาขาวิชา <span class="text-danger">*</span></label>
                                <div class="regis-custom-select-wrapper" id="wrap-major-t1">
                                    <select name="major_t1" id="major-t1" style="display: none;">
                                        <option value="" disabled <?php echo (!isset($correct_info['st_program'])) ? "selected" : ""; ?>>เลือกสาขาวิชา</option>
                                        <?php foreach ($major_options as $id => $name): ?>
                                            <option value="<?= $id ?>" <?php echo (isset($correct_info['st_program']) && $correct_info['st_program'] == $id) ? "selected" : ""; ?>><?= htmlspecialchars($name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="regis-custom-select-trigger">
                                        <span><?php echo (isset($correct_info['st_program']) && isset($major_options[$correct_info['st_program']])) ? htmlspecialchars($major_options[$correct_info['st_program']]) : "เลือกสาขาวิชา"; ?></span>
                                        <i class="fa-solid fa-chevron-down ms-2"></i>
                                    </div>
                                    <div class="regis-custom-options">
                                        <?php foreach ($major_options as $id => $name) : ?>
                                            <div class="regis-custom-option" data-value="<?= $id ?>"><?= htmlspecialchars($name) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4" id="group-email-t1">
                                <label class="fw-bold regis-label mb-2">E-mail <span class="text-danger">*</span></label>
                                <input type="email" name="email_t1" id="email-t1" class="form-control" placeholder="รหัสนักศึกษา@psu.ac.th" value="<?php echo $correct_info['st_email'] ?? ''; ?>" oninput="checkInput(this)">
                            </div>
                            <div class="mb-4" id="group-profile-pic">
                                <label class="fw-bold regis-label mb-2">ภาพประจำตัว <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center mb-2">
                                    <input type="file" id="profile-pic" name="profile_pic" accept=".jpg, .jpeg" style="display:none;">
                                    <label for="profile-pic" id="label-profile-pic" class="regis-custom-file-btn">Choose File</label>
                                    <span id="file-name-info" class="regis-file-info-text ms-2">No file chosen</span>
                                </div>
                            </div>
                            <div class="mb-4" id="group-conditions">
                                <div class="regis-conditions-box p-4" style="background: #fdfdfd; border: 1px solid #e0e0e0; border-radius: 10px;">
                                    <h5 class="fw-bold mb-3" style="font-size: 18px;">เงื่อนไขการสมัคร <span class="text-danger">*</span></h5>
                                    <ol class="text-muted" style="font-size: 14px; line-height: 1.8;">
                                        <li>ผู้สมัครต้องมีสถานะกำลังศึกษาอยู่ในคณะศิลปศาสตร์</li>
                                        <li>ผู้สมัครต้องไม่มีประวัติการกระทำผิดทางวินัยนักศึกษา</li>
                                        <li>ผู้สมัครต้องแต่งกายชุดนักศึกษาเรียบร้อย</li>
                                        <li>ผู้สมัครต้องเข้ามายืนยันใบสมัครทางอีเมลภายใน 1 วัน</li>
                                        <li>ระบบจะลบข้อมูลการสมัครทุกภาคการศึกษา</li>
                                    </ol>
                                    <label class="regis-accept-label mt-3 cursor-pointer" id="label-accept-conditions">
                                        <input type="checkbox" name="accept_conditions" id="accept_conditions" class="form-check-input" onchange="checkCheckbox(this)">
                                        <span class="ms-2 fw-medium">ข้าพเจ้ายอมรับเงื่อนไข</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="fields-type-2" class="dynamic-section">
                            <div class="row mb-4">
                                <div class="col-md-6" id="group-major-t2">
                                    <label class="fw-bold regis-label mb-2">สาขา <span class="text-danger">*</span></label>
                                    <div class="regis-custom-select-wrapper" id="wrap-major-t2">
                                        <?php
                                        $val_major2 = $existing_member_data['programe'] ?? ($correct_info['st_program'] ?? '');
                                        $txt_major2 = isset($major_options[$val_major2]) ? $major_options[$val_major2] : "--เลือก--";
                                        ?>
                                        <select name="major_t2" id="major-t2" style="display: none;">
                                            <option value="" disabled <?php echo ($val_major2 == "") ? "selected" : ""; ?>>--เลือก--</option>
                                            <?php foreach ($major_options as $id => $name): ?>
                                                <option value="<?= $id ?>" <?php echo ($val_major2 == $id) ? "selected" : ""; ?>><?= htmlspecialchars($name) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="regis-custom-select-trigger">
                                            <span><?= htmlspecialchars($txt_major2) ?></span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </div>
                                        <div class="regis-custom-options">
                                            <?php foreach ($major_options as $id => $name) : ?>
                                                <div class="regis-custom-option" data-value="<?= $id ?>"><?= htmlspecialchars($name) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" id="group-class-mem">
                                    <label class="fw-bold regis-label mb-2">ชั้นปี <span class="text-danger">*</span></label>
                                    <div class="regis-custom-select-wrapper" id="wrap-class-mem">
                                        <?php
                                        $val_class = $existing_member_data['class_mem'] ?? '';
                                        $txt_class = ($val_class != "") ? "ชั้นปีที่ " . $val_class : "--เลือก--";
                                        ?>
                                        <select name="class_mem" id="class-mem" style="display: none;">
                                            <option value="" disabled <?php echo ($val_class == "") ? "selected" : ""; ?>>--เลือก--</option>
                                            <option value="1" <?php echo ($val_class == "1") ? "selected" : ""; ?>>ชั้นปีที่ 1</option>
                                            <option value="2" <?php echo ($val_class == "2") ? "selected" : ""; ?>>ชั้นปีที่ 2</option>
                                            <option value="3" <?php echo ($val_class == "3") ? "selected" : ""; ?>>ชั้นปีที่ 3</option>
                                            <option value="4" <?php echo ($val_class == "4") ? "selected" : ""; ?>>ชั้นปีที่ 4</option>
                                        </select>
                                        <div class="regis-custom-select-trigger"><span><?= $txt_class ?></span><i class="fa-solid fa-chevron-down"></i></div>
                                        <div class="regis-custom-options">
                                            <div class="regis-custom-option" data-value="1">ชั้นปีที่ 1</div>
                                            <div class="regis-custom-option" data-value="2">ชั้นปีที่ 2</div>
                                            <div class="regis-custom-option" data-value="3">ชั้นปีที่ 3</div>
                                            <div class="regis-custom-option" data-value="4">ชั้นปีที่ 4</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4" id="group-advisor">
                                <label class="fw-bold regis-label mb-2">อาจารย์ที่ปรึกษา <span class="text-danger">*</span></label>
                                <div class="regis-custom-select-wrapper" id="wrap-tea-mem">
                                    <?php
                                    $val_tea = $existing_member_data['tea_mem'] ?? '';
                                    $txt_tea = isset($teacher_options[$val_tea]) ? $teacher_options[$val_tea] : "--เลือก--";
                                    ?>
                                    <select name="tea_mem" id="tea-mem" style="display: none;">
                                        <option value="" disabled <?php echo ($val_tea == "") ? "selected" : ""; ?>>--เลือก--</option>
                                        <?php foreach ($teacher_options as $id => $name): ?>
                                            <option value="<?= $id ?>" <?php echo ($val_tea == $id) ? "selected" : ""; ?>><?= htmlspecialchars($name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="regis-custom-select-trigger"><span><?= htmlspecialchars($txt_tea) ?></span><i class="fa-solid fa-chevron-down"></i></div>
                                    <div class="regis-custom-options">
                                        <?php foreach ($teacher_options as $id => $name) : ?>
                                            <div class="regis-custom-option" data-value="<?= $id ?>"><?= htmlspecialchars($name) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4" id="group-tel">
                                <label class="fw-bold regis-label mb-2">เบอร์โทร <span class="text-danger">*</span></label>
                                <input type="text" name="tel_mem" id="tel-mem" class="form-control" placeholder="กรอกเบอร์โทรศัพท์" value="<?php echo $existing_member_data['tel_mem'] ?? ''; ?>" oninput="checkInput(this); validateTelImmediate(this)">
                                <div id="tel-error-text" class="text-danger mt-1" style="font-size: 13px; display: none;">*กรุณากรอกเบอร์โทรศัพท์มือถือที่ถูกต้อง (10 หลัก และไม่ขึ้นต้นด้วย 01-05)</div>
                            </div>
                            <div class="mb-5" id="group-email-member">
                                <label class="fw-bold regis-label mb-2">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email_mem" id="email-mem" class="form-control" value="<?php echo $existing_member_data['email_mem'] ?? ($correct_info['st_email'] ?? ''); ?>" oninput="checkInput(this)">
                            </div>
                            <div class="mb-5" id="group-schedule">
                                <p class="fw-bold mb-3 text-secondary">ข้อมูลตารางเวลาที่สามารถปฏิบัติงานได้ (อย่างน้อย 1 วัน) <span class="text-danger">*</span></p>
                                <div class="table-responsive">
                                    <table class="table table-bordered schedule-table" id="schedule-table">
                                        <thead>
                                            <tr>
                                                <th>วัน</th>
                                                <th>ตั้งแต่เวลา</th>
                                                <th>ถึงเวลา</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($days as $id_d => $name_d):
                                                $s_val = "";
                                                $e_val = "";
                                                if (isset($existing_schedule[$id_d])) {
                                                    $time_parts = explode(' - ', $existing_schedule[$id_d]);
                                                    $s_val = $time_parts[0] ?? "";
                                                    $e_val = $time_parts[1] ?? "";
                                                }
                                            ?>
                                                <tr>
                                                    <td class="text-center fw-bold"><?= $name_d ?></td>
                                                    <td>
                                                        <select name="start_time[<?= $id_d ?>]" class="form-select form-select-sm sched-select" onchange="checkTableSelect()">
                                                            <option value="">--เลือก--</option>
                                                            <?php foreach ($time_steps as $t): if ($t <= "12:00"): ?>
                                                                    <option value='<?= $t ?>' <?php echo ($s_val == $t) ? "selected" : ""; ?>><?= $t ?></option>
                                                            <?php endif;
                                                            endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="end_time[<?= $id_d ?>]" class="form-select form-select-sm sched-select" onchange="checkTableSelect()">
                                                            <option value="">--เลือก--</option>
                                                            <?php foreach ($time_steps as $t): if ($t >= "13:00"): ?>
                                                                    <option value='<?= $t ?>' <?php echo ($e_val == $t) ? "selected" : ""; ?>><?= $t ?></option>
                                                            <?php endif;
                                                            endforeach; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="mb-5" id="group-com-skill">
                                <p class="fw-bold mb-3 text-secondary">ข้อมูลความสามารถด้านคอมพิวเตอร์ (ครบทุกข้อ) <span class="text-danger">*</span></p>
                                <?php
                                $com_skills = ["Ms Word", "Ms Excel", "Canva", "PSD-AI"];
                                $old_com = isset($existing_member_data['com_mem']) ? explode('|o|', $existing_member_data['com_mem']) : array_fill(0, 4, "");

                                foreach ($com_skills as $idx => $skill):
                                    $s_val = $old_com[$idx] ?? "";
                                ?>
                                    <div class="skill-grid" id="grid-com-<?= $idx ?>"><span>- <?= $skill ?></span>
                                        <div class="radio-group"><?php $opts = [1 => "ดีมาก", 2 => "ดี", 3 => "ปานกลาง", 4 => "พอใช้"];
                                                                    foreach ($opts as $v => $l): ?>
                                                <label class="radio-item"><input type="radio" name="com_skill_<?= $idx ?>" value="<?= $v ?>" onchange="checkRadio(this)" <?php echo ($s_val == $v) ? "checked" : ""; ?>> <?= $l ?></label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div><?php endforeach; ?>
                            </div>
                            <div class="mb-5" id="group-eng-skill">
                                <p class="fw-bold mb-3 text-secondary">ความรู้ด้านภาษาอังกฤษ (ครบทุกข้อ) <span class="text-danger">*</span></p>
                                <?php
                                $eng_skills = ["Writting", "Speaking", "Listening"];
                                $old_eng = isset($existing_member_data['eng_mem']) ? explode('|o|', $existing_member_data['eng_mem']) : array_fill(0, 3, "");

                                foreach ($eng_skills as $idx => $skill):
                                    $s_val = $old_eng[$idx] ?? "";
                                ?>
                                    <div class="skill-grid" id="grid-eng-<?= $idx ?>"><span>- <?= $skill ?></span>
                                        <div class="radio-group"><?php foreach ($opts as $v => $l): ?>
                                                <label class="radio-item"><input type="radio" name="eng_skill_<?= $idx ?>" value="<?= $v ?>" onchange="checkRadio(this)" <?php echo ($s_val == $v) ? "checked" : ""; ?>> <?= $l ?></label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div><?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-3 mt-5">
                            <a href="../student/regis.php" class="btn btn-regis-cancel px-5">กลับ</a>
                            <button type="submit" id="submitBtn" class="btn btn-regis-submit px-5 border-0">ส่งใบสมัคร</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include('../include/footer.php'); ?>

    <script>
        const stActivateStatus = <?= (int)$st_activate_status ?>;
        const stTypeStatus = <?= (int)$st_type_db ?>;
        const alreadyAppliedType2 = <?= $already_applied_type2 ? 'true' : 'false' ?>;

        function selectScholarshipMain(id) {
            const h = document.getElementById('hidden-scholarship-type');
            if (h) {
                h.value = id;
                h.dispatchEvent(new Event('change'));
            }
        }

        function validateTelImmediate(el) {
            const val = el.value.trim();
            const errorText = document.getElementById('tel-error-text');
            const forbiddenPrefixes = ['01', '02', '03', '04', '05'];
            if (val !== "" && (forbiddenPrefixes.includes(val.substring(0, 2)) || val.length !== 10 || isNaN(val))) {
                errorText.style.display = 'block';
                el.classList.add('is-invalid');
            } else {
                errorText.style.display = 'none';
                el.classList.remove('is-invalid');
            }
        }

        function checkInput(el) {
            el.classList.remove('is-invalid');
        }

        function checkCheckbox(el) {
            document.getElementById('label-accept-conditions').classList.remove('is-invalid-checkbox-label');
        }

        function checkRadio(el) {
            el.closest('.skill-grid').classList.remove('is-invalid-grid');
        }

        function checkTableSelect() {
            document.getElementById('schedule-table').classList.remove('is-invalid');
        }

        function syncCompletenessAndHighlight() {
            const type = document.getElementById('hidden-scholarship-type').value;
            let isComplete = true;

            document.querySelectorAll('.is-invalid, .is-invalid-checkbox-label, .is-invalid-grid, .is-invalid-file-label').forEach(el => {
                el.classList.remove('is-invalid', 'is-invalid-checkbox-label', 'is-invalid-grid', 'is-invalid-file-label');
            });
            document.querySelectorAll('.regis-custom-select-wrapper').forEach(el => el.classList.remove('is-invalid'));

            const title = document.getElementById('title');
            if (title.value === "") {
                document.getElementById('wrap-title').classList.add('is-invalid');
                isComplete = false;
            }
            const fname = document.getElementById('firstname');
            if (fname.value.trim() === "") {
                fname.classList.add('is-invalid');
                isComplete = false;
            }
            const lname = document.getElementById('lastname');
            if (lname.value.trim() === "") {
                lname.classList.add('is-invalid');
                isComplete = false;
            }

            if (type === '1' || type === '3') {
                const gpa = document.getElementById('gpa');
                if (gpa.value.trim() === "") {
                    gpa.classList.add('is-invalid');
                    isComplete = false;
                }
                const sid = document.getElementById('student-id-t1');
                if (sid.value.trim() === "") {
                    sid.classList.add('is-invalid');
                    isComplete = false;
                }
                const major = document.getElementById('major-t1');
                if (major.value === "") {
                    document.getElementById('wrap-major-t1').classList.add('is-invalid');
                    isComplete = false;
                }
                const email = document.getElementById('email-t1');
                if (email.value.trim() === "") {
                    email.classList.add('is-invalid');
                    isComplete = false;
                }
                const pic = document.getElementById('profile-pic');
                if (pic.files.length === 0) {
                    document.getElementById('label-profile-pic').classList.add('is-invalid-file-label');
                    isComplete = false;
                }
                const accept = document.getElementById('accept_conditions');
                if (!accept.checked) {
                    document.getElementById('label-accept-conditions').classList.add('is-invalid-checkbox-label');
                    isComplete = false;
                }

            } else if (type === '2') {
                const major2 = document.getElementById('major-t2');
                if (major2.value === "") {
                    document.getElementById('wrap-major-t2').classList.add('is-invalid');
                    isComplete = false;
                }
                const classMem = document.getElementById('class-mem');
                if (classMem.value === "") {
                    document.getElementById('wrap-class-mem').classList.add('is-invalid');
                    isComplete = false;
                }
                const tea = document.getElementById('tea-mem');
                if (tea.value === "") {
                    document.getElementById('wrap-tea-mem').classList.add('is-invalid');
                    isComplete = false;
                }
                const tel = document.getElementById('tel-mem');
                const telVal = tel.value.trim();
                if (telVal === "" || ['01', '02', '03', '04', '05'].includes(telVal.substring(0, 2)) || telVal.length !== 10) {
                    tel.classList.add('is-invalid');
                    isComplete = false;
                }
                const email2 = document.getElementById('email-mem');
                if (email2.value.trim() === "") {
                    email2.classList.add('is-invalid');
                    isComplete = false;
                }

                let scheduleSelected = false;
                const dArray = [2, 3, 4, 5, 6];
                for (let d of dArray) {
                    const s_el = document.getElementsByName('start_time[' + d + ']')[0];
                    const e_el = document.getElementsByName('end_time[' + d + ']')[0];
                    if (s_el && e_el && s_el.value !== "" && e_el.value !== "") {
                        scheduleSelected = true;
                        break;
                    }
                }
                if (!scheduleSelected) {
                    document.getElementById('schedule-table').classList.add('is-invalid');
                    isComplete = false;
                }

                for (let i = 0; i < 4; i++) {
                    if (!document.querySelector('input[name="com_skill_' + i + '"]:checked')) {
                        document.getElementById('grid-com-' + i).classList.add('is-invalid-grid');
                        isComplete = false;
                    }
                }
                for (let i = 0; i < 3; i++) {
                    if (!document.querySelector('input[name="eng_skill_' + i + '"]:checked')) {
                        document.getElementById('grid-eng-' + i).classList.add('is-invalid-grid');
                        isComplete = false;
                    }
                }
            }
            return isComplete;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.regis-custom-select-wrapper').forEach(wrapper => {
                const trigger = wrapper.querySelector('.regis-custom-select-trigger'),
                    options = wrapper.querySelectorAll('.regis-custom-option'),
                    select = wrapper.querySelector('select');
                if (!trigger || !select) return;
                trigger.onclick = (e) => {
                    e.stopPropagation();
                    wrapper.classList.toggle('open');
                };
                options.forEach(opt => {
                    opt.onclick = () => {
                        select.value = opt.dataset.value;
                        trigger.querySelector('span').textContent = opt.textContent;
                        wrapper.classList.remove('open');
                        wrapper.classList.remove('is-invalid');
                    };
                });
            });
            window.onclick = () => document.querySelectorAll('.regis-custom-select-wrapper').forEach(w => w.classList.remove('open'));

            const fileInput = document.getElementById('profile-pic');
            if (fileInput) fileInput.addEventListener('change', function() {
                document.getElementById('file-name-info').textContent = this.files[0] ? this.files[0].name : 'No file chosen';
                document.getElementById('label-profile-pic').classList.remove('is-invalid-file-label');
            });

            const hiddenType = document.getElementById('hidden-scholarship-type');

            function updateFormView() {
                const val = hiddenType.value;

                if (val === '1' || val === '3') {
                    if (val == stTypeStatus) {
                        if (stActivateStatus === 1) {
                            window.location.href = 'confirm_page.php';
                            return;
                        } else if (stActivateStatus === 0) {
                            window.location.href = '../student/apply_form.php';
                            return;
                        }
                    }
                }

                if (!val || val === "0" || val === "") {
                    document.getElementById('default-scholarship-selector').style.display = 'block';
                    document.getElementById('registration-form-content').style.display = 'none';
                    if (typeof syncStatusBarCards === 'function') syncStatusBarCards("");
                } else {
                    document.getElementById('default-scholarship-selector').style.display = 'none';
                    document.getElementById('registration-form-content').style.display = 'block';
                    document.querySelectorAll('.dynamic-section').forEach(s => s.classList.remove('active'));

                    const subBtn = document.getElementById('submitBtn');
                    if (val === '2') {
                        document.getElementById('fields-type-2').classList.add('active');
                        if (alreadyAppliedType2) {
                            subBtn.style.display = 'none';
                            document.querySelectorAll('#fields-type-2 input, #fields-type-2 select').forEach(el => el.disabled = true);
                            document.querySelectorAll('#fields-type-2 .regis-custom-select-trigger').forEach(el => el.style.pointerEvents = 'none');
                        } else {
                            subBtn.style.display = 'block';
                            document.querySelectorAll('#fields-type-2 input, #fields-type-2 select').forEach(el => el.disabled = false);
                            document.querySelectorAll('#fields-type-2 .regis-custom-select-trigger').forEach(el => el.style.pointerEvents = 'auto');
                        }
                    } else if (val === '1' || val === '3') {
                        document.getElementById('fields-type-1').classList.add('active');
                        subBtn.style.display = 'block';
                    }

                    const phpOptions = <?= json_encode($scholarship_options) ?>;
                    if ($st_activate_status == 0) {
                        $status_label = "ยังกรอกข้อมูลไม่ครบถ้วน";
                        $status_style = "color: #dc3545;";
                    } else {
                        if ($st_confirm_db == 1) {
                            $status_label = "ได้รับการพิจารณา";
                            $status_style = "color: #198754;";
                        } else if ($st_confirm_db == 2) {
                            $status_label = "ไม่ผ่านการพิจารณา";
                            $status_style = "color: #dc3545;";
                        } else {
                            $status_label = "รอพิจารณา";
                            $status_style = "color: #ffaa00;";
                        }
                    }
                    document.getElementById('scholarship-title-header').innerHTML = "ข้อมูลการสมัคร <span style='color: #0056b3;'>" + (phpOptions[val] || "") + "</span>";
                }
            }
            if (hiddenType) {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.attributeName === 'value') updateFormView();
                    });
                });
                observer.observe(hiddenType, {
                    attributes: true
                });
                hiddenType.addEventListener('change', updateFormView);
                updateFormView();
            }
        });

        function validateForm() {
            const isFormValid = syncCompletenessAndHighlight();
            if (!isFormValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    text: 'โปรดตรวจสอบช่องที่มีกรอบสีแดงและระบุข้อมูลให้ครบทุกส่วน',
                    confirmButtonColor: '#003c71'
                });
                return false;
            }
            return true;
        }
    </script>
</body>

</html>