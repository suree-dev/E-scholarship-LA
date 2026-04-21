<?php
session_start();
include '../../include/config.php';

$sql = "SELECT 
            i.issue_id, 
            i.issue_topic, 
            i.issue_date, 
            s.st_firstname, 
            s.st_lastname, 
            s.st_code 
        FROM 
            tb_issue AS i
        LEFT JOIN 
            tb_student AS s ON i.student_id = s.st_id
        ORDER BY 
            i.issue_date DESC";

$result = mysqli_query($connect1, $sql);

include '../../include/header.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - แจ้งปัญหาการใช้งาน</title>
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
                        <h1 class="m-0 fw-bold" style="font-size: 22px; color: #333;">รายการแจ้งปัญหาการใช้งาน</h1>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 80px;">ลำดับ</th>
                                    <th>หัวข้อปัญหา</th>
                                    <th>รหัสผู้แจ้ง</th>
                                    <th>ชื่อผู้แจ้ง</th>
                                    <th style="width: 150px;">วันที่แจ้ง</th>
                                    <th class="text-center" style="width: 120px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && mysqli_num_rows($result) > 0) {
                                    $i = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $st_code = !empty($row['st_code']) ? htmlspecialchars($row['st_code']) : '-';
                                        $st_fullname = !empty($row['st_firstname']) ? htmlspecialchars($row['st_firstname'] . ' ' . $row['st_lastname']) : 'ไม่พบข้อมูลนักศึกษา';
                                ?>
                                        <tr>
                                            <td class="text-center fw-medium"><?php echo $i++; ?>.</td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($row['issue_topic']); ?></td>
                                            <td class="fw-medium"><?php echo $st_code; ?></td>
                                            <td class="fw-medium"><?php echo $st_fullname; ?></td>
                                            <td class="fw-medium"><?php echo date('d/m/', strtotime($row['issue_date'])) . (date('Y', strtotime($row['issue_date'])) + 543); ?></td>
                                            <td class="text-center">
                                                <a href="../issues/issue_view.php?id=<?php echo $row['issue_id']; ?>" class="btn-outline-circle btn-outline-view" title="ดูรายละเอียด">
                                                    <i class="fas fa-search" style="font-size: 14px;"></i>
                                                </a>
                                                <a href="../issues/delete_issue.php?id=<?php echo $row['issue_id']; ?>" class="btn-outline-circle btn-outline-delete btn-delete" title="ลบข้อมูล">
                                                    <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center py-5 text-muted">ไม่มีรายการแจ้งปัญหาในระบบ</td></tr>';
                                }
                                ?>
                            </tbody>
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
            const menuHeader = document.querySelector('.sidebar .menu-header');
            if (menuHeader && sidebar) {
                menuHeader.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        sidebar.classList.toggle('is-open');
                    }
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
                        text: "คุณแน่ใจหรือไม่ว่าต้องการลบรายการปัญหานี้?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#32a838ff',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'ยืนยัน',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) window.location.href = deleteUrl;
                    });
                });
            });
        });
    </script>
</body>

</html>