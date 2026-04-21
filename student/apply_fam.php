<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../include/config.php';

if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('กรุณาเข้าสู่ระบบก่อนดำเนินการกรอกข้อมูล'); window.location.href='index.php';</script>";
    exit();
}

$session_st_code = $_SESSION['student_id'];

function clearInit($val)
{
    $val = trim($val);
    return ($val === '0' || $val === '0.00' || empty($val)) ? '' : $val;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $q_get_id = mysqli_query($connect1, "SELECT st_id FROM tb_student WHERE st_code = '$session_st_code'");
    $r_get_id = mysqli_fetch_assoc($q_get_id);
    $current_st_id = $r_get_id['st_id'];

    if ($current_st_id) {
        mysqli_query($connect1, "DELETE FROM tb_parent WHERE id_student = '$current_st_id'");
        $parent_data_strings = [];
        $parent_keys = ['father', 'mother', 'guardian'];

        foreach ($parent_keys as $p_key) {
            $p_name   = mysqli_real_escape_string($connect1, $_POST[$p_key . '_name'] ?? '-');
            $p_age    = intval($_POST[$p_key . '_age'] ?? 0);
            $p_status = intval($_POST[$p_key . '_status'] ?? 1);
            $p_job    = mysqli_real_escape_string($connect1, $_POST[$p_key . '_job'] ?? '-');
            $p_income = mysqli_real_escape_string($connect1, $_POST[$p_key . '_income'] ?? '0');
            $p_work   = mysqli_real_escape_string($connect1, $_POST[$p_key . '_work'] ?? '-');
            $p_tel    = mysqli_real_escape_string($connect1, $_POST[$p_key . '_tel'] ?? '-');

            $sql_insert_parent = "INSERT INTO tb_parent (parent_name, parent_age, parent_status, parent_career, parent_revenue, parent_workplace, parent_tel, id_student) 
                                  VALUES ('$p_name', '$p_age', '$p_status', '$p_job', '$p_income', '$p_work', '$p_tel', '$current_st_id')";
            mysqli_query($connect1, $sql_insert_parent);
            $parent_data_strings[$p_key] = implode('|-o-|', [$p_name, $p_age, $p_status, $p_job, $p_income, $p_work, $p_tel]);
        }

        $p_status_map = ['together', 'divorced', 'separated', 'father_died', 'mother_died'];
        $status_slots = ["", "", "", "", ""];
        foreach (($_POST['p_status'] ?? []) as $val) {
            $found_idx = array_search($val, $p_status_map);
            if ($found_idx !== false) $status_slots[$found_idx] = ($found_idx + 1);
        }
        $st_family_status = implode('|-o-|', $status_slots);

        mysqli_query($connect1, "DELETE FROM tb_relatives WHERE id_student = '$current_st_id'");
        $sib_entries = [];
        if (isset($_POST['sib_name'])) {
            for ($k = 0; $k < count($_POST['sib_name']); $k++) {
                if (!empty($_POST['sib_name'][$k])) {
                    $r_name   = mysqli_real_escape_string($connect1, $_POST['sib_name'][$k]);
                    $r_edu    = mysqli_real_escape_string($connect1, $_POST['sib_edu'][$k] ?? '-');
                    $r_work   = mysqli_real_escape_string($connect1, $_POST['sib_work'][$k] ?? '-');
                    $r_income = mysqli_real_escape_string($connect1, $_POST['sib_income'][$k] ?? '0');

                    $sql_insert_sib = "INSERT INTO tb_relatives (re_name, ra_edu, ra_workplace, ra_revenue, id_student) 
                                      VALUES ('$r_name', '$r_edu', '$r_work', '$r_income', '$current_st_id')";
                    mysqli_query($connect1, $sql_insert_sib);
                    $sib_entries[] = "$r_name:$r_edu:$r_work:$r_income";
                }
            }
        }
        $st_siblings = implode('|-o-|', $sib_entries);

        $loan_val = $_POST['loan'] ?? '';
        $borrow_slots = ["", ""];
        $borrow_detail = ($loan_val == 'yes') ? ($_POST['loan_amt'] ?? '0') : ($_POST['loan_reason'] ?? '-');
        if ($loan_val == 'yes') $borrow_slots[0] = "1";
        else if ($loan_val == 'no') $borrow_slots[1] = "2";
        $st_borrow_money = implode('|-o-|', $borrow_slots) . '|-o-|' . $borrow_detail;

        $recv_map = ['บิดา', 'มารดา', 'ผู้ปกครอง', 'กองทุนกู้ยืม', 'อื่นๆ'];
        $recv_slots = ["", "", "", "", ""];
        foreach (($_POST['received_src'] ?? []) as $val) {
            $found_idx = array_search($val, $recv_map);
            if ($found_idx !== false) $recv_slots[$found_idx] = ($found_idx + 1);
        }
        $st_received = implode('|-o-|', $recv_slots) . '|-o-|' . ($_POST['exp_amt'] ?? '0');

        $job_hist_val = $_POST['job_hist'] ?? '';
        $job_slots = ["", "", "", ""];
        if ($job_hist_val == 'yes') {
            $job_slots[0] = "1";
            $job_slots[2] = mysqli_real_escape_string($connect1, $_POST['job_hist_detail'] ?? '-');
            $job_slots[3] = mysqli_real_escape_string($connect1, $_POST['job_hist_income'] ?? '0');
        } else if ($job_hist_val == 'no') {
            $job_slots[1] = "2";
        }
        $st_job = implode('|-o-|', $job_slots);

        $curr_job_val = $_POST['curr_job'] ?? '';
        $curr_slots = ["", "", ""];
        if ($curr_job_val == 'yes') {
            $curr_slots[0] = "1";
            $curr_slots[2] = mysqli_real_escape_string($connect1, $_POST['curr_job_detail'] ?? '-');
        } else if ($curr_job_val == 'no') {
            $curr_slots[1] = "2";
            $curr_slots[2] = mysqli_real_escape_string($connect1, $_POST['curr_job_reason'] ?? '-');
        }
        $st_current_job = implode('|-o-|', $curr_slots);

        $fin_prob_val = $_POST['fin_prob'] ?? '';
        $peri_slots = ["", "", ""];
        if ($fin_prob_val == 'often') {
            $peri_slots[0] = "1";
            $peri_slots[2] = mysqli_real_escape_string($connect1, $_POST['fin_prob_reason'] ?? '-');
        } else if ($fin_prob_val == 'not_often') {
            $peri_slots[1] = "2";
        }
        $st_peripeteia = implode('|-o-|', $peri_slots);

        $sol_map = ['loan_in', 'loan_out', 'relative', 'parttime'];
        $sol_slots = ["", "", "", ""];
        foreach (($_POST['solve'] ?? []) as $val) {
            $found_idx = array_search($val, $sol_map);
            if ($found_idx !== false) $sol_slots[$found_idx] = ($found_idx + 1);
        }
        $st_solutions = implode('|-o-|', $sol_slots);

        mysqli_query($connect1, "DELETE FROM tb_bursary WHERE id_student = '$current_st_id'");
        $history_rows = [];
        $st_history_bursary = ($_POST['hist_sch'] ?? '') == 'yes' ? 1 : (($_POST['hist_sch'] ?? '') == 'no' ? 2 : 0);
        if ($st_history_bursary == 1 && isset($_POST['bur_year'])) {
            for ($i = 0; $i < count($_POST['bur_year']); $i++) {
                if (!empty($_POST['bur_name'][$i])) {
                    $b_year = mysqli_real_escape_string($connect1, $_POST['bur_year'][$i]);
                    $b_name = mysqli_real_escape_string($connect1, $_POST['bur_name'][$i]);
                    $b_qty  = mysqli_real_escape_string($connect1, $_POST['bur_qty'][$i]);
                    mysqli_query($connect1, "INSERT INTO tb_bursary (bur_year, bur_name, bur_quantity, id_student) VALUES ('$b_year', '$b_name', '$b_qty', '$current_st_id')");
                    $history_rows[] = "$b_year:$b_name:$b_qty";
                }
            }
        }
        $st_history_detail = implode('|-o-|', $history_rows);

        $sql_update_curr = "UPDATE tb_student SET 
            st_family_status = ?, st_borrow_money = ?, st_received = ?, 
            st_job = ?, st_current_job = ?, st_peripeteia = ?, 
            st_solutions = ?, st_history_bursary = ?, st_history_detail = ?, 
            st_father = ?, st_mother = ?, st_guardian = ?, st_siblings = ? 
            WHERE st_code = ?";

        $stmt = $connect1->prepare($sql_update_curr);
        $stmt->bind_param(
            "sssssssissssss",
            $st_family_status,
            $st_borrow_money,
            $st_received,
            $st_job,
            $st_current_job,
            $st_peripeteia,
            $st_solutions,
            $st_history_bursary,
            $st_history_detail,
            $parent_data_strings['father'],
            $parent_data_strings['mother'],
            $parent_data_strings['guardian'],
            $st_siblings,
            $session_st_code
        );

        if ($stmt->execute()) {
            if (isset($_POST['target_page']) && !empty($_POST['target_page'])) {
                $target = $_POST['target_page'];
                echo "<script>window.location.href='$target';</script>";
                exit();
            } elseif (isset($_POST['btn_save_fam'])) {
                echo "<script>window.location.href='apply_reasons.php';</script>";
                exit();
            }
        }
    }
}

$student = [];
$parent_info = [];
$sib_raw = [];
$history_arr = [];
if (isset($connect1)) {
    $sql_main = "SELECT s.*, p.g_program AS major_name, t.tc_name AS advisor_name, y.st_name_1, y.st_name_2, y.st_name_3 
                 FROM tb_student s 
                 LEFT JOIN tb_program p ON s.st_program = p.g_id 
                 LEFT JOIN tb_teacher t ON s.id_teacher = t.tc_id 
                 LEFT JOIN tb_year y ON y.y_id = 1 
                 WHERE s.st_code = '$session_st_code'";

    $result_main = mysqli_query($connect1, $sql_main);
    if ($result_main && $row = mysqli_fetch_assoc($result_main)) {
        $current_st_id = $row['st_id'];
        $student = $row;
        $student['scholarship_name'] = ($row['st_type'] == 1) ? $row['st_name_1'] : (($row['st_type'] == 2) ? $row['st_name_2'] : $row['st_name_3']);
        $student['prefix'] = ($row['st_sex'] == 1) ? 'นาย' : 'นางสาว';
        $student['advisor'] = (!empty($row['advisor_name'])) ? $row['advisor_name'] : 'ยังไม่ได้ระบุ';

        $history_arr = explode('|-o-|', $row['st_history_detail'] ?? '');
        $p_status_arr = explode('|-o-|', $row['st_family_status'] ?? '');
        $borrow_arr   = explode('|-o-|', $row['st_borrow_money'] ?? '');
        $received_arr = explode('|-o-|', $row['st_received'] ?? '');
        $job_arr      = explode('|-o-|', $row['st_job'] ?? '');
        $curr_job_arr = explode('|-o-|', $row['st_current_job'] ?? '');
        $peri_arr     = explode('|-o-|', $row['st_peripeteia'] ?? '');
        $sol_arr      = explode('|-o-|', $row['st_solutions'] ?? '');

        $sql_parent = "SELECT * FROM tb_parent WHERE id_student = '$current_st_id' ORDER BY parent_id ASC";
        $res_parent = mysqli_query($connect1, $sql_parent);
        $parents_fetched = [];
        while ($row_p = mysqli_fetch_assoc($res_parent)) {
            $parents_fetched[] = $row_p;
        }

        $map_indices = [0 => 'father', 1 => 'mother', 2 => 'guardian'];
        foreach ($map_indices as $idx => $key) {
            $parent_info[$key] = [
                clearInit($parents_fetched[$idx]['parent_name'] ?? ''),
                clearInit($parents_fetched[$idx]['parent_age'] ?? ''),
                $parents_fetched[$idx]['parent_status'] ?? '1',
                clearInit($parents_fetched[$idx]['parent_career'] ?? ''),
                clearInit($parents_fetched[$idx]['parent_revenue'] ?? ''),
                clearInit($parents_fetched[$idx]['parent_workplace'] ?? ''),
                clearInit($parents_fetched[$idx]['parent_tel'] ?? '')
            ];
        }
        $sql_sib = "SELECT * FROM tb_relatives WHERE id_student = '$current_st_id' ORDER BY re_id ASC";
        $res_sib = mysqli_query($connect1, $sql_sib);
        while ($row_sib = mysqli_fetch_assoc($res_sib)) {
            $sib_raw[] = $row_sib['re_name'] . ":" . $row_sib['ra_edu'] . ":" . $row_sib['ra_workplace'] . ":" . $row_sib['ra_revenue'];
        }

        $is_profile_complete = (!empty($row['st_birthday']) && !empty($row['st_age']) && !empty($row['st_address1']) && !empty($row['st_tel1']));
        $is_family_complete = (!empty($parent_info['father'][0]) && !empty($parent_info['mother'][0]) && !empty($row['st_family_status']) && !empty($received_arr[5]));
        $is_reason_complete = (mb_strlen(trim($row['st_note'] ?? ''), 'UTF-8') >= 50);
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
    <title>ข้อมูลครอบครัว - PSU E-Scholarship</title>
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

        .form-control:disabled {
            background-color: var(--bs-secondary-bg);
            opacity: 1;
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
                <span><strong>ชื่อ:</strong> <?php echo htmlspecialchars(($student['prefix'] ?? '') . ($student['st_firstname'] ?? '') . " " . ($student['st_lastname'] ?? '')); ?></span>
                <span><strong>รหัสนักศึกษา:</strong> <?php echo htmlspecialchars($session_st_code); ?></span><br>
                <span><strong>เกรดเฉลี่ย:</strong> <?php echo htmlspecialchars($student['st_score'] ?? ''); ?></span>
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
                    <a href="apply_fam.php" class="nav-link-custom active">
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

            <form action="" method="POST" id="famForm" class="px-2" novalidate>
                <input type="hidden" name="st_id" value="<?php echo $student['st_id'] ?? ''; ?>">
                <input type="hidden" name="target_page" id="target_page" value="">

                <?php
                $heads = [1 => 'บิดา', 2 => 'มารดา', 3 => 'ผู้ปกครอง'];
                $keys = ['father', 'mother', 'guardian'];
                foreach ($heads as $i => $title):
                    $p_key = $keys[$i - 1];
                    $data = $parent_info[$p_key] ?? ['', '', '1', '', '', '', ''];
                ?>
                    <div class="section-header-app">
                        ข้อมูล<?php echo $title; ?>
                        <?php if ($p_key == 'guardian'): ?>
                            <div class="d-inline-flex gap-3 ms-3" style="font-size: 0.85rem; font-weight: normal;">
                                <span class="text-muted">คัดลอกข้อมูลจาก:</span>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="copy_opt" id="copy_f" onclick="copyParentData('father')">
                                    <label class="form-check-label" for="copy_f">บิดา</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="copy_opt" id="copy_m" onclick="copyParentData('mother')">
                                    <label class="form-check-label" for="copy_m">มารดา</label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="indent-app parent-section mb-4" data-parent="<?php echo $p_key; ?>">
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 fw-medium">ชื่อ-สกุล: <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="<?php echo $p_key; ?>_name" class="form-control" value="<?php echo htmlspecialchars($data[0]); ?>" placeholder="ชื่อ-นามสกุล">
                                <div class="invalid-feedback">กรุณาระบุทั้งชื่อและนามสกุลให้ครบถ้วน</div>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 fw-medium">อายุ / สถานะ: <span class="text-danger">*</span></label>
                            <div class="col-sm-9 d-flex align-items-start gap-3">
                                <div style="flex: 0 0 120px;">
                                    <input type="text" name="<?php echo $p_key; ?>_age" class="form-control text-center" style="text-align: left !important;" value="<?php echo htmlspecialchars($data[1]); ?>" placeholder="อายุ">
                                    <div class="invalid-feedback">ระบุอายุ</div>
                                </div>
                                <span class="pt-2">ปี</span>
                                <?php if ($p_key != 'guardian'): ?>
                                    <div class="d-flex gap-3 pt-2 ms-2">
                                        <div class="form-check"><input type="radio" name="<?php echo $p_key; ?>_status" value="1" class="form-check-input status-radio" <?php echo ($data[2] == '1') ? 'checked' : ''; ?>> มีชีวิตอยู่</div>
                                        <div class="form-check"><input type="radio" name="<?php echo $p_key; ?>_status" value="0" class="form-check-input status-radio" <?php echo ($data[2] == '0') ? 'checked' : ''; ?>> ถึงแก่กรรม</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 fw-medium">อาชีพ / รายได้: <span class="text-danger">*</span></label>
                            <div class="col-sm-9 d-flex align-items-start gap-3">
                                <div style="flex-grow: 1;">
                                    <input type="text" name="<?php echo $p_key; ?>_job" class="form-control" value="<?php echo htmlspecialchars($data[3]); ?>" placeholder="ระบุอาชีพ">
                                    <div class="invalid-feedback">กรุณาระบุอาชีพ </div>
                                </div>
                                <div style="width: 180px;">
                                    <input type="number" name="<?php echo $p_key; ?>_income" class="form-control text-end" value="<?php echo htmlspecialchars($data[4]); ?>" placeholder="รายได้">
                                    <div class="invalid-feedback">ระบุรายได้</div>
                                </div>
                                <span class="pt-2">บาท/เดือน</span>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 fw-medium">ที่ทำงาน / เบอร์: <span class="text-danger">*</span></label>
                            <div class="col-sm-9 d-flex align-items-start gap-3">
                                <div style="flex-grow: 1;">
                                    <input type="text" name="<?php echo $p_key; ?>_work" class="form-control" value="<?php echo htmlspecialchars($data[5]); ?>" placeholder="สถานที่ทำงาน">
                                    <div class="invalid-feedback">ระบุสถานที่ทำงาน </div>
                                </div>
                                <div style="width: 180px;">
                                    <input type="text" name="<?php echo $p_key; ?>_tel" class="form-control tel-input" value="<?php echo htmlspecialchars($data[6]); ?>" maxlength="10" placeholder="เบอร์โทร">
                                    <div class="invalid-feedback">เบอร์โทร 10 หลัก</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="section-header-app">สถานภาพของบิดามารดา <span class="text-danger">*</span></div>
                <div class="indent-app mb-4">
                    <div class="d-flex gap-3 flex-wrap">
                        <?php
                        $st_lbls = ['together' => 'อยู่ด้วยกัน', 'divorced' => 'หย่าร้าง', 'separated' => 'แยกกันอยู่', 'father_died' => 'บิดาเสียชีวิต', 'mother_died' => 'มารดาเสียชีวิต'];
                        $ki = 0;
                        foreach ($st_lbls as $val => $lbl): ?>
                            <div class="form-check"><input type="checkbox" name="p_status[]" class="form-check-input check-group" value="<?php echo $val; ?>" <?php echo (isset($p_status_arr[$ki]) && $p_status_arr[$ki] != "") ? 'checked' : '';
                                                                                                                                                                $ki++; ?>> <?php echo $lbl; ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div id="p_status_error" class="text-danger small mt-1" style="display:none;">กรุณาเลือกสถานภาพอย่างน้อย 1 รายการ</div>
                </div>

                <div class="section-header-app">จำนวนพี่น้องร่วมบิดามารดา </div>
                <div class="indent-app mb-4">
                    <table class="table table-bordered align-middle" id="sibTable">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th>ชื่อ-สกุล</th>
                                <th>สถานศึกษา</th>
                                <th>ที่ทำงาน</th>
                                <th>รายได้/เดือน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count_sib = count($sib_raw);
                            $display_rows = ($count_sib > 0) ? $count_sib : 1;
                            for ($j = 0; $j < $display_rows; $j++):
                                $s_data = explode(':', $sib_raw[$j] ?? ':::');
                            ?>
                                <tr>
                                    <td>
                                        <input type="text" name="sib_name[]" class="form-control sib-name" value="<?php echo htmlspecialchars(clearInit($s_data[0])); ?>" placeholder="ชื่อ-นามสกุล">
                                        <div class="invalid-feedback px-2 pb-1">กรุณาระบุทั้งชื่อและนามสกุล</div>
                                    </td>
                                    <td><input type="text" name="sib_edu[]" class="form-control " value="<?php echo htmlspecialchars(clearInit($s_data[1])); ?>" placeholder="สถานศึกษา"></td>
                                    <td><input type="text" name="sib_work[]" class="form-control " value="<?php echo htmlspecialchars(clearInit($s_data[2])); ?>" placeholder="ที่ทำงาน"></td>
                                    <td>
                                        <input type="number" name="sib_income[]" class="form-control text-end sib-income" value="<?php echo htmlspecialchars(clearInit($s_data[3])); ?>" placeholder="รายได้/เดือน">
                                        <div class="invalid-feedback px-2 pb-1">ระบุรายได้หรือ 0</div>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addSiblingRow()">
                        <i class="fa-solid fa-plus me-1"></i> เพิ่มรายชื่อพี่น้อง
                    </button>
                </div>

                <div class="section-header-app">กู้ยืมเงินทุน กยศ. หรือ กรอ. <span class="text-danger">*</span></div>
                <div class="indent-app mb-4">
                    <div class="row mb-2 align-items-center">
                        <div class="col-auto">
                            <div class="form-check"><input type="radio" name="loan" value="yes" class="form-check-input" <?php echo (isset($borrow_arr[0]) && $borrow_arr[0] == "1") ? 'checked' : ''; ?>> กู้</div>
                        </div>
                        <div class="col-sm-2">
                            <input type="number" id="loan_amt" name="loan_amt" class="form-control text-end" value="<?php echo (isset($borrow_arr[0]) && $borrow_arr[0] == "1") ? htmlspecialchars($borrow_arr[2]) : ''; ?>" placeholder="จำนวนเงิน">
                            <div class="invalid-feedback">ระบุยอดเงิน</div>
                        </div> บาท/ปี
                    </div>
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="form-check"><input type="radio" name="loan" value="no" class="form-check-input" <?php echo (isset($borrow_arr[1]) && $borrow_arr[1] == "2") ? 'checked' : ''; ?>> ไม่ได้กู้</div>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" id="loan_reason" name="loan_reason" class="form-control" value="<?php echo (isset($borrow_arr[1]) && $borrow_arr[1] == "2") ? htmlspecialchars($borrow_arr[2]) : ''; ?>" placeholder="เหตุผลที่ไม่กู้">
                            <div class="invalid-feedback">กรุณาระบุเหตุผล</div>
                        </div>
                    </div>
                </div>

                <div class="section-header-app">นักศึกษาได้รับค่าครองชีพจาก <span class="text-danger">*</span></div>
                <div class="indent-app mb-4">
                    <div class="d-flex gap-3 flex-wrap mb-3">
                        <?php $recv_lbls = ['บิดา', 'มารดา', 'ผู้ปกครอง', 'กองทุนกู้ยืม', 'อื่นๆ'];
                        foreach ($recv_lbls as $idx => $lbl): ?>
                            <div class="form-check"><input type="checkbox" name="received_src[]" class="form-check-input check-group-src" value="<?php echo $lbl; ?>" <?php echo (isset($received_arr[$idx]) && $received_arr[$idx] != "") ? 'checked' : ''; ?>> <?php echo $lbl; ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        รวมเดือนละ <input type="number" name="exp_amt" class="form-control text-end" style="width:150px;" value="<?php echo htmlspecialchars($received_arr[5] ?? ''); ?>" placeholder="0"> บาท
                        <div id="exp_error" class="text-danger small" style="display:none;">ระบุที่มาและยอดเงิน</div>
                    </div>
                </div>

                <div class="section-header-app">เคยทำงานพิเศษระหว่างเรียนหรือไม่ <span class="text-danger">*</span></div>
                <div class="indent-app mb-4">
                    <div class="form-check"><input type="radio" name="job_hist" value="no" class="form-check-input" <?php echo (isset($job_arr[1]) && $job_arr[1] == "2") ? 'checked' : ''; ?>> ไม่เคย</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <div class="form-check"><input type="radio" name="job_hist" value="yes" class="form-check-input" <?php echo (isset($job_arr[0]) && $job_arr[0] == "1") ? 'checked' : ''; ?>> เคย</div>
                        ประเภท <input type="text" id="job_hist_detail" name="job_hist_detail" class="form-control" style="width:200px;" value="<?php echo htmlspecialchars($job_arr[2] ?? ''); ?>" placeholder="ประเภทงาน">
                        รายได้ <input type="number" id="job_hist_income" name="job_hist_income" class="form-control text-end" style="width:120px;" value="<?php echo htmlspecialchars($job_arr[3] ?? ''); ?>" placeholder="0"> บาท/เดือน
                    </div>
                    <div id="jobhist_error" class="text-danger small mt-1" style="display:none;">ระบุข้อมูลงานและรายได้ให้ครบถ้วน</div>
                </div>

                <div class="section-header-app">ปัจจุบันยังทำอยู่หรือไม่ <span class="text-danger">*</span><span id="skip-job-msg" class="text-muted small" style="display:none;">(ข้ามข้อนี้เนื่องจากไม่เคยทำงาน)</span></div>
                <div id="currJobSection" class="indent-app mb-4">
                    <div class="row mb-2 align-items-center">
                        <div class="col-auto">
                            <div class="form-check"><input type="radio" name="curr_job" value="yes" class="form-check-input" <?php echo (isset($curr_job_arr[0]) && $curr_job_arr[0] == "1") ? 'checked' : ''; ?>> ทำ</div>
                        </div>
                        <div class="col-sm-4"><input type="text" id="curr_job_detail" name="curr_job_detail" class="form-control" placeholder="ระบุประเภทงานที่ทำ" value="<?php echo (isset($curr_job_arr[0]) && $curr_job_arr[0] == "1") ? htmlspecialchars($curr_job_arr[2]) : ''; ?>"></div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="form-check"><input type="radio" name="curr_job" value="no" class="form-check-input" <?php echo (isset($curr_job_arr[1]) && $curr_job_arr[1] == "2") ? 'checked' : ''; ?>> ไม่ทำ</div>
                        </div>
                        <div class="col-sm-6"><input type="text" id="curr_job_reason" name="curr_job_reason" class="form-control" placeholder="ระบุเหตุผลที่เลิกทำ" value="<?php echo (isset($curr_job_arr[1]) && $curr_job_arr[1] == "2") ? htmlspecialchars($curr_job_arr[2]) : ''; ?>"></div>
                    </div>
                    <div id="currjob_error" class="text-danger small mt-1" style="display:none;">กรุณาเลือกสถานะงานปัจจุบันและระบุรายละเอียด</div>
                </div>

                <div class="section-header-app">ครอบครัวประสบปัญหาการเงินบ่อยเพียงใด <span class="text-danger">*</span></div>
                <div class="indent-app mb-4">
                    <div class="row mb-2 align-items-center">
                        <div class="col-auto">
                            <div class="form-check"><input type="radio" name="fin_prob" value="often" class="form-check-input" <?php echo (isset($peri_arr[0]) && $peri_arr[0] == "1") ? 'checked' : ''; ?>> บ่อย</div>
                        </div>
                        <div class="col-sm-6">
                            <input type="text" id="fin_prob_reason" name="fin_prob_reason" class="form-control" placeholder="ระบุสาเหตุ (เช่น ค้าขายไม่ดี, ภาระหนี้สิน)" value="<?php echo (isset($peri_arr[0]) && $peri_arr[0] == "1") ? htmlspecialchars($peri_arr[2]) : ''; ?>">
                            <div class="invalid-feedback">ระบุสาเหตุของปัญหาการเงิน</div>
                        </div>
                    </div>
                    <div class="form-check"><input type="radio" name="fin_prob" value="not_often" class="form-check-input" <?php echo (isset($peri_arr[1]) && $peri_arr[1] == "2") ? 'checked' : ''; ?>> ไม่บ่อย</div>
                </div>

                <div class="section-header-app">วิธีแก้ไขเมื่อมีปัญหาการเงิน <span class="text-danger">*</span></div>
                <div class="indent-app mb-4">
                    <div class="d-flex gap-3 flex-wrap">
                        <?php $sol_list = ['loan_in' => 'กู้ในระบบ', 'loan_out' => 'กู้ยืมนอกระบบ', 'relative' => 'ญาติ/เพื่อน', 'parttime' => 'ทำงานพิเศษ'];
                        $si = 0;
                        foreach ($sol_list as $v => $l): ?>
                            <div class="form-check"><input type="checkbox" name="solve[]" class="form-check-input solve-group" value="<?php echo $v; ?>" <?php echo (isset($sol_arr[$si]) && $sol_arr[$si] != "") ? 'checked' : '';
                                                                                                                                                            $si++; ?>> <?php echo $l; ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div id="solve_error" class="text-danger small mt-1" style="display:none;">กรุณาเลือกวิธีการแก้ไขอย่างน้อย 1 รายการ</div>
                </div>

                <div class="section-header-app">เคยได้รับทุนการศึกษาอื่นหรือไม่ (ย้อนหลัง 3 ปี) <span class="text-danger">*</span></div>
                <div class="indent-app mb-4">
                    <div class="d-flex gap-4 mb-3">
                        <div class="form-check">
                            <input type="radio" name="hist_sch" value="yes" class="form-check-input hist-sch-radio" <?php echo (($student['st_history_bursary'] ?? 0) == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label">เคยได้รับ</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="hist_sch" value="no" class="form-check-input hist-sch-radio" <?php echo (($student['st_history_bursary'] ?? 0) == 2) ? 'checked' : ''; ?>>
                            <label class="form-check-label">ไม่เคยได้รับ</label>
                        </div>
                    </div>
                    <table class="table table-bordered" id="burTable">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>ปีการศึกษา</th>
                                <th>ชื่อทุน</th>
                                <th>จำนวนเงิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hist_rows_count = count($history_arr);
                            $display_b_rows = ($hist_rows_count > 3) ? $hist_rows_count : 3;
                            for ($i = 0; $i < $display_b_rows; $i++):
                                $b_data = explode(':', $history_arr[$i] ?? '::');
                            ?>
                                <tr>
                                    <td><input type="text" name="bur_year[]" class="form-control text-center bur-input" value="<?php echo htmlspecialchars($b_data[0]); ?>" placeholder="พ.ศ."></td>
                                    <td><input type="text" name="bur_name[]" class="form-control bur-input" value="<?php echo htmlspecialchars($b_data[1]); ?>" placeholder="ชื่อทุน"></td>
                                    <td><input type="number" name="bur_qty[]" class="form-control text-end bur-input" value="<?php echo htmlspecialchars($b_data[2]); ?>" placeholder="0"></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addBursaryRow()">
                        <i class="fa-solid fa-plus me-1"></i> เพิ่มช่องรายการทุน
                    </button>
                    <div id="bur_error" class="text-danger small mt-1" style="display:none;">กรุณากรอกประวัติทุนอย่างน้อย 1 รายการ</div>
                </div>

                <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                    <a href="apply_form.php" class="btn-back-step-grey shadow-sm"><i class="fa-solid fa-chevron-left me-2"></i> ย้อนกลับ</a>
                    <button type="submit" name="btn_save_fam" id="btn_submit" class="btn-next-step shadow-sm">ถัดไป <i class="fa-solid fa-chevron-right ms-2"></i></button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../include/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const dirtyFields = new Set();

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

        const form = document.getElementById('famForm');
        const submitBtn = document.getElementById('btn_submit');
        const targetInput = document.getElementById('target_page');
        const tabLinks = document.querySelectorAll('.tab-save-link');

        function isFullName(val) {
            const parts = val.trim().split(/\s+/);
            return parts.length >= 2 && parts[0] !== "" && parts[1] !== "";
        }

        function toggleError(element, isInvalid, errorElement = null) {
            if (!element) return;

            const shouldShow = isInvalid && dirtyFields.has(element);

            if (shouldShow) {
                element.classList.add('is-invalid');
                if (errorElement) errorElement.style.display = 'block';
            } else {
                element.classList.remove('is-invalid');
                if (errorElement) errorElement.style.display = 'none';
            }
        }

        function copyParentData(type) {
            const fields = ['name', 'age', 'job', 'income', 'work', 'tel'];
            fields.forEach(f => {
                const source = document.getElementsByName(type + '_' + f)[0];
                const target = document.getElementsByName('guardian_' + f)[0];
                if (source && target) {
                    target.value = source.value;
                    dirtyFields.add(target);
                }
            });
            validateAll();
        }

        function addSiblingRow() {
            const tableBody = document.querySelector('#sibTable tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
            <td>
                <input type="text" name="sib_name[]" class="form-control sib-name" placeholder="ชื่อ-นามสกุล">
                <div class="invalid-feedback px-2 pb-1">กรุณาระบุทั้งชื่อและนามสกุล</div>
            </td>
            <td><input type="text" name="sib_edu[]" class="form-control" placeholder="สถานศึกษา"></td>
            <td><input type="text" name="sib_work[]" class="form-control" placeholder="ที่ทำงาน"></td>
            <td>
                <input type="number" name="sib_income[]" class="form-control text-end sib-income" placeholder="รายได้/เดือน">
                <div class="invalid-feedback px-2 pb-1">ระบุรายได้หรือ 0</div>
            </td>
        `;
            tableBody.appendChild(newRow);

            newRow.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', (e) => {
                    dirtyFields.add(e.target);
                    validateAll();
                });
            });
        }

        function addBursaryRow() {
            const tableBody = document.querySelector('#burTable tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td><input type="text" name="bur_year[]" class="form-control text-center bur-input" placeholder="พ.ศ."></td>
                <td><input type="text" name="bur_name[]" class="form-control bur-input" placeholder="ชื่อทุน"></td>
                <td><input type="number" name="bur_qty[]" class="form-control text-end bur-input" placeholder="0"></td>
            `;
            tableBody.appendChild(newRow);

            const burRadio = document.querySelector('input[name="hist_sch"]:checked');
            if (burRadio && burRadio.value === 'no') {
                newRow.querySelectorAll('.bur-input').forEach(i => i.disabled = true);
            }

            newRow.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', (e) => {
                    dirtyFields.add(e.target);
                    validateAll();
                });
            });
        }

        function validateAll() {
            let isValid = true;

            const parents = ['father', 'mother', 'guardian'];
            parents.forEach(p => {
                const statusRadios = document.getElementsByName(p + '_status');
                const nameInput = document.getElementsByName(p + '_name')[0];
                const ageInput = document.getElementsByName(p + '_age')[0];
                const jobInput = document.getElementsByName(p + '_job')[0];
                const incomeInput = document.getElementsByName(p + '_income')[0];
                const workInput = document.getElementsByName(p + '_work')[0];
                const telInput = document.getElementsByName(p + '_tel')[0];

                let isAlive = true;
                if (p !== 'guardian' && statusRadios.length > 0) {
                    const checkedStatus = Array.from(statusRadios).find(r => r.checked);
                    isAlive = checkedStatus ? checkedStatus.value === "1" : true;

                    if (checkedStatus && checkedStatus.value === "0") {
                        const targetVal = (p === 'father') ? 'father_died' : 'mother_died';
                        const cb = document.querySelector(`input[name="p_status[]"][value="${targetVal}"]`);
                        if (cb && !cb.checked) {
                            cb.checked = true;
                        }
                    }
                }

                [nameInput, ageInput, jobInput, incomeInput, workInput, telInput].forEach(el => {
                    if (el) {
                        el.disabled = !isAlive;
                        if (!isAlive) {
                            if (el.tagName === 'INPUT' && el.type !== 'number') el.value = "-";
                            if (el.type === 'number') el.value = "0";
                            el.classList.remove('is-invalid');
                        }
                    }
                });

                if (isAlive) {
                    if (nameInput) toggleError(nameInput, (nameInput.value.trim() === "" || nameInput.value === "-" || !isFullName(nameInput.value)));
                    if (ageInput) toggleError(ageInput, (ageInput.value.trim() === "" || isNaN(ageInput.value) || ageInput.value == "0"));
                    if (jobInput) toggleError(jobInput, jobInput.value.trim() === "");
                    if (incomeInput) toggleError(incomeInput, incomeInput.value.trim() === "");
                    if (workInput) toggleError(workInput, workInput.value.trim() === "");
                    if (telInput) toggleError(telInput, (telInput.value.trim() === "" || telInput.value.length < 10));
                }
            });

            const fStatusRadios = document.getElementsByName('father_status');
            const mStatusRadios = document.getElementsByName('mother_status');
            const copyFRadio = document.getElementById('copy_f');
            const copyMRadio = document.getElementById('copy_m');

            if (fStatusRadios.length > 0) {
                const fIsDied = Array.from(fStatusRadios).find(r => r.checked && r.value === "0");
                if (fIsDied) {
                    copyFRadio.disabled = true;
                    if (copyFRadio.checked) copyFRadio.checked = false;
                } else {
                    copyFRadio.disabled = false;
                }
            }

            if (mStatusRadios.length > 0) {
                const mIsDied = Array.from(mStatusRadios).find(r => r.checked && r.value === "0");
                if (mIsDied) {
                    copyMRadio.disabled = true;
                    if (copyMRadio.checked) copyMRadio.checked = false;
                } else {
                    copyMRadio.disabled = false;
                }
            }

            const pStatusCheck = document.querySelectorAll('input[name="p_status[]"]:checked');
            const pStatusError = document.getElementById('p_status_error');
            if (pStatusError) pStatusError.style.display = (pStatusCheck.length === 0 && dirtyFields.has(document.querySelector('input[name="p_status[]"]'))) ? 'block' : 'none';

            const loanRadio = document.querySelector('input[name="loan"]:checked');
            const loanAmt = document.getElementById('loan_amt');
            const loanReason = document.getElementById('loan_reason');
            if (loanRadio) {
                if (loanRadio.value === 'yes') {
                    loanReason.disabled = true;
                    loanAmt.disabled = false;
                    toggleError(loanAmt, (loanAmt.value === "" || loanAmt.value <= 0));
                } else {
                    loanAmt.disabled = true;
                    loanReason.disabled = false;
                    toggleError(loanReason, (loanReason.value.trim() === ""));
                }
            }

            const expAmt = document.getElementsByName('exp_amt')[0];
            const expError = document.getElementById('exp_error');
            if (expError && expAmt) {
                const isExpInvalid = (expAmt.value === "" || expAmt.value <= 0);
                expError.style.display = (isExpInvalid && dirtyFields.has(expAmt)) ? 'block' : 'none';
            }

            const jobHistRadio = document.querySelector('input[name="job_hist"]:checked');
            const jobDetail = document.getElementById('job_hist_detail');
            const jobIncome = document.getElementById('job_hist_income');
            const currJobRadios = document.querySelectorAll('input[name="curr_job"]');
            const skipMsg = document.getElementById('skip-job-msg');

            if (jobHistRadio) {
                if (jobHistRadio.value === 'yes') {
                    jobDetail.disabled = false;
                    jobIncome.disabled = false;
                    currJobRadios.forEach(r => r.disabled = false);
                    if (skipMsg) skipMsg.style.display = 'none';

                    const isHistInvalid = (jobDetail.value.trim() === "" || jobIncome.value === "" || jobIncome.value <= 0);
                    const jobHistErrEl = document.getElementById('jobhist_error');
                    if (jobHistErrEl) jobHistErrEl.style.display = (isHistInvalid && (dirtyFields.has(jobDetail) || dirtyFields.has(jobIncome))) ? 'block' : 'none';

                    const currJobChecked = document.querySelector('input[name="curr_job"]:checked');
                    if (currJobChecked) {
                        if (currJobChecked.value === 'yes') {
                            document.getElementById('curr_job_detail').disabled = false;
                            document.getElementById('curr_job_reason').disabled = true;
                        } else {
                            document.getElementById('curr_job_detail').disabled = true;
                            document.getElementById('curr_job_reason').disabled = false;
                        }
                    }
                } else {
                    jobDetail.disabled = true;
                    jobIncome.disabled = true;
                    currJobRadios.forEach(r => {
                        r.disabled = true;
                        r.checked = false;
                    });
                    document.getElementById('curr_job_detail').disabled = true;
                    document.getElementById('curr_job_reason').disabled = true;
                    if (jobHistRadio.value === 'no' && skipMsg) skipMsg.style.display = 'inline';
                }
            }

            const finRadio = document.querySelector('input[name="fin_prob"]:checked');
            const finReason = document.getElementById('fin_prob_reason');

            if (finRadio && finRadio.value === 'not_often') {
                finReason.disabled = true;
                finReason.classList.remove('is-invalid');
            } else {
                finReason.disabled = false;
                if (finRadio && finRadio.value === 'often') {
                    toggleError(finReason, finReason.value.trim() === "");
                }
            }

            const burRadio = document.querySelector('input[name="hist_sch"]:checked');
            const burInputs = document.querySelectorAll('.bur-input');
            if (burRadio && burRadio.value === 'no') {
                burInputs.forEach(i => {
                    i.disabled = true;
                    i.classList.remove('is-invalid');
                });
            } else {
                burInputs.forEach(i => i.disabled = false);
            }

            submitBtn.disabled = false;
        }

        form.addEventListener('input', (e) => {
            dirtyFields.add(e.target);
            if (e.target.name && e.target.name.startsWith('guardian_')) {
                const copyOpts = document.getElementsByName('copy_opt');
                copyOpts.forEach(opt => opt.checked = false);
            }
            validateAll();
        });

        form.addEventListener('change', (e) => {
            dirtyFields.add(e.target);
            validateAll();
        });

        window.addEventListener('load', validateAll);

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                targetInput.value = this.getAttribute('href');
                form.submit();
            });
        });

        form.addEventListener('submit', function() {});
    </script>
</body>

</html>