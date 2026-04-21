<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../../include/config.php';

$idnews = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql_news = "SELECT * FROM tb_news WHERE idnews = '$idnews'";
$res_news = mysqli_query($connect1, $sql_news);
$news = mysqli_fetch_assoc($res_news);

if (!$news) {
    die("ไม่พบข้อมูลข่าว");
}

$sql_files = "SELECT * FROM tb_files WHERE idnews = '$idnews'";
$res_files = mysqli_query($connect1, $sql_files);

function thai_date_full($strDate)
{
    $thai_months = [1 => "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    $date = date_create($strDate);
    return date_format($date, "j") . " " . $thai_months[(int)date_format($date, "n")] . " " . (date_format($date, "Y") + 543);
}

function thai_date_short($strDate)
{
    $thai_months = [1 => "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
    $date = date_create($strDate);
    return date_format($date, "j") . " " . $thai_months[(int)date_format($date, "n")] . " " . (date_format($date, "Y") + 543);
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $news['titlenews']; ?> - PSU E-Scholarship</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/bg/head_01.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="../../assets/css/global2.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/navigation.css">
    <link rel="stylesheet" href="../../assets/css/ui-elements.css">
    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets/css/tables.css">
    <link rel="stylesheet" href="../../assets/css/pages.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-light">

    <?php include('../../include/navbar.php'); ?>

    <div class="news-header-banner">
        <div class="container">
            <h2>ข่าวสารและประกาศ</h2>
            <p>ติดตามข่าวสารการรับสมัครทุนการศึกษาและประกาศต่างๆ ของคณะศิลปศาสตร์</p>
        </div>
    </div>

    <div class="news-detail-wrapper">
        <div class="container">
            <div class="news-card-container">

                <div class="meta-info">
                    <span><i class="fa-regular fa-calendar-check me-1"></i> ประกาศเมื่อ: <?php echo thai_date_short($news['datenews']); ?></span>
                    <span><i class="fa-solid fa-bullhorn me-1"></i> ข่าวประชาสัมพันธ์</span>
                </div>

                <h1 class="news-title-h"><?php echo htmlspecialchars($news['titlenews']); ?></h1>

                <div class="news-content-text">
                    <?php echo nl2br(htmlspecialchars($news['detailnews'])); ?>
                </div>

                <?php if (mysqli_num_rows($res_files) > 0): ?>
                    <div class="attachment-section">
                        <div class="attachment-title">
                            <i class="fa-solid fa-paperclip text-primary"></i> เอกสารแนบที่เกี่ยวข้อง
                        </div>
                        <div class="attachment-list">
                            <?php while ($file = mysqli_fetch_assoc($res_files)): ?>
                                <a href="../../uploads/<?php echo $file['filenab']; ?>" target="_blank" class="attachment-item">
                                    <div class="file-icon">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div class="file-details">
                                        <span class="file-name"><?php echo htmlspecialchars($file['namefile']); ?></span>
                                        <span class="file-path"><?php echo htmlspecialchars($file['filenab']); ?></span>
                                    </div>
                                    <div class="download-btn-icon">
                                        <i class="fa-solid fa-download"></i>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="back-button-container">
                    <a href="all_news.php" class="btn-back-custom">
                        <i class="fa-solid fa-chevron-left"></i> กลับไปหน้าข่าวสารทั้งหมด
                    </a>
                </div>

            </div>
        </div>
    </div>

    <?php include '../../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>