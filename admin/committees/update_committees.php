<?php
session_start();
include '../../include/config.php';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['committee_id']) &&
    !empty($_POST['committee_username']) &&
    !empty($_POST['committee_fullname'])
) {

    $id = mysqli_real_escape_string($connect1, $_POST['committee_id']);
    $username = mysqli_real_escape_string($connect1, $_POST['committee_username']);
    $password = mysqli_real_escape_string($connect1, $_POST['committee_password']);
    $fullname = mysqli_real_escape_string($connect1, $_POST['committee_fullname']);

    $sql = "UPDATE tb_teacher SET 
                tc_user = '$username', 
                tc_name = '$fullname'";

    if (!empty($password)) {
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", tc_pass = '$password'";
    }

    $sql .= " WHERE tc_id = '$id'";

    if (mysqli_query($connect1, $sql)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'อัปเดตข้อมูลคณะกรรมการสำเร็จ!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการอัปเดต: ' . mysqli_error($connect1)];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'ข้อมูลไม่ครบถ้วน'];
}

header('Location: ../committees/committees.php');
exit();
