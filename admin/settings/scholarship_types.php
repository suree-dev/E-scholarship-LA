<?php
session_start();
include '../../include/config.php';

$page_title = "จัดการข้อมูลชื่อทุนการศึกษา";

$sql = "SELECT st_name_1, st_1, date_start_1, date_end_1, 
               st_name_2, st_2, date_start_2, date_end_2, 
               st_name_3, st_3, date_start_3, date_end_3 
        FROM tb_year WHERE y_id = 1";
$result = mysqli_query($connect1, $sql);
$data = mysqli_fetch_assoc($result);

$scholarship_types = [];
if ($data) {
    for ($i = 1; $i <= 3; $i++) {
        $scholarship_types[] = [
            'id' => $i,
            'name' => $data["st_name_$i"],
            'is_active' => ($data["st_$i"] == 0),
            'start_date' => $data["date_start_$i"],
            'end_date' => $data["date_end_$i"]
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
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

        .date-input,
        .flatpickr-input+input {
            cursor: pointer !important;
            background-color: #ffffff !important;
        }

        .form-check-input,
        .form-check-label {
            cursor: pointer;
        }

        .btn-save:not(:disabled) {
            cursor: pointer;
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

                    <form id="scholarshipForm" action="../settings/update_scholarship_types.php" method="post">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 80px;">ลำดับ</th>
                                        <th style="width: 300px;">ประเภททุน</th>
                                        <th>วันที่เปิดรับสมัคร - ปิดรับสมัคร</th>
                                        <th class="checkbox-cell">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($scholarship_types as $index => $type): ?>
                                        <tr class="scholarship-row">
                                            <td class="text-center fw-medium"><?php echo $index + 1; ?>.</td>
                                            <td>
                                                <input type="text" name="scholarship_name[<?php echo $type['id']; ?>]" value="<?php echo htmlspecialchars($type['name']); ?>" class="form-control change-track">
                                            </td>
                                            <td>
                                                <div class="date-inputs-group">
                                                    <div class="date-input-wrapper">
                                                        <input type="date" name="start_date[<?php echo $type['id']; ?>]" value="<?php echo htmlspecialchars($type['start_date']); ?>" class="form-control date-input change-track" placeholder="เลือกวันที่เริ่ม">
                                                    </div>
                                                    <span class="fw-medium">ถึง</span>
                                                    <div class="date-input-wrapper">
                                                        <input type="date" name="end_date[<?php echo $type['id']; ?>]" value="<?php echo htmlspecialchars($type['end_date']); ?>" class="form-control date-input change-track" placeholder="เลือกวันที่สิ้นสุด">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="checkbox-cell">
                                                <div class="form-check d-inline-block">
                                                    <input type="checkbox" id="close_<?php echo $type['id']; ?>" name="close_scholarship[<?php echo $type['id']; ?>]" value="1" <?php echo !$type['is_active'] ? 'checked' : ''; ?> class="form-check-input close-checkbox change-track">
                                                    <label class="form-check-label fw-medium ms-1" for="close_<?php echo $type['id']; ?>">ปิดรับสมัคร</label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                            <button type="submit" id="submitBtn" class="btn btn-save rounded-pill" disabled>บันทึก</button>
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

            const scholarshipForm = document.getElementById('scholarshipForm');
            const submitBtn = document.getElementById('submitBtn');
            const trackingInputs = document.querySelectorAll('.change-track');

            const initialValues = Array.from(trackingInputs).map(input => {
                return input.type === 'checkbox' ? input.checked : input.value;
            });

            function checkChanges() {
                let isChanged = false;
                trackingInputs.forEach((input, index) => {
                    const currentValue = input.type === 'checkbox' ? input.checked : input.value;
                    if (currentValue !== initialValues[index]) {
                        isChanged = true;
                    }
                });
                submitBtn.disabled = !isChanged;
            }

            submitBtn.disabled = true;

            const datePickers = flatpickr(".date-input", {
                "locale": "th",
                altInput: true,
                altFormat: "j F Y",
                dateFormat: "Y-m-d",
                disableMobile: "true",
                onChange: function() {
                    checkChanges();
                }
            });

            function getTodayDateString() {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function handleCheckboxChange(checkbox) {
                const row = checkbox.closest('.scholarship-row');
                if (!row) return;

                const startDateInput = row.querySelector('input[name^="start_date"]');
                const endDateInput = row.querySelector('input[name^="end_date"]');
                const isDisabled = checkbox.checked;

                if (startDateInput && startDateInput._flatpickr) {
                    startDateInput._flatpickr.set("disable", isDisabled ? [true] : [false]);
                    if (isDisabled) startDateInput._flatpickr.clear();
                }
                if (endDateInput && endDateInput._flatpickr) {
                    endDateInput._flatpickr.set("disable", isDisabled ? [true] : [false]);
                    if (isDisabled) endDateInput._flatpickr.clear();
                }

                if (!isDisabled && startDateInput && startDateInput.value === '') {
                    if (startDateInput._flatpickr) {
                        startDateInput._flatpickr.setDate(getTodayDateString(), true);
                    }
                }

                checkChanges();
            }

            const checkboxes = document.querySelectorAll('.close-checkbox');
            checkboxes.forEach(checkbox => {
                handleCheckboxChange(checkbox);
                checkbox.addEventListener('change', () => handleCheckboxChange(checkbox));
            });

            trackingInputs.forEach(input => {
                if (input.type === 'text') {
                    input.addEventListener('input', checkChanges);
                }
            });

            scholarshipForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerText = 'กำลังบันทึก...';
            });
        });
    </script>
</body>

</html>