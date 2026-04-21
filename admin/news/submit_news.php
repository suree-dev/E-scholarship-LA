<?php
session_start();
include '../../include/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../news/news.php');
    exit();
}

$title = mysqli_real_escape_string($connect1, $_POST['news_title']);
$details = mysqli_real_escape_string($connect1, $_POST['news_details']);
$file_display_name = mysqli_real_escape_string($connect1, $_POST['news_file_name']);

$new_server_filename = null;

if (isset($_FILES['news_file']) && $_FILES['news_file']['error'] === UPLOAD_ERR_OK) {

    $upload_dir = '../../uploads/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $original_filename = basename($_FILES['news_file']['name']);

    $new_server_filename = time() . "_" . $original_filename;
    $target_path = $upload_dir . $new_server_filename;

    if (!move_uploaded_file($_FILES['news_file']['tmp_name'], $target_path)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'ไม่สามารถอัปโหลดไฟล์ได้: ตรวจสอบสิทธิ์ของโฟลเดอร์ uploads'];
        header('Location: ../news/news.php');
        exit();
    }
}

mysqli_begin_transaction($connect1);

try {
    $sql_news = "INSERT INTO tb_news (titlenews, detailnews, datenews, typenews) VALUES ('$title', '$details', NOW(), 1)";
    if (!mysqli_query($connect1, $sql_news)) {
        throw new Exception("เกิดข้อผิดพลาดในการบันทึกข่าว: " . mysqli_error($connect1));
    }

    $last_news_id = mysqli_insert_id($connect1);

    if ($new_server_filename !== null && !empty($file_display_name)) {
        $sql_file = "INSERT INTO tb_files (namefile, filenab, idnews) VALUES ('$file_display_name', '$new_server_filename', '$last_news_id')";
        if (!mysqli_query($connect1, $sql_file)) {
            throw new Exception("เกิดข้อผิดพลาดในการบันทึกข้อมูลไฟล์: " . mysqli_error($connect1));
        }
    }

    mysqli_commit($connect1);
    $_SESSION['message'] = ['type' => 'success', 'text' => 'เพิ่มข่าวประชาสัมพันธ์สำเร็จ!'];
} catch (Exception $e) {
    mysqli_rollback($connect1);

    if ($new_server_filename !== null && file_exists($upload_dir . $new_server_filename)) {
        unlink($upload_dir . $new_server_filename);
    }

    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
}

header('Location: ../news/news.php');
exit();
