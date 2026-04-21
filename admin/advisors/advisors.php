<?php
session_start();
include '../../include/config.php';

$page_title = "จัดการข้อมูลอาจารย์ที่ปรึกษา";

// --- ตรวจสอบการทำงาน (เพิ่ม หรือ แก้ไข) ---
$edit_mode = false;
$advisor_to_edit = ['id' => null, 'name' => '']; // ค่าเริ่มต้นสำหรับฟอร์ม

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_mode = true;
    $advisor_id = mysqli_real_escape_string($connect1, $_GET['id']);

    // ดึงข้อมูลอาจารย์ที่ปรึกษา(tc_type = 4)ที่ต้องการแก้ไข
    $sql_edit = "SELECT tc_id, tc_name FROM tb_teacher WHERE tc_id = '$advisor_id' AND tc_type = 4";
    $result_edit = mysqli_query($connect1, $sql_edit);
    $data_edit = mysqli_fetch_assoc($result_edit);

    if ($data_edit) {
        $advisor_to_edit['id'] = $data_edit['tc_id'];
        $advisor_to_edit['name'] = $data_edit['tc_name'];
    }
}

// --- ดึงข้อมูลอาจารย์ที่ปรึกษาทั้งหมด (tc_type = 4) มาแสดงในตาราง ---
$sql_list = "SELECT tc_id, tc_name FROM tb_teacher WHERE tc_type = 4 ORDER BY tc_name ASC";
$result_list = mysqli_query($connect1, $sql_list);

$advisors = [];
if ($result_list) {
    while ($row = mysqli_fetch_assoc($result_list)) {
        $advisors[] = ['id' => $row['tc_id'], 'name' => $row['tc_name']];
    }
}

include '../include/header.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แอดมิน - <?php echo $page_title; ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/bg/head_01.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/css/global2.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">

    <link rel="stylesheet" href="../../assets/css/navigation.css">
    <link rel="stylesheet" href="../../assets/css/ui-elements.css">

    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets/css/tables.css">

    <link rel="stylesheet" href="../../assets/css/pages.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
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
                        <form id="advisorForm" action="<?php echo $edit_mode ? '../advisors/update_advisor.php' : '../advisors/add_advisor.php'; ?>" method="post">
                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="advisor_id" value="<?php echo htmlspecialchars($advisor_to_edit['id']); ?>">
                            <?php endif; ?>

                            <div class="row align-items-center g-3">
                                <div class="col-auto">
                                    <label for="advisor-name" class="fw-bold">ชื่อ-สกุล:</label>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" id="advisor-name" name="advisor_name" value="<?php echo htmlspecialchars($advisor_to_edit['name']); ?>" required autocomplete="off">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" id="submitBtn" class="btn btn-save rounded-pill" disabled>บันทึก</button>
                                    <?php if ($edit_mode): ?>
                                        <a href="../advisors/advisors.php" class="btn btn-secondary rounded-pill px-5">ยกเลิก</a>
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
                                    <th>ชื่อ-สกุล</th>
                                    <th class="text-center" style="width: 120px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($advisors)): ?>
                                    <?php foreach ($advisors as $index => $advisor): ?>
                                        <tr>
                                            <td class="text-center fw-medium"><?php echo $index + 1; ?>.</td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($advisor['name']); ?></td>
                                            <td class="text-center">
                                                <a href="../advisors/advisors.php?action=edit&id=<?php echo $advisor['id']; ?>" class="btn-outline-circle btn-outline-edit" title="แก้ไขข้อมูล">
                                                    <i class="fas fa-pencil-alt" style="font-size: 14px;"></i>
                                                </a>
                                                <a href="../advisors/delete_advisor.php?id=<?php echo $advisor['id']; ?>" class="btn-outline-circle btn-outline-delete btn-delete" title="ลบข้อมูล">
                                                    <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">ไม่มีข้อมูลอาจารย์ที่ปรึกษาในระบบ</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="pt-4 text-center text-muted fw-bold">ทั้งหมด <?php echo count($advisors); ?> ท่าน</td>
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
                        text: "คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?",
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

            const advisorInput = document.getElementById('advisor-name');
            const submitBtn = document.getElementById('submitBtn');
            const originalValue = advisorInput.value.trim();

            const existingAdvisors = <?php echo json_encode(array_column($advisors, 'name')); ?>;

            let typingTimer;

            function validateAdvisorInput() {
                const currentValue = advisorInput.value.trim();
                const isModified = currentValue.toLowerCase() !== originalValue.toLowerCase();
                const isDuplicate = existingAdvisors.some(name => {
                    return name.trim().toLowerCase() === currentValue.toLowerCase() && currentValue.toLowerCase() !== originalValue.toLowerCase();
                });

                if (isModified && currentValue !== "" && !isDuplicate) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            }

            submitBtn.disabled = true;
            advisorInput.addEventListener('input', function() {
                submitBtn.disabled = true;
                clearTimeout(typingTimer);
                typingTimer = setTimeout(validateAdvisorInput, 1000);
            });

            const advisorForm = document.getElementById('advisorForm');
            if (advisorForm) {
                advisorForm.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'กำลังบันทึก...';
                });
            }
        });
    </script>
</body>

</html>