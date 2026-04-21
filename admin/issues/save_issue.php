<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!file_exists('../../include/config.php')) {
    die("Fatal Error: ไม่พบไฟล์ ../../include/config.php");
}
require_once '../../include/config.php';

if (!file_exists('PHPMailer/PHPMailer.php')) {
    die("Fatal Error: ไม่พบไฟล์ในโฟลเดอร์ PHPMailer");
}
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }

        .swal2-container {
            font-family: 'Prompt', sans-serif !important;
        }
    </style>
</head>

<body>

    <?php
    if (isset($_POST['save_issue'])) {
        $topic = $_POST['issue_topic'];
        $details = $_POST['issue_details'];
        $reporter = $_POST['reporter_name'];
        $student_code = $_POST['student_id'];
        $date = date("Y-m-d H:i:s");

        echo "<script>
        Swal.fire({
            title: 'กำลังส่งข้อมูล...',
            text: 'กรุณารอสักครู่ ระบบกำลังบันทึกและส่งอีเมลแจ้งเตือน',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    </script>";
        if (ob_get_level() > 0) ob_flush();
        flush();

        if (!preg_match('/^[0-9]{2}1111[0-9]{4}$/', $student_code)) {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'รหัสนักศึกษาไม่ถูกต้อง',
                text: 'กรุณาตรวจสอบรูปแบบรหัสนักศึกษาอีกครั้ง'
            }).then(() => { window.location.href='report_issue.php'; });
        </script>";
            exit();
        }

        if (!isset($connect1)) {
            die("Fatal Error: ไม่พบตัวแปรเชื่อมต่อฐานข้อมูล (\$connect1)");
        }

        try {

            $stmt_check = mysqli_prepare($connect1, "SELECT st_id FROM tb_student WHERE st_code = ?");
            mysqli_stmt_bind_param($stmt_check, "s", $student_code);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            $student_data = mysqli_fetch_assoc($result_check);

            if (!$student_data) {
                echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่พบข้อมูลนักศึกษา',
                    text: 'ไม่พบรหัสนักศึกษานี้ในระบบ'
                }).then(() => { window.location.href='report_issue.php'; });
            </script>";
                exit();
            }

            $real_student_id = $student_data['st_id'];


            $stmt = mysqli_prepare($connect1, "INSERT INTO tb_issue (issue_topic, issue_details, student_id, issue_date) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $topic, $details, $real_student_id, $date);

            if (mysqli_stmt_execute($stmt)) {

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = '106765033@yru.ac.th';
                    $mail->Password   = 'svvs ewob qmhw zsdi';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('ateez81seonghwa@gmail.com', 'PSU Scholarship System');
                    $mail->addAddress('ateez81seonghwa@gmail.com');

                    $mail->isHTML(true);
                    $mail->Subject = 'แจ้งปัญหาการใช้งานใหม่: ' . $topic;
                    $mail->Body    = "<h3>พบการแจ้งปัญหาใหม่จากนักศึกษา</h3>
                                  <p><b>หัวข้อ:</b> {$topic}</p>
                                  <p><b>ผู้แจ้ง:</b> {$reporter}</p>
                                  <p><b>รายละเอียด:</b> {$details}</p>
                                  <p><b>รหัสนักศึกษา:</b> {$student_code}</p>
                                  <p><b>วันที่แจ้ง:</b> {$date}</p>";

                    $mail->send();

                    echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'ส่งข้อมูลสำเร็จ',
                        text: 'บันทึกข้อมูลและส่งอีเมลแจ้งเตือนเรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง'
                    }).then(() => { window.location.href='report_issue.php?status=success'; });
                </script>";
                } catch (Exception $e) {
                    echo "<script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'บันทึกสำเร็จแต่ส่งเมลไม่สำเร็จ',
                        text: 'ข้อผิดพลาด: {$mail->ErrorInfo}',
                        confirmButtonText: 'ตกลง'
                    }).then(() => { window.location.href='report_issue.php?status=mail_error'; });
                </script>";
                }
            } else {
                throw new Exception(mysqli_error($connect1));
            }
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาดทางฐานข้อมูล',
                text: '" . addslashes($e->getMessage()) . "'
            }).then(() => { window.history.back(); });
        </script>";
        }
    } else {
        header("Location: report_issue.php");
    }
    ?>
</body>

</html>
<?php exit(); ?>