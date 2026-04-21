<?php
session_start();
include '../../include/config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'ไม่ได้ระบุ ID ของข่าวที่ต้องการลบ'];
    header('Location: ../news/news.php');
    exit();
}

$news_id = mysqli_real_escape_string($connect1, $_GET['id']);
$upload_dir = '../../uploads/';
$filename_to_delete = null;

mysqli_begin_transaction($connect1);

try {
    $sql_find_file = "SELECT filenab FROM tb_files WHERE idnews = '$news_id' LIMIT 1";
    $result_file = mysqli_query($connect1, $sql_find_file);
    if ($result_file && mysqli_num_rows($result_file) > 0) {
        $file_data = mysqli_fetch_assoc($result_file);
        $filename_to_delete = $file_data['filenab'];
    }

    $sql_delete_file_record = "DELETE FROM tb_files WHERE idnews = '$news_id'";
    if (!mysqli_query($connect1, $sql_delete_file_record)) {
        throw new Exception("เกิดข้อผิดพลาดในการลบข้อมูลไฟล์: " . mysqli_error($connect1));
    }

    $sql_delete_news = "DELETE FROM tb_news WHERE idnews = '$news_id'";
    if (!mysqli_query($connect1, $sql_delete_news)) {
        throw new Exception("เกิดข้อผิดพลาดในการลบข่าว: " . mysqli_error($connect1));
    }

    if (mysqli_commit($connect1)) {
        if ($filename_to_delete !== null) {
            $file_path = $upload_dir . $filename_to_delete;
            if (file_exists($file_path)) {
                unlink($file_path); // คำสั่งลบไฟล์
            }
        }
        $_SESSION['message'] = ['type' => 'success', 'text' => 'ลบข่าวประชาสัมพันธ์สำเร็จ!'];
    } else {
        throw new Exception("เกิดข้อผิดพลาดในการยืนยันการลบข้อมูล (Commit failed)");
    }
} catch (Exception $e) {
    mysqli_rollback($connect1);
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
}

header('Location: ../news/news.php');
exit();
