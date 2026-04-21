<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../include/config.php';
$get_student_id = isset($_GET['student_id']) ? mysqli_real_escape_string($connect1, $_GET['student_id']) : '';

$sql = "SELECT s.*, p.g_program, t.tc_name, y.y_year, y.st_name_1, y.st_name_2, y.st_name_3 
        FROM tb_student s
        LEFT JOIN tb_program p ON s.st_program = p.g_id
        LEFT JOIN tb_teacher t ON s.id_teacher = t.tc_id
        LEFT JOIN tb_year y ON y.y_id = 1
        WHERE s.st_id = '$get_student_id' OR s.st_code = '$get_student_id' LIMIT 1";

$result = mysqli_query($connect1, $sql);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("<script>alert('ไม่พบข้อมูลนักศึกษาในระบบ'); window.close();</script>");
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

function renderFileFullPage($filename, $title)
{
    if (!$filename) return "";
    $path = "../images/student/" . $filename;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $html = '<div class="page-container attachment-page">';
    $html .= '<div class="section-h" style="text-align:center; margin-bottom: 10px;">เอกสารแนบ: ' . $title . '</div>';

    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $html .= '<div class="image-wrapper"><img src="' . $path . '"></div>';
    } elseif ($ext == 'pdf') {
        $html .= '<div class="pdf-render-container" data-pdf-url="' . $path . '">
                    <div class="pdf-loading text-center py-5">กำลังโหลดเอกสาร PDF...</div>
                  </div>';
    } else {
        $html .= '<div class="pdf-placeholder">ไม่สามารถพรีวิวไฟล์: ' . $filename . '</div>';
    }

    $html .= '<div class="student-id-footer no-print" style="text-align:right; font-size:12px; margin-top:5px;">รหัสนักศึกษา: ' . $GLOBALS['student']['st_code'] . '</div>';
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
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/bg/head_01.png">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">

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
                padding: 20mm 20mm !important;
                page-break-after: always;
                box-sizing: border-box;
                display: block !important;
            }

            .section-page-break {
                page-break-before: always !important;
                padding-top: 1.5cm !important;
            }
        }

        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 16px;
            line-height: 1.6;
            background: #eee;
            padding: 20px;
            color: #000;
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
            font-size: 20px;
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
            margin-top: 35px;
            width: 100%;
            position: relative;
        }

        .section-h {
            font-weight: bold;
            font-size: 18px;
            border-bottom: 1.5px solid #000;
            margin-bottom: 12px;
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
            min-width: 120px;
        }

        .lbl-short {
            min-width: 60px;
        }

        .lbl-mid {
            min-width: 150px;
        }

        .val {
            border-bottom: 1px dotted #555;
            flex-grow: 1;
            padding-left: 8px;
            color: #000;
            min-height: 22px;
        }

        .note-box {
            border: 1px solid #999;
            padding: 15px;
            min-height: 150px;
            text-align: justify;
            margin-top: 5px;
            font-size: 16px;
            line-height: 1.8;
            background: #fafafa;
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
            line-height: 2.2;
        }

        .cert-statement {
            text-indent: 1.5cm;
            text-align: justify;
            line-height: 2.2;
            margin-top: 10px;
        }

        .inline-val {
            border-bottom: 1px dotted #000;
            padding: 0 10px;
            font-weight: bold;
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
            font-family: 'Sarabun';
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
            font-family: 'Sarabun';
            font-weight: bold;
        }
    </style>
</head>

<body>

    <button class="no-print-btn-back no-print" onclick="window.close();">ย้อนกลับ</button>
    <button id="btnPrint" class="no-print-btn no-print" onclick="window.print();">สั่งพิมพ์ใบสมัคร</button>

    <div class="page-container">
        <img src="../assets/images/bg/head_01.png" class="psu-logo">
        <div class="doc-header">
            <div class="doc-title">ใบสมัครขอรับทุนการศึกษา</div>
            <div class="doc-title"><?php echo $scholarship_name; ?></div>
            <div style="font-weight: bold; font-size: 18px;">คณะศิลปศาสตร์ มหาวิทยาลัยสงขลานครินทร์</div>
            <div>ประจำปีการศึกษา <?php echo $edu_year; ?></div>
        </div>
        <div class="photo-area">
            <img src="../images/student/<?php echo $student['st_image']; ?>" onerror="this.src='../assets/images/bg/no-profile.png'">
        </div>

        <div class="section" style="margin-top: 45px;">
            <div class="section-h">ส่วนที่ 1: ข้อมูลพื้นฐานนักศึกษา</div>
            <div class="row-data">
                <span class="lbl">ชื่อ-นามสกุล:</span> <span class="val"><?php echo $student['st_firstname'] . " " . $student['st_lastname']; ?></span>
                <span class="lbl lbl-short" style="margin-left:15px;">รหัส:</span> <span class="val" style="flex-grow:0; width: 140px;"><?php echo $student['st_code']; ?></span>
            </div>
            <div class="row-data">
                <span class="lbl">สาขาวิชา:</span> <span class="val"><?php echo $student['g_program']; ?></span>
                <span class="lbl lbl-short" style="margin-left:15px;">GPAX:</span> <span class="val" style="flex-grow:0; width: 80px; text-align:center;"><?php echo $student['st_score']; ?></span>
            </div>
            <div class="row-data">
                <span class="lbl">ที่อยู่ปัจจุบัน:</span> <span class="val"><?php echo $student['st_address1']; ?></span>
            </div>
            <div class="row-data">
                <span class="lbl">โทรศัพท์:</span> <span class="val" style="flex-grow:0; width: 180px;"><?php echo $student['st_tel1']; ?></span>
                <span class="lbl lbl-short" style="margin-left:15px;">อีเมล:</span> <span class="val"><?php echo $student['st_email']; ?></span>
            </div>
        </div>

        <div class="section">
            <div class="section-h">ส่วนที่ 2: ข้อมูลครอบครัวและสถานะทางการเงิน</div>
            <div class="row-data">
                <span class="lbl">ชื่อ-สกุล บิดา:</span> <span class="val"><?php echo $father['name']; ?></span>
                <span class="lbl lbl-short" style="margin-left:10px;">อายุ:</span> <span class="val" style="flex-grow:0; width: 40px; text-align:center;"><?php echo $father['age']; ?></span> <span style="margin-left:5px;">ปี</span>
            </div>
            <div class="row-data" style="margin-top:-2px;">
                <span class="lbl lbl-short">สถานะ:</span> <span class="val" style="flex-grow:0; width: 100px;"><?php echo ($father['status'] == '1') ? 'มีชีวิต' : 'ถึงแก่กรรม'; ?></span>
                <span class="lbl lbl-short" style="margin-left:10px;">อาชีพ:</span> <span class="val"><?php echo $father['job']; ?></span>
                <span class="lbl lbl-short" style="margin-left:10px;">รายได้:</span> <span class="val" style="flex-grow:0; width: 100px; text-align:right;"><?php echo number_format((float)$father['income']); ?></span> <span style="margin-left:5px;">บาท/ด.</span>
            </div>

            <div class="row-data" style="margin-top:5px;">
                <span class="lbl">ชื่อ-สกุล มารดา:</span> <span class="val"><?php echo $mother['name']; ?></span>
                <span class="lbl lbl-short" style="margin-left:10px;">อายุ:</span> <span class="val" style="flex-grow:0; width: 40px; text-align:center;"><?php echo $mother['age']; ?></span> <span style="margin-left:5px;">ปี</span>
            </div>
            <div class="row-data" style="margin-top:-2px;">
                <span class="lbl lbl-short">สถานะ:</span> <span class="val" style="flex-grow:0; width: 100px;"><?php echo ($mother['status'] == '1') ? 'มีชีวิต' : 'ถึงแก่กรรม'; ?></span>
                <span class="lbl lbl-short" style="margin-left:10px;">อาชีพ:</span> <span class="val"><?php echo $mother['job']; ?></span>
                <span class="lbl lbl-short" style="margin-left:10px;">รายได้:</span> <span class="val" style="flex-grow:0; width: 100px; text-align:right;"><?php echo number_format((float)$mother['income']); ?></span> <span style="margin-left:5px;">บาท/ด.</span>
            </div>

            <div class="row-data" style="margin-top:5px;">
                <span class="lbl">ผู้ปกครอง:</span> <span class="val"><?php echo (!empty($guardian['name']) && $guardian['name'] != '-') ? $guardian['name'] : '-'; ?></span>
                <span class="lbl lbl-short" style="margin-left:10px;">รายได้:</span> <span class="val" style="flex-grow:0; width: 100px; text-align:right;"><?php echo number_format((float)$guardian['income']); ?></span> <span style="margin-left:5px;">บาท/ด.</span>
            </div>
            <div class="row-data">
                <span class="lbl">สถานภาพครอบครัว:</span>
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
            <div style="margin-top: 8px; font-weight: bold;">จำนวนพี่น้องร่วมบิดามารดา:</div>
            <?php if (count($siblings) > 0): foreach ($siblings as $idx => $sib): ?>
                    <div class="row-data" style="padding-left: 20px;">
                        <span style="min-width: 25px;"><?php echo ($idx + 1); ?>.</span>
                        <span class="lbl lbl-short">ชื่อ:</span> <span class="val"><?php echo $sib['name']; ?></span>
                        <span class="lbl lbl-short" style="margin-left:10px;">รายได้:</span> <span class="val" style="flex-grow:0; width: 100px; text-align:right;"><?php echo number_format((float)$sib['income']); ?></span> <span style="margin-left:5px;">บาท</span>
                    </div>
            <?php endforeach;
            else: echo "<div style='padding-left:20px;'>- ไม่มีข้อมูลพี่น้อง -</div>";
            endif; ?>
            <div class="row-data" style="margin-top: 5px;">
                <span class="lbl lbl-mid">กู้ยืมกองทุน กยศ./กรอ.:</span> <span class="val"><?php echo ($loan[0] == '1') ? "กู้ยืม (" . $loan[2] . ")" : "ไม่ได้กู้ยืม"; ?></span>
            </div>
            <div class="row-data">
                <span class="lbl lbl-mid">ค่าครองชีพที่ได้รับต่อเดือน:</span> <span class="val" style="flex-grow:0; width: 120px; text-align:right;"><?php echo number_format((float)$expense_total); ?></span> <span style="margin-left:10px;">บาท</span>
            </div>
            <div class="row-data">
                <span class="lbl lbl-mid">ประวัติงานพิเศษ:</span> <span class="val"><?php echo ($work_past[0] == '1') ? "เคย (ประเภท: " . $work_past[2] . ")" : "ไม่เคย"; ?></span>
                <span class="lbl lbl-short" style="margin-left:10px;">ปัจจุบัน:</span> <span class="val"><?php echo ($work_now[0] == '1') ? "ทำอยู่ (" . $work_now[2] . ")" : "ไม่ทำ"; ?></span>
            </div>
            <div class="row-data">
                <span class="lbl lbl-mid">ปัญหาการเงิน:</span> <span class="val"><?php echo ($finance_prob[0] == '1') ? "บ่อย" : "ไม่บ่อย"; ?></span>
                <span class="lbl lbl-short" style="margin-left:10px;">วิธีแก้ไข:</span>
                <span class="val">
                    <?php
                    $sol = [];
                    if (($finance_solu[0] ?? '') == '1') $sol[] = "กู้ยืม";
                    if (($finance_solu[2] ?? '') == '3') $sol[] = "ญาติช่วยเหลือ";
                    if (($finance_solu[4] ?? '') == '5') $sol[] = "อื่นๆ";
                    echo count($sol) > 0 ? implode(', ', $sol) : "-";
                    ?>
                </span>
            </div>
        </div>

        <!-- หน้า 2 -->
        <div class="section section-page-break">
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
            <div class="row-data" style="margin-top: 15px;">
                <span class="lbl" style="min-width: 180px;">ความเห็นของอาจารย์ที่ปรึกษา:</span>
                <span class="val"></span>
            </div>
            <div class="row-data"><span class="val"></span></div>
            <div class="row-data"><span class="val"></span></div>
            <div class="row-data"><span class="val"></span></div>
            <div class="row-data"><span class="val"></span></div>
            <div class="footer-sign" style="margin-top: 40px; margin-bottom: 20px;">
                <div class="sign-col">ลงชื่อ..........................................................<br>(<?php echo $student['st_firstname'] . " " . $student['st_lastname']; ?>)<br>ผู้สมัคร</div>
                <div class="sign-col">ลงชื่อ..........................................................<br>(<?php echo $student['tc_name']; ?>)<br>อาจารย์ที่ปรึกษา</div>
            </div>
        </div>
    </div>

</body>

</html>