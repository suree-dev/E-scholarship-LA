<?php
include '../include/config.php';

$latest_news = [];
if (isset($connect1)) {
    $sql_news = "SELECT idnews, titlenews, datenews FROM tb_news ORDER BY datenews DESC LIMIT 3";
    $result_news = mysqli_query($connect1, $sql_news);
    if ($result_news) {
        while ($row = mysqli_fetch_assoc($result_news)) {
            $latest_news[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - PSU E-Scholarship</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/bg/head_01.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/global2.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">

    <style>
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                width: 90% !important;
                max-width: 400px;
                height: auto !important;
                margin: 20px auto;
            }

            .info-panel {
                display: none !important;
            }

            .passport-panel {
                width: 100% !important;
                padding: 40px 20px !important;
                border-radius: 20px !important;
            }

            .news-box {
                position: relative !important;
                top: 0 !important;
                left: 0 !important;
                width: 90% !important;
                margin: 20px auto !important;
                transform: none !important;
            }

            .main-wrapper {
                height: auto !important;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 20px 0;
            }

            .issue-reporter-login {
                position: relative !important;
                bottom: 0 !important;
                margin-top: 20px;
                padding: 10px;
                text-align: center;
            }

            .title-divider-login {
                margin-bottom: 46px !important;
            }

            .user-icon-login {
                margin-bottom: 25px !important;
            }

            .scholarship-logo-login {
                margin-bottom: 55px !important;
            }
        }
    </style>
</head>

<body class="login">

    <div class="main-wrapper">

        <div class="news-box">
            <div class="news-box-header">
                <span>ข่าวประชาสัมพันธ์</span>
                <a href="../admin/news/all_news.php">ทั้งหมด</a>
            </div>
            <div class="news-box-body">
                <?php if (!empty($latest_news)): ?>
                    <?php foreach ($latest_news as $index => $news): ?>
                        <a href="../admin/news/news_detail.php?id=<?php echo $news['idnews']; ?>" class="news-item">
                            <span class="icon">+</span>
                            <span><?php echo htmlspecialchars($news['titlenews']); ?></span>
                            <?php if ($index == 0): ?>
                                <span class="new-badge">NEW</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="news-item px-3 text-muted">ไม่มีข่าวสารล่าสุด</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="login-container">

            <div class="info-panel">
                <img src="../assets/images/bg/update-10.png" alt="Student Illustration" class="illustration">
                <div class="info-footer">
                    <img src="../assets/images/bg/update-06.png" alt="Faculty Logo" class="faculty-logo">
                    <a href="https://www.facebook.com/pages/งานกิจการนักศึกษา-คณะศิลปศาสตร์-มอ/374074482603129" target="_blank" class="social-button-login">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z" />
                        </svg>
                        <span>ติดตามข่าวสาร</span>
                    </a>
                </div>
            </div>

            <div class="passport-panel">
                <img src="../assets/images/bg/update-09.png" alt="Scholarship Logo" class="scholarship-logo-login">

                <div class="user-icon-login">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>

                <div class="title-divider-login">
                    <div class="line"></div>
                    <span class="text">PSU PASSPORT</span>
                    <div class="line"></div>
                </div>

                <div style="width: 100%;">
                    <a href="login_temp.php" class="btn-login-pill text-decoration-none">เข้าสู่ระบบ</a>
                </div>
            </div>
        </div>

        <div class="issue-reporter-login">
            *แจ้งปัญหาการใช้งาน <a href="../admin/issues/report_issue.php" target="_blank">ที่นี่</a> หรือโทร.074-286656 (ในวันและเวลาทำการ)
        </div>

    </div>

</body>

</html>