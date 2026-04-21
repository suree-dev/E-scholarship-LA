<?php
session_start();
include '../../include/config.php';

$page_title = "จัดการข้อมูลคณะกรรมการ";

$edit_mode = false;
$committee_to_edit = ['id' => null, 'username' => '', 'password' => '', 'fullname' => ''];

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_mode = true;
    $id = mysqli_real_escape_string($connect1, $_GET['id']);

    $sql_edit = "SELECT tc_id, tc_user, tc_name FROM tb_teacher WHERE tc_id = '$id' AND tc_type = 5";
    $result_edit = mysqli_query($connect1, $sql_edit);
    $data_edit = mysqli_fetch_assoc($result_edit);

    if ($data_edit) {
        $committee_to_edit['id'] = $data_edit['tc_id'];
        $committee_to_edit['username'] = $data_edit['tc_user'];
        $committee_to_edit['fullname'] = $data_edit['tc_name'];
    }
}

// ดึงข้อมูลคณะกรรมการทั้งหมด (tc_type = 5) มาแสดงในตาราง
$sql_list = "SELECT tc_id, tc_user, tc_pass, tc_name FROM tb_teacher WHERE tc_type = 5 ORDER BY tc_name ASC";
$result_list = mysqli_query($connect1, $sql_list);

$committees = [];
if ($result_list) {
    while ($row = mysqli_fetch_assoc($result_list)) {
        $committees[] = [
            'id' => $row['tc_id'],
            'username' => $row['tc_user'],
            'password' => $row['tc_pass'],
            'fullname' => $row['tc_name']
        ];
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

    <button onclick="scrollToTop()" id="scrollTopBtn" class="scroll-top-btn" title="เลื่อนขึ้นข้างบน">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

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

                    <div class="card-form-wrapper">
                        <form id="committeeForm" action="<?php echo $edit_mode ? '../committees/update_committees.php' : '../committees/add_committees.php'; ?>" method="post">
                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="committee_id" value="<?php echo htmlspecialchars($committee_to_edit['id']); ?>">
                            <?php endif; ?>

                            <div class="row mb-3 align-items-center">
                                <label for="committee-username" class="col-auto label-column">ชื่อผู้ใช้:</label>
                                <div class="col">
                                    <input type="text" class="form-control" id="committee-username" name="committee_username" value="<?php echo htmlspecialchars($committee_to_edit['username']); ?>" required autocomplete="off">
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label for="committee-password" class="col-auto label-column">รหัสผ่าน:</label>
                                <div class="col">
                                    <input type="password" class="form-control" id="committee-password" name="committee_password" placeholder="<?php echo $edit_mode ? '(กรอกหากต้องการเปลี่ยน)' : ''; ?>" <?php echo !$edit_mode ? 'required' : ''; ?>>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label for="committee-fullname" class="col-auto label-column">ชื่อ-สกุล:</label>
                                <div class="col">
                                    <input type="text" class="form-control" id="committee-fullname" name="committee_fullname" value="<?php echo htmlspecialchars($committee_to_edit['fullname']); ?>" required autocomplete="off">
                                </div>
                            </div>

                            <div class="form-actions-wrapper">
                                <?php if ($edit_mode): ?>
                                    <a href="../committees/committees.php" class="btn btn-cancel rounded-pill">ยกเลิก</a>
                                    <button type="submit" id="submitBtn" class="btn btn-save rounded-pill" disabled>บันทึก</button>
                                <?php else: ?>
                                    <button type="submit" id="submitBtn" class="btn btn-save rounded-pill" disabled>บันทึก</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 80px;">ลำดับ</th>
                                    <th>ชื่อผู้ใช้</th>
                                    <th>รหัสผ่าน</th>
                                    <th>ชื่อ-สกุล</th>
                                    <th class="text-center" style="width: 120px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($committees)): ?>
                                    <?php foreach ($committees as $index => $committee): ?>
                                        <tr>
                                            <td class="text-center fw-medium"><?php echo $index + 1; ?>.</td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($committee['username']); ?></td>
                                            <td class="fw-medium text-muted">••••••••</td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($committee['fullname']); ?></td>
                                            <td class="text-center">
                                                <a href="../committees/committees.php?action=edit&id=<?php echo $committee['id']; ?>" class="btn-outline-circle btn-outline-edit" title="แก้ไขข้อมูล">
                                                    <i class="fas fa-pencil-alt" style="font-size: 14px;"></i>
                                                </a>
                                                <a href="../committees/delete_committees.php?id=<?php echo $committee['id']; ?>" class="btn-outline-circle btn-outline-delete btn-delete" title="ลบข้อมูล">
                                                    <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">ไม่มีข้อมูลคณะกรรมการในระบบ</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="pt-4 text-center text-muted fw-bold">ทั้งหมด <?php echo count($committees); ?> ท่าน</td>
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
            if (menuHeader) {
                menuHeader.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) sidebar.classList.toggle('is-open');
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
                        text: "คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?",
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

            const committeeForm = document.getElementById('committeeForm');
            const submitBtn = document.getElementById('submitBtn');
            const usernameInput = document.getElementById('committee-username');
            const passwordInput = document.getElementById('committee-password');
            const fullnameInput = document.getElementById('committee-fullname');

            const originalUsername = usernameInput.value.trim();
            const originalFullname = fullnameInput.value.trim();
            const editMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;
            const existingCommittees = <?php echo json_encode($committees); ?>;

            function validateForm() {
                const currentUsername = usernameInput.value.trim();
                const currentPassword = passwordInput.value;
                const currentFullname = fullnameInput.value.trim();

                let isModified = false;
                if (editMode) {
                    isModified = (currentUsername.toLowerCase() !== originalUsername.toLowerCase()) ||
                        (currentFullname.toLowerCase() !== originalFullname.toLowerCase()) ||
                        (currentPassword !== "");
                } else {
                    isModified = (currentUsername !== "" && currentFullname !== "" && currentPassword !== "");
                }

                const isDuplicate = existingCommittees.some(item => {
                    const normalizedInputUser = currentUsername.toLowerCase();
                    const normalizedInputName = currentFullname.toLowerCase();
                    const userMatch = (normalizedInputUser === item.username.toLowerCase() && normalizedInputUser !== originalUsername.toLowerCase());
                    const nameMatch = (normalizedInputName === item.fullname.toLowerCase() && normalizedInputName !== originalFullname.toLowerCase());
                    return userMatch || nameMatch;
                });

                submitBtn.disabled = !(isModified && currentUsername !== "" && currentFullname !== "" && !isDuplicate && (!(!editMode && currentPassword === "")));
            }

            submitBtn.disabled = true;
            usernameInput.addEventListener('input', validateForm);
            passwordInput.addEventListener('input', validateForm);
            fullnameInput.addEventListener('input', validateForm);

            committeeForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerText = 'กำลังบันทึก...';
            });
        });
    </script>
</body>

</html>