<?php
session_start();
include '../../include/config.php';

if (!isset($_SESSION['ad_id'])) {
    header("Location: ../root/index.php");
    exit();
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $success = false;
    $error_msg = "";

    mysqli_begin_transaction($connect1);

    try {
        switch ($action) {
            case 'student_all':
                mysqli_query($connect1, "TRUNCATE TABLE tb_student");
                mysqli_query($connect1, "TRUNCATE TABLE tb_parent");
                mysqli_query($connect1, "TRUNCATE TABLE tb_relatives");
                break;

            case 'student_type1':
                mysqli_query($connect1, "DELETE FROM tb_student WHERE st_type = 1");
                break;

            case 'student_type2':
                mysqli_query($connect1, "DELETE FROM tb_student WHERE st_type = 2");
                break;

            case 'student_type3':
                mysqli_query($connect1, "DELETE FROM tb_student WHERE st_type = 3");
                break;

            case 'staff_all':
                mysqli_query($connect1, "TRUNCATE TABLE tb_teacher");
                mysqli_query($connect1, "TRUNCATE TABLE tb_committee");
                break;

            case 'committees':
                mysqli_query($connect1, "TRUNCATE TABLE tb_committee");
                break;

            case 'advisors':
                mysqli_query($connect1, "TRUNCATE TABLE tb_teacher");
                break;

            case 'programs':
                mysqli_query($connect1, "TRUNCATE TABLE tb_program");
                break;

            case 'news':
                mysqli_query($connect1, "TRUNCATE TABLE tb_news");
                mysqli_query($connect1, "TRUNCATE TABLE tb_files");
                break;

            case 'reset_system':
                mysqli_query($connect1, "TRUNCATE TABLE tb_student");
                mysqli_query($connect1, "TRUNCATE TABLE tb_parent");
                mysqli_query($connect1, "TRUNCATE TABLE tb_relatives");
                mysqli_query($connect1, "TRUNCATE TABLE tb_teacher");
                mysqli_query($connect1, "TRUNCATE TABLE tb_committee");
                mysqli_query($connect1, "TRUNCATE TABLE tb_program");
                mysqli_query($connect1, "TRUNCATE TABLE tb_news");
                mysqli_query($connect1, "TRUNCATE TABLE tb_files");
                mysqli_query($connect1, "TRUNCATE TABLE tb_ban");
                break;

            default:
                throw new Exception("ไม่พบคำสั่งที่ระบุ");
        }

        mysqli_commit($connect1);
        $success = true;
        $_SESSION['clear_msg'] = "ล้างข้อมูลเรียบร้อยแล้ว";
        $_SESSION['clear_type'] = "success";
    } catch (Exception $e) {
        mysqli_rollback($connect1);
        $_SESSION['clear_msg'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        $_SESSION['clear_type'] = "error";
    }
}


header("Location: ../system/clear_data.php");
exit();
