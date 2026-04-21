<?php
session_start();
include '../../include/config.php';

$page_title = "จัดการข้อมูลสาขาวิชา";

$edit_mode = false;
$major_to_edit = ['id' => null, 'name' => ''];

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_mode = true;
    $major_id = mysqli_real_escape_string($connect1, $_GET['id']);

    $sql_edit = "SELECT g_id, g_program FROM tb_program WHERE g_id = '$major_id'";
    $result_edit = mysqli_query($connect1, $sql_edit);
    $data_edit = mysqli_fetch_assoc($result_edit);

    if ($data_edit) {
        $major_to_edit['id'] = $data_edit['g_id'];
        $major_to_edit['name'] = $data_edit['g_program'];
    }
}

$sql_list = "SELECT g_id, g_program FROM tb_program ORDER BY g_program ASC";
$result_list = mysqli_query($connect1, $sql_list);

$majors = [];
if ($result_list) {
    while ($row = mysqli_fetch_assoc($result_list)) {
        $majors[] = ['id' => $row['g_id'], 'name' => $row['g_program']];
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
                        <h1 class="m-0 fw-bold" style="font-size: 22px; color: #333;">จัดการข้อมูลสาขาวิชา</h1>
                    </div>

                    <div class="card-form-wrapper">
                        <form id="majorForm" action="<?php echo $edit_mode ? '../settings/update_major.php' : '../settings/add_major.php'; ?>" method="post">
                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="major_id" value="<?php echo htmlspecialchars($major_to_edit['id']); ?>">
                            <?php endif; ?>

                            <div class="row align-items-center g-3">
                                <div class="col-auto">
                                    <label for="major-name" class="fw-bold">สาขาวิชา:</label>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" id="major-name" name="major_name" value="<?php echo htmlspecialchars($major_to_edit['name']); ?>" required autocomplete="off">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" id="submitBtn" class="btn btn-save rounded-pill" disabled>บันทึก</button>
                                    <?php if ($edit_mode): ?>
                                        <a href="../settings/majors.php" class="btn btn-secondary rounded-pill px-5">ยกเลิก</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 100px;">ลำดับ</th>
                                    <th>สาขาวิชา</th>
                                    <th class="text-center" style="width: 120px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($majors)): ?>
                                    <?php foreach ($majors as $index => $major): ?>
                                        <tr>
                                            <td class="text-center fw-medium"><?php echo $index + 1; ?>.</td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($major['name']); ?></td>
                                            <td class="text-center">
                                                <a href="../settings/majors.php?action=edit&id=<?php echo $major['id']; ?>" class="btn-outline-circle btn-outline-edit" title="แก้ไขข้อมูล">
                                                    <i class="fas fa-pencil-alt" style="font-size: 14px;"></i>
                                                </a>
                                                <a href="../settings/delete_major.php?id=<?php echo $major['id']; ?>" class="btn-outline-circle btn-outline-delete btn-delete" title="ลบข้อมูล">
                                                    <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">ไม่มีข้อมูลสาขาวิชาในระบบ</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="pt-4 text-center text-muted fw-bold">ทั้งหมด <?php echo count($majors); ?> สาขา</td>
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

            const majorInput = document.getElementById('major-name');
            const submitBtn = document.getElementById('submitBtn');
            const originalValue = majorInput.value.trim();

            function checkMajorChange() {
                if (majorInput.value.trim().toLowerCase() !== originalValue.toLowerCase() && majorInput.value.trim() !== "") {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            }

            submitBtn.disabled = true;
            majorInput.addEventListener('input', checkMajorChange);

            const majorForm = document.getElementById('majorForm');
            if (majorForm) {
                majorForm.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'กำลังบันทึก...';
                });
            }

            const tableBody = document.querySelector('.data-table tbody');
            if (tableBody) {
                tableBody.addEventListener('click', function(event) {
                    const button = event.target.closest('a.btn-delete');
                    if (!button) return;

                    event.preventDefault();
                    const deleteUrl = button.href;
                    Swal.fire({
                        title: 'ยืนยันการลบ',
                        text: "คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลสาขาวิชานี้?",
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
            }
        });
    </script>
</body>

</html>