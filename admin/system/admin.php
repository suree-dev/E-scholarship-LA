<?php
session_start();
include '../../include/config.php';

if (isset($_POST['action']) && $_POST['action'] == 'check_current_password') {
    $current_pass = mysqli_real_escape_string($connect1, $_POST['current_password']);
    $admin_id = 1;

    $sql_check = "SELECT ad_pass FROM tb_admin WHERE ad_id = '$admin_id'";
    $res_check = mysqli_query($connect1, $sql_check);
    $row_check = mysqli_fetch_assoc($res_check);

    if ($current_pass === $row_check['ad_pass'] || md5($current_pass) === $row_check['ad_pass']) {
        echo "match";
    } else {
        echo "not_match";
    }
    exit;
}

include '../../include/header.php';

$admin_id = 1;

$sql = "SELECT * FROM tb_admin WHERE ad_id = '$admin_id'";
$result = mysqli_query($connect1, $sql);
$admin_data = mysqli_fetch_assoc($result);

if (!$admin_data) {
    $admin_data = [
        'ad_name' => 'ไม่พบข้อมูล',
        'ad_user' => 'ไม่พบข้อมูล',
        'ad_tel' => 'ไม่พบข้อมูล'
    ];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แอดมิน - PSU E-Scholarship</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #e3edf7 !important;
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

        .password-field-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            cursor: pointer;
        }

        .password-field-wrapper .form-control.is-invalid {
            background-position: right 45px center !important;
            padding-right: 70px !important;
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
                        echo '<div id="notification-message" class="alert border-0 shadow-sm" style="background-color: ' . $bg_color . '; color: white; transition: opacity 0.5s ease; opacity: 1;">';
                        echo htmlspecialchars($_SESSION['message']['text']);
                        echo '</div>';
                        unset($_SESSION['message']);
                    }
                    ?>

                    <div class="content-header">
                        <h1 class="font m-0" style="font-size: 22px; font-weight: 700;">ยินดีต้อนรับผู้ดูแลระบบ "จัดการข้อมูล"</h1>
                    </div>

                    <form class="admin-form" action="../profile/update_ad.php" method="post" id="adminUpdateForm" autocomplete="off">
                        <div class="mb-3 d-flex align-items-center form-group-flex">
                            <label for="fullname" class="form-label-custom">ชื่อ-สกุล<span class="required">*</span></label>
                            <div class="flex-grow-1">
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($admin_data['ad_name']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-center form-group-flex">
                            <label for="username" class="form-label-custom">ชื่อผู้ใช้<span class="required">*</span></label>
                            <div class="flex-grow-1">
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($admin_data['ad_user']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-center form-group-flex">
                            <label for="contact-number" class="form-label-custom">เบอร์ติดต่อ<span class="required">*</span></label>
                            <div class="flex-grow-1">
                                <input type="text" class="form-control" id="contact-number" name="contact_number" value="<?php echo htmlspecialchars($admin_data['ad_tel']); ?>" required>
                            </div>
                        </div>

                        <h2 class="form-section-header">เปลี่ยนแปลงรหัสผ่าน</h2>

                        <div class="mb-3 d-flex align-items-start form-group-flex">
                            <label for="current-password" class="form-label-custom pt-2">รหัสผ่านเดิม</label>
                            <div class="flex-grow-1">
                                <div class="password-field-wrapper">
                                    <input type="password" class="form-control" id="current-password" name="current_password" autocomplete="new-password">
                                    <i class="fa-solid fa-eye-slash password-toggle" onclick="togglePassword('current-password', this)"></i>
                                </div>
                                <span class="error-text" id="current-password-error"></span>
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-start form-group-flex">
                            <label for="new-password" class="form-label-custom pt-2">รหัสใหม่</label>
                            <div class="flex-grow-1">
                                <div class="password-field-wrapper">
                                    <input type="password" class="form-control" id="new-password" name="new_password" autocomplete="new-password">
                                    <i class="fa-solid fa-eye-slash password-toggle" onclick="togglePassword('new-password', this)"></i>
                                </div>
                                <span class="error-text" id="new-password-error"></span>
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-start form-group-flex">
                            <label for="confirm-new-password" class="form-label-custom pt-2">ยืนยันรหัสใหม่</label>
                            <div class="flex-grow-1">
                                <div class="password-field-wrapper">
                                    <input type="password" class="form-control" id="confirm-new-password" name="confirm_new_password" autocomplete="new-password">
                                    <i class="fa-solid fa-eye-slash password-toggle" onclick="togglePassword('confirm-new-password', this)"></i>
                                </div>
                                <span class="error-text" id="confirm-password-error"></span>
                            </div>
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
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.search.indexOf('error=') > -1) {
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
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

            const sidebar = document.querySelector('.sidebar');
            const menuHeader = document.querySelector('.sidebar .menu-header');
            if (menuHeader && sidebar) {
                menuHeader.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        sidebar.classList.toggle('is-open');
                    }
                });
            }

            const fullname = document.getElementById('fullname');
            const username = document.getElementById('username');
            const contact = document.getElementById('contact-number');
            const currentPasswordInput = document.getElementById('current-password');
            const newPasswordInput = document.getElementById('new-password');
            const confirmPasswordInput = document.getElementById('confirm-new-password');
            const submitBtn = document.getElementById('submitBtn');

            const currentPasswordError = document.getElementById('current-password-error');
            const newPasswordError = document.getElementById('new-password-error');
            const confirmPasswordError = document.getElementById('confirm-password-error');

            let typingTimer;
            let isCurrentPasswordCorrect = true;

            const originalValues = {
                fullname: fullname.value,
                username: username.value,
                contact: contact.value
            };

            function validateForm() {
                let isBaseInfoChanged =
                    fullname.value !== originalValues.fullname ||
                    username.value !== originalValues.username ||
                    contact.value !== originalValues.contact;

                const hasNewPass = newPasswordInput.value !== '';
                const hasConfirmPass = confirmPasswordInput.value !== '';
                const hasCurrentPass = currentPasswordInput.value !== '';

                let isPassValid = true;

                newPasswordInput.classList.remove('is-invalid');
                if (newPasswordError) newPasswordError.style.display = 'none';
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordError.style.display = 'none';

                if (hasNewPass || hasConfirmPass || hasCurrentPass) {
                    if (!hasCurrentPass || !hasNewPass || !hasConfirmPass || !isCurrentPasswordCorrect || (newPasswordInput.value !== confirmPasswordInput.value)) {
                        isPassValid = false;

                        if (hasNewPass && hasConfirmPass && newPasswordInput.value !== confirmPasswordInput.value) {
                            confirmPasswordInput.classList.add('is-invalid');
                            confirmPasswordError.textContent = 'รหัสผ่านใหม่ทั้งสองช่องไม่ตรงกัน';
                            confirmPasswordError.style.display = 'block';
                        }
                    }

                    if (hasNewPass) {
                        const passwordValue = newPasswordInput.value;
                        const hasLetter = /[a-zA-Zก-ฮ]/.test(passwordValue);
                        const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(passwordValue);
                        const isLengthValid = passwordValue.length >= 10;

                        if (!isLengthValid || !hasLetter || !hasSymbol) {
                            isPassValid = false;
                            newPasswordInput.classList.add('is-invalid');
                            if (newPasswordError) {
                                newPasswordError.textContent = 'รหัสผ่านต้องมีอย่างน้อย 10 ตัว ประกอบด้วยตัวอักษรและสัญลักษณ์อย่างน้อย 1 ตัว';
                                newPasswordError.style.display = 'block';
                            }
                        }
                    }
                } else {
                    isPassValid = isBaseInfoChanged;
                }

                if (fullname.value.trim() === '' || username.value.trim() === '' || contact.value.trim() === '') {
                    isPassValid = false;
                }

                submitBtn.disabled = !isPassValid;
            }

            function checkCurrentPassword() {
                const val = currentPasswordInput.value;
                if (val === '') {
                    isCurrentPasswordCorrect = true;
                    currentPasswordInput.classList.remove('is-invalid');
                    currentPasswordError.style.display = 'none';
                    validateForm();
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'check_current_password');
                formData.append('current_password', val);

                fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === 'match') {
                            isCurrentPasswordCorrect = true;
                            currentPasswordInput.classList.remove('is-invalid');
                            currentPasswordError.style.display = 'none';
                        } else {
                            isCurrentPasswordCorrect = false;
                            currentPasswordInput.classList.add('is-invalid');
                            currentPasswordError.textContent = 'รหัสผ่านไม่ถูกต้อง';
                            currentPasswordError.style.display = 'block';
                        }
                        validateForm();
                    });
            }

            currentPasswordInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                currentPasswordInput.classList.remove('is-invalid');
                currentPasswordError.style.display = 'none';
                submitBtn.disabled = true;

                typingTimer = setTimeout(checkCurrentPassword, 2000);
            });

            [fullname, username, contact, newPasswordInput, confirmPasswordInput].forEach(el => {
                if (el) el.addEventListener('input', validateForm);
            });

            validateForm();
        });
    </script>

</body>

</html>