<?php
session_start();
include '../../include/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../settings/academic_year.php');
    exit();
}

$academic_year = mysqli_real_escape_string($connect1, $_POST['academic_year']);
$web_url = mysqli_real_escape_string($connect1, $_POST['web_url']);

$sql = "UPDATE tb_year SET 
            y_year = '$academic_year', 
            y_url = '$web_url' 
        WHERE y_id = 1";

if (mysqli_query($connect1, $sql)) {
    $_SESSION['message'] = ['type' => 'success', 'text' => 'บันทึกข้อมูลปีการศึกษาสำเร็จ!'];
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . mysqli_error($connect1)];
}

header('Location: ../settings/academic_year.php');
exit();
