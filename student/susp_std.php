<?php
session_start();
include '../include/config.php';

$page_title = "รายชื่อนักศึกษาที่ถูกระงับการขอทุน";

$sql = "SELECT b.id_ban, b.code_student, s.st_firstname, s.st_lastname 
        FROM tb_ban AS b
        LEFT JOIN tb_student AS s ON b.code_student = s.st_code";

if (isset($_GET['search_id']) && !empty($_GET['search_id'])) {
    $search_id = mysqli_real_escape_string($connect1, $_GET['search_id']);
    $sql .= " WHERE b.code_student LIKE '%$search_id%'";
}

$sql .= " ORDER BY b.date_ban DESC";
$result = mysqli_query($connect1, $sql);

$susp_std = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $susp_std[] = [
            'id' => $row['id_ban'],
            'student_id' => $row['code_student'],
            'fullname' => ($row['st_firstname']) ? $row['st_firstname'] . ' ' . $row['st_lastname'] : 'ไม่พบข้อมูลในระบบ'
        ];
    }
}

include '../include/header.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $page_title; ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/bg/head_01.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/global2.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
    <link rel="stylesheet" href="../assets/css/ui-elements.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
    <link rel="stylesheet" href="../assets/css/tables.css">
    <link rel="stylesheet" href="../assets/css/pages.css">

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
        <?php include('../include/navbar.php'); ?>
        <?php include('../include/status_bar.php'); ?>
    </div>

    <button onclick="scrollToTop()" id="scrollTopBtn" class="scroll-top-btn" title="เลื่อนขึ้นข้างบน">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <div class="container-fluid dashboard-container">
        <div class="row g-4">
            <div class="col-12 col-sidebar-20">
                <?php include '../include/sidebar.php'; ?>
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

                    <div class="action-bar-wrapper">
                        <form action="../student/susp_std.php" method="get" class="d-flex gap-2">
                            <input type="text" name="search_id" class="form-control" style="width: 250px;" placeholder="รหัสนักศึกษา" value="<?php echo isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : ''; ?>">
                            <button type="submit" class="btn-search-custom">ค้นหา</button>
                        </form>
                        <a href="../student/susp_std_add.php" class="btn-primary-pill text-decoration-none">เพิ่มรหัสนักศึกษา</a>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 80px;">ลำดับ</th>
                                    <th>รหัสนักศึกษา</th>
                                    <th>ชื่อ-สกุล</th>
                                    <th>สถานะ</th>
                                    <th class="text-center" style="width: 120px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($susp_std)): ?>
                                    <?php foreach ($susp_std as $index => $student): ?>
                                        <tr>
                                            <td class="text-center fw-medium"><?php echo $index + 1; ?>.</td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($student['student_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                                            <td>
                                                <span class="status-suspended">
                                                    <i class="fas fa-times-circle"></i>
                                                    ถูกระงับ
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <!-- ปุ่มไอคอนลบแบบวงกลม Outline สีแดง -->
                                                <a href="../admin/students/unsuspend_student.php?id=<?php echo $student['id']; ?>" class="btn-outline-circle btn-outline-delete btn-delete" title="ยกเลิกการระงับ">
                                                    <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted fw-medium">
                                            <?php echo isset($_GET['search_id']) ? 'ไม่พบข้อมูลที่ค้นหา' : 'ไม่มีรายชื่อนักศึกษาที่ถูกระงับในขณะนี้'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="pt-4 text-center text-muted fw-bold">ทั้งหมด <?php echo count($susp_std); ?> รายการ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let mybutton = document.getElementById("scrollTopBtn");
        window.onscroll = function() {
            scrollFunction()
        };

        function scrollFunction() {
            if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
                mybutton.style.display = "block";
            } else {
                mybutton.style.display = "none";
            }
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
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

            const notification = document.getElementById('notification-message');
            if (notification) {
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 500);
                }, 2000);
            }

            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const deleteUrl = this.href;

                    Swal.fire({
                        title: 'ยืนยันการยกเลิก',
                        text: "คุณต้องการยกเลิกการระงับรหัสนักศึกษานี้ใช่หรือไม่?",
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