<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set("Asia/Bangkok");
session_start();
include '../../include/config.php';

if (!isset($_POST['student_id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/advisors/teacher.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-container,
        .swal2-popup,
        .swal2-title,
        .swal2-html-container,
        .swal2-confirm,
        .swal2-cancel {
            font-family: 'Prompt', sans-serif !important;
        }
    </style>
</head>

<body>

    <?php
    if (!isset($_SESSION['id_teacher'])) {
        echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'กรุณาเข้าสู่ระบบ',
            text: 'กรุณาเข้าสู่ระบบก่อนทำการบันทึกคะแนน',
            confirmButtonColor: '#003366'
        }).then(() => {
            window.location.href = '../login_temp.php'; 
        });
    </script>";
        exit();
    }

    $committee_id = $_SESSION['id_teacher'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $st_id     = mysqli_real_escape_string($connect1, $_POST['student_id']);
        $score_val = mysqli_real_escape_string($connect1, $_POST['score']);
        $comment   = mysqli_real_escape_string($connect1, $_POST['comment']);
        $now       = date("Y-m-d H:i:s");

        if (empty($st_id)) {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'ผิดพลาด',
                text: 'ไม่พบรหัสนักศึกษาในระบบ',
                confirmButtonColor: '#003366'
            }).then(() => { window.history.back(); });
        </script>";
            exit();
        }

        if ($score_val === "") {
            echo "<script>
            Swal.fire({
                icon: 'info',
                title: 'ข้อมูลไม่ครบถ้วน',
                text: 'กรุณาเลือกผลการให้คะแนน',
                confirmButtonColor: '#003366'
            }).then(() => { window.history.back(); });
        </script>";
            exit();
        }

        $check_sql = "SELECT sco_id FROM tb_scores WHERE st_id = '$st_id' AND tc_id = '$committee_id'";
        $check_result = mysqli_query($connect1, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $sql = "UPDATE tb_scores 
                SET scores = '$score_val', 
                    sco_comment = '$comment', 
                    sco_date = '$now' 
                WHERE st_id = '$st_id' AND tc_id = '$committee_id'";
            $msg = "อัปเดตผลการประเมินเรียบร้อยแล้ว";
        } else {
            $sql = "INSERT INTO tb_scores (tc_id, scores, sco_comment, sco_date, sco_status, st_id) 
                VALUES ('$committee_id', '$score_val', '$comment', '$now', 1, '$st_id')";
            $msg = "บันทึกผลการประเมินเรียบร้อยแล้ว";
        }

        if (mysqli_query($connect1, $sql)) {
            echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: '$msg',
                confirmButtonColor: '#198754'
            }).then(() => {
                // ปรับตำแหน่ง path ให้ถูกต้องตามโครงสร้างไฟล์
                window.location.href = '../advisors/give_score.php?student_id=$st_id'; 
            });
        </script>";
        } else {
            $db_error = mysqli_error($connect1);
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถบันทึกข้อมูลได้: $db_error',
                confirmButtonColor: '#d33'
            }).then(() => { window.history.back(); });
        </script>";
        }
    } else {
        header("Location: ../advisors/teacher.php");
        exit();
    }
    ?>
</body>

</html>