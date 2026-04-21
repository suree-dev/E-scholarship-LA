<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../../include/config.php';

if (!isset($_SESSION['id_teacher'])) {
    header("Location: ../../root/login_temp.php");
    exit();
}

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
if ($student_id <= 0) {
    die("ไม่พบรหัสนักศึกษาที่ต้องการตรวจสอบ");
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

$student = null;
$family = [];

if ($student_id > 0) {
    $sql_student = "SELECT s.*, p.g_program, t.tc_name AS advisor_name FROM tb_student AS s
                    LEFT JOIN tb_program AS p ON s.st_program = p.g_id
                    LEFT JOIN tb_teacher AS t ON s.id_teacher = t.tc_id
                    WHERE s.st_id = '$student_id'";
    $result_student = mysqli_query($connect1, $sql_student);

    if ($result_student && mysqli_num_rows($result_student) > 0) {
        $student_data = mysqli_fetch_assoc($result_student);
        $prefix = ($student_data['st_sex'] == 1) ? 'นาย' : (($student_data['st_sex'] == 2) ? 'นางสาว' : '');
        $student = [
            'id' => $student_data['st_code'],
            'prefix' => $prefix,
            'firstname' => $student_data['st_firstname'],
            'lastname' => $student_data['st_lastname'],
            'gpa' => $student_data['st_score'],
            'major' => $student_data['g_program'] ?: 'N/A',
            'advisor' => $student_data['advisor_name'] ?: 'N/A',
            'scholarship_name' => $scholarship_options[$student_data['st_type']] ?? 'ไม่ระบุประเภททุน',
            'image_url' => !empty($student_data['st_image']) ? '../../images/student/' . $student_data['st_image'] : '../../assets/images/bg/no-profile.png'
        ];

        function parseFamilyData($data)
        {
            $parts = explode('|-o-|', $data);
            return [
                'name' => $parts[0] ?? '',
                'age' => $parts[1] ?? '',
                'status_id' => $parts[2] ?? '',
                'job' => $parts[3] ?? '',
                'income' => $parts[4] ?? '',
                'workplace' => $parts[5] ?? '',
                'phone' => $parts[6] ?? ''
            ];
        }

        $father = parseFamilyData($student_data['st_father'] ?? '');
        $family['father_name'] = $father['name'];
        $family['father_age'] = $father['age'];
        $family['father_status'] = ($father['status_id'] == '1') ? 'alive' : (($father['status_id'] == '0') ? 'deceased' : '');
        $family['father_job'] = $father['job'];
        $family['father_income'] = $father['income'];
        $family['father_workplace'] = $father['workplace'];
        $family['father_phone'] = $father['phone'];

        $mother = parseFamilyData($student_data['st_mother'] ?? '');
        $family['mother_name'] = $mother['name'];
        $family['mother_age'] = $mother['age'];
        $family['mother_status'] = ($mother['status_id'] == '1') ? 'alive' : (($mother['status_id'] == '0') ? 'deceased' : '');
        $family['mother_job'] = $mother['job'];
        $family['mother_income'] = $mother['income'];
        $family['mother_workplace'] = $mother['workplace'];
        $family['mother_phone'] = $mother['phone'];

        $guardian = parseFamilyData($student_data['st_guardian'] ?? '');
        $family['guardian_name'] = $guardian['name'];
        $family['guardian_age'] = $guardian['age'];
        $family['guardian_job'] = $guardian['job'];
        $family['guardian_income'] = $guardian['income'];
        $family['guardian_workplace'] = $guardian['workplace'];
        $family['guardian_phone'] = $guardian['phone'];

        $family['siblings'] = [];
        if (!empty($student_data['st_siblings'])) {
            foreach (explode('|-o-|', $student_data['st_siblings']) as $row) {
                $parts = explode(':', $row);
                if (!empty($parts[0])) {
                    $family['siblings'][] = [
                        'name'   => $parts[0],
                        'edu'    => $parts[1] ?? '-',
                        'work'   => $parts[2] ?? '-',
                        'income' => $parts[3] ?? '0'
                    ];
                }
            }
        }

        $res_bur = mysqli_query($connect1, "SELECT * FROM tb_bursary WHERE id_student = '$student_id' ORDER BY bur_year DESC");
        $family['history_list'] = [];
        while ($row = mysqli_fetch_assoc($res_bur)) $family['history_list'][] = ['year' => $row['bur_year'], 'name' => $row['bur_name'], 'amount' => $row['bur_quantity']];
        $family['history_scholarship'] = !empty($family['history_list']) ? 'yes' : 'no';

        $family['parents_status'] = [];
        $family_status_parts = array_filter(explode('|-o-|', $student_data['st_family_status']));
        if (in_array('1', $family_status_parts)) $family['parents_status'][] = 'together';
        if (in_array('2', $family_status_parts)) $family['parents_status'][] = 'divorced';
        if (in_array('3', $family_status_parts)) $family['parents_status'][] = 'separated';
        if (in_array('4', $family_status_parts)) $family['parents_status'][] = 'father_died';
        if (in_array('5', $family_status_parts)) $family['parents_status'][] = 'mother_died';

        $loan_parts = explode('|-o-|', $student_data['st_borrow_money']);
        $family['loan_status'] = ($loan_parts[0] == '1') ? 'yes' : 'no';
        $family['loan_amount'] = $loan_parts[2] ?? '';
        $family['loan_no_reason'] = ($family['loan_status'] == 'no') ? ($loan_parts[1] ?? '') : '';

        $expense_parts = array_filter(explode('|-o-|', $student_data['st_received']));
        $family['expense_source'] = [];
        if (in_array('1', $expense_parts)) $family['expense_source'][] = 'father';
        if (in_array('2', $expense_parts)) $family['expense_source'][] = 'mother';
        if (in_array('3', $expense_parts)) $family['expense_source'][] = 'guardian';
        if (in_array('4', $expense_parts)) $family['expense_source'][] = 'loan';
        if (in_array('5', $expense_parts)) $family['expense_source'][] = 'other';
        $family['expense_amount'] = end($expense_parts) ?: '';

        $work_parts = explode('|-o-|', $student_data['st_job']);
        $family['work_history'] = ($work_parts[0] == '1') ? 'yes' : 'no';
        $family['work_history_type'] = $work_parts[2] ?? '';
        $family['work_history_income'] = $work_parts[3] ?? '';

        $curr_work_parts = explode('|-o-|', $student_data['st_current_job']);
        $family['current_work'] = ($curr_work_parts[0] == '1') ? 'yes' : 'no';
        $family['current_work_reason'] = ($family['current_work'] == 'no') ? ($curr_work_parts[2] ?? '') : '';

        $prob_parts = explode('|-o-|', $student_data['st_peripeteia']);
        $family['financial_prob'] = ($prob_parts[0] == '1') ? 'often' : 'not_often';
        $family['financial_prob_reason'] = $prob_parts[2] ?? '';

        $solve_parts = array_filter(explode('|-o-|', $student_data['st_solutions']));
        $family['solve_prob'] = '';
        if (in_array('1', $solve_parts)) $family['solve_prob'] = 'loan_out';
        if (in_array('2', $solve_parts)) $family['solve_prob'] = 'loan_in';
        if (in_array('3', $solve_parts)) $family['solve_prob'] = 'relative';
        if (in_array('4', $solve_parts)) $family['solve_prob'] = 'parttime';
        if (in_array('5', $solve_parts)) $family['solve_prob'] = 'other';
        $family['solve_prob_other'] = ($family['solve_prob'] == 'other') ? end($solve_parts) : '';
    }
}

$committee_id = $_SESSION['id_teacher'] ?? 0;
$has_scored = false;
$existing_score = "";
$existing_comment = "";
if ($committee_id > 0 && $student_id > 0) {
    $res_check = mysqli_query($connect1, "SELECT * FROM tb_scores WHERE st_id = '$student_id' AND tc_id = '$committee_id'");
    if ($row_score = mysqli_fetch_assoc($res_check)) {
        $has_scored = true;
        $existing_score = $row_score['scores'];
        $existing_comment = $row_score['sco_comment'];
    }
}

if ($student === null) die("Error: ไม่พบข้อมูลนักศึกษา");
$current_month_th = [1 => "มกราคม", 2 => "กุมภาพันธ์", 3 => "มีนาคม", 4 => "เมษายน", 5 => "พฤษภาคม", 6 => "มิถุนายน", 7 => "กรกฎาคม", 8 => "สิงหาคม", 9 => "กันยายน", 10 => "ตุลาคม", 11 => "พฤศจิกายน", 12 => "ธันวาคม"][(int)date("n")];
$current_year_th = date("Y") + 543;
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบสมัคร - ข้อมูลครอบครัว</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/bg/head_01.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/css/global2.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">

    <link rel="stylesheet" href="../../assets/css/navigation.css">
    <link rel="stylesheet" href="../../assets/css/ui-elements.css">

    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets/css/tables.css">

    <link rel="stylesheet" href="../../assets/css/pages.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-light">

    <div class="sticky-header-wrapper">
        <?php include('../../include/navbar.php'); ?>
        <?php include('../../include/status_bar.php'); ?>
    </div>

    <button onclick="scrollToTop()" id="scrollTopBtn" class="scroll-top-btn" title="เลื่อนขึ้นข้างบน">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <div class="container py-5">
        <div class="app-form-container mx-auto">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <a href="../advisors/teacher.php" class="btn btn-secondary rounded-pill px-4">ย้อนกลับ</a>
                <div class="text-center flex-grow-1">
                    <h5 class="fw-bold mb-1">ใบสมัครขอรับทุนการศึกษา<?php echo htmlspecialchars($student['scholarship_name']); ?></h5>
                    <h5 class="fw-bold mb-1">คณะศิลปศาสตร์ มหาวิทยาลัยสงขลานครินทร์</h5>
                    <h5 class="fw-bold mb-1">ประจำปีการศึกษา <?php echo $current_year_th; ?></h5>
                    <p class="text-muted small mt-2">วันที่ <?php echo date("j"); ?> <?php echo $current_month_th; ?> <?php echo $current_year_th; ?> เวลา <?php echo date("H:i"); ?> น.</p>
                </div>
                <div class="student-photo-wrapper">
                    <img src="<?php echo htmlspecialchars($student['image_url']); ?>" alt="Student">
                </div>
            </div>

            <div class="text-center mb-5" style="line-height: 2;">
                <span class="mx-2"><strong>ชื่อ:</strong> <?php echo htmlspecialchars($student['prefix'] . $student['firstname'] . " " . $student['lastname']); ?></span>
                <span class="mx-2"><strong>รหัสนักศึกษา:</strong> <?php echo htmlspecialchars($student['id']); ?></span><br>
                <span class="mx-2"><strong>เกรดเฉลี่ย:</strong> <?php echo htmlspecialchars($student['gpa']); ?></span>
                <span class="mx-2"><strong>สาขาวิชา:</strong> <?php echo htmlspecialchars($student['major']); ?></span>
                <span class="mx-2"><strong>อาจารย์ที่ปรึกษา:</strong> <?php echo htmlspecialchars($student['advisor']); ?></span>
            </div>

            <ul class="nav-tabs-app">
                <li class="nav-item"><a href="../advisors/give_score.php?student_id=<?php echo $student_id; ?>" class="nav-link-custom inactive">ข้อมูลส่วนตัว</a></li>
                <li class="nav-item"><a href="../advisors/family.php?student_id=<?php echo $student_id; ?>" class="nav-link-custom active">ข้อมูลครอบครัว</a></li>
                <li class="nav-item"><a href="../advisors/reasons.php?student_id=<?php echo $student_id; ?>" class="nav-link-custom inactive">ระบุเหตุผลการขอทุน</a></li>
                <li class="nav-item"><a href="../advisors/document.php?student_id=<?php echo $student_id; ?>" class="nav-link-custom inactive">หนังสือรับรองและเอกสารแนบ</a></li>
            </ul>

            <div class="px-2">
                <div class="section-header-app">ข้อมูลบิดา</div>
                <div class="indent-app">
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">ชื่อ-สกุล บิดา:</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['father_name']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">อายุ:</label>
                        <div class="col-sm-9 d-flex align-items-center gap-2">
                            <div class="form-control-static text-center" style="width: 80px;"><?php echo htmlspecialchars($family['father_age']) ?: '&nbsp;'; ?></div>
                            <span>ปี</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" <?php echo ($family['father_status'] == 'alive') ? 'checked' : 'disabled'; ?>> มีชีวิตอยู่</div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" <?php echo ($family['father_status'] == 'deceased') ? 'checked' : 'disabled'; ?>> ถึงแก่กรรม</div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">อาชีพ:</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['father_job']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">รายได้ต่อเดือน:</label>
                        <div class="col-sm-9 d-flex align-items-center gap-2">
                            <div class="form-control-static text-end" style="max-width: 250px;"><?php echo htmlspecialchars($family['father_income']) ?: '&nbsp;'; ?></div>
                            <span>บาท</span>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">สถานที่ทำงาน :</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['father_workplace']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">หมายเลขโทรศัพท์:</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['father_phone']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                </div>

                <div class="section-header-app">ข้อมูลมารดา</div>
                <div class="indent-app">
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">ชื่อ-สกุล มารดา:</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['mother_name']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">อายุ:</label>
                        <div class="col-sm-9 d-flex align-items-center gap-2">
                            <div class="form-control-static text-center" style="width: 80px;"><?php echo htmlspecialchars($family['mother_age']) ?: '&nbsp;'; ?></div>
                            <span>ปี</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" <?php echo ($family['mother_status'] == 'alive') ? 'checked' : 'disabled'; ?>> มีชีวิตอยู่</div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" <?php echo ($family['mother_status'] == 'deceased') ? 'checked' : 'disabled'; ?>> ถึงแก่กรรม</div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">อาชีพ:</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['mother_job']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">รายได้ต่อเดือน:</label>
                        <div class="col-sm-9 d-flex align-items-center gap-2">
                            <div class="form-control-static text-end" style="max-width: 250px;"><?php echo htmlspecialchars($family['mother_income']) ?: '&nbsp;'; ?></div>
                            <span>บาท</span>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">สถานที่ทำงาน :</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['mother_workplace']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">หมายเลขโทรศัพท์:</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['mother_phone']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                </div>

                <div class="section-header-app">ข้อมูลผู้ปกครอง</div>
                <div class="indent-app">
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">ชื่อ-สกุล ผู้ปกครอง:</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['guardian_name']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">อายุ:</label>
                        <div class="col-sm-9 d-flex align-items-center gap-2">
                            <div class="form-control-static text-center" style="width: 80px;"><?php echo htmlspecialchars($family['guardian_age']) ?: '&nbsp;'; ?></div>
                            <span>ปี</span>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">อาชีพ:</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['guardian_job']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">รายได้ต่อเดือน:</label>
                        <div class="col-sm-9 d-flex align-items-center gap-2">
                            <div class="form-control-static text-end" style="max-width: 250px;"><?php echo htmlspecialchars($family['guardian_income']) ?: '&nbsp;'; ?></div>
                            <span>บาท</span>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">สถานที่ทำงาน :</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['guardian_workplace']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-3 fw-medium">หมายเลขโทรศัพท์:</label>
                        <div class="col-sm-9">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['guardian_phone']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                </div>

                <div class="section-header-app">สถานภาพของบิดามารดา</div>
                <div class="indent-app mb-4">
                    <div class="form-check form-check-app"><input class="form-check-input" type="checkbox" <?php echo in_array('together', $family['parents_status']) ? 'checked' : 'disabled'; ?>> อยู่ด้วยกัน</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="checkbox" <?php echo in_array('divorced', $family['parents_status']) ? 'checked' : 'disabled'; ?>> หย่าร้าง</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="checkbox" <?php echo in_array('separated', $family['parents_status']) ? 'checked' : 'disabled'; ?>> แยกกันอยู่</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="checkbox" <?php echo in_array('father_died', $family['parents_status']) ? 'checked' : 'disabled'; ?>> บิดาเสียชีวิต</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="checkbox" <?php echo in_array('mother_died', $family['parents_status']) ? 'checked' : 'disabled'; ?>> มารดาเสียชีวิต</div>
                </div>

                <div class="section-header-app">จำนวนพี่น้องร่วมบิดามารดา</div>
                <div class="indent-app">
                    <div class="table-responsive">
                        <table class="table-app">
                            <thead>
                                <tr>
                                    <th>ชื่อ-สกุล</th>
                                    <th>สถานศึกษา</th>
                                    <th>สถานที่ทำงาน</th>
                                    <th style="width: 140px;">รายได้/บาท</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($family['siblings']) && is_array($family['siblings'])): ?>
                                    <?php foreach ($family['siblings'] as $sibling): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($sibling['name'] ?? '-') ?: '&nbsp;'; ?></td>
                                            <td><?php echo htmlspecialchars($sibling['edu'] ?? '-') ?: '&nbsp;'; ?></td>
                                            <td><?php echo htmlspecialchars($sibling['work'] ?? '-') ?: '&nbsp;'; ?></td>
                                            <td style="text-align: right; font-family: sans-serif;">
                                                <?php echo number_format((float)($sibling['income'] ?? 0), 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #888; padding: 20px; height: 62px; vertical-align: middle;">- ไม่มีข้อมูล -</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="section-header-app">นักศึกษากู้ยืมกองทุน กยศ. หรือ กรอ. หรือไม่</div>
                <div class="indent-app">
                    <div class="row mb-3 align-items-center">
                        <div class="col-auto">
                            <div class="form-check"><input class="form-check-input" type="radio" <?php echo ($family['loan_status'] == 'yes') ? 'checked' : 'disabled'; ?>> กู้</div>
                        </div>
                        <div class="col-auto">จำนวน</div>
                        <div class="col-sm-3">
                            <div class="form-control-static text-end"><?php echo htmlspecialchars($family['loan_amount']) ?: '&nbsp;'; ?></div>
                        </div>
                        <div class="col-auto">บาท/ปี</div>
                    </div>
                    <div class="row align-items-start">
                        <div class="col-auto">
                            <div class="form-check"><input class="form-check-input" type="radio" <?php echo ($family['loan_status'] == 'no') ? 'checked' : 'disabled'; ?>> ไม่ได้กู้</div>
                        </div>
                        <div class="col-auto">สาเหตุ</div>
                        <div class="col-sm-9">
                            <div class="form-control-static bg-light"><?php echo htmlspecialchars($family['loan_no_reason']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                </div>

                <div class="section-header-app">นักศึกษาได้รับค่าครองชีพจาก</div>
                <div class="indent-app mb-4">
                    <div class="form-check form-check-app"><input class="form-check-input" type="checkbox" <?php echo in_array('father', $family['expense_source']) ? 'checked' : 'disabled'; ?>> บิดา</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="checkbox" <?php echo in_array('mother', $family['expense_source']) ? 'checked' : 'disabled'; ?>> มารดา</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="checkbox" <?php echo in_array('guardian', $family['expense_source']) ? 'checked' : 'disabled'; ?>> ผู้ปกครอง</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="checkbox" <?php echo in_array('loan', $family['expense_source']) ? 'checked' : 'disabled'; ?>> กองทุนกู้ยืมเพื่อการศึกษา</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="checkbox" <?php echo in_array('other', $family['expense_source']) ? 'checked' : 'disabled'; ?>> อื่นๆ</div>
                    <div class="row align-items-center mt-2">
                        <div class="col-auto">สามารถเลือกได้มากกว่า 1 ตัวเลือก รวมเดือนละ</div>
                        <div class="col-sm-3">
                            <div class="form-control-static text-end"><?php echo htmlspecialchars($family['expense_amount']) ?: '&nbsp;'; ?></div>
                        </div>
                        <div class="col-auto">บาท</div>
                    </div>
                </div>

                <div class="section-header-app">นักศึกษาเคยทำงานพิเศษระหว่างที่ศึกษาอยู่หรือไม่</div>
                <div class="indent-app mb-4">
                    <div class="row mb-3 align-items-center">
                        <div class="col-auto">
                            <div class="form-check"><input class="form-check-input" type="radio" <?php echo ($family['work_history'] == 'yes') ? 'checked' : 'disabled'; ?>> เคย</div>
                        </div>
                        <div class="col-auto">ระบุประเภท</div>
                        <div class="col-sm-3">
                            <div class="form-control-static"><?php echo htmlspecialchars($family['work_history_type']) ?: '&nbsp;'; ?></div>
                        </div>
                        <div class="col-auto">รายได้ต่อเดือน:</div>
                        <div class="col-sm-2">
                            <div class="form-control-static text-end"><?php echo htmlspecialchars($family['work_history_income']) ?: '&nbsp;'; ?></div>
                        </div>
                        <div class="col-auto">บาท</div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" <?php echo ($family['work_history'] == 'no') ? 'checked' : 'disabled'; ?>> ไม่เคย (ข้ามไปทำข้อ 10.)
                    </div>
                </div>

                <div class="section-header-app">ถ้านักศึกษาเคยทำงานพิเศษ ปัจจุบันยังทำอยู่หรือไม่</div>
                <div class="indent-app mb-4">
                    <div class="row mb-3 align-items-center">
                        <div class="col-auto">
                            <div class="form-check"><input class="form-check-input" type="radio" <?php echo ($family['current_work'] == 'yes') ? 'checked' : 'disabled'; ?>> ทำ</div>
                        </div>
                        <div class="col-auto">ระบุประเภท</div>
                        <div class="col-sm-8">
                            <div class="form-control-static"><?php echo ($family['current_work'] == 'yes') ? htmlspecialchars($family['work_history_type']) : '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="row align-items-start">
                        <div class="col-auto">
                            <div class="form-check"><input class="form-check-input" type="radio" <?php echo ($family['current_work'] == 'no') ? 'checked' : 'disabled'; ?>> ไม่ทำ</div>
                        </div>
                        <div class="col-auto">เนื่องด้วย</div>
                        <div class="col-sm-8">
                            <div class="form-control-static bg-light"><?php echo ($family['current_work'] == 'no') ? htmlspecialchars($family['current_work_reason']) : '&nbsp;'; ?></div>
                        </div>
                    </div>
                </div>

                <div class="section-header-app">ครอบครัวของนักศึกษาประสบปัญหาการเงินบ่อยเพียงใด</div>
                <div class="indent-app mb-4">
                    <div class="row mb-3 align-items-center">
                        <div class="col-auto">
                            <div class="form-check"><input class="form-check-input" type="radio" <?php echo ($family['financial_prob'] == 'often') ? 'checked' : 'disabled'; ?>> บ่อย</div>
                        </div>
                        <div class="col-auto">เนื่องจาก</div>
                        <div class="col-sm-8">
                            <div class="form-control-static bg-light"><?php echo htmlspecialchars($family['financial_prob_reason']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" <?php echo ($family['financial_prob'] == 'not_often') ? 'checked' : 'disabled'; ?>> ไม่บ่อย
                    </div>
                </div>

                <div class="section-header-app">เมื่อครอบครัวมีปัญหาการเงิน ผู้ปกครองหรือนักศึกษา จะแก้ไขด้วยวิธีใด</div>
                <div class="indent-app mb-4">
                    <div class="form-check form-check-app"><input class="form-check-input" type="radio" <?php echo ($family['solve_prob'] == 'loan_out') ? 'checked' : 'disabled'; ?>> กู้ยืมนอกระบบ</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="radio" <?php echo ($family['solve_prob'] == 'loan_in') ? 'checked' : 'disabled'; ?>> กู้ยืมในระบบ</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="radio" <?php echo ($family['solve_prob'] == 'relative') ? 'checked' : 'disabled'; ?>> ญาติ/เพื่อน</div>
                    <div class="form-check form-check-app"><input class="form-check-input" type="radio" <?php echo ($family['solve_prob'] == 'parttime') ? 'checked' : 'disabled'; ?>> ทำงานพิเศษ</div>
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="form-check"><input class="form-check-input" type="radio" <?php echo ($family['solve_prob'] == 'other') ? 'checked' : 'disabled'; ?>> อื่นๆ</div>
                        </div>
                        <div class="col-auto">ระบุ</div>
                        <div class="col-sm-9">
                            <div class="form-control-static bg-light"><?php echo htmlspecialchars($family['solve_prob_other']) ?: '&nbsp;'; ?></div>
                        </div>
                    </div>
                </div>

                <div class="section-header-app">นักศึกษาเคยได้รับทุนการศึกษาจากคณะฯ หรือหน่วยงานอื่นๆ หรือไม่</div>
                <div class="indent-app mb-5">
                    <div class="row mb-3">
                        <div class="col-auto">
                            <div class="form-check"><input class="form-check-input" type="radio" <?php echo ($family['history_scholarship'] == 'yes') ? 'checked' : 'disabled'; ?>> เคย (ระบุ 3 ปีย้อนหลัง)</div>
                        </div>
                        <div class="col-auto">
                            <div class="form-check"><input class="form-check-input" type="radio" <?php echo ($family['history_scholarship'] == 'no') ? 'checked' : 'disabled'; ?>> ไม่เคย</div>
                        </div>
                    </div>

                    <?php if ($family['history_scholarship'] == 'yes'): ?>
                        <div class="table-responsive">
                            <table class="table-app">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">ลำดับ</th>
                                        <th style="width: 150px;">เมื่อปี</th>
                                        <th>ชื่อทุน</th>
                                        <th style="width: 200px;">จำนวน (บาท)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($family['history_list'])): ?>
                                        <?php foreach ($family['history_list'] as $idx => $hist): ?>
                                            <tr>
                                                <td class="text-center"><?php echo $idx + 1; ?>.</td>
                                                <td class="text-center"><?php echo htmlspecialchars($hist['year']) ?: '&nbsp;'; ?></td>
                                                <td><?php echo htmlspecialchars($hist['name']) ?: '&nbsp;'; ?></td>
                                                <td class="text-end"><?php echo htmlspecialchars($hist['amount']) ?: '&nbsp;'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center; color: #888; height: 62px; vertical-align: middle;">- ไม่มีข้อมูลประวัติทุน -</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <form action="../scores/save_score.php" method="POST" class="scoring-panel mt-5" id="scoringForm">
                    <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <textarea name="comment" id="scoreComment" class="form-control w-100 h-100" rows="4" placeholder="เสนอแนะเพิ่มเติม" <?php echo ($has_scored) ? 'readonly' : ''; ?>><?php echo htmlspecialchars($existing_comment); ?></textarea>
                        </div>
                        <div class="col-md-4 d-flex flex-column justify-content-between align-items-end">
                            <div class="w-100">
                                <select name="score" id="scoreSelect" class="form-select scoring-select-custom mb-3" required <?php echo ($has_scored) ? 'disabled' : ''; ?>>
                                    <option value="" disabled <?php echo (!$has_scored) ? 'selected' : ''; ?>>ให้คะแนน</option>
                                    <option value="4" <?php echo ($existing_score == "4") ? 'selected' : ''; ?>>4 [สมควรได้รับทุนอย่างยิ่ง]</option>
                                    <option value="3.5" <?php echo ($existing_score == "3.5") ? 'selected' : ''; ?>>3.5 [สมควรได้รับทุน]</option>
                                    <option value="3" <?php echo ($existing_score == "3") ? 'selected' : ''; ?>>3 [สมควรได้รับทุน]</option>
                                    <option value="2.5" <?php echo ($existing_score == "2.5") ? 'selected' : ''; ?>>2.5 [สมควรรับไว้พิจารณา]</option>
                                    <option value="2" <?php echo ($existing_score == "2") ? 'selected' : ''; ?>>2 [สมควรรับไว้พิจารณา]</option>
                                    <option value="1.5" <?php echo ($existing_score == "1.5") ? 'selected' : ''; ?>>1.5 [ยังไม่สมควรได้รับทุน]</option>
                                    <option value="1" <?php echo ($existing_score == "1") ? 'selected' : ''; ?>>1 [ยังไม่สมควรได้รับทุน]</option>
                                </select>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-2">
                                <?php if ($has_scored): ?>
                                    <div class="text-success small fw-bold mb-1">
                                        <i class="fa-solid fa-circle-check"></i> บันทึกข้อมูลแล้ว (ไม่สามารถแก้ไขได้)
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-warning rounded-pill px-4 text-white" onclick="clearDraft()">รีเซ็ต</button>
                                        <button type="submit" class="btn btn-success rounded-pill px-4">ยืนยัน</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../include/footer.php'; ?>

    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        window.onscroll = function() {
            let btn = document.getElementById("scrollTopBtn");
            if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) btn.style.display = "block";
            else btn.style.display = "none";
        };

        document.addEventListener('DOMContentLoaded', function() {
            const committeeId = <?php echo json_encode($committee_id); ?>;
            const studentId = <?php echo json_encode($student_id); ?>;
            const hasScored = <?php echo json_encode($has_scored); ?>;
            const storageKey = `score_draft_${committeeId}_${studentId}`;

            const commentInput = document.getElementById('scoreComment');
            const scoreSelect = document.getElementById('scoreSelect');
            const scoringForm = document.getElementById('scoringForm');

            if (!hasScored && commentInput && scoreSelect) {
                const savedDraft = JSON.parse(localStorage.getItem(storageKey));
                if (savedDraft) {
                    if (savedDraft.comment) commentInput.value = savedDraft.comment;
                    if (savedDraft.score) scoreSelect.value = savedDraft.score;
                }

                const saveToDraft = () => {
                    const draftData = {
                        comment: commentInput.value,
                        score: scoreSelect.value
                    };
                    localStorage.setItem(storageKey, JSON.stringify(draftData));
                };

                commentInput.addEventListener('input', saveToDraft);
                scoreSelect.addEventListener('change', saveToDraft);

                scoringForm.addEventListener('submit', function() {
                    localStorage.removeItem(storageKey);
                });
            }

            window.clearDraft = function() {
                if (commentInput) commentInput.value = '';
                if (scoreSelect) scoreSelect.value = '';
                localStorage.removeItem(storageKey);
            };
        });
    </script>



</body>

</html>