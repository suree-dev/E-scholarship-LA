<?php
session_start();
include '../include/config.php';

$page_title = "เพิ่มรหัสนักศึกษา (ระงับการขอทุน)";

$sql_check = "SELECT code_student FROM tb_ban";
$result_check = mysqli_query($connect1, $sql_check);
$existing_ids = [];
if ($result_check) {
    while ($row = mysqli_fetch_assoc($result_check)) {
        $existing_ids[] = $row['code_student'];
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
    <link rel="stylesheet" href="../assets/css/responsive.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="sticky-header-wrapper">
        <?php include('../include/navbar.php'); ?>
        <?php include('../include/status_bar.php'); ?>
    </div>

    <div class="container-fluid dashboard-container">
        <div class="row g-4">
            <div class="col-12 col-sidebar-20">
                <?php include '../include/sidebar.php'; ?>
            </div>

            <div class="col-12 col-main-80">
                <main class="main-content shadow-sm">
                    <div class="content-header border-bottom pb-3 mb-4">
                        <h1 class="m-0 fw-bold" style="font-size: 22px; color: #333;"><?php echo $page_title; ?></h1>
                        <a href="../student/susp_std.php" class="btn btn-secondary rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-arrow-left me-2"></i> ย้อนกลับ
                        </a>
                    </div>

                    <form action="../admin/students/save_suspended.php" id="suspendedForm" method="post">
                        <div class="mb-4">
                            <button type="button" id="add-row-btn" class="btn-primary-pill text-decoration-none">
                                <i class="fa fa-plus me-2"></i> เพิ่มช่อง
                            </button>
                        </div>

                        <div id="form-rows-container" class="mt-2">
                        </div>

                        <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                            <button type="submit" id="submit-btn" class="btn btn-save rounded-pill" disabled>บันทึก</button>
                        </div>
                    </form>
                </main>
            </div>
        </div>
    </div>

    <template id="row-template">
        <div class="form-row-wrapper-item">
            <div class="row align-items-start g-3">
                <div class="col-auto pt-2">
                    <label class="fw-bold text-muted">รหัสนักศึกษา:</label>
                </div>
                <div class="col">
                    <div class="dynamic-input-group">
                        <input type="text" name="student_id[]" class="form-control student-input" required placeholder="กรอกรหัสนักศึกษา">
                        <div class="error-feedback-text"></div>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn-outline-circle btn-outline-delete btn-delete">
                        <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addRowBtn = document.getElementById('add-row-btn');
            const rowsContainer = document.getElementById('form-rows-container');
            const rowTemplate = document.getElementById('row-template');
            const submitBtn = document.getElementById('submit-btn');

            const existingIds = <?php echo json_encode($existing_ids); ?>;

            function validateAll() {
                const inputs = rowsContainer.querySelectorAll('.student-input');
                let isFormValid = true;
                const currentValues = [];

                inputs.forEach((input, index) => {
                    const value = input.value.trim();
                    const errorElement = input.nextElementSibling;
                    let errorText = "";

                    if (value === "") {
                        isFormValid = false;
                    } else if (existingIds.includes(value)) {
                        errorText = "รหัสนักศึกษานี้ถูกระงับอยู่ในระบบแล้ว";
                        isFormValid = false;
                    } else if (currentValues.includes(value)) {
                        errorText = "ข้อมูลรหัสซ้ำกับช่องด้านบน";
                        isFormValid = false;
                    }

                    if (errorText) {
                        errorElement.innerText = errorText;
                        input.classList.add('is-invalid');
                    } else {
                        errorElement.innerText = "";
                        input.classList.remove('is-invalid');
                    }

                    if (value !== "") currentValues.push(value);
                });

                submitBtn.disabled = !isFormValid;
            }

            function addRow() {
                const templateContent = rowTemplate.content.cloneNode(true);
                const newInput = templateContent.querySelector('.student-input');
                newInput.addEventListener('input', validateAll);
                rowsContainer.appendChild(templateContent);
                validateAll();
            }

            addRow();

            addRowBtn.addEventListener('click', addRow);

            rowsContainer.addEventListener('click', function(event) {
                if (event.target && event.target.classList.contains('btn-delete-row-custom')) {
                    if (rowsContainer.querySelectorAll('.form-row-wrapper-item').length > 1) {
                        event.target.closest('.form-row-wrapper-item').remove();
                        validateAll();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            text: 'ไม่สามารถลบแถวสุดท้ายได้',
                            confirmButtonColor: '#003c71'
                        });
                    }
                }
            });

            const sidebar = document.querySelector('.sidebar');
            const menuHeader = document.querySelector('.sidebar .menu-header');
            if (menuHeader) {
                menuHeader.addEventListener('click', () => {
                    if (window.innerWidth <= 1024) sidebar.classList.toggle('is-open');
                });
            }
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>

</html>