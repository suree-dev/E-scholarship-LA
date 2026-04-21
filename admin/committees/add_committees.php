<?php
session_start();
include '../../include/config.php';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !empty($_POST['committee_username']) &&
    !empty($_POST['committee_password']) &&
    !empty($_POST['committee_fullname'])
) {

    $username = mysqli_real_escape_string($connect1, $_POST['committee_username']);
    $password = mysqli_real_escape_string($connect1, $_POST['committee_password']);
    $fullname = mysqli_real_escape_string($connect1, $_POST['committee_fullname']);

    // $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // tc_type = 5 (คณะกรรมการ)
    $sql = "INSERT INTO tb_teacher (tc_user, tc_pass, tc_name, tc_type) 
            VALUES ('$username', '$password', '$fullname', 5)";

    if (mysqli_query($connect1, $sql)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'เพิ่มข้อมูลคณะกรรมการสำเร็จ!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล: ' . mysqli_error($connect1)];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'กรุณากรอกข้อมูลให้ครบถ้วน'];
}

header('Location: ../committees/committees.php');
exit();
