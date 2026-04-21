<?php
session_start();
include '../../include/config.php';

$page_title = "รายละเอียดปัญหาการใช้งาน";
$issue_title = "N/A";
$issue_reporter = "N/A";
$issue_date = "N/A";
$issue_details = "ไม่พบข้อมูล";

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $issue_id = mysqli_real_escape_string($connect1, $_GET['id']);

    $sql = "SELECT 
                i.issue_topic, 
                i.issue_details,
                i.issue_date, 
                s.st_firstname, 
                s.st_lastname, 
                s.st_code 
            FROM 
                tb_issue AS i
            LEFT JOIN 
                tb_student AS s ON i.student_id = s.st_id
            WHERE 
                i.issue_id = '$issue_id'";

    $result = mysqli_query($connect1, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $issue_data = mysqli_fetch_assoc($result);
        $issue_title = $issue_data['issue_topic'] ?: "N/A";

        if (!empty($issue_data['st_firstname'])) {
            $issue_reporter = $issue_data['st_firstname'] . ' ' . $issue_data['st_lastname'] . ' (' . $issue_data['st_code'] . ')';
        } else {
            $issue_reporter = "N/A";
        }

        $issue_date = date('d/m/', strtotime($issue_data['issue_date'])) . (date('Y', strtotime($issue_data['issue_date'])) + 543);
        $issue_details = $issue_data['issue_details'] ?: "ไม่พบข้อมูล";
    }
}

include '../../include/header.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $page_title; ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/bg/head_01.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        .dashboard-container {
            flex: 1 0 auto;
        }

        .site-footer {
            flex-shrink: 0;
        }

        @media (max-width: 1024px) {
            .sidebar .menu-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                line-height: 1 !important;
                height: auto !important;
                padding: 15px 20px !important;
            }

            .sidebar .menu-header::after {
                float: none !important;
                margin-top: 0 !important;
                position: static !important;
            }
        }
    </style>
</head>

<body>

    <div class="sticky-header-wrapper">
        <?php include('../../include/navbar.php'); ?>
        <?php include('../../include/status_bar.php'); ?>
    </div>

    <div class="container-fluid dashboard-container">
        <div class="row g-4">
            <div class="col-12 col-sidebar-20">
                <?php include '../../include/sidebar.php'; ?>
            </div>

            <div class="col-12 col-main-80">
                <main class="main-content shadow-sm">
                    <div class="content-header border-bottom pb-3 mb-4">
                        <h1 class="m-0 fw-bold" style="font-size: 22px; color: #333;"><?php echo $page_title; ?></h1>
                    </div>

                    <div class="details-view px-2">
                        <div class="mb-4">
                            <label class="detail-label-bold">หัวข้อปัญหา:</label>
                            <div class="detail-box-gray"><?php echo htmlspecialchars($issue_title); ?></div>
                        </div>

                        <div class="mb-4">
                            <label class="detail-label-bold">ผู้แจ้ง:</label>
                            <div class="detail-box-gray"><?php echo htmlspecialchars($issue_reporter); ?></div>
                        </div>

                        <div class="mb-4">
                            <label class="detail-label-bold">วันที่แจ้ง:</label>
                            <div class="detail-box-gray"><?php echo htmlspecialchars($issue_date); ?></div>
                        </div>

                        <div class="mb-4">
                            <label class="detail-label-bold">รายละเอียดปัญหา:</label>
                            <div class="detail-box-gray textarea-style"><?php echo nl2br(htmlspecialchars($issue_details)); ?></div>
                        </div>

                        <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                            <a href="../issues/issue.php" class="btn-back-step-grey shadow-sm">ย้อนกลับ</a>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <?php include '../../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const menuHeader = document.querySelector('.sidebar .menu-header');
            if (menuHeader && sidebar) {
                menuHeader.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        sidebar.classList.toggle('is-open');
                    }
                });
            }
        });
    </script>

</body>

</html>