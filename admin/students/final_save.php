<?php
session_start();
include '../../include/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {

    $student_id = mysqli_real_escape_string($connect1, $_POST['student_id']);

    $sql = "UPDATE tb_student SET 
            st_activate = '1', 
            st_date_send = NOW() 
            WHERE st_id = '$student_id' OR st_code = '$student_id'";

    if (mysqli_query($connect1, $sql)) {
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>ส่งใบสมัครทุนเรียบร้อย - PSU E-Scholarship</title>
            <link rel='icon' type='image/png' sizes='16x16' href='../../assets/images/bg/head_01.png'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap' rel='stylesheet'>
            <style>
                * { font-family: 'Prompt', sans-serif; }
            </style>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: 'ส่งเอกสารการสมัครเรียบร้อย!',
                    text: 'ระบบได้รับข้อมูลของคุณแล้ว ขณะนี้อยู่ระหว่างรอเจ้าหน้าที่ตรวจสอบ',
                    icon: 'success',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#003c71'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../../student/confirm_page.php?status=success';
                    }
                });
            </script>
        </body>
        </html>";
        exit();
    } else {
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>เกิดข้อผิดพลาด - PSU E-Scholarship</title>
            <link rel='icon' type='image/png' sizes='16x16' href='../../assets/images/bg/head_01.png'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap' rel='stylesheet'>
            <style>
                * { font-family: 'Prompt', sans-serif; }
            </style>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: '" . mysqli_error($connect1) . "',
                    icon: 'error',
                    confirmButtonText: 'ย้อนกลับ',
                    confirmButtonColor: '#e53935'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.history.back();
                    }
                });
            </script>
        </body>
        </html>";
        exit();
    }
} else {
    header("Location: ../../student/apply_form.php");
    exit();
}

mysqli_close($connect1);
