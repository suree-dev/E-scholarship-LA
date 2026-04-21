<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../../include/config.php';

$st_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'personal';

if ($st_id <= 0) die("ไม่พบข้อมูลนักศึกษา");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_save_edit'])) {
    $st_firstname = mysqli_real_escape_string($connect1, $_POST['st_firstname']);
    $st_lastname = mysqli_real_escape_string($connect1, $_POST['st_lastname']);
    $st_score = mysqli_real_escape_string($connect1, $_POST['st_score']);
    $st_age = mysqli_real_escape_string($connect1, $_POST['st_age']);
    $st_address1 = mysqli_real_escape_string($connect1, $_POST['st_address1']);
    $st_address2 = mysqli_real_escape_string($connect1, $_POST['st_address2']);
    $st_tel1 = mysqli_real_escape_string($connect1, $_POST['st_tel1']);
    $st_email = mysqli_real_escape_string($connect1, $_POST['st_email']);
    $st_note = mysqli_real_escape_string($connect1, $_POST['st_note']);

    $st_father = implode('|-o-|', [$_POST['f_name'], $_POST['f_age'], $_POST['f_status'], $_POST['f_job'], $_POST['f_income'], $_POST['f_work'], $_POST['f_tel']]);
    $st_mother = implode('|-o-|', [$_POST['m_name'], $_POST['m_age'], $_POST['m_status'], $_POST['m_job'], $_POST['m_income'], $_POST['m_work'], $_POST['m_tel']]);
    $st_guardian = implode('|-o-|', [$_POST['g_name'], $_POST['g_age'], $_POST['g_status'] ?? '1', $_POST['g_job'], $_POST['g_income'], $_POST['g_work'], $_POST['g_tel']]);

    $fam_status = [isset($_POST['parents_status_1']) ? '1' : '', isset($_POST['parents_status_2']) ? '2' : '', isset($_POST['parents_status_3']) ? '3' : '', isset($_POST['parents_status_4']) ? '4' : '', isset($_POST['parents_status_5']) ? '5' : ''];
    $st_family_status = implode('|-o-|', $fam_status);

    $sib_rows = [];
    if (isset($_POST['sib_name'])) {
        for ($i = 0; $i < count($_POST['sib_name']); $i++) {
            if (!empty($_POST['sib_name'][$i])) {
                $sib_rows[] = $_POST['sib_name'][$i] . ":-:" . $_POST['sib_work'][$i] . ":" . $_POST['sib_income'][$i];
            }
        }
    }
    $st_siblings = implode('|-o-|', $sib_rows);

    $loan_status = (isset($_POST['loan_status']) && $_POST['loan_status'] == 'yes') ? '1' : '';
    $loan_no_status = (isset($_POST['loan_status']) && $_POST['loan_status'] == 'no') ? '2' : '';
    $st_borrow_money = $loan_status . '|-o-|' . $loan_no_status . '|-o-|' . ($_POST['loan_val'] ?? '');

    $recv_slots = [isset($_POST['recv_father']) ? '1' : '', isset($_POST['recv_mother']) ? '2' : '', isset($_POST['recv_guardian']) ? '3' : '', isset($_POST['recv_loan']) ? '4' : '', isset($_POST['recv_other']) ? '5' : ''];
    $st_received = implode('|-o-|', $recv_slots) . '|-o-|' . ($_POST['expense_amount'] ?? '');

    $job_h = (($_POST['work_history'] ?? '') == 'yes' ? '1' : '') . '|-o-|' . (($_POST['work_history'] ?? '') == 'no' ? '2' : '') . '|-o-|' . ($_POST['work_history_type'] ?? '') . '|-o-|' . ($_POST['work_history_income'] ?? '');
    $curr_j = (($_POST['current_work'] ?? '') == 'yes' ? '1' : '') . '|-o-|' . (($_POST['current_work'] ?? '') == 'no' ? '2' : '') . '|-o-|' . ($_POST['current_work_reason'] ?? '');
    $peri = (($_POST['financial_prob'] ?? '') == 'often' ? '1' : '') . '|-o-|' . (($_POST['financial_prob'] ?? '') == 'not_often' ? '2' : '') . '|-o-|' . ($_POST['financial_prob_reason'] ?? '');

    $sol_val = $_POST['solve_prob'] ?? '';
    $sol_slots = ['', '', '', '', ''];
    if ($sol_val == 'loan_out') $sol_slots[0] = '1';
    elseif ($sol_val == 'loan_in') $sol_slots[1] = '2';
    elseif ($sol_val == 'relative') $sol_slots[2] = '3';
    elseif ($sol_val == 'parttime') $sol_slots[3] = '4';
    elseif ($sol_val == 'other') $sol_slots[4] = '5';
    $st_solutions = implode('|-o-|', $sol_slots) . '|-o-|' . ($_POST['solve_prob_other'] ?? '');

    $hist_rows = [];
    if (isset($_POST['h_year'])) {
        for ($i = 0; $i < count($_POST['h_year']); $i++) {
            if (!empty($_POST['h_year'][$i])) $hist_rows[] = $_POST['h_year'][$i] . ":" . $_POST['h_name'][$i] . ":" . $_POST['h_amount'][$i];
        }
    }
    $st_history_detail = implode('|-o-|', $hist_rows);
    $st_history_bursary = $_POST['hist_bur'] ?? '2';

    $sql_update = "UPDATE tb_student SET 
        st_firstname='$st_firstname', st_lastname='$st_lastname', st_score='$st_score', st_age='$st_age',
        st_address1='$st_address1', st_address2='$st_address2', st_tel1='$st_tel1', st_email='$st_email',
        st_father='$st_father', st_mother='$st_mother', st_guardian='$st_guardian', st_family_status='$st_family_status',
        st_siblings='$st_siblings', st_borrow_money='$st_borrow_money', st_received='$st_received',
        st_job='$job_h', st_current_job='$curr_j', st_peripeteia='$peri', st_solutions='$st_solutions',
        st_note='$st_note', st_history_detail='$st_history_detail', st_history_bursary='$st_history_bursary'
        WHERE st_id='$st_id'";

    if (mysqli_query($connect1, $sql_update)) {
        echo "<script>alert('บันทึกการแก้ไขเรียบร้อยแล้ว'); window.location.href='edit_student.php?id=$st_id&tab=$current_tab';</script>";
    }
}

$sql = "SELECT s.*, p.g_program, t.tc_name 
        FROM tb_student s 
        LEFT JOIN tb_program p ON s.st_program = p.g_id 
        LEFT JOIN tb_teacher t ON s.id_teacher = t.tc_id 
        WHERE s.st_id = '$st_id'";
$result = mysqli_query($connect1, $sql);
$student = mysqli_fetch_assoc($result);

if (!$student) die("ไม่พบข้อมูลนักศึกษาในระบบ");

function splitData($data)
{
    return explode('|-o-|', $data ?? '');
}
function parseParent($data)
{
    $p = explode('|-o-|', $data ?? '');
    return [
        'name' => $p[0] ?? '',
        'age' => $p[1] ?? '',
        'status' => $p[2] ?? '1',
        'job' => $p[3] ?? '',
        'income' => $p[4] ?? '',
        'work' => $p[5] ?? '',
        'tel' => $p[6] ?? ''
    ];
}

$father = parseParent($student['st_father']);
$mother = parseParent($student['st_mother']);
$guardian = parseParent($student['st_guardian']);
$parents_status_raw = splitData($student['st_family_status']);

$siblings = [];
if (!empty($student['st_siblings'])) {
    foreach (explode('|-o-|', $student['st_siblings']) as $row) {
        $parts = explode(':-:', $row);
        if (!empty($parts[0])) {
            $extra = explode(':', $parts[1] ?? '');
            $siblings[] = ['name' => $parts[0], 'work' => $extra[0] ?? '', 'income' => $extra[1] ?? '0'];
        }
    }
}

$loan = splitData($student['st_borrow_money']);
$expense_source = splitData($student['st_received']);
$expense_total = end($expense_source);
$work_past = splitData($student['st_job']);
$work_now = splitData($student['st_current_job']);
$finance_prob = splitData($student['st_peripeteia']);
$finance_solu = splitData($student['st_solutions']);
$hist_bur = $student['st_history_bursary'];
$hist_list = [];
if (!empty($student['st_history_detail'])) {
    foreach (explode('|-o-|', $student['st_history_detail']) as $row) {
        $hp = explode(':', $row);
        if (count($hp) >= 3) $hist_list[] = ['year' => $hp[0], 'name' => $hp[1], 'amount' => $hp[2]];
    }
}

$scholarship_options = [];
$res_types = mysqli_query($connect1, "SELECT st_name_1, st_name_2, st_name_3 FROM tb_year WHERE y_id = 1");
$d_types = mysqli_fetch_assoc($res_types);
$scholarship_options[1] = $d_types['st_name_1'] ?? 'ทุนประเภทที่ 1';
$scholarship_options[2] = $d_types['st_name_2'] ?? 'ทุนประเภทที่ 2';
$scholarship_options[3] = $d_types['st_name_3'] ?? 'ทุนประเภทที่ 3';

$thai_months = [1 => "ม.ค.", 2 => "ก.พ.", 3 => "มี.ค.", 4 => "เม.ย.", 5 => "พ.ค.", 6 => "มิ.ย.", 7 => "ก.ค.", 8 => "ส.ค.", 9 => "ก.ย.", 10 => "ต.ค.", 11 => "พ.ย.", 12 => "ธ.ค."];
$current_date_th = date("j") . " " . $thai_months[(int)date("n")] . " " . (date("Y") + 543);

function renderFilePreview($filename)
{
    if (!$filename) return '<div class="no-file-box">ไม่ได้แนบเอกสาร</div>';
    $path = "../images/student/" . $filename;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $html = '<div class="fixed-preview-container shadow-sm border mb-3">';
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        $html .= '<img src="' . $path . '" class="standard-fit-view" style="max-width:100%; height:auto;">';
    } elseif ($ext == 'pdf') {
        $html .= '<embed src="' . $path . '#toolbar=0" type="application/pdf" class="standard-fit-view" width="100%" height="500px">';
    } else {
        $html .= '<div class="p-4 text-center">ไม่รองรับพรีวิว (' . $ext . ') <br><a href="' . $path . '" target="_blank" class="btn btn-sm btn-primary">เปิดไฟล์</a></div>';
    }
    $html .= '</div>';
    return $html;
}
$current_year_th = date("Y") + 543;
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลนักศึกษา - <?php echo $student['st_firstname']; ?></title>
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
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        .tab-section {
            display: none;
        }

        .tab-section.active {
            display: block;
        }

        .photo-box-fixed {
            position: absolute;
            top: 0;
            right: 0;
            width: 110px;
            height: 140px;
            border: 1.5px solid #333;
            padding: 2px;
            background: #fff;
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
        }

        .photo-box-fixed img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @media (max-width: 1024px) {
            .photo-box-fixed {
                position: relative;
                margin: 0 auto 20px;
                top: auto;
                right: auto;
            }
        }
    </style>
</head>

<body>
    <div class="sticky-header-wrapper">
        <?php include('../../include/navbar.php'); ?>
        <?php include('../../include/status_bar.php'); ?>
    </div>

    <div class="container-fluid dashboard-container">
        <div class="row g-4">
            <div class="col-12 col-sidebar-20">
                <?php include '../../include/sidebar.php'; ?>
            </div>

            <div class="col-12 col-main-80">
                <main class="main-content shadow-sm">

                    <div class="mb-4">
                        <a href="../students/student_data.php?type=<?php echo $student['st_type']; ?>" class="btn btn-secondary rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-arrow-left me-2"></i> กลับหน้ารายการ
                        </a>
                    </div>

                    <form action="" method="POST">
                        <div class="header-content position-relative text-center mb-5">
                            <h4 class="fw-bold">แก้ไขข้อมูลใบสมัคร <?php echo htmlspecialchars($scholarship_options[$student['st_type']] ?? ''); ?></h4>
                            <h5 class="fw-bold mb-1">คณะศิลปศาสตร์ มหาวิทยาลัยสงขลานครินทร์</h5>
                            <h5 class="fw-bold mb-1">ประจำปีการศึกษา <?php echo $current_year_th; ?></h5>
                            <p class="text-muted small mt-2">แก้ไขข้อมูลเมื่อ: <?php echo $current_date_th; ?></p>
                            <div class="photo-box-fixed">
                                <img src="../../images/student/<?php echo $student['st_image']; ?>" onerror="this.src='../../assets/images/bg/no-profile.png'">
                            </div>
                        </div>

                        <div class="student-info-highlight shadow-sm">
                            <span><strong>ชื่อ:</strong> <?php echo htmlspecialchars($student['st_firstname'] . " " . $student['st_lastname']); ?></span>
                            <span><strong>รหัสนักศึกษา:</strong> <?php echo htmlspecialchars($student['st_code']); ?></span><br>
                            <span><strong>เกรดเฉลี่ย:</strong> <?php echo htmlspecialchars($student['st_score']); ?></span>
                            <span><strong>สาขาวิชา:</strong> <?php echo htmlspecialchars($student['g_program']); ?></span>
                            <span><strong>อาจารย์ที่ปรึกษา:</strong> <?php echo htmlspecialchars($student['tc_name']); ?></span>
                        </div>

                        <ul class="nav-tabs-app mb-4">
                            <li class="nav-item"><a href="javascript:void(0)" onclick="openTab(event, 'personal')" class="nav-link-custom active">ข้อมูลส่วนตัว</a></li>
                            <li class="nav-item"><a href="javascript:void(0)" onclick="openTab(event, 'family')" class="nav-link-custom inactive">ข้อมูลครอบครัว</a></li>
                            <li class="nav-item"><a href="javascript:void(0)" onclick="openTab(event, 'reason')" class="nav-link-custom inactive">เหตุผลการขอทุน</a></li>
                            <li class="nav-item"><a href="javascript:void(0)" onclick="openTab(event, 'document')" class="nav-link-custom inactive">ดูเอกสารแนบ</a></li>
                        </ul>

                        <div class="tab-content pt-2">
                            <div id="personal" class="tab-section active">
                                <div class="section-header-app">ข้อมูลพื้นฐานนักศึกษา</div>
                                <div class="indent-app">
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">ชื่อ:</label>
                                        <div class="col-md-9"><input type="text" name="st_firstname" class="form-control" value="<?php echo htmlspecialchars($student['st_firstname']); ?>"></div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">นามสกุล:</label>
                                        <div class="col-md-9"><input type="text" name="st_lastname" class="form-control" value="<?php echo htmlspecialchars($student['st_lastname']); ?>"></div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">GPAX:</label>
                                        <div class="col-md-9"><input type="text" name="st_score" class="form-control" value="<?php echo htmlspecialchars($student['st_score']); ?>"></div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">อายุ:</label>
                                        <div class="col-md-2 d-flex align-items-center gap-2"><input type="text" name="st_age" class="form-control text-center" value="<?php echo htmlspecialchars($student['st_age']); ?>" style="text-align: left !important;"><span>ปี</span></div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">ที่อยู่:</label>
                                        <div class="col-md-9"><input type="text" name="st_address1" class="form-control" value="<?php echo htmlspecialchars($student['st_address1']); ?>"></div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">ภูมิลำเนา:</label>
                                        <div class="col-md-9"><input type="text" name="st_address2" class="form-control" value="<?php echo htmlspecialchars($student['st_address2']); ?>"></div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">โทรศัพท์:</label>
                                        <div class="col-md-9"><input type="text" name="st_tel1" class="form-control" value="<?php echo htmlspecialchars($student['st_tel1']); ?>"></div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">อีเมล:</label>
                                        <div class="col-md-9"><input type="text" name="st_email" class="form-control" value="<?php echo htmlspecialchars($student['st_email']); ?>"></div>
                                    </div>
                                </div>
                            </div>

                            <div id="family" class="tab-section">
                                <?php $ps = [['t' => 'บิดา', 'd' => $father, 'p' => 'f'], ['t' => 'มารดา', 'd' => $mother, 'p' => 'm'], ['t' => 'ผู้ปกครอง', 'd' => $guardian, 'p' => 'g']];
                                foreach ($ps as $p): $d = $p['d'];
                                    $pre = $p['p']; ?>
                                    <div class="section-header-app">ข้อมูล<?php echo $p['t']; ?></div>
                                    <div class="indent-app">
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">ชื่อ-สกุล:</label>
                                            <div class="col-md-9"><input type="text" name="<?php echo $pre; ?>_name" class="form-control" value="<?php echo htmlspecialchars($d['name']); ?>"></div>
                                        </div>
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">อายุ / สถานะ:</label>
                                            <div class="col-md-9 d-flex align-items-center gap-4">
                                                <input type="text" name="<?php echo $pre; ?>_age" class="form-control text-center" style="width: 70px;" value="<?php echo htmlspecialchars($d['age']); ?>">ปี
                                                <div class="form-check form-check-inline"><input type="radio" name="<?php echo $pre; ?>_status" value="1" class="form-check-input" <?php echo ($d['status'] == '1') ? 'checked' : ''; ?>> มีชีวิต</div>
                                                <div class="form-check form-check-inline"><input type="radio" name="<?php echo $pre; ?>_status" value="0" class="form-check-input" <?php echo ($d['status'] == '0') ? 'checked' : ''; ?>> ถึงแก่กรรม</div>
                                            </div>
                                        </div>
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">อาชีพ / รายได้:</label>
                                            <div class="col-md-9 d-flex gap-3 align-items-center"><input type="text" name="<?php echo $pre; ?>_job" class="form-control" style="flex: 2;" value="<?php echo htmlspecialchars($d['job']); ?>"><input type="text" name="<?php echo $pre; ?>_income" class="form-control text-end" style="flex: 1;" value="<?php echo htmlspecialchars($d['income']); ?>"><span>บาท</span></div>
                                        </div>
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">สถานที่ทำงาน:</label>
                                            <div class="col-md-9"><input type="text" name="<?php echo $pre; ?>_work" class="form-control" value="<?php echo htmlspecialchars($d['work']); ?>"></div>
                                        </div>
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">โทรศัพท์:</label>
                                            <div class="col-md-9"><input type="text" name="<?php echo $pre; ?>_tel" class="form-control" value="<?php echo htmlspecialchars($d['tel']); ?>"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="section-header-app">4. สถานภาพของบิดามารดา</div>
                                <div class="indent-app checkbox-group" style="display:flex; flex-direction:column; gap:8px;">
                                    <label><input type="checkbox" name="parents_status_1" value="1" <?php echo (isset($parents_status_raw[0]) && $parents_status_raw[0] == '1') ? 'checked' : ''; ?>> อยู่ด้วยกัน</label>
                                    <label><input type="checkbox" name="parents_status_2" value="2" <?php echo (isset($parents_status_raw[1]) && $parents_status_raw[1] == '2') ? 'checked' : ''; ?>> หย่าร้าง</label>
                                    <label><input type="checkbox" name="parents_status_3" value="3" <?php echo (isset($parents_status_raw[2]) && $parents_status_raw[2] == '3') ? 'checked' : ''; ?>> แยกกันอยู่</label>
                                    <label><input type="checkbox" name="parents_status_4" value="4" <?php echo (isset($parents_status_raw[3]) && $parents_status_raw[3] == '4') ? 'checked' : ''; ?>> บิดาเสียชีวิต</label>
                                    <label><input type="checkbox" name="parents_status_5" value="5" <?php echo (isset($parents_status_raw[4]) && $parents_status_raw[4] == '5') ? 'checked' : ''; ?>> มารดาเสียชีวิต</label>
                                </div>

                                <div class="section-header-app">5. จำนวนพี่น้องร่วมบิดามารดา</div>
                                <div class="indent-app">
                                    <table class="table-custom">
                                        <thead>
                                            <tr>
                                                <th>ชื่อ-สกุล</th>
                                                <th>สถานศึกษา / ที่ทำงาน</th>
                                                <th>รายได้/เดือน</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for ($i = 0; $i < 3; $i++):
                                                $sib = $siblings[$i] ?? ['name' => '', 'work' => '', 'income' => ''];
                                            ?>
                                                <tr>
                                                    <td><input type="text" name="sib_name[]" class="form-control" value="<?php echo htmlspecialchars($sib['name']); ?>"></td>
                                                    <td><input type="text" name="sib_work[]" class="form-control" value="<?php echo htmlspecialchars($sib['work']); ?>"></td>
                                                    <td><input type="text" name="sib_income[]" class="form-control" value="<?php echo htmlspecialchars($sib['income']); ?>"></td>
                                                </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="section-header-app">6. นักศึกษากู้ยืมกองทุน กยศ. หรือ กรอ. หรือไม่</div>
                                <div class="indent-app">
                                    <div class="radio-group" style="width: 100%;">
                                        <div style="margin-bottom: 10px; display: flex; align-items: center;">
                                            <label><input type="radio" name="loan_status" value="yes" <?php echo (isset($loan[0]) && $loan[0] == '1') ? 'checked' : ''; ?>> กู้</label>
                                            <span style="margin-left: 10px;">สาเหตุ/จำนวน</span>
                                            <input type="text" name="loan_val" class="form-control" style="flex: 1; margin-left: 10px;" value="<?php echo htmlspecialchars($loan[2] ?? ''); ?>">
                                        </div>
                                        <div>
                                            <label><input type="radio" name="loan_status" value="no" <?php echo (isset($loan[1]) && $loan[1] == '2') ? 'checked' : ''; ?>> ไม่ได้กู้</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-header-app">7. นักศึกษาได้รับค่าครองชีพจาก</div>
                                <div class="indent-app">
                                    <div class="checkbox-group" style="display:flex; flex-direction:column; gap:5px; margin-bottom:10px;">
                                        <label><input type="checkbox" name="recv_father" <?php echo (isset($expense_source[0]) && $expense_source[0] == '1') ? 'checked' : ''; ?>> บิดา</label>
                                        <label><input type="checkbox" name="recv_mother" <?php echo (isset($expense_source[1]) && $expense_source[1] == '2') ? 'checked' : ''; ?>> มารดา</label>
                                        <label><input type="checkbox" name="recv_guardian" <?php echo (isset($expense_source[2]) && $expense_source[2] == '3') ? 'checked' : ''; ?>> ผู้ปกครอง</label>
                                        <label><input type="checkbox" name="recv_loan" <?php echo (isset($expense_source[3]) && $expense_source[3] == '4') ? 'checked' : ''; ?>> กองทุนกู้ยืมเพื่อการศึกษา</label>
                                        <label><input type="checkbox" name="recv_other" <?php echo (isset($expense_source[4]) && $expense_source[4] == '5') ? 'checked' : ''; ?>> อื่นๆ</label>
                                    </div>
                                    <div style="display: flex; align-items: center;">
                                        <span>รวมเดือนละ</span>
                                        <input type="text" name="expense_amount" class="form-control" style="width: 150px; margin: 0 10px;" value="<?php echo htmlspecialchars($expense_total); ?>">
                                        <span>บาท</span>
                                    </div>
                                </div>

                                <div class="section-header-app">8. นักศึกษาเคยทำงานพิเศษระหว่างที่ศึกษาอยู่หรือไม่</div>
                                <div class="indent-app">
                                    <div class="radio-group">
                                        <div style="margin-bottom: 10px; display: flex; align-items: center; flex-wrap: wrap;">
                                            <label><input type="radio" name="work_history" value="yes" <?php echo (isset($work_past[0]) && $work_past[0] == '1') ? 'checked' : ''; ?>> เคย</label>
                                            <span style="margin-left: 10px;">ประเภท</span>
                                            <input type="text" name="work_history_type" class="form-control" style="width: 250px; margin: 0 10px;" value="<?php echo htmlspecialchars($work_past[2] ?? ''); ?>">
                                            <span>รายได้/เดือน</span>
                                            <input type="text" name="work_history_income" class="form-control" style="width: 120px; margin: 0 10px;" value="<?php echo htmlspecialchars($work_past[3] ?? ''); ?>">
                                        </div>
                                        <div>
                                            <label><input type="radio" name="work_history" value="no" <?php echo (isset($work_past[1]) && $work_past[1] == '2') ? 'checked' : ''; ?>> ไม่เคย</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-header-app">9. ถ้านักศึกษาเคยทำงานพิเศษ ปัจจุบันยังทำอยู่หรือไม่</div>
                                <div class="indent-app">
                                    <div class="radio-group" style="width: 100%;">
                                        <div style="margin-bottom: 10px; display: flex; align-items: center;">
                                            <label><input type="radio" name="current_work" value="yes" <?php echo (isset($work_now[0]) && $work_now[0] == '1') ? 'checked' : ''; ?>> ทำ</label>
                                        </div>
                                        <div style="display: flex; align-items: center;">
                                            <label><input type="radio" name="current_work" value="no" <?php echo (isset($work_now[1]) && $work_now[1] == '2') ? 'checked' : ''; ?>> ไม่ทำ</label>
                                            <span style="margin-left: 10px;">เนื่องจาก</span>
                                            <input type="text" name="current_work_reason" class="form-control" style="flex: 1; margin-left:10px;" value="<?php echo htmlspecialchars($work_now[2] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="section-header-app">10. ครอบครัวของนักศึกษาประสบปัญหาการเงินบ่อยเพียงใด</div>
                                <div class="indent-app">
                                    <div class="radio-group" style="width: 100%;">
                                        <div style="margin-bottom: 10px; display: flex; align-items: center;">
                                            <label><input type="radio" name="financial_prob" value="often" <?php echo (isset($finance_prob[0]) && $finance_prob[0] == '1') ? 'checked' : ''; ?>> บ่อย</label>
                                            <span style="margin-left: 10px;">เนื่องจาก</span>
                                            <input type="text" name="financial_prob_reason" class="form-control" style="flex: 1; margin-left:10px;" value="<?php echo htmlspecialchars($finance_prob[2] ?? ''); ?>">
                                        </div>
                                        <div>
                                            <label><input type="radio" name="financial_prob" value="not_often" <?php echo (isset($finance_prob[1]) && $finance_prob[1] == '2') ? 'checked' : ''; ?>> ไม่บ่อย</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-header-app">11. วิธีแก้ไขเมื่อครอบครัวมีปัญหาการเงิน</div>
                                <div class="indent-app radio-group" style="display: flex; flex-direction: column; gap: 8px;">
                                    <label><input type="radio" name="solve_prob" value="loan_out" <?php echo (isset($finance_solu[0]) && $finance_solu[0] == '1') ? 'checked' : ''; ?>> กู้ยืมนอกระบบ</label>
                                    <label><input type="radio" name="solve_prob" value="loan_in" <?php echo (isset($finance_solu[1]) && $finance_solu[1] == '2') ? 'checked' : ''; ?>> กู้ยืมในระบบ</label>
                                    <label><input type="radio" name="solve_prob" value="relative" <?php echo (isset($finance_solu[2]) && $finance_solu[2] == '3') ? 'checked' : ''; ?>> ญาติ/เพื่อน</label>
                                    <label><input type="radio" name="solve_prob" value="parttime" <?php echo (isset($finance_solu[3]) && $finance_solu[3] == '4') ? 'checked' : ''; ?>> ทำงานพิเศษ</label>
                                    <div style="display: flex; align-items: center;">
                                        <label><input type="radio" name="solve_prob" value="other" <?php echo (isset($finance_solu[4]) && $finance_solu[4] == '5') ? 'checked' : ''; ?>> อื่นๆ</label>
                                        <span style="margin-left: 10px;">ระบุ</span>
                                        <input type="text" name="solve_prob_other" class="form-control" style="flex: 1; margin-left:10px;" value="<?php echo htmlspecialchars($finance_solu[5] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="section-header-app">12. ประวัติทุนการศึกษา</div>
                                <div class="indent-app">
                                    <div class="radio-group" style="margin-bottom: 15px;">
                                        <label><input type="radio" name="hist_bur" value="1" <?php echo ($hist_bur == '1') ? 'checked' : ''; ?>> เคย (ระบุ 3 ปีย้อนหลัง)</label>
                                        <label><input type="radio" name="hist_bur" value="2" <?php echo ($hist_bur == '2') ? 'checked' : ''; ?>> ไม่เคย</label>
                                    </div>
                                    <table style="width: 100%;">
                                        <?php for ($i = 0; $i < 3; $i++):
                                            $h = $hist_list[$i] ?? ['year' => '', 'name' => '', 'amount' => ''];
                                        ?>
                                            <tr>
                                                <td>ปี <input type="text" name="h_year[]" class="form-control" style="width:80px; display:inline-block;" value="<?php echo htmlspecialchars($h['year']); ?>"></td>
                                                <td>ชื่อทุน <input type="text" name="h_name[]" class="form-control" style="width:250px; display:inline-block; margin: 0 5px;" value="<?php echo htmlspecialchars($h['name']); ?>"></td>
                                                <td>จำนวน <input type="text" name="h_amount[]" class="form-control" style="width:120px; display:inline-block;" value="<?php echo htmlspecialchars($h['amount']); ?>"> บาท</td>
                                            </tr>
                                        <?php endfor; ?>
                                    </table>
                                </div>
                            </div>

                            <div id="reason" class="tab-section">
                                <div class="section-header-app">ระบุเหตุผลความจำเป็น</div>
                                <textarea name="st_note" class="form-control" style="height: 250px;"><?php echo htmlspecialchars($student['st_note']); ?></textarea>
                            </div>

                            <div id="document" class="tab-section">
                                <div class="section-header-app">เอกสารแนบ (ดูได้อย่างเดียว)</div>
                                <?php echo renderFilePreview($student['st_doc']); ?>
                                <div class="mt-4"><?php echo renderFilePreview($student['st_doc1']); ?></div>
                                <div class="mt-4"><?php echo renderFilePreview($student['st_doc2']); ?></div>
                            </div>
                        </div>

                        <div class="text-center mt-5 mb-3 border-top pt-4">
                            <button type="submit" name="btn_save_edit" class="btn btn-success rounded-pill px-5 py-3 shadow border-0">
                                <i class="fa-solid fa-save me-2"></i> บันทึกการแก้ไขข้อมูลทั้งหมด
                            </button>
                        </div>
                    </form>
                </main>
            </div>
        </div>
    </div>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openTab(evt, tabName) {
            const sects = document.querySelectorAll(".tab-section");
            const links = document.querySelectorAll(".nav-tabs-app a");
            sects.forEach(s => {
                s.classList.remove("active");
                s.style.display = "none";
            });
            links.forEach(l => {
                l.classList.replace("active", "inactive");
            });
            document.getElementById(tabName).style.display = "block";
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.replace("inactive", "active");
        }
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const menuHeader = document.querySelector('.sidebar .menu-header');
            if (menuHeader) {
                menuHeader.addEventListener('click', () => {
                    if (window.innerWidth <= 1024) sidebar.classList.toggle('is-open');
                });
            }
        });
    </script>
</body>

</html>