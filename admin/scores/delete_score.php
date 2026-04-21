<?php
session_start();
include '../../include/config.php';

$scholarship_type = isset($_GET['type']) ? '&type=' . (int)$_GET['type'] : '';

if (isset($_GET['id']) && !empty($_GET['id'])) {

    $student_id = mysqli_real_escape_string($connect1, $_GET['id']);

    $sql_delete = "DELETE FROM tb_scores WHERE st_id = '$student_id'";

    if (mysqli_query($connect1, $sql_delete)) {
        $sql_update_student = "UPDATE tb_student SET sum_score = 0, st_average = 0.00 WHERE st_id = '$student_id'";
        mysqli_query($connect1, $sql_update_student);

        $_SESSION['message'] = ['type' => 'success', 'text' => 'ลบข้อมูลคะแนนสำเร็จ!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . mysqli_error($connect1)];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'ไม่ได้ระบุ ID ของข้อมูลที่ต้องการลบ'];
}

header('Location: ../scores/scholarship_scores.php?' . $scholarship_type);
exit();
