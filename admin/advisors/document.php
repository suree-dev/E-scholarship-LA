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
$cert_data = [];
$documents = [];

if ($student_id > 0) {
    $sql = "SELECT s.*, p.g_program, t.tc_name AS advisor_name FROM tb_student AS s
            LEFT JOIN tb_program AS p ON s.st_program = p.g_id
            LEFT JOIN tb_teacher AS t ON s.id_teacher = t.tc_id
            WHERE s.st_id = '$student_id'";
    $result = mysqli_query($connect1, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $student_data = mysqli_fetch_assoc($result);
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

        $cert_data = [
            'advisor_name' => $student['advisor'],
            'student_name' => $student['prefix'] . $student['firstname'] . ' ' . $student['lastname'],
            'student_id'   => $student['id'],
            'major'        => $student['major'],
            'gpa'          => $student['gpa']
        ];

        $doc_path = '../../images/student/';
        $documents = [
            'doc1' => !empty($student_data['st_doc']) ? $doc_path . $student_data['st_doc'] : null,
            'doc2' => !empty($student_data['st_doc1']) ? $doc_path . $student_data['st_doc1'] : null,
            'doc3' => !empty($student_data['st_doc2']) ? $doc_path . $student_data['st_doc2'] : null,
        ];
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
    <title>ใบสมัคร - หนังสือรับรองและเอกสารแนบ</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/bg/head_01.png">

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .btn-pdf-download {
            background-color: #a5a5a5;
            color: #ffffff !important;
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none !important;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(92, 92, 92, 0.2);
            border: none;
        }

        .btn-pdf-download:hover {
            background-color: #929292;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(70, 70, 70, 0.3);
        }

        .btn-pdf-download i {
            font-size: 1.1rem;
        }

        .cert-text-paragraph {
            line-height: 2.5;
            text-align: justify;
        }

        .inline-readonly-field {
            display: inline-block;
            border-bottom: 1px dotted #666;
            text-align: center;
            font-weight: 600;
            color: #003b6f;
            padding: 0 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: bottom;
        }
    </style>
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
                <li class="nav-item"><a href="../advisors/family.php?student_id=<?php echo $student_id; ?>" class="nav-link-custom inactive">ข้อมูลครอบครัว</a></li>
                <li class="nav-item"><a href="../advisors/reasons.php?student_id=<?php echo $student_id; ?>" class="nav-link-custom inactive">ระบุเหตุผลการขอทุน</a></li>
                <li class="nav-item"><a href="../advisors/document.php?student_id=<?php echo $student_id; ?>" class="nav-link-custom active">หนังสือรับรองและเอกสารแนบ</a></li>
            </ul>

            <div class="cert-container px-2">
                <div class="cert-title-h">หนังสือรับรองการขอรับทุนการศึกษาของคณะศิลปศาสตร์</div>
                <div class="cert-text-paragraph">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ข้าพเจ้า
                    <span class="inline-readonly-field" style="width: 40%;"><?php echo htmlspecialchars($cert_data['advisor_name']); ?></span>
                    ในฐานะอาจารย์ที่ปรึกษาของผู้ขอรับทุนการศึกษา ขอรับรองว่า
                    <span class="inline-readonly-field" style="width: 40%;"><?php echo htmlspecialchars($cert_data['student_name']); ?></span>
                    รหัสนักศึกษา
                    <span class="inline-readonly-field" style="width: 15%;"><?php echo htmlspecialchars($cert_data['student_id']); ?></span>
                    สาขาวิชา
                    <span class="inline-readonly-field" style="width: 45%;"><?php echo htmlspecialchars($cert_data['major']); ?></span>
                    ได้รับคะแนนเฉลี่ยสะสม
                    <span class="inline-readonly-field" style="width: 10%;"><?php echo htmlspecialchars($cert_data['gpa']); ?></span>
                    เป็นผู้ที่มีความประพฤติดี ขาดแคลนทุนทรัพย์ ตามข้อมูลที่ได้แสดงไว้ในใบสมัครทุกประการ และเป็นบุคคลที่สมควรได้รับทุนการศึกษานี้
                </div>

                <div class="section-header-app mt-5">เอกสารแนบประกอบการพิจารณา</div>
                <div class="doc-list-wrapper mt-3">
                    <div class="doc-download-row d-flex justify-content-between align-items-center p-3 border-bottom">
                        <span class="fw-medium">1. สำเนาบัตรประจำตัวนักศึกษา/ประชาชน และใบแสดงผลการเรียน</span>
                        <?php if ($documents['doc1']): ?>
                            <a href="<?php echo htmlspecialchars($documents['doc1']); ?>" target="_blank" class="btn-pdf-download"><i class="fa-solid fa-file-pdf"></i> DOWNLOAD PDF</a>
                        <?php else: ?><span class="text-muted small italic">- ไม่ได้แนบไฟล์ -</span><?php endif; ?>
                    </div>
                    <div class="doc-download-row d-flex justify-content-between align-items-center p-3 border-bottom">
                        <span class="fw-medium">2. เอกสารรับรองรายได้ และสำเนาสมุดบัญชีธนาคาร</span>
                        <?php if ($documents['doc2']): ?>
                            <a href="<?php echo htmlspecialchars($documents['doc2']); ?>" target="_blank" class="btn-pdf-download"><i class="fa-solid fa-file-pdf"></i> DOWNLOAD PDF</a>
                        <?php else: ?><span class="text-muted small italic">- ไม่ได้แนบไฟล์ -</span><?php endif; ?>
                    </div>
                    <div class="doc-download-row d-flex justify-content-between align-items-center p-3">
                        <span class="fw-medium">3. ภาพถ่ายบ้านพักนักศึกษา</span>
                        <?php if ($documents['doc3']): ?>
                            <a href="<?php echo htmlspecialchars($documents['doc3']); ?>" target="_blank" class="btn-pdf-download"><i class="fa-solid fa-file-pdf"></i> DOWNLOAD PDF</a>
                        <?php else: ?><span class="text-muted small italic">- ไม่ได้แนบไฟล์ -</span><?php endif; ?>
                    </div>
                </div>
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

    <?php include '../../include/footer.php'; ?>

    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

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