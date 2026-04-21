<?php
session_start();
include '../include/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['st_code'])) {
        $st_code = mysqli_real_escape_string($connect1, $_POST['st_code']);
        $st_pass = mysqli_real_escape_string($connect1, $_POST['st_pass']);
        $today = date('Y-m-d');

        $sql_ban = "SELECT code_student FROM tb_ban 
                    WHERE code_student = '$st_code' 
                    AND '$today' BETWEEN date_start AND date_end LIMIT 1";
        $res_ban = mysqli_query($connect1, $sql_ban);

        if ($res_ban && mysqli_num_rows($res_ban) > 0) {
            echo "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <link href='https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap' rel='stylesheet'>
                <style>* { font-family: 'Prompt', sans-serif; }</style>
            </head>
            <body>
                <script>
                    Swal.fire({
                        title: 'ไม่สามารถเข้าสู่ระบบได้',
                        text: 'รหัสนักศึกษาของคุณอยู่ในรายชื่อระงับการขอรับทุนการศึกษาในขณะนี้',
                        icon: 'error',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#003c71'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login_temp.php';
                        }
                    });
                </script>
            </body>
            </html>";
            exit();
        }

        $sql_st = "SELECT st_code, st_firstname, st_lastname, st_activate, st_type FROM tb_student 
                   WHERE st_code = '$st_code' AND st_pass = '$st_pass' LIMIT 1";
        $res_st = mysqli_query($connect1, $sql_st);

        if ($res_st && mysqli_num_rows($res_st) > 0) {
            $row = mysqli_fetch_assoc($res_st);

            unset($_SESSION['id_teacher']);
            unset($_SESSION['tc_name']);

            $_SESSION['student_id'] = $row['st_code'];
            $_SESSION['st_name'] = $row['st_firstname'];
            $_SESSION['st_surname'] = $row['st_lastname']; // *** เพิ่มบรรทัดนี้เพื่อเก็บนามสกุล ***

            unset($_SESSION['login_error']);

            header("Location: ../student/regis.php");
            exit();
        } else {
            $_SESSION['login_error'] = "รหัสนักศึกษาหรือรหัสผ่านไม่ถูกต้อง";
            header("Location: login_temp.php");
            exit();
        }
    } elseif (isset($_POST['tc_user'])) {
        $tc_user = mysqli_real_escape_string($connect1, $_POST['tc_user']);
        $tc_pass = mysqli_real_escape_string($connect1, $_POST['tc_pass']);

        $sql_tc = "SELECT tc_id, tc_name, tc_type FROM tb_teacher WHERE tc_user = '$tc_user' AND tc_pass = '$tc_pass' LIMIT 1";
        $res_tc = mysqli_query($connect1, $sql_tc);

        if ($res_tc && mysqli_num_rows($res_tc) > 0) {
            $row_tc = mysqli_fetch_assoc($res_tc);

            unset($_SESSION['student_id']);
            unset($_SESSION['st_name']);
            unset($_SESSION['st_surname']);

            $_SESSION['id_teacher'] = $row_tc['tc_id'];
            $_SESSION['tc_name']    = $row_tc['tc_name'];
            $_SESSION['tc_type']    = $row_tc['tc_type'];

            unset($_SESSION['login_error_tc']);
            header("Location: ../admin/advisors/teacher.php");
            exit();
        } else {
            $_SESSION['login_error_tc'] = "ชื่อผู้ใช้งานหรือรหัสผ่านอาจารย์ไม่ถูกต้อง";
            header("Location: login_temp.php");
            exit();
        }
    } elseif (isset($_POST['ad_user'])) {
        $ad_user = mysqli_real_escape_string($connect1, $_POST['ad_user']);
        $ad_pass = mysqli_real_escape_string($connect1, $_POST['ad_pass']);
        $sql_ad = "SELECT ad_id, ad_name FROM tb_admin WHERE ad_user = '$ad_user' AND ad_pass = '$ad_pass' LIMIT 1";
        $res_ad = mysqli_query($connect1, $sql_ad);
        if ($res_ad && mysqli_num_rows($res_ad) > 0) {
            $row_ad = mysqli_fetch_assoc($res_ad);
            unset($_SESSION['student_id'], $_SESSION['st_name'], $_SESSION['st_surname'], $_SESSION['id_teacher'], $_SESSION['tc_name']);
            $_SESSION['id_admin'] = $row_ad['ad_id'];
            $_SESSION['ad_name'] = $row_ad['ad_name'];
            header("Location: ../admin/system/admin.php");
            exit();
        } else {
            $_SESSION['login_error'] = "ชื่อผู้ใช้งานหรือรหัสผ่านแอดมินไม่ถูกต้อง";
            header("Location: login_temp.php");
            exit();
        }
    }
} else {
    header("Location: login_temp.php");
    exit();
}
