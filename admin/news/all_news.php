<?php
date_default_timezone_set("Asia/Bangkok");
session_start();
include '../../include/config.php';

$sql_news = "SELECT * FROM tb_news ORDER BY datenews DESC";
$result_news = mysqli_query($connect1, $sql_news);

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
    <title>ข่าวสารและประกาศทั้งหมด - PSU E-Scholarship</title>
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

    <div class="main-content-wrapper">

        <?php include('../../include/navbar.php'); ?>

        <div class="news-header-banner">
            <div class="container">
                <h2>ข่าวสารและประกาศ</h2>
                <p>ติดตามข่าวสารการรับสมัครทุนการศึกษาและประกาศต่างๆ ของคณะศิลปศาสตร์</p>
            </div>
        </div>

        <div class="container news-detail-wrapper">
            <div class="news-grid">
                <?php if ($result_news && mysqli_num_rows($result_news) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result_news)): ?>
                        <a href="news_detail.php?id=<?php echo $row['idnews']; ?>" class="news-card-item">
                            <div class="card-body">
                                <div class="text-primary fw-bold small mb-2">
                                    <i class="fa-solid fa-bullhorn me-1"></i> ข่าวประชาสัมพันธ์
                                </div>
                                <h5 class="fw-bold mb-3 text-dark"><?php echo htmlspecialchars($row['titlenews']); ?></h5>
                                <p class="text-muted small">
                                    <?php echo mb_substr(strip_tags($row['detailnews']), 0, 120, 'UTF-8'); ?>...
                                </p>
                            </div>
                            <div class="card-footer bg-white border-top-0 px-4 pb-4 d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i> <?php echo thai_date_short($row['datenews']); ?></small>
                                <span class="text-primary fw-bold small">อ่านต่อ <i class="fa-solid fa-arrow-right ms-1"></i></span>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <div class="back-button-container">
                <a href="../../root/index.php" class="btn-back-custom">
                    <i class="fa-solid fa-right-to-bracket"></i> ย้อนกลับหน้าเข้าสู่ระบบ
                </a>
            </div>

        </div>

    </div>

    <?php include '../../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>