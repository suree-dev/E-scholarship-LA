<?php
session_start();
include '../../include/config.php';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !empty($_POST['student_code']) &&
    !empty($_POST['date_start']) &&
    !empty($_POST['date_end'])
) {

    $student_code = mysqli_real_escape_string($connect1, $_POST['student_code']);
    $date_start = mysqli_real_escape_string($connect1, $_POST['date_start']);
    $date_end = mysqli_real_escape_string($connect1, $_POST['date_end']);
    $date_ban = date("Y-m-d");

    $sql = "INSERT INTO tb_ban (code_student, date_start, date_end, date_ban) 
            VALUES ('$student_code', '$date_start', '$date_end', '$date_ban')";

    if (mysqli_query($connect1, $sql)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'เพิ่มข้อมูลนักศึกษาที่ถูกระงับสำเร็จ!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาด: ' . mysqli_error($connect1)];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'กรุณากรอกข้อมูลให้ครบถ้วน'];
}

header('Location: ../../student/susp_std.php');
exit();
