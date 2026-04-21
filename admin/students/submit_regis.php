<?php
session_start();
include '../../include/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../student/regis.php');
    exit();
}

$st_sex = ($_POST['title'] == 'นาย') ? 1 : 2;
$st_firstname = mysqli_real_escape_string($connect1, $_POST['firstname']);
$st_lastname = mysqli_real_escape_string($connect1, $_POST['lastname']);
$st_score = mysqli_real_escape_string($connect1, $_POST['gpa'] ?? '0.00');

$st_code_session = mysqli_real_escape_string($connect1, $_SESSION['student_id']);

$st_program = (int)($_POST['major_t1'] ?? $_POST['major_t2'] ?? 0);

$st_email = mysqli_real_escape_string($connect1, $_POST['email_t1'] ?? $_POST['email_mem'] ?? '');

$st_pass = mysqli_real_escape_string($connect1, $_POST['password'] ?? '');
$st_type = (int)$_POST['scholarship_type'];

$new_image_filename = null;
$upload_dir = '../../images/student/';

if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $new_image_filename = time() . "_" . uniqid() . "." . $file_extension;
    $target_path = $upload_dir . $new_image_filename;

    if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
        $_SESSION['regis_error'] = "ไม่สามารถย้ายไฟล์ไปยังโฟลเดอร์เป้าหมายได้";
        header('Location: ../../student/regis.php');
        exit();
    }
} else {
    if ($st_type != 2) {
        $_SESSION['regis_error'] = "กรุณาแนบไฟล์ภาพประจำตัว";
        header('Location: ../../student/regis.php');
        exit();
    }
}


if ($st_type == 2) {

    $class_mem = (int)($_POST['class_mem'] ?? 0);
    $tea_mem = (int)($_POST['tea_mem'] ?? 0);
    $tel_mem = mysqli_real_escape_string($connect1, $_POST['tel_mem'] ?? '');

    $com_skills = [];
    for ($i = 0; $i < 4; $i++) {
        $com_skills[] = mysqli_real_escape_string($connect1, $_POST['com_skill_' . $i] ?? '');
    }
    $com_mem = implode('|o|', $com_skills);

    $eng_skills = [];
    for ($i = 0; $i < 3; $i++) {
        $eng_skills[] = mysqli_real_escape_string($connect1, $_POST['eng_skill_' . $i] ?? '');
    }
    $eng_mem = implode('|o|', $eng_skills);

    $sql_member = "INSERT INTO tb_member (
                        no_mem, name_mem, sur_mem, programe, class_mem, 
                        tea_mem, tel_mem, email_mem, com_mem, eng_mem, date_mem
                    ) VALUES (
                        '$st_type', '$st_firstname', '$st_lastname', '$st_program', '$class_mem',
                        '$tea_mem', '$tel_mem', '$st_email', '$com_mem', '$eng_mem', NOW()
                    )";

    if (mysqli_query($connect1, $sql_member)) {
        $id_mem = mysqli_insert_id($connect1);

        if (isset($_POST['start_time']) && isset($_POST['end_time'])) {
            foreach ($_POST['start_time'] as $day_id => $start_val) {
                $end_val = $_POST['end_time'][$day_id] ?? '';
                if (!empty($start_val) && !empty($end_val)) {
                    $day_id_esc = mysqli_real_escape_string($connect1, $day_id);
                    $time_range = mysqli_real_escape_string($connect1, $start_val . " - " . $end_val);
                    $sql_date = "INSERT INTO tb_mem_date (date_date, date_time, id_mem) 
                                 VALUES ('$day_id_esc', '$time_range', '$id_mem')";
                    mysqli_query($connect1, $sql_date);
                }
            }
        }

        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500&display=swap' rel='stylesheet'>
            <style>* { font-family: 'Prompt', sans-serif; }</style>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: 'สำเร็จ!',
                    text: 'ส่งใบสมัครเสร็จเรียบร้อยแล้ว',
                    icon: 'success',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#003c71',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../../student/regis.php';
                    }
                });
            </script>
        </body>
        </html>";
        exit();
    } else {
        $_SESSION['regis_error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูลสมาชิก: " . mysqli_error($connect1);
        header('Location: ../../student/regis.php');
        exit();
    }
} else if ($st_type == 1 || $st_type == 3) {

    $sql = "UPDATE tb_student SET 
                st_sex = '$st_sex', 
                st_firstname = '$st_firstname', 
                st_lastname = '$st_lastname', 
                st_score = '$st_score', 
                st_program = '$st_program', 
                st_email = '$st_email', 
                st_image = '$new_image_filename',  
                st_type = '$st_type', 
                st_date_regis = NOW(), 
                st_confirm = 0 
            WHERE st_code = '$st_code_session'";

    if (mysqli_query($connect1, $sql)) {
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500&display=swap' rel='stylesheet'>
            <style>* { font-family: 'Prompt', sans-serif; }</style>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: 'สำเร็จ!',
                    text: 'ส่งใบสมัครเรียบร้อยแล้ว ท่านสามารถกรอกรายละเอียดขอรับทุนได้ทันที',
                    icon: 'success',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#003c71',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../../student/apply_form.php';
                    }
                });
            </script>
        </body>
        </html>";
        exit();
    } else {
        if ($new_image_filename && file_exists($upload_dir . $new_image_filename)) {
            unlink($upload_dir . $new_image_filename);
        }
        $_SESSION['regis_error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูลนักศึกษา: " . mysqli_error($connect1);
        header('Location: ../../student/regis.php');
        exit();
    }
}
