<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../../include/config.php';

$st_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($st_id <= 0) die("ไม่พบข้อมูลนักศึกษา");

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
        'status' => $p[2] ?? '1',
        'job' => $p[3] ?? '-',
        'income' => $p[4] ?? '-',
        'work' => $p[5] ?? '-',
        'tel' => $p[6] ?? '-'
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
            $siblings[] = ['name' => $parts[0], 'work' => $extra[0] ?? '-', 'income' => $extra[1] ?? '0'];
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

$hist_list = [];
if (!empty($student['st_history_detail'])) {
    foreach (explode('|-o-|', $student['st_history_detail']) as $row) {
        $hp = explode(':', $row);
        if (count($hp) >= 3) $hist_list[] = ['year' => $hp[0], 'name' => $hp[1], 'amount' => $hp[2]];
    }
}
$hist_bur = $student['st_history_bursary'];

$res_types = mysqli_query($connect1, "SELECT st_name_1, st_name_2, st_name_3, y_year FROM tb_year WHERE y_id = 1");
$d_types = mysqli_fetch_assoc($res_types);
$scholarship_name = $d_types['st_name_' . $student['st_type']] ?? 'ทุนการศึกษา';
$edu_year = $d_types['y_year'] ?? (date("Y") + 543);

function renderFileFullPage($filename, $title, $st_code)
{
    if (!$filename) return "";
    $path = "../../images/student/" . $filename;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $html = '<div class="page-container attachment-page">';
    $html .= '<div class="section-h" style="text-align:center; margin-bottom: 10px;">เอกสารแนบ: ' . $title . '</div>';

    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $html .= '<div class="image-wrapper"><img src="' . $path . '"></div>';
    } elseif ($ext == 'pdf') {
        $html .= '<div class="pdf-render-container" data-pdf-url="' . $path . '" data-title="' . $title . '" data-st-code="' . $st_code . '">
                    <div class="pdf-loading text-center py-5">กำลังโหลดเอกสาร PDF...</div>
                  </div>';
    } else {
        $html .= '<div class="pdf-placeholder">ไฟล์เอกสาร: ' . $filename . '</div>';
    }

    $html .= '<div class="student-id-footer" style="text-align:right; font-size:12px; margin-top:5px;">รหัสนักศึกษา: ' . $st_code . '</div>';
    $html .= '</div>';
    return $html;
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พิมพ์ใบสมัคร_<?php echo $student['st_code']; ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/bg/head_01.png">

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
                background: #fff;
                overflow: visible !important;
            }

            .no-print {
                display: none !important;
            }

            .page-container {
                margin: 0 !important;
                box-shadow: none !important;
                width: 210mm !important;
                min-height: 297mm !important;
                padding: 15mm 20mm !important;
                page-break-after: always !important;
                box-sizing: border-box;
                display: block !important;
            }

            .section {
                page-break-inside: avoid;
                break-inside: avoid;
                padding-top: 1cm !important;
            }

            .section:first-of-type {
                padding-top: 0 !important;
                margin-top: 0 !important;
            }

            .attachment-page {
                page-break-before: always !important;
                padding-top: 1.5cm !important;
            }

            .pdf-wrapper {
                height: auto !important;
                border: none !important;
            }

            .student-id-footer {
                margin-top: 5px !important;
            }

            canvas {
                width: 100% !important;
                height: auto !important;
                display: block;
            }
        }

        body {
            font-family: "TH Sarabun New", "TH Sarabun PSK", serif;
            font-size: 20px;
            line-height: 1.6;
            background: #eee;
            padding: 20px;
            color: #000;
            overflow-x: hidden;
        }

        .page-container {
            background: white;
            width: 210mm;
            min-height: 297mm;
            padding: 25mm 20mm;
            margin: 0 auto 1cm auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
        }

        .doc-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .psu-logo {
            width: 65px;
            position: absolute;
            top: 25mm;
            left: 20mm;
        }

        .doc-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .photo-area {
            position: absolute;
            top: 25mm;
            right: 20mm;
            width: 3cm;
            height: 4cm;
            border: 1px solid #000;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }

        .photo-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .section {
            margin-top: 40px;
            width: 100%;
            position: relative;
        }

        .section-h {
            font-weight: bold;
            font-size: 22px;
            border-bottom: 1.5px solid #000;
            margin-bottom: 10px;
            padding-bottom: 2px;
        }

        .row-data {
            margin-bottom: 6px;
            display: flex;
            align-items: baseline;
            width: 100%;
        }

        .lbl {
            font-weight: bold;
            white-space: nowrap;
            margin-right: 5px;
        }

        .val {
            border-bottom: 1px dotted #555;
            flex-grow: 1;
            padding-left: 8px;
            color: #000;
            min-height: 22px;
        }

        .schedule-table-print {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .schedule-table-print th,
        .schedule-table-print td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 14px;
        }

        .skill-row-print {
            display: flex;
            margin-bottom: 5px;
            align-items: center;
        }

        .skill-name-print {
            width: 120px;
            font-weight: bold;
        }

        .skill-val-print {
            display: flex;
            gap: 15px;
        }

        .note-box {
            border: 1px solid #999;
            padding: 15px;
            min-height: 120px;
            text-align: justify;
            margin-top: 5px;
            font-size: 20px;
            line-height: 1.8;
        }

        .footer-sign {
            margin-top: 40px;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        .sign-col {
            width: 48%;
            text-align: center;
            line-height: 2;
        }

        .cert-statement {
            text-indent: 1.5cm;
            text-align: justify;
            line-height: 2;
            margin-top: 10px;
        }

        .inline-val {
            border-bottom: 1px dotted #000;
            padding: 0 10px;
            font-weight: bold;
        }

        .image-wrapper {
            width: 100%;
            height: 230mm;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #ccc;
            overflow: hidden;
            background: #fff;
        }

        .image-wrapper img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .pdf-page-wrapper {
            margin-bottom: 10px;
            text-align: center;
            width: 100%;
        }

        canvas {
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .no-print-btn {
            position: fixed;
            top: 20px;
            right: 35px;
            background: #00468c;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            z-index: 9999;
            /* แก้ไข: เปลี่ยน font-family เป็น Prompt */
            font-family: 'Prompt';
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .no-print-btn-back {
            position: fixed;
            top: 20px;
            left: 40px;
            background: #6c757d;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            z-index: 9999;
            /* แก้ไข: เปลี่ยน font-family เป็น Prompt */
            font-family: 'Prompt';
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body>

    <button class="no-print-btn-back no-print" onclick="window.history.back();">ย้อนกลับ</button>
    <button class="no-print-btn no-print" onclick="window.focus(); window.print();">กดเพื่อสั่งพิมพ์เอกสาร</button>

    <div id="print-content">
        <div class="page-container">
            <img src="../../assets/images/bg/head_01.png" class="psu-logo">
            <div class="doc-header">
                <div class="doc-title">ใบสมัครขอรับทุนการศึกษา</div>
                <div class="doc-title"><?php echo $scholarship_name; ?></div>
                <div style="font-weight: bold; font-size: 22px;">คณะศิลปศาสตร์ มหาวิทยาลัยสงขลานครินทร์</div>
                <div>ประจำปีการศึกษา <?php echo $edu_year; ?></div>
            </div>
            <div class="photo-area">
                <img src="../../images/student/<?php echo $student['st_image']; ?>" onerror="this.src='../../assets/images/bg/no-profile.png'">
            </div>

            <?php if ($student['st_type'] == 2 && $member_data): ?>
                <div class="section" style="margin-top: 40px;">
                    <div class="section-h">ข้อมูลผู้สมัคร</div>
                    <div class="row-data">
                        <span class="lbl">ชื่อ-นามสกุล:</span>
                        <span class="val" style="flex-grow:0; width: 80px;"><?php echo ($student['st_sex'] == 1 ? 'นาย' : 'นางสาว'); ?></span>
                        <span class="val"><?php echo $member_data['name_mem'] . " " . $member_data['sur_mem']; ?></span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">หลักสูตร/สาขาวิชา:</span> <span class="val"><?php echo $student['g_program']; ?></span>
                        <span class="lbl" style="margin-left:15px;">ชั้นปีที่:</span> <span class="val" style="flex-grow:0; width: 50px; text-align:center;"><?php echo $member_data['class_mem']; ?></span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">อาจารย์ที่ปรึกษา:</span> <span class="val"><?php echo $member_data['advisor_name']; ?></span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">โทรศัพท์:</span> <span class="val"><?php echo $member_data['tel_mem']; ?></span>
                        <span class="lbl" style="margin-left:15px;">อีเมล:</span> <span class="val"><?php echo $member_data['email_mem']; ?></span>
                    </div>
                </div>

                <div class="section">
                    <div class="section-h">ข้อมูลตารางเวลาที่สามารถปฏิบัติงานได้</div>
                    <table class="schedule-table-print">
                        <thead>
                            <tr>
                                <th>วัน</th>
                                <th>ตั้งแต่เวลา</th>
                                <th>ถึงเวลา</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $days_th = [2 => 'จันทร์', 3 => 'อังคาร', 4 => 'พุธ', 5 => 'พฤหัสบดี', 6 => 'ศุกร์'];
                            foreach ($days_th as $id_day => $label):
                                $time_info = isset($schedule_list[$id_day]) ? $schedule_list[$id_day] : '';
                                $t_split = ($time_info && $time_info != '-') ? explode(' - ', $time_info) : ['', ''];
                            ?>
                                <tr>
                                    <td style="font-weight:bold;"><?php echo $label; ?></td>
                                    <td><?php echo ($t_split[0]) ? str_replace(':', '.', $t_split[0]) : '-'; ?></td>
                                    <td><?php echo (isset($t_split[1])) ? str_replace(':', '.', $t_split[1]) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="section">
                    <div class="section-h">ข้อมูลความสามารถด้านคอมพิวเตอร์</div>
                    <?php
                    $com_labels = ["Ms Word", "Ms Excel", "Canva", "PSD-AI"];
                    $opts = [1 => "ดีมาก", 2 => "ดี", 3 => "ปานกลาง", 4 => "พอใช้"];
                    foreach ($com_labels as $idx => $label):
                        $val = isset($com_skills[$idx]) ? trim($com_skills[$idx]) : '';
                    ?>
                        <div class="skill-row-print">
                            <div class="skill-name-print">- <?php echo $label; ?>:</div>
                            <div class="skill-val-print">
                                <?php foreach ($opts as $k => $v): ?>
                                    <span style="<?php echo ($val == $k) ? 'font-weight:bold; text-decoration:underline;' : 'color:#777;'; ?>">
                                        [<?php echo ($val == $k) ? 'X' : ' '; ?>] <?php echo $v; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="section">
                    <div class="section-h">ความรู้ด้านภาษาอังกฤษ</div>
                    <?php
                    $eng_labels = ["Writting", "Speaking", "Listening"];
                    foreach ($eng_labels as $idx => $label):
                        $val = isset($eng_skills[$idx]) ? trim($eng_skills[$idx]) : '';
                    ?>
                        <div class="skill-row-print">
                            <div class="skill-name-print">- <?php echo $label; ?>:</div>
                            <div class="skill-val-print">
                                <?php foreach ($opts as $k => $v): ?>
                                    <span style="<?php echo ($val == $k) ? 'font-weight:bold; text-decoration:underline;' : 'color:#777;'; ?>">
                                        [<?php echo ($val == $k) ? 'X' : ' '; ?>] <?php echo $v; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="section" style="margin-top: 40px;">
                    <div class="section-h">ส่วนที่ 1: ข้อมูลพื้นฐานนักศึกษา</div>
                    <div class="row-data">
                        <span class="lbl">ชื่อ-นามสกุล:</span> <span class="val"><?php echo $student['st_firstname'] . " " . $student['st_lastname']; ?></span>
                        <span class="lbl" style="margin-left:15px;">รหัสนักศึกษา:</span> <span class="val"><?php echo $student['st_code']; ?></span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">หลักสูตร/สาขาวิชา:</span> <span class="val"><?php echo $student['g_program']; ?></span>
                        <span class="lbl" style="margin-left:15px;">GPAX:</span> <span class="val" style="flex-grow:0; width: 70px; text-align:center;"><?php echo $student['st_score']; ?></span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">ที่อยู่ปัจจุบัน:</span> <span class="val"><?php echo $student['st_address1']; ?></span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">โทรศัพท์:</span> <span class="val"><?php echo $student['st_tel1']; ?></span>
                        <span class="lbl" style="margin-left:15px;">อีเมล:</span> <span class="val"><?php echo $student['st_email']; ?></span>
                    </div>
                </div>

                <div class="section">
                    <div class="section-h">ส่วนที่ 2: ข้อมูลครอบครัวและสถานะทางการเงิน</div>
                    <div class="row-data">
                        <span class="lbl">1. ข้อมูลบิดา ชื่อ-สกุล:</span> <span class="val"><?php echo $father['name']; ?></span>
                        <span class="lbl" style="margin-left:10px;">อายุ:</span> <span class="val" style="flex-grow:0; width: 40px;"><?php echo $father['age']; ?></span> <span class="lbl">ปี</span>
                    </div>
                    <div class="row-data" style="margin-top:-5px;">
                        <span class="lbl">สถานะ:</span> <span class="val"><?php echo ($father['status'] == '1') ? 'มีชีวิต' : 'ถึงแก่กรรม'; ?></span>
                        <span class="lbl" style="margin-left:10px;">อาชีพ:</span> <span class="val"><?php echo $father['job']; ?></span>
                        <span class="lbl" style="margin-left:10px;">รายได้:</span> <span class="val" style="flex-grow:0; width: 90px; text-align:right; margin-right: 15px;"><?php echo $father['income']; ?></span> <span class="lbl">บาท/ด.</span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">2. ข้อมูลมารดา ชื่อ-สกุล:</span> <span class="val"><?php echo $mother['name']; ?></span>
                        <span class="lbl" style="margin-left:10px;">อายุ:</span> <span class="val" style="flex-grow:0; width: 40px;"><?php echo $mother['age']; ?></span> <span class="lbl">ปี</span>
                    </div>
                    <div class="row-data" style="margin-top:-5px;">
                        <span class="lbl">สถานะ:</span> <span class="val"><?php echo ($mother['status'] == '1') ? 'มีชีวิต' : 'ถึงแก่กรรม'; ?></span>
                        <span class="lbl" style="margin-left:10px;">อาชีพ:</span> <span class="val"><?php echo $mother['job']; ?></span>
                        <span class="lbl" style="margin-left:10px;">รายได้:</span> <span class="val" style="flex-grow:0; width: 90px; text-align:right; margin-right: 15px;"><?php echo $mother['income']; ?></span> <span class="lbl">บาท/ด.</span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">3. ข้อมูลผู้ปกครอง ชื่อ-สกุล:</span> <span class="val"><?php echo (!empty($guardian['name']) && $guardian['name'] != '-') ? $guardian['name'] : '-'; ?></span>
                        <span class="lbl" style="margin-left:10px;">รายได้:</span> <span class="val" style="flex-grow:0; width: 90px; text-align:right; margin-right: 15px;"><?php echo $guardian['income']; ?></span> <span class="lbl">บาท/ด.</span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">4. สถานภาพบิดามารดา:</span>
                        <span class="val">
                            <?php
                            $st_l = [];
                            if (($parents_status_raw[0] ?? '') == '1') $st_l[] = "อยู่ด้วยกัน";
                            if (($parents_status_raw[1] ?? '') == '2') $st_l[] = "หย่าร้าง";
                            if (($parents_status_raw[2] ?? '') == '3') $st_l[] = "แยกกันอยู่";
                            if (($parents_status_raw[3] ?? '') == '4') $st_l[] = "บิดาเสียชีวิต";
                            if (($parents_status_raw[4] ?? '') == '5') $st_l[] = "มารดาเสียชีวิต";
                            echo count($st_l) > 0 ? implode(', ', $st_l) : "-";
                            ?>
                        </span>
                    </div>
                    <div style="margin-top: 5px;"><span class="lbl">5. จำนวนพี่น้องร่วมบิดามารดา:</span></div>
                    <?php if (count($siblings) > 0): foreach ($siblings as $idx => $sib): ?>
                            <div class="row-data" style="padding-left: 20px;">
                                <span class="lbl"><?php echo ($idx + 1); ?>. ชื่อ-สกุล:</span> <span class="val" style="flex:2;"><?php echo $sib['name']; ?></span>
                                <span class="lbl" style="margin-left:10px;">รายได้:</span> <span class="val" style="flex:1; text-align:right; margin-right: 15px;"><?php echo number_format((float)$sib['income']); ?></span> <span class="lbl">บาท</span>
                            </div>
                    <?php endforeach;
                    else: echo "<div style='padding-left:20px;'>- ไม่มีข้อมูลพี่น้อง -</div>";
                    endif; ?>
                    <div class="row-data">
                        <span class="lbl">6. กู้ยืมกองทุน กยศ./กรอ.:</span> <span class="val"><?php echo ($loan[0] == '1') ? "กู้ยืม (" . $loan[2] . ")" : "ไม่ได้กู้ยืม"; ?></span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">7. ได้รับค่าครองชีพรวมเดือนละ:</span> <span class="val" style="flex-grow:0; width: 100px; text-align:right; margin-right: 15px;"><?php echo number_format((float)$expense_total); ?></span> <span class="lbl">บาท</span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">8-9. ประวัติงานพิเศษ:</span> <span class="val"><?php echo ($work_past[0] == '1') ? "เคย (ประเภท: " . $work_past[2] . ")" : "ไม่เคย"; ?></span>
                        <span class="lbl" style="margin-left:10px;">ปัจจุบัน:</span> <span class="val"><?php echo ($work_now[0] == '1') ? "ทำอยู่ (" . $work_now[2] . ")" : "ไม่ทำ"; ?></span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">10-11. ปัญหาการเงิน:</span> <span class="val"><?php echo ($finance_prob[0] == '1') ? "บ่อย" : "ไม่บ่อย"; ?></span>
                        <span class="lbl" style="margin-left:10px;">วิธีแก้ไข:</span>
                        <span class="val">
                            <?php
                            $sol = [];
                            if (($finance_solu[0] ?? '') == '1') $sol[] = "กู้";
                            if (($finance_solu[2] ?? '') == '3') $sol[] = "ญาติ";
                            if (($finance_solu[4] ?? '') == '5') $sol[] = "อื่นๆ";
                            echo count($sol) > 0 ? implode(',', $sol) : "-";
                            ?>
                        </span>
                    </div>
                    <div class="row-data">
                        <span class="lbl">12. ประวัติทุนการศึกษา:</span> <span class="val"><?php echo ($hist_bur == '1') ? "เคยได้รับ" : "ไม่เคย"; ?></span>
                    </div>
                </div>

                <div class="section">
                    <div class="section-h">ส่วนที่ 3: เหตุผลความจำเป็นในการขอรับทุนการศึกษา</div>
                    <div class="note-box"><?php echo nl2br(htmlspecialchars($student['st_note'])); ?></div>
                </div>

                <div class="section" style="page-break-inside: avoid;">
                    <div class="section-h">ส่วนที่ 4: หนังสือรับรองและลายเซ็น</div>

                    <div class="cert-statement">
                        ข้าพเจ้า <span class="inline-val"><?php echo !empty($student['tc_name']) ? $student['tc_name'] : ".........................................................."; ?></span>
                        ในฐานะอาจารย์ที่ปรึกษาของผู้ขอรับทุนการศึกษา ขอรับรองว่า
                        <span class="inline-val"><?php echo $student['st_firstname'] . " " . $student['st_lastname']; ?></span>
                        รหัสนักศึกษา <span class="inline-val"><?php echo $student['st_code']; ?></span>
                        สาขาวิชา <span class="inline-val"><?php echo $student['g_program']; ?></span>
                        ได้รับคะแนนเฉลี่ยสะสม <span class="inline-val"><?php echo $student['st_score']; ?></span>
                        เป็นผู้ที่มีความประพฤติดี ขาดแคลนทุนทรัพย์ ตามข้อมูลที่ได้แสดงไว้ในใบสมัครทุกประการ และเป็นบุคคลที่สมควรได้รับทุนการศึกษานี้
                    </div>
                    <div class="footer-sign" style="margin-top: 40px; margin-bottom: 20px;">
                        <div class="sign-col">ลงชื่อ..........................................................<br>(<?php echo $student['st_firstname'] . " " . $student['st_lastname']; ?>)<br>ผู้สมัคร</div>
                        <div class="sign-col">ลงชื่อ..........................................................<br>(<?php echo $student['tc_name']; ?>)<br>อาจารย์ที่ปรึกษา</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($student['st_type'] != 2): ?>
            <?php
            echo renderFileFullPage($student['st_doc'], "1. สำเนาบัตรนักศึกษา", $student['st_code']);
            echo renderFileFullPage($student['st_doc1'], "2. สำเนาใบแสดงผลการศึกษา", $student['st_code']);
            echo renderFileFullPage($student['st_doc2'], "3. สำเนาใบแสดงผลกิจกรรม", $student['st_code']);
            ?>
        <?php endif; ?>
    </div>

    <script>
        async function runPDFProcess() {
            const containers = document.querySelectorAll('.pdf-render-container');
            for (const container of containers) {
                const url = container.getAttribute('data-pdf-url');
                const title = container.getAttribute('data-title');
                const stCode = container.getAttribute('data-st-code');
                const parentPage = container.closest('.page-container');

                try {
                    const loadingTask = pdfjsLib.getDocument(url);
                    const pdf = await loadingTask.promise;
                    container.innerHTML = '';

                    let lastInserted = parentPage;

                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const page = await pdf.getPage(pageNum);
                        const canvas = document.createElement('canvas');
                        const viewport = page.getViewport({
                            scale: 2
                        });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        await page.render({
                            canvasContext: canvas.getContext('2d'),
                            viewport: viewport
                        }).promise;

                        if (pageNum === 1) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'pdf-page-wrapper';
                            wrapper.appendChild(canvas);
                            container.appendChild(wrapper);
                        } else {
                            const newContainer = document.createElement('div');
                            newContainer.className = 'page-container attachment-page';
                            newContainer.innerHTML = `
                                <div class="section-h" style="text-align:center; margin-bottom: 10px;">เอกสารแนบ: ${title} (หน้า ${pageNum})</div>
                                <div class="pdf-page-wrapper"></div>
                                <div class="student-id-footer" style="text-align:right; font-size:12px; margin-top:15px; font-weight:bold;">รหัสนักศึกษา: ${stCode}</div>
                            `;
                            newContainer.querySelector('.pdf-page-wrapper').appendChild(canvas);
                            lastInserted.parentNode.insertBefore(newContainer, lastInserted.nextSibling);
                            lastInserted = newContainer;
                        }
                    }
                } catch (e) {
                    container.innerHTML = '<div style="color:red; padding:20px; text-align:center;">ไม่สามารถโหลด PDF ได้</div>';
                }
            }
        }
        window.addEventListener('load', runPDFProcess);
    </script>
</body>

</html>