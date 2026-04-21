<?php
session_start();
include '../../include/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../news/news.php');
    exit();
}

$news_id = mysqli_real_escape_string($connect1, $_POST['news_id']);
$title = mysqli_real_escape_string($connect1, $_POST['news_title']);
$details = mysqli_real_escape_string($connect1, $_POST['news_details']);
$file_display_name = mysqli_real_escape_string($connect1, $_POST['news_file_name']);

$new_server_filename = null;
$upload_dir = '../../uploads/';

if (isset($_FILES['news_file']) && $_FILES['news_file']['error'] === UPLOAD_ERR_OK) {

    $original_filename = basename($_FILES['news_file']['name']);
    $new_server_filename = time() . "_" . $original_filename;
    $target_path = $upload_dir . $new_server_filename;

    if (!move_uploaded_file($_FILES['news_file']['tmp_name'], $target_path)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'ไม่สามารถอัปโหลดไฟล์ใหม่ได้'];
        header('Location: ../news/news.php');
        exit();
    }
}

mysqli_begin_transaction($connect1);

try {
    $sql_update_news = "UPDATE tb_news SET titlenews = '$title', detailnews = '$details' WHERE idnews = '$news_id'";
    if (!mysqli_query($connect1, $sql_update_news)) {
        throw new Exception("เกิดข้อผิดพลาดในการอัปเดตข่าว: " . mysqli_error($connect1));
    }

    $sql_find_old_file = "SELECT idfile, filenab FROM tb_files WHERE idnews = '$news_id' LIMIT 1";
    $result_old_file = mysqli_query($connect1, $sql_find_old_file);
    $old_file_data = mysqli_fetch_assoc($result_old_file);

    if ($new_server_filename !== null) {
        if ($old_file_data) {
            $old_server_filename = $old_file_data['filenab'];
            $idfile = $old_file_data['idfile'];
            $sql_update_file = "UPDATE tb_files SET namefile = '$file_display_name', filenab = '$new_server_filename' WHERE idfile = '$idfile'";
            if (!mysqli_query($connect1, $sql_update_file)) {
                throw new Exception("เกิดข้อผิดพลาดในการอัปเดตข้อมูลไฟล์");
            }
            if (file_exists($upload_dir . $old_server_filename)) {
                unlink($upload_dir . $old_server_filename);
            }
        } else {
            $sql_insert_file = "INSERT INTO tb_files (namefile, filenab, idnews) VALUES ('$file_display_name', '$new_server_filename', '$news_id')";
            if (!mysqli_query($connect1, $sql_insert_file)) {
                throw new Exception("เกิดข้อผิดพลาดในการเพิ่มข้อมูลไฟล์");
            }
        }
    } else {
        if ($old_file_data && !empty($file_display_name)) {
            $idfile = $old_file_data['idfile'];
            $sql_update_display_name = "UPDATE tb_files SET namefile = '$file_display_name' WHERE idfile = '$idfile'";
            if (!mysqli_query($connect1, $sql_update_display_name)) {
                throw new Exception("เกิดข้อผิดพลาดในการอัปเดตชื่อไฟล์");
            }
        }
    }

    mysqli_commit($connect1);
    $_SESSION['message'] = ['type' => 'success', 'text' => 'อัปเดตข่าวประชาสัมพันธ์สำเร็จ!'];
} catch (Exception $e) {
    mysqli_rollback($connect1);
    if ($new_server_filename !== null && file_exists($upload_dir . $new_server_filename)) {
        unlink($upload_dir . $new_server_filename);
    }
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
}

header('Location: ../news/news.php');
exit();
