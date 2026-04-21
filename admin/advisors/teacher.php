<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../../include/config.php';

$committee_id = isset($_SESSION['id_teacher']) ? $_SESSION['id_teacher'] : (isset($_SESSION['committee_id']) ? $_SESSION['committee_id'] : 0);

$scholarship_types = [];
$sql_types = "SELECT st_name_1, st_1, st_name_2, st_2, st_name_3, st_3 FROM tb_year WHERE y_id = 1";
$result_types = mysqli_query($connect1, $sql_types);
if ($result_types && mysqli_num_rows($result_types) > 0) {
    $data_types = mysqli_fetch_assoc($result_types);
    // ตรวจสอบว่าชื่อทุนไม่ว่าง และสถานะเป็น 0 (เปิดใช้งาน)
    if (!empty($data_types['st_name_1']) && $data_types['st_1'] == 0) $scholarship_types[1] = $data_types['st_name_1'];

    if (!empty($data_types['st_name_3']) && $data_types['st_3'] == 0) $scholarship_types[3] = $data_types['st_name_3'];
}

$selected_scholarship_id = isset($_GET['type']) ? (int)$_GET['type'] : (array_key_first($scholarship_types) ?? 0);

if ($selected_scholarship_id > 0) {
    $sql_sync = "UPDATE tb_student s
                 SET 
                    s.sum_score = (SELECT SUM(scores) FROM tb_scores WHERE st_id = s.st_id),
                    s.st_average = (SELECT AVG(scores) FROM tb_scores WHERE st_id = s.st_id)
                 WHERE s.st_type = '$selected_scholarship_id' 
                 AND s.st_activate = 1
                 AND EXISTS (SELECT 1 FROM tb_scores WHERE st_id = s.st_id)";
    mysqli_query($connect1, $sql_sync);
}

$students = [];
if ($selected_scholarship_id > 0) {
    $sql_students = "SELECT s.st_id, s.st_firstname, s.st_lastname, sc.sco_id 
                 FROM tb_student AS s
                 LEFT JOIN tb_scores AS sc ON s.st_id = sc.st_id AND sc.tc_id = '$committee_id'
                 WHERE s.st_type = '$selected_scholarship_id' AND s.st_confirm = 1 
                 ORDER BY s.st_firstname ASC";
    $result_students = mysqli_query($connect1, $sql_students);
    if ($result_students) {
        while ($row = mysqli_fetch_assoc($result_students)) {
            $students[] = [
                'st_id' => $row['st_id'],
                'st_firstname' => $row['st_firstname'],
                'st_lastname' => $row['st_lastname'],
                'is_scored' => ($row['sco_id'] !== null) ? 1 : 0
            ];
        }
    }
}

$thai_months = [1 => "มกราคม", 2 => "กุมภาพันธ์", 3 => "มีนาคม", 4 => "เมษายน", 5 => "พฤษภาคม", 6 => "มิถุนายน", 7 => "กรกฎาคม", 8 => "สิงหาคม", 9 => "กันยายน", 10 => "ตุลาคม", 11 => "พฤศจิกายน", 12 => "ธันวาคม"];
$current_month_th = $thai_months[(int)date("n")];
$current_year_th = date("Y") + 543;
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าสำหรับคณะกรรมการ - PSU E-Scholarship</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/bg/head_01.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-light">

    <div class="sticky-header-wrapper">
        <?php include('../../include/navbar.php'); ?>
        <?php include('../../include/status_bar.php'); ?>
    </div>

    <div class="teacher-page-wrapper">
        <div class="teacher-centered-card shadow-sm">

            <div class="mb-4">
                <h5 class="teacher-title-h">การสอบสัมภาษณ์ทุนการศึกษา</h5>
                <h5 class="teacher-title-h">คณะศิลปศาสตร์ มหาวิทยาลัยสงขลานครินทร์</h5>
                <h6 class="teacher-title-h mt-2">วันที่ <?php echo date("j"); ?> <?php echo $current_month_th; ?> <?php echo $current_year_th; ?></h6>
            </div>

            <p class="fw-bold mt-4" style="font-size: 17px; color: #333;">ยินดีต้อนรับ คณะกรรมการสอบสัมภาษณ์</p>

            <form action="teacher.php" method="get" id="scholarship-form">
                <select name="type" class="form-select form-select-sm teacher-dropdown-box w-100 w-md-50 mx-auto" onchange="this.form.submit();">
                    <?php if (!empty($scholarship_types)): ?>
                        <?php foreach ($scholarship_types as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php if ($id == $selected_scholarship_id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled selected>ไม่มีทุนที่เปิดรับสมัครในขณะนี้</option>
                    <?php endif; ?>
                </select>
            </form>

            <!-- Cards Grid -->
            <div class="student-card-grid mt-4">
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $index => $student): ?>
                        <a href="give_score.php?student_id=<?php echo $student['st_id']; ?>"
                            class="student-card-box <?php echo $student['is_scored'] ? 'scored' : 'not-scored'; ?>">

                            <i class="fa-solid fa-user"></i>
                            <span class="label-text">ลำดับที่ <?php echo $index + 1; ?></span>
                            <span class="name-text">
                                <?php echo htmlspecialchars($student['st_firstname'] . ' ' . $student['st_lastname']); ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="py-5 w-100">
                        <p class="text-muted">ไม่พบรายชื่อนักศึกษาในหมวดหมู่นี้</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-4 pt-3 border-top">
                <p class="summary-footer-text">
                    นักศึกษาทั้งหมด <?php echo count($students); ?> คน
                    <span class="text-success fw-bold ms-1">(ประเมินแล้ว <?php echo array_sum(array_column($students, 'is_scored')); ?> คน)</span>
                </p>
            </div>

        </div>
    </div>

    <?php include '../../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>