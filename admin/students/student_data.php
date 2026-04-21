<?php
session_start();
include '../../include/config.php';

$page_title = "ข้อมูล student";
$scholarship_title = "กรุณาเลือกประเภททุน";
$scholarship_id = isset($_GET['type']) ? (int)$_GET['type'] : 0;

if ($scholarship_id >= 1 && $scholarship_id <= 3) {
    $column_name = "st_name_" . $scholarship_id;
    $sql_title = "SELECT `$column_name` FROM tb_year WHERE y_id = 1";
    $result_title = mysqli_query($connect1, $sql_title);
    if ($result_title && mysqli_num_rows($result_title) > 0) {
        $data_title = mysqli_fetch_row($result_title);
        $scholarship_title = !empty($data_title[0]) ? $data_title[0] : "ทุนประเภทที่ {$scholarship_id}";
    }
}

$students = [];
if ($scholarship_id > 0) {
    if ($scholarship_id == 2) {
        $sql_students = "SELECT 
                            s.st_id, 
                            s.st_firstname, 
                            s.st_lastname, 
                            s.st_code,
                            s.st_confirm,
                            p.g_program,
                            m.tel_mem
                        FROM 
                            tb_student AS s
                        LEFT JOIN 
                            tb_program AS p ON s.st_program = p.g_id
                        LEFT JOIN
                            tb_member AS m ON (s.st_firstname = m.name_mem AND s.st_lastname = m.sur_mem)
                        WHERE 
                            s.st_type = '$scholarship_id'";
    } else {
        $sql_students = "SELECT 
                            s.st_id, 
                            s.st_firstname, 
                            s.st_lastname, 
                            s.st_code,
                            s.st_confirm,
                            p.g_program
                        FROM 
                            tb_student AS s
                        LEFT JOIN 
                            tb_program AS p ON s.st_program = p.g_id
                        WHERE 
                            s.st_type = '$scholarship_id'";
    }

    if (isset($_GET['search_id']) && !empty($_GET['search_id'])) {
        $search_id = mysqli_real_escape_string($connect1, $_GET['search_id']);
        $sql_students .= " AND s.st_code LIKE '%$search_id%'";
    }

    $sql_students .= " ORDER BY s.st_firstname ASC";

    $result_students = mysqli_query($connect1, $sql_students);
    if ($result_students) {
        while ($row = mysqli_fetch_assoc($result_students)) {
            $students[] = $row;
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

        .btn-outline-cancel {
            color: #dc3545;
            border: 1.5px solid #dc3545;
        }

        .btn-outline-cancel:hover {
            background-color: #dc3545;
            color: white;
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
                    <h1 class="content-header fw-bold">
                        <?php echo htmlspecialchars($page_title . ' [' . $scholarship_title . ']'); ?>
                    </h1>

                    <div class="action-bar-wrapper">
                        <form id="searchForm" action="../students/student_data.php" method="get" class="d-flex gap-2">
                            <input type="hidden" name="type" value="<?php echo $scholarship_id; ?>">
                            <input type="text" name="search_id" id="search_id" class="form-control" style="width: 250px;" placeholder="รหัสนักศึกษา"
                                value="<?php echo isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : ''; ?>"
                                oninput="if(this.value === '') { this.form.submit(); }">
                            <button type="submit" class="btn-search-custom">ค้นหา</button>
                        </form>

                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold" style="color: #555;">export ข้อมูลทั้งหมด :</span>
                            <a href="../students/export_students.php?type=<?php echo $scholarship_id; ?>" class="btn-export-pill"><i class="fa-solid fa-file-excel me-2"></i>Excel</a>
                            <a href="../students/export_students_pdf.php?type=<?php echo $scholarship_id; ?>" class="btn-export-pill">
                                <i class="fa-solid fa-file-pdf me-1"></i> PDF
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 70px;">ลำดับ</th>
                                    <th style="width: 150px;">รหัสนักศึกษา</th>
                                    <th style="width: 150px;">ชื่อ-สกุล</th>
                                    <th>สาขาวิชา</th>
                                    <th class="text-center" style="width: 150px;">สถานะ</th>
                                    <th class="text-center" style="width: 200px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted fw-medium">
                                            <?php
                                            if ($scholarship_id == 0) {
                                                echo 'กรุณาเลือกประเภททุนจากเมนูด้านข้าง';
                                            } else {
                                                echo 'ไม่พบข้อมูลนักศึกษาในระบบ';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $index => $student): ?>
                                        <tr>
                                            <td class="text-center fw-medium"><?php echo $index + 1; ?>.</td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($student['st_code']); ?></td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($student['st_firstname'] . ' ' . $student['st_lastname']); ?></td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($student['g_program'] ?: 'N/A'); ?></td>
                                            <td class="text-center">
                                                <?php if ($student['st_confirm'] == 1): ?>
                                                    <span class="badge-custom badge-success-light"><i class="fa-solid fa-check"></i> อนุมัติแล้ว</span>
                                                <?php else: ?>
                                                    <span class="badge-custom badge-warning-light"><i class="fa-solid fa-clock"></i> รอดำเนินการ</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="../students/view_student_details.php?id=<?php echo $student['st_id']; ?>" class="btn-outline-circle btn-outline-view" title="ดูรายละเอียด"><i class="fas fa-search"></i></a>

                                                    <a href="../students/save_student_pdf.php?id=<?php echo $student['st_id']; ?>" class="btn-outline-circle btn-outline-pdf" title="บันทึกเป็น PDF"><i class="fas fa-file-pdf"></i></a>

                                                    <a href="../students/edit_student.php?id=<?php echo $student['st_id']; ?>" class="btn-outline-circle btn-outline-edit" title="แก้ไขข้อมูล"><i class="fas fa-pencil-alt"></i></a>
                                                    <a href="../students/delete_student.php?id=<?php echo $student['st_id']; ?>" class="btn-outline-circle btn-outline-delete btn-delete" title="ลบข้อมูล"><i class="fas fa-trash-alt"></i></a>

                                                    <?php if ($student['st_confirm'] == 1): ?>
                                                        <a href="../students/approve_student.php?id=<?php echo $student['st_id']; ?>&action=cancel" class="btn-outline-circle btn-outline-cancel btn-approve" title="ยกเลิกการอนุมัติ"><i class="fas fa-times-circle"></i></a>
                                                    <?php else: ?>
                                                        <a href="../students/approve_student.php?id=<?php echo $student['st_id']; ?>&action=approve" class="btn-outline-circle btn-outline-approve btn-approve" title="กดเพื่ออนุมัติ"><i class="fas fa-check-circle"></i></a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="pt-4 text-center text-muted fw-bold">ทั้งหมด <?php echo count($students); ?> รายการ</td>
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

            const submenuToggles = document.querySelectorAll('.sidebar .has-submenu > a');
            submenuToggles.forEach(toggle => {
                toggle.addEventListener('click', function(event) {
                    if (window.innerWidth <= 1024) {
                        event.preventDefault();
                        const parentLi = this.parentElement;
                        document.querySelectorAll('.sidebar .has-submenu').forEach(li => {
                            if (li !== parentLi) li.classList.remove('submenu-open');
                        });
                        parentLi.classList.toggle('submenu-open');
                    }
                });
            });

            const tableBody = document.querySelector('.data-table tbody');
            if (tableBody) {
                tableBody.addEventListener('click', function(event) {
                    const button = event.target.closest('a.btn-outline-circle');
                    if (!button) return;

                    if (button.classList.contains('btn-delete')) {
                        event.preventDefault();
                        const deleteUrl = button.href;
                        Swal.fire({
                            title: 'ยืนยันการลบ',
                            text: "คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนักศึกษานี้?",
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
                    }

                    if (button.classList.contains('btn-approve')) {
                        event.preventDefault();
                        const currentUrl = button.href;
                        const isCancel = currentUrl.includes('action=cancel');

                        Swal.fire({
                            title: isCancel ? 'ยืนยันการยกเลิกอนุมัติ' : 'ยืนยันการอนุมัติ',
                            text: isCancel ? "คุณต้องการยกเลิกการอนุมัติและเปลี่ยนสถานะเป็นรอดำเนินการใช่หรือไม่?" : "คุณต้องการอนุมัตินักศึกษาคนนี้ใช่หรือไม่?",
                            icon: isCancel ? 'warning' : 'question',
                            showCancelButton: true,
                            confirmButtonColor: isCancel ? '#dc3545' : '#28a745',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: isCancel ? 'ยืนยันยกเลิก' : 'อนุมัติทันที',
                            cancelButtonText: 'ย้อนกลับ'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = currentUrl;
                            }
                        });
                    }
                });
            }
        });
    </script>
</body>

</html>