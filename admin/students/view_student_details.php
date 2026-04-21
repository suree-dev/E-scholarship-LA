<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../../include/config.php';

$st_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'personal';

if ($st_id <= 0) die("ไม่พบข้อมูลนักศึกษา");

$scholarship_options = [];
$sql_types = "SELECT st_name_1, st_1, st_name_2, st_2, st_name_3, st_3 FROM tb_year WHERE y_id = 1";
$res_types = mysqli_query($connect1, $sql_types);
$d_types = mysqli_fetch_assoc($res_types);
$scholarship_options[1] = $d_types['st_name_1'] ?? 'ทุนประเภทที่ 1';
$scholarship_options[2] = $d_types['st_name_2'] ?? 'ทุนประเภทที่ 2';
$scholarship_options[3] = $d_types['st_name_3'] ?? 'ทุนประเภทที่ 3';

$sql = "SELECT s.*, p.g_program, t.tc_name 
        FROM tb_student s 
        LEFT JOIN tb_program p ON s.st_program = p.g_id 
        LEFT JOIN tb_teacher t ON s.id_teacher = t.tc_id 
        WHERE s.st_id = '$st_id'";
$result = mysqli_query($connect1, $sql);
$student = mysqli_fetch_assoc($result);

if (!$student) die("ไม่พบข้อมูลนักศึกษาในระบบ");

$member_data = null;
$schedule_list = [];
$com_skills = [];
$eng_skills = [];

if ($student['st_type'] == 2) {
    $fname = mysqli_real_escape_string($connect1, $student['st_firstname']);
    $lname = mysqli_real_escape_string($connect1, $student['st_lastname']);

    $sql_mem = "SELECT m.*, t.tc_name as advisor_name
                FROM tb_member m
                LEFT JOIN tb_teacher t ON m.tea_mem = t.tc_id
                WHERE m.name_mem = '$fname' AND m.sur_mem = '$lname' LIMIT 1";
    $res_mem = mysqli_query($connect1, $sql_mem);
    $member_data = mysqli_fetch_assoc($res_mem);

    if ($member_data) {
        $id_mem = $member_data['id_mem'];

        $com_skills = explode('|o|', $member_data['com_mem']);
        $eng_skills = explode('|o|', $member_data['eng_mem']);

        $sql_date = "SELECT * FROM tb_mem_date WHERE id_mem = '$id_mem'";
        $res_date = mysqli_query($connect1, $sql_date);
        while ($d_row = mysqli_fetch_assoc($res_date)) {
            $schedule_list[$d_row['date_date']] = $d_row['date_time'];
        }
    }
}

function splitData($data)
{
    return explode('|-o-|', $data ?? '');
}

function parseParent($data)
{
    $p = explode('|-o-|', $data ?? '');
    return [
        'name' => $p[0] ?? '-',
        'age' => $p[1] ?? '-',
        'status' => $p[2] ?? '',
        'job' => $p[3] ?? '-',
        'income' => $p[4] ?? '-',
        'work' => $p[5] ?? '-',
        'tel' => $p[6] ?? '-'
    ];
}

$father = parseParent($student['st_father']);
$mother = parseParent($student['st_mother']);
$guardian = parseParent($student['st_guardian']);

$siblings = [];
if (!empty($student['st_siblings'])) {
    foreach (explode('|-o-|', $student['st_siblings']) as $row) {
        $parts = explode(':', $row);
        if (!empty($parts[0])) {
            $siblings[] = ['name' => $parts[0], 'edu' => $parts[1] ?? '-', 'work' => $parts[2] ?? '-', 'income' => $parts[3] ?? '0'];
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
$hist_bur = $student['st_history_bursary'] ?? '2';
$hist_list = [];

if ($hist_bur == '1' && !empty($student['st_history_detail'])) {
    foreach (explode('|-o-|', $student['st_history_detail']) as $row) {
        $parts = explode(':', $row);
        if (count($parts) >= 2) {
            $hist_list[] = [
                'year'   => $parts[0] ?? '-',
                'name'   => $parts[1] ?? '-',
                'amount' => $parts[2] ?? '0'
            ];
        }
    }
}

$formatted_dob = '-';
if ($student['st_birthday'] && $student['st_birthday'] != '0000-00-00') {
    $date_obj = DateTime::createFromFormat('Y-m-d', $student['st_birthday']);
    if ($date_obj) {
        $formatted_dob = $date_obj->format('d/m/') . ($date_obj->format('Y') + 543);
    }
}

$thai_months = [1 => "มกราคม", 2 => "กุมภาพันธ์", 3 => "มีนาคม", 4 => "เมษายน", 5 => "พฤษภาคม", 6 => "มิถุนายน", 7 => "กรกฎาคม", 8 => "สิงหาคม", 9 => "กันยายน", 10 => "ตุลาคม", 11 => "พฤศจิกายน", 12 => "ธันวาคม"];
$current_date_th = date("j") . " " . $thai_months[(int)date("n")] . " " . (date("Y") + 543);

function renderFilePreview($filename)
{
    if (!$filename) return '<div class="no-file-box">ไม่ได้แนบเอกสาร</div>';
    $path = "../../images/student/" . $filename;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $html = '<div class="a4-preview-wrapper">';
    $html .= '<div class="a4-document-card shadow">';

    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $html .= '<img src="' . $path . '" class="img-fluid">';
    } elseif ($ext == 'pdf') {
        $html .= '<iframe src="' . $path . '#toolbar=0" width="100%" height="1100px" style="border:none;"></iframe>';
    } else {
        $html .= '<div class="p-5 text-center">ไม่รองรับพรีวิวไฟล์นามสกุล (.' . $ext . ') <br><br>
                  <a href="' . $path . '" target="_blank" class="btn btn-outline-primary rounded-pill">
                    <i class="fa-solid fa-up-right-from-square me-2"></i> เปิดไฟล์ในหน้าต่างใหม่
                  </a>
                </div>';
    }

    $html .= '</div>';
    $html .= '<div class="text-center mt-3 no-print">
                <a href="' . $path . '" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="fa-solid fa-magnifying-glass-plus me-1"></i> ดูขนาดเต็ม
                </a>
              </div>';
    $html .= '</div>';
    return $html;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดนักศึกษา - <?php echo $student['st_firstname']; ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/bg/head_01.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/global2.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/navigation.css">
    <link rel="stylesheet" href="../../assets/css/ui-elements.css">
    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets/css/tables.css">
    <link rel="stylesheet" href="../../assets/css/pages.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        .a4-preview-wrapper {
            background-color: #f0f2f5;
            padding: 40px 20px;
            border-radius: 12px;
            margin: 20px 0 50px 0;
        }

        .a4-document-card {
            background-color: #ffffff;
            width: 100%;
            max-width: 850px;
            margin: 0 auto;
            min-height: 600px;
            border: 1px solid #dee2e6;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .no-file-box {
            width: 100%;
            height: 120px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff5f5;
            color: #d9534f;
            border: 2px dashed #f5c6cb;
            border-radius: 12px;
            font-weight: 500;
            margin: 20px 0;
        }

        .photo-box-view {
            position: absolute;
            top: -20px;
            right: 15px;
            width: 115px;
            height: 145px;
            border: 1.5px solid #333;
            padding: 3px;
            background: #fff;
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 5;
        }

        .photo-box-view img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tab-section {
            display: none;
        }

        .tab-section.active {
            display: block;
        }

        .type2-form-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .type2-row {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
        }

        .type2-label {
            width: 150px;
            font-weight: 600;
            flex-shrink: 0;
            color: #333;
        }

        .type2-value-box {
            flex-grow: 1;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #fff;
            min-height: 44px;
            display: flex;
            align-items: center;
            font-size: 15px;
        }

        .schedule-table-view {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .schedule-table-view th,
        .schedule-table-view td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        .skill-item-view {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            gap: 20px;
        }

        .skill-name {
            width: 120px;
            font-weight: 500;
            color: #444;
        }

        .skill-options {
            display: flex;
            gap: 15px;
        }

        .skill-opt {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
        }

        @media (max-width: 1024px) {
            .photo-box-view {
                position: relative;
                margin: 0 auto 30px;
                top: auto;
                right: auto;
            }

            .type2-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .type2-label {
                width: 100%;
                margin-bottom: 5px;
            }

            .type2-value-box {
                width: 100%;
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

                    <div class="mb-4 no-print d-flex justify-content-between align-items-center">
                        <a href="student_data.php?type=<?php echo $student['st_type']; ?>" class="btn btn-secondary rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-arrow-left me-2"></i> กลับหน้ารายการ
                        </a>
                        <button onclick="window.open('../../student/print_scholarship.php?student_id=<?php echo $st_id; ?>', '_blank')" class="btn btn-primary rounded-pill px-4 shadow-sm border-0">
                            <i class="fa-solid fa-print me-2"></i> พิมพ์เอกสาร
                        </button>
                    </div>

                    <div class="header-content position-relative text-center mb-5">
                        <h4 class="fw-bold m-0">ใบสมัครขอรับทุนการศึกษา <?php echo htmlspecialchars($scholarship_options[$student['st_type']] ?? ''); ?></h4>
                        <h5 class="fw-bold mt-2">คณะศิลปศาสตร์ มหาวิทยาลัยสงขลานครินทร์</h5>
                        <p class="text-muted small mt-2">เรียกดูเมื่อ: <?php echo $current_date_th; ?> | <?php echo date("H:i"); ?> น.</p>
                        <div class="photo-box-view">
                            <img src="../../images/student/<?php echo $student['st_image']; ?>" onerror="this.src='../../assets/images/bg/no-profile.png'">
                        </div>
                    </div>

                    <?php if ($student['st_type'] == 2 && $member_data): ?>
                        <div class="type2-form-container mt-4">
                            <div class="type2-row">
                                <div class="type2-label">ชื่อ-สกุล </div>
                                <div class="d-flex gap-2 flex-grow-1 w-100">
                                    <div class="type2-value-box" style="width: 100px; flex-shrink: 0; flex-grow: 0;"><?php echo ($student['st_sex'] == 1 ? 'นาย' : 'นางสาว'); ?></div>
                                    <div class="type2-value-box"><?php echo $member_data['name_mem']; ?></div>
                                    <div class="type2-value-box"><?php echo $member_data['sur_mem']; ?></div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <div class="type2-row mb-0">
                                        <div class="type2-label">สาขา </div>
                                        <div class="type2-value-box"><?php echo $student['g_program']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="type2-row mb-0">
                                        <div class="type2-label" style="width: 60px;">ชั้นปี </div>
                                        <div class="type2-value-box">ชั้นปีที่ <?php echo $member_data['class_mem']; ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="type2-row">
                                <div class="type2-label">อาจารย์ที่ปรึกษา </div>
                                <div class="type2-value-box"><?php echo $member_data['advisor_name'] ?: 'ยังไม่ได้ระบุ'; ?></div>
                            </div>

                            <div class="type2-row">
                                <div class="type2-label">เบอร์โทร </div>
                                <div class="type2-value-box"><?php echo $member_data['tel_mem']; ?></div>
                            </div>

                            <div class="type2-row">
                                <div class="type2-label">Email Address </div>
                                <div class="type2-value-box"><?php echo $member_data['email_mem']; ?></div>
                            </div>

                            <div class="mt-5 mb-4">
                                <p class="fw-bold mb-2">ข้อมูลตารางเวลาที่สามารถปฏิบัติงานได้ (อย่างน้อย 1 วัน) </p>
                                <table class="schedule-table-view">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 150px;">วัน</th>
                                            <th>ตั้งแต่เวลา</th>
                                            <th>ถึงเวลา</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $days_th = [2 => 'จันทร์', 3 => 'อังคาร', 4 => 'พุธ', 5 => 'พฤหัสบดี', 6 => 'ศุกร์'];
                                        foreach ($days_th as $id_day => $label):
                                            $time_info = isset($schedule_list[$id_day]) ? $schedule_list[$id_day] : '';
                                            $t_split = ($time_info && $time_info != '-') ? explode(' - ', $time_info) : ['--เลือก--', '--เลือก--'];
                                        ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo $label; ?></td>
                                                <td><?php echo str_replace(':', '.', $t_split[0]); ?></td>
                                                <td><?php echo str_replace(':', '.', $t_split[1] ?? '--เลือก--'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-5 mb-4">
                                <p class="fw-bold mb-3">ข้อมูลความสามารถด้านคอมพิวเตอร์ (ครบทุกข้อ) </p>
                                <?php
                                $com_labels = ["Ms Word", "Ms Excel", "Canva", "PSD-AI"];
                                $opts = [1 => "ดีมาก", 2 => "ดี", 3 => "ปานกลาง", 4 => "พอใช้"];
                                foreach ($com_labels as $idx => $label):
                                    $val = isset($com_skills[$idx]) ? trim($com_skills[$idx]) : '';
                                ?>
                                    <div class="skill-item-view">
                                        <div class="skill-name">- <?php echo $label; ?></div>
                                        <div class="skill-options">
                                            <?php foreach ($opts as $k => $v): ?>
                                                <div class="skill-opt">
                                                    <input type="radio" disabled <?php echo ($val == $k) ? 'checked' : ''; ?>> <?php echo $v; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-5 mb-4">
                                <p class="fw-bold mb-3">ความรู้ด้านภาษาอังกฤษ (ครบทุกข้อ) </p>
                                <?php
                                $eng_labels = ["Writting", "Speaking", "Listening"];
                                foreach ($eng_labels as $idx => $label):
                                    $val = isset($eng_skills[$idx]) ? trim($eng_skills[$idx]) : '';
                                ?>
                                    <div class="skill-item-view">
                                        <div class="skill-name">- <?php echo $label; ?></div>
                                        <div class="skill-options">
                                            <?php foreach ($opts as $k => $v): ?>
                                                <div class="skill-opt">
                                                    <input type="radio" disabled <?php echo ($val == $k) ? 'checked' : ''; ?>> <?php echo $v; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="student-info-highlight shadow-sm mb-5">
                            <span><strong>ชื่อ-สกุล:</strong> <?php echo ($student['st_sex'] == 1 ? 'นาย' : 'นางสาว') . $student['st_firstname'] . ' ' . $student['st_lastname']; ?></span>
                            <span class="ms-md-4"><strong>รหัสนักศึกษา:</strong> <?php echo $student['st_code']; ?></span><br>
                            <span><strong>GPAX:</strong> <?php echo $student['st_score']; ?></span>
                            <span class="ms-md-4"><strong>สาขาวิชา:</strong> <?php echo $student['g_program']; ?></span>
                        </div>

                        <ul class="nav-tabs-app no-print">
                            <li class="nav-item"><a href="javascript:void(0)" onclick="openTab(event, 'personal')" class="nav-link-custom active">ข้อมูลส่วนตัว</a></li>
                            <li class="nav-item"><a href="javascript:void(0)" onclick="openTab(event, 'family')" class="nav-link-custom inactive">ข้อมูลครอบครัว</a></li>
                            <li class="nav-item"><a href="javascript:void(0)" onclick="openTab(event, 'reason')" class="nav-link-custom inactive">เหตุผลการขอทุน</a></li>
                            <li class="nav-item"><a href="javascript:void(0)" onclick="openTab(event, 'document')" class="nav-link-custom inactive">เอกสารแนบ</a></li>
                        </ul>

                        <div class="tab-content pt-2 px-2">
                            <div id="personal" class="tab-section active">
                                <div class="section-header-app">ข้อมูลพื้นฐานนักศึกษา</div>
                                <div class="indent-app">
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">วันเกิด:</label>
                                        <div class="col-md-9">
                                            <div class="form-control-static"><?php echo $formatted_dob; ?></div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">อายุ:</label>
                                        <div class="col-md-9 d-flex align-items-center gap-2">
                                            <div class="form-control-static text-center" style="width: 80px;"><?php echo $student['st_age']; ?></div><span>ปี</span>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">ที่อยู่ปัจจุบัน:</label>
                                        <div class="col-md-9">
                                            <div class="form-control-static"><?php echo $student['st_address1']; ?></div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">ภูมิลำเนา :</label>
                                        <div class="col-md-9">
                                            <div class="form-control-static"><?php echo $student['st_address2']; ?></div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">โทรศัพท์:</label>
                                        <div class="col-md-9">
                                            <div class="form-control-static"><?php echo $student['st_tel1']; ?></div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">อีเมล:</label>
                                        <div class="col-md-9">
                                            <div class="form-control-static"><?php echo $student['st_tel2']; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="family" class="tab-section">
                                <?php $ps = [['t' => 'ข้อมูลบิดา', 'd' => $father], ['t' => 'ข้อมูลมารดา', 'd' => $mother], ['t' => 'ข้อมูลผู้ปกครอง', 'd' => $guardian]];
                                foreach ($ps as $p): $d = $p['d']; ?>
                                    <div class="section-header-app"><?php echo $p['t']; ?></div>
                                    <div class="indent-app">
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">ชื่อ-สกุล:</label>
                                            <div class="col-md-9">
                                                <div class="form-control-static"><?php echo $d['name']; ?></div>
                                            </div>
                                        </div>
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">อายุ:</label>
                                            <div class="col-md-9">
                                                <div class="form-control-static"><?php echo $d['age']; ?></div>
                                            </div>
                                        </div>
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">สถานภาพ:</label>
                                            <div class="col-md-9">
                                                <div class="form-control-static"><?php echo $d['status']; ?></div>
                                            </div>
                                        </div>
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">อาชีพ / รายได้:</label>
                                            <div class="col-md-9 d-flex gap-3 align-items-center">
                                                <div class="form-control-static" style="flex: 2;"><?php echo $d['job']; ?></div>
                                                <div class="form-control-static text-end" style="flex: 1;"><?php echo number_format((float)$d['income']); ?></div><span>บาท</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">สถานที่ทำงาน:</label>
                                            <div class="col-md-9">
                                                <div class="form-control-static"><?php echo $d['work']; ?></div>
                                            </div>
                                        </div>
                                        <div class="row mb-3 align-items-center"><label class="col-md-3 fw-bold text-muted">โทรศัพท์:</label>
                                            <div class="col-md-9">
                                                <div class="form-control-static"><?php echo $d['tel']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

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
                                                <?php if (!empty($siblings)): foreach ($siblings as $sibling): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($sibling['name']); ?></td>
                                                            <td><?php echo htmlspecialchars($sibling['edu']); ?></td>
                                                            <td><?php echo htmlspecialchars($sibling['work']); ?></td>
                                                            <td style="text-align: right; font-family: sans-serif;"><?php echo number_format((float)$sibling['income'], 2); ?></td>
                                                        </tr>
                                                    <?php endforeach;
                                                else: ?>
                                                    <tr>
                                                        <td colspan="4" style="text-align: center; color: #888; padding: 20px;">- ไม่มีข้อมูล -</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="section-header-app">นักศึกษากู้ยืมกองทุน กยศ. หรือ กรอ. หรือไม่</div>
                                <div class="indent-app">
                                    <div class="radio-group" style="width: 100%;">
                                        <div style="margin-bottom: 10px; display: flex; align-items: center;"><label><input type="radio" <?php echo (isset($loan[0]) && $loan[0] == '1') ? 'checked' : 'disabled'; ?>> กู้</label><span style="margin-left: 10px;">จำนวน</span><input type="text" class="form-control-static" style="width: 150px; margin: 0 10px;" value="<?php echo (isset($loan[0]) && $loan[0] == '1') ? $loan[2] : ''; ?>" readonly><span>บาท/ปี</span></div>
                                        <div style="display: flex; align-items: center;"><label><input type="radio" <?php echo (isset($loan[1]) && $loan[1] == '2') ? 'checked' : 'disabled'; ?>> ไม่ได้กู้</label><span style="margin-left: 10px;">เนื่องจาก</span><input type="text" class="form-control-static" style="flex: 1; margin-left: 10px;" value="<?php echo (isset($loan[1]) && $loan[1] == '2') ? $loan[2] : ''; ?>" readonly></div>
                                    </div>
                                </div>

                                <div class="section-header-app">นักศึกษาได้รับค่าครองชีพจาก</div>
                                <div class="indent-app">
                                    <div class="checkbox-group" style="display:flex; flex-direction:column; gap:5px; margin-bottom:10px;">
                                        <label><input type="checkbox" <?php echo isset($expense_source[0]) && $expense_source[0] == '1' ? 'checked' : 'disabled'; ?>> บิดา</label>
                                        <label><input type="checkbox" <?php echo isset($expense_source[1]) && $expense_source[1] == '2' ? 'checked' : 'disabled'; ?>> มารดา</label>
                                        <label><input type="checkbox" <?php echo isset($expense_source[2]) && $expense_source[2] == '3' ? 'checked' : 'disabled'; ?>> ผู้ปกครอง</label>
                                        <label><input type="checkbox" <?php echo isset($expense_source[3]) && $expense_source[3] == '4' ? 'checked' : 'disabled'; ?>> กองทุนกู้ยืมเพื่อการศึกษา</label>
                                        <label><input type="checkbox" <?php echo isset($expense_source[4]) && $expense_source[4] == '5' ? 'checked' : 'disabled'; ?>> อื่นๆ</label>
                                    </div>
                                    <div style="display: flex; align-items: center;"><span>รวมเดือนละ</span><input type="text" class="form-control-static" style="width: 150px; margin: 0 10px;" value="<?php echo number_format((float)$expense_total); ?>" readonly><span>บาท</span></div>
                                </div>
                            </div>

                            <div id="reason" class="tab-section">
                                <div class="section-header-app">เหตุผลความจำเป็น</div>
                                <div class="content-display-gray shadow-sm border p-4" style="background: #fcfcfc;"><?php echo nl2br(htmlspecialchars($student['st_note'] ?: 'ไม่พบข้อมูลเหตุผล')); ?></div>
                            </div>

                            <div id="document" class="tab-section">
                                <div class="section-header-app">เอกสารแนบ 1: บัตรนักศึกษา/Transcript</div><?php echo renderFilePreview($student['st_doc']); ?>
                                <div class="section-header-app mt-5">เอกสารแนบ 2: รายได้/สมุดบัญชี</div><?php echo renderFilePreview($student['st_doc1']); ?>
                                <div class="section-header-app mt-5">เอกสารแนบ 3: ภาพถ่ายบ้าน</div><?php echo renderFilePreview($student['st_doc2']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mt-5 border-top pt-4"></div>
                </main>
            </div>
        </div>
    </div>

    <?php include '../../include/footer.php'; ?>

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
            const activeSec = document.getElementById(tabName);
            if (activeSec) {
                activeSec.style.display = "block";
                activeSec.classList.add("active");
            }
            evt.currentTarget.classList.replace("inactive", "active");
        }
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const menuHeader = document.querySelector('.sidebar .menu-header');
            if (menuHeader && window.innerWidth <= 1024) {
                menuHeader.addEventListener('click', () => {
                    sidebar.classList.toggle('is-open');
                });
            }
        });
    </script>
</body>

</html>