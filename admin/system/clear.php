<?php
session_start();
include '../../include/config.php';

if (!isset($_GET['target'])) {
    header('Location: clear_data.php');
    exit();
}

$target = $_GET['target'];

mysqli_begin_transaction($connect1);

try {
    switch ($target) {
        case 'students_all':
            mysqli_query($connect1, "TRUNCATE TABLE tb_student");
            mysqli_query($connect1, "TRUNCATE TABLE tb_bursary");
            mysqli_query($connect1, "TRUNCATE TABLE tb_activity");
            mysqli_query($connect1, "TRUNCATE TABLE tb_parent");
            mysqli_query($connect1, "TRUNCATE TABLE tb_relatives");
            mysqli_query($connect1, "TRUNCATE TABLE tb_scores");
            $_SESSION['message'] = ['type' => 'success', 'text' => 'ล้างข้อมูลนักศึกษาและประวัติทั้งหมดสำเร็จ!'];
            break;

        case 'personnel_all':
            mysqli_query($connect1, "DELETE FROM tb_teacher WHERE tc_type = 4 OR tc_type = 5");
            $_SESSION['message'] = ['type' => 'success', 'text' => 'ล้างข้อมูลคณะกรรมการและอาจารย์ที่ปรึกษาสำเร็จ!'];
            break;

        case 'majors':
            mysqli_query($connect1, "TRUNCATE TABLE tb_program");
            $_SESSION['message'] = ['type' => 'success', 'text' => 'ล้างข้อมูลสาขาวิชาสำเร็จ!'];
            break;

        case 'news':
            mysqli_query($connect1, "TRUNCATE TABLE tb_news");
            mysqli_query($connect1, "TRUNCATE TABLE tb_files");
            $_SESSION['message'] = ['type' => 'success', 'text' => 'ล้างข้อมูลข่าวสารสำเร็จ!'];
            break;

        case 'all':
            mysqli_query($connect1, "TRUNCATE TABLE tb_student");
            mysqli_query($connect1, "TRUNCATE TABLE tb_bursary");
            mysqli_query($connect1, "TRUNCATE TABLE tb_activity");
            mysqli_query($connect1, "TRUNCATE TABLE tb_parent");
            mysqli_query($connect1, "TRUNCATE TABLE tb_relatives");
            mysqli_query($connect1, "TRUNCATE TABLE tb_scores");
            mysqli_query($connect1, "DELETE FROM tb_teacher WHERE tc_type != 1"); // ลบ teacher ทุกประเภท ยกเว้น admin (ถ้ามี)
            mysqli_query($connect1, "TRUNCATE TABLE tb_program");
            mysqli_query($connect1, "TRUNCATE TABLE tb_news");
            mysqli_query($connect1, "TRUNCATE TABLE tb_files");
            mysqli_query($connect1, "TRUNCATE TABLE tb_ban");
            $_SESSION['message'] = ['type' => 'success', 'text' => 'ล้างข้อมูลทั้งหมดของระบบสำเร็จ!'];
            break;

        default:
            $_SESSION['message'] = ['type' => 'error', 'text' => 'เป้าหมายการล้างข้อมูลไม่ถูกต้อง'];
            break;
    }

    mysqli_commit($connect1);
} catch (Exception $e) {
    mysqli_rollback($connect1);
    $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการล้างข้อมูล: ' . $e->getMessage()];
}

header('Location: ../system/clear_data.php');
exit();
