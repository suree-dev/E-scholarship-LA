<?php
session_start();
include '../../include/config.php';

$page_title = "ข้อมูลคะแนน";
$scholarship_title = "กรุณาเลือกประเภททุน";
$scholarship_id = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$search_id = isset($_GET['search_id']) ? mysqli_real_escape_string($connect1, $_GET['search_id']) : '';

if ($scholarship_id >= 1 && $scholarship_id <= 3) {
    $column_name = "st_name_" . $scholarship_id;
    $sql_title = "SELECT `$column_name` FROM tb_year WHERE y_id = 1";
    $result_title = mysqli_query($connect1, $sql_title);
    if ($result_title && mysqli_num_rows($result_title) > 0) {
        $data_title = mysqli_fetch_row($result_title);
        $scholarship_title = !empty($data_title[0]) ? $data_title[0] : "ทุนประเภทที่ {$scholarship_id}";
    }
}

$scores = [];
if ($scholarship_id > 0) {
    $sql_scores = "SELECT s.st_id, s.st_code, s.st_firstname, s.st_lastname, p.g_program, s.sum_score, s.st_average, s.st_confirm
                    FROM tb_student AS s
                    LEFT JOIN 
                        tb_program AS p ON s.st_program = p.g_id
                    WHERE s.st_type = '$scholarship_id' AND s.sum_score >= 0";

    if (!empty($search_id)) {
        $sql_scores .= " AND st_code LIKE '%$search_id%'";
    }

    $sql_scores .= " ORDER BY st_average DESC";

    $result_scores = mysqli_query($connect1, $sql_scores);
    if ($result_scores) {
        while ($row = mysqli_fetch_assoc($result_scores)) {
            $scores[] = $row;
        }
    }
}
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
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
                height: auto !important;
                padding: 15px 20px !important;
                cursor: pointer;
            }

            .sidebar .has-submenu ul {
                display: none;
                position: static !important;
                width: 100% !important;
                background-color: #f8f9fa !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                list-style: none;
            }

            .sidebar .has-submenu.submenu-open>ul {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .sidebar .has-submenu ul li a {
                padding: 12px 30px !important;
                border-bottom: 1px solid #eee;
                display: block;
                color: #333 !important;
                font-size: 14px;
            }


            .sidebar .has-submenu>a i.fa-chevron-right {
                transition: transform 0.3s;
            }

            .sidebar .has-submenu.submenu-open>a i.fa-chevron-right {
                transform: rotate(90deg);
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
                <main class="main-content">
                    <?php
                    if (isset($_SESSION['message'])) {
                        $bg_color = $_SESSION['message']['type'] == 'success' ? '#28a745' : '#dc3545';
                        echo '<div id="notification-message" class="alert border-0 shadow-sm" style="background-color:' . $bg_color . '; color: white; transition: opacity 0.5s ease;">';
                        echo htmlspecialchars($_SESSION['message']['text']);
                        echo '</div>';
                        unset($_SESSION['message']);
                    }
                    ?>

                    <h1 class="content-header fw-bold">
                        <?php echo htmlspecialchars($page_title . ' [' . $scholarship_title . ']'); ?>
                    </h1>

                    <div class="action-bar-wrapper">
                        <form action="" method="get" class="d-flex gap-2">
                            <input type="hidden" name="type" value="<?php echo $scholarship_id; ?>">
                            <input type="text" name="search_id" class="form-control" style="width: 250px;" placeholder="รหัสนักศึกษา" value="<?php echo htmlspecialchars($search_id); ?>">
                            <button type="submit" class="btn-search-custom">ค้นหา</button>
                        </form>
                        <a href="../scores/download_scores.php?type=<?php echo $scholarship_id; ?>" class="btn-export-pill">Export</a>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 70px;">ลำดับ</th>
                                    <th style="width: 150px;">รหัสนักศึกษา</th>
                                    <th style="width: 200px;">ชื่อ-สกุล</th>
                                    <th>สาขาวิชา</th>
                                    <th style="width: 150px;" class="text-center">คะแนน (%)</th>
                                    <th class="text-center" style="width: 150px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($scores)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted fw-medium">
                                            <?php
                                            if ($scholarship_id == 0) {
                                                echo 'กรุณาเลือกประเภททุนจากเมนูด้านข้าง';
                                            } else {
                                                echo 'ไม่พบข้อมูลคะแนนในระบบ';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($scores as $index => $score): ?>
                                        <tr id="score-row-<?php echo $score['st_id']; ?>">
                                            <td class="text-center fw-medium"><?php echo $index + 1; ?>.</td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($score['st_code']); ?></td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($score['st_firstname'] . ' ' . $score['st_lastname']); ?></td>
                                            <td><?php echo htmlspecialchars($score['g_program']); ?></td>
                                            <td class="text-center fw-bold">
                                                <?php
                                                $percent = ($score['st_average'] / 4) * 100;
                                                echo number_format($percent, 2);
                                                ?> %
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="view_score_details.php?id=<?php echo $score['st_id']; ?>&type=<?php echo $scholarship_id; ?>" class="btn-outline-circle btn-outline-view" title="ดูรายละเอียดคะแนน">
                                                        <i class="fas fa-search" style="font-size: 14px;"></i>
                                                    </a>

                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="pt-4 text-center text-muted fw-bold">ทั้งหมด <?php echo count($scores); ?> รายการ</td>
                                </tr>
                            </tfoot>
                        </table>
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
            if (sidebar) {
                const menuHeader = sidebar.querySelector('.sidebar .menu-header');
                if (menuHeader) {
                    menuHeader.addEventListener('click', function() {
                        if (window.innerWidth <= 1024) {
                            sidebar.classList.toggle('is-open');
                        }
                    });
                }
                const submenuToggles = sidebar.querySelectorAll('.has-submenu > a');
                submenuToggles.forEach(toggle => {
                    toggle.addEventListener('click', function(event) {
                        if (window.innerWidth <= 1024) {
                            event.preventDefault();
                            const parentLi = this.parentElement;
                            parentLi.classList.toggle('submenu-open');
                        }
                    });
                });
            }

            const notification = document.getElementById('notification-message');
            if (notification) {
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 500);
                }, 5000);
            }

            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const deleteUrl = this.href;
                    Swal.fire({
                        title: 'ยืนยันการลบ',
                        text: "คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลคะแนนทั้งหมดของนักศึกษาคนนี้?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#32a838ff',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'ยืนยัน',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = deleteUrl;
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>