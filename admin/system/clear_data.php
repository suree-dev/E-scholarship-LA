<?php
session_start();
include '../../include/config.php';

$sch_names = ["", "ทุนที่ 1", "ทุนที่ 2", "ทุนที่ 3"];
if (isset($connect1)) {
    $sql_year = "SELECT st_name_1, st_name_2, st_name_3 FROM tb_year WHERE y_id = 1";
    $res_year = mysqli_query($connect1, $sql_year);
    if ($res_year && $row = mysqli_fetch_assoc($res_year)) {
        $sch_names[1] = $row['st_name_1'];
        $sch_names[2] = $row['st_name_2'];
        $sch_names[3] = $row['st_name_3'];
    }
}

$page_title = "ล้างข้อมูลเว็บไซต์";

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
            width: 100%;
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

        .clear-list-wrapper {
            margin-top: 10px;
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
                        <h1 class="m-0 fw-bold d-flex align-items-center" style="font-size: 22px; color: #333;">
                            <i class="fa-solid fa-broom me-2"></i>
                            <span><?php echo $page_title; ?></span>
                        </h1>
                    </div>

                    <?php
                    if (isset($_SESSION['clear_msg'])) {
                        $msg_type = $_SESSION['clear_type'] == 'success' ? 'success' : 'error';
                        echo "<script>
                            Swal.fire({
                                icon: '$msg_type',
                                title: '" . $_SESSION['clear_msg'] . "',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        </script>";
                        unset($_SESSION['clear_msg']);
                        unset($_SESSION['clear_type']);
                    }
                    ?>

                    <div class="clear-list-wrapper">
                        <div class="clear-list card border-0 shadow-none">
                            <div class="clear-item">
                                <div class="item-text fw-medium">1. ล้างข้อมูลนักศึกษา / ประวัติการขอทุน ทั้งหมด</div>
                                <button class="btn-clear" onclick="confirmClear(this, 'student_all')">ล้างข้อมูล</button>
                            </div>

                            <div class="clear-item sub-item">
                                <div class="item-text">- <?php echo htmlspecialchars($sch_names[1]); ?></div>
                                <button class="btn-clear" onclick="confirmClear(this, 'student_type1')">ล้างข้อมูล</button>
                            </div>
                            <div class="clear-item sub-item">
                                <div class="item-text">- <?php echo htmlspecialchars($sch_names[2]); ?></div>
                                <button class="btn-clear" onclick="confirmClear(this, 'student_type2')">ล้างข้อมูล</button>
                            </div>
                            <div class="clear-item sub-item">
                                <div class="item-text">- <?php echo htmlspecialchars($sch_names[3]); ?></div>
                                <button class="btn-clear" onclick="confirmClear(this, 'student_type3')">ล้างข้อมูล</button>
                            </div>

                            <div class="clear-item">
                                <div class="item-text fw-medium">2. ล้างข้อมูลคณะกรรมการ / อาจารย์ที่ปรึกษา ทั้งหมด</div>
                                <button class="btn-clear" onclick="confirmClear(this, 'staff_all')">ล้างข้อมูล</button>
                            </div>
                            <div class="clear-item sub-item">
                                <div class="item-text">- ล้างข้อมูลคณะกรรมการ</div>
                                <button class="btn-clear" onclick="confirmClear(this, 'committees')">ล้างข้อมูล</button>
                            </div>
                            <div class="clear-item sub-item">
                                <div class="item-text">- ล้างข้อมูลอาจารย์ที่ปรึกษา</div>
                                <button class="btn-clear" onclick="confirmClear(this, 'advisors')">ล้างข้อมูล</button>
                            </div>

                            <div class="clear-item">
                                <div class="item-text fw-medium">3. ล้างข้อมูลสาขาวิชา ทั้งหมด</div>
                                <button class="btn-clear" onclick="confirmClear(this, 'programs')">ล้างข้อมูล</button>
                            </div>

                            <div class="clear-item">
                                <div class="item-text fw-medium">4. ล้างข้อมูลข่าวสารประชาสัมพันธ์ ทั้งหมด</div>
                                <button class="btn-clear" onclick="confirmClear(this, 'news')">ล้างข้อมูล</button>
                            </div>

                            <div class="clear-item">
                                <div class="item-text text-danger fw-bold">5. ล้างข้อมูล ทั้งหมด (Reset ระบบ)</div>
                                <button class="btn-clear" onclick="confirmClear(this, 'reset_system')">ล้างข้อมูล</button>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <?php include '../../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function confirmClear(btn, actionType) {
            Swal.fire({
                title: 'ยืนยันการลบข้อมูล?',
                text: "การดำเนินการนี้ไม่สามารถย้อนกลับได้ ข้อมูลจะถูกลบออกจากฐานข้อมูลถาวร!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'ยืนยัน ลบข้อมูล',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const allButtons = document.querySelectorAll('.btn-clear');
                    allButtons.forEach(b => b.disabled = true);
                    btn.innerText = 'กำลังลบ...';
                    window.location.href = `../system/process_clear.php?action=${actionType}`;
                }
            })
        }

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