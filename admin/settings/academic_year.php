<?php
session_start();
include '../../include/config.php';

$sql = "SELECT y_year, y_url FROM tb_year WHERE y_id = 1";
$result = mysqli_query($connect1, $sql);
$year_data = mysqli_fetch_assoc($result);

$current_academic_year = $year_data ? $year_data['y_year'] : '';
$current_url = $year_data ? $year_data['y_url'] : '';

$page_title = "จัดการข้อมูลปีการศึกษา";

include '../../include/header.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $page_title; ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/bg/head_01.png">

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                    <?php
                    if (isset($_SESSION['message'])) {
                        $bg_color = $_SESSION['message']['type'] == 'success' ? '#28a745' : '#dc3545';
                        echo '<div id="notification-message" class="alert border-0 shadow-sm" style="background-color:' . $bg_color . '; color: white; transition: opacity 0.5s ease;">';
                        echo htmlspecialchars($_SESSION['message']['text']);
                        echo '</div>';
                        unset($_SESSION['message']);
                    }
                    ?>

                    <div class="content-header">
                        <h1 class="m-0 fw-bold" style="font-size: 22px; color: #333;"><?php echo $page_title; ?></h1>
                    </div>

                    <form id="academicForm" action="../settings/update_academic_year.php" method="post">
                        <div class="mb-4 d-flex align-items-center form-group-flex">
                            <label for="academic-year" class="form-label-custom">ปีการศึกษา:</label>
                            <div class="flex-grow-1">
                                <input type="text" class="form-control" id="academic-year" name="academic_year" value="<?php echo htmlspecialchars($current_academic_year); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4 d-flex align-items-center form-group-flex">
                            <label for="web-url" class="form-label-custom">Url Web:</label>
                            <div class="flex-grow-1">
                                <input type="text" class="form-control" id="web-url" name="web_url" value="<?php echo htmlspecialchars($current_url); ?>" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                            <button type="submit" id="submitBtn" class="btn btn-save" disabled>บันทึก</button>
                        </div>
                    </form>
                </main>
            </div>
        </div>
    </div>

    <?php include '../../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.getElementById('notification-message');
            if (notification) {
                setTimeout(function() {
                    notification.style.opacity = '0';
                    setTimeout(function() {
                        notification.style.display = 'none';
                    }, 500);
                }, 2000);
            }

            const sidebar = document.querySelector('.sidebar');
            const menuHeader = document.querySelector('.sidebar .menu-header');
            if (menuHeader) {
                menuHeader.addEventListener('click', () => {
                    if (window.innerWidth <= 1024) sidebar.classList.toggle('is-open');
                });
            }

            const academicYearInput = document.getElementById('academic-year');
            const webUrlInput = document.getElementById('web-url');
            const submitBtn = document.getElementById('submitBtn');
            const origY = academicYearInput.value,
                origU = webUrlInput.value;

            function check() {
                submitBtn.disabled = (academicYearInput.value === origY && webUrlInput.value === origU);
            }
            academicYearInput.addEventListener('input', check);
            webUrlInput.addEventListener('input', check);
        });
    </script>
</body>

</html>