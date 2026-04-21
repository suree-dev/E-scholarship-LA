<?php
$current_page = basename($_SERVER['PHP_SELF']);

$logout_path = '/scholar/root/logout.php';

$open_scholarship_options = [];
if (isset($connect1)) {
    $sql_types = "SELECT st_name_1, st_1, st_name_2, st_2, st_name_3, st_3 FROM tb_year WHERE y_id = ?";
    $stmt = mysqli_prepare($connect1, $sql_types);
    if ($stmt) {
        $y_id_param = 1;
        mysqli_stmt_bind_param($stmt, "i", $y_id_param);
        mysqli_stmt_execute($stmt);
        $result_types = mysqli_stmt_get_result($stmt);

        if ($result_types && mysqli_num_rows($result_types) > 0) {
            $data_types = mysqli_fetch_assoc($result_types);
            if (!empty($data_types['st_name_1']) && $data_types['st_1'] == 0) $open_scholarship_options[1] = $data_types['st_name_1'];
            if (!empty($data_types['st_name_2']) && $data_types['st_2'] == 0) $open_scholarship_options[2] = $data_types['st_name_2'];
            if (!empty($data_types['st_name_3']) && $data_types['st_3'] == 0) $open_scholarship_options[3] = $data_types['st_name_3'];
        }
        mysqli_stmt_close($stmt);
    }

    if (isset($_SESSION['st_id']) && !in_array($current_page, ['confirm_page.php', 'logout.php'])) {
        $sql_check_status = "SELECT st_activate FROM student WHERE st_id = ?";
        $stmt_status = mysqli_prepare($connect1, $sql_check_status);
        if ($stmt_status) {
            mysqli_stmt_bind_param($stmt_status, "s", $_SESSION['st_id']);
            mysqli_stmt_execute($stmt_status);
            $res_status = mysqli_stmt_get_result($stmt_status);
            if ($row_status = mysqli_fetch_assoc($res_status)) {
                if ($row_status['st_activate'] == 1) {
                    $filling_pages = ['regis.php', 'apply_form.php', 'apply_fam.php', 'apply_reasons.php', 'apply_document.php'];
                    if (in_array($current_page, $filling_pages)) {
                        header("Location: confirm_page.php");
                        exit();
                    }
                }
            }
            mysqli_stmt_close($stmt_status);
        }
    }
}

if (isset($_SESSION['ad_name'])) {
    $display_name = $_SESSION['ad_name'];
} elseif (isset($_SESSION['tc_name'])) {
    $display_name = $_SESSION['tc_name'];
} elseif (isset($_SESSION['st_name'])) {
    $surname = isset($_SESSION['st_surname']) ? ' ' . $_SESSION['st_surname'] : '';
    $display_name = $_SESSION['st_name'] . $surname;
} else {
    $display_name = 'User';
}

$is_regis_page = ($current_page == 'regis.php');

$no_selector_pages = [
    'apply_form.php',
    'apply_fam.php',
    'apply_reasons.php',
    'apply_document.php',
    'confirm_page.php',
    'teacher.php',
    'give_score.php',
    'family.php',
    'reasons.php',
    'document.php',
    'admin.php',
    'news.php',
    'issue.php',
    'academic_year.php',
    'scholarship_types.php',
    'majors.php',
    'advisors.php',
    'committees.php',
    'susp_std.php',
    'student_data.php',
    'scholarship_scores.php',
    'clear_data.php',
    'add_news.php',
    'susp_std_add.php',
    'issue_view.php',
    'view_student_details.php',
    'view_score_details.php',
    'edit_student.php',
    'news_detail.php'
];
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    body,
    .user-status-bar,
    .swal2-container,
    .scholarship-dropdown-mobile {
        font-family: 'Prompt', sans-serif !important;
    }

    .user-status-bar {
        background: #003c71;
        padding: 8px 0;
        min-height: 65px;
        display: flex;
        align-items: center;
    }

    .user-status-content {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
    }

    .scholarship-selector-group {
        transition: all 0.3s ease;
    }

    .hidden-bar {
        display: none !important;
    }

    .scholarship-cards-container {
        display: flex;
        flex-direction: row;
        gap: 12px;
    }

    .scholarship-card-item {
        background: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        padding: 6px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 170px;
        text-align: center;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .scholarship-card-item:hover {
        background-color: #f8fafc;
        transform: translateY(-2px);
    }

    .scholarship-card-item.active {
        background-color: #eef6ff;
        border-color: #fff;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
    }

    .scholarship-card-item.active::after {
        content: '\f058';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        top: -6px;
        right: -6px;
        background: #fff;
        color: #003c71;
        font-size: 15px;
        border-radius: 50%;
        line-height: 1;
        z-index: 5;
    }

    .scholarship-card-name {
        font-weight: 500;
        color: #475569;
        font-size: 14px;
        margin: 0;
    }

    .scholarship-card-item.active .scholarship-card-name {
        color: #003c71;
        font-weight: 600;
    }

    .scholarship-dropdown-mobile {
        max-width: 160px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 8px;
    }

    .user-avatar-circle {
        width: 35px;
        height: 35px;
        background: transparent;
        color: #fff;
        border: 2px solid #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
        flex-shrink: 0;
    }

    .user-name {
        font-weight: 500;
        color: #ffffff;
        font-size: 15px;
        white-space: nowrap;
    }

    .logout-link {
        color: #ffffff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .logout-link:hover {
        opacity: 0.8;
        color: #fff;
    }

    @media (max-width: 991px) {
        .scholarship-cards-container {
            display: none !important;
        }
    }

    @media (min-width: 992px) {
        .scholarship-dropdown-mobile {
            display: none !important;
        }
    }

    @media (max-width: 768px) {
        .scholarship-dropdown-mobile {
            width: 260px;
        }
    }

    @media (max-width: 576px) {
        .user-status-content {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .scholarship-dropdown-mobile {
            width: 130px;
        }
    }
</style>

<div class="user-status-bar shadow-sm">
    <div class="user-status-content container-fluid px-4 d-flex justify-content-between align-items-center">

        <div id="statusBarSelector" class="scholarship-selector-group d-flex align-items-center <?php echo ($is_regis_page) ? 'hidden-bar' : ''; ?>">
            <?php if (!in_array($current_page, $no_selector_pages)): ?>

                <div class="scholarship-cards-container">
                    <?php foreach ($open_scholarship_options as $id => $name): ?>
                        <div class="scholarship-card-item bar-card" data-value="<?= (int)$id ?>">
                            <p class="scholarship-card-name"><?= htmlspecialchars($name) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <select class="form-select scholarship-dropdown-mobile" id="mobileScholarshipSelect">
                    <option value="" selected disabled>ประเภททุน</option>
                    <?php foreach ($open_scholarship_options as $id => $name): ?>
                        <option value="<?= (int)$id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>

            <?php endif; ?>
        </div>

        <div class="user-info-actions d-flex align-items-center gap-3 ms-auto">

            <div class="d-flex align-items-center gap-2">
                <div class="user-avatar-circle">
                    <?php echo htmlspecialchars(mb_substr($display_name, 0, 1, 'UTF-8')); ?>
                </div>
                <span class="user-name d-none d-md-inline">
                    <?php echo htmlspecialchars($display_name); ?>
                </span>
            </div>

            <a href="javascript:void(0);" onclick="confirmLogout(event, '<?php echo htmlspecialchars($logout_path); ?>');" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="logout-text">ออกจากระบบ</span>
            </a>

        </div>

    </div>
</div>

<script>
    function confirmLogout(event, logoutUrl) {
        if (event) event.preventDefault();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการออกจากระบบ?',
                text: "คุณต้องการออกจากระบบใช่หรือไม่",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ออกจากระบบ',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = logoutUrl;
                }
            });
        } else {
            if (confirm("คุณต้องการออกจากระบบใช่หรือไม่?")) {
                window.location.href = logoutUrl;
            }
        }
    }

    function syncStatusBarCards(selectedId) {
        const barCards = document.querySelectorAll('.bar-card');
        const mobileSelect = document.getElementById('mobileScholarshipSelect');
        const statusBarSelector = document.getElementById('statusBarSelector');

        if (selectedId && selectedId !== "" && selectedId !== "0") {
            if (statusBarSelector) {
                statusBarSelector.classList.remove('hidden-bar');
                statusBarSelector.style.display = 'flex';
            }

            barCards.forEach(card => {
                if (card.getAttribute('data-value') == selectedId) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });

        } else if (statusBarSelector && <?= $is_regis_page ? 'true' : 'false' ?>) {
            statusBarSelector.classList.add('hidden-bar');

            barCards.forEach(card => card.classList.remove('active'));
            if (mobileSelect) mobileSelect.value = "";
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const barCards = document.querySelectorAll('.bar-card');
        const mobileSelect = document.getElementById('mobileScholarshipSelect');
        const formHiddenType = document.getElementById('hidden-scholarship-type');

        barCards.forEach(card => {
            card.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                if (formHiddenType) {
                    formHiddenType.value = value;
                    formHiddenType.dispatchEvent(new Event('change'));
                }
                syncStatusBarCards(value);
            });
        });

        if (mobileSelect) {
            mobileSelect.addEventListener('change', function() {
                const value = this.value;
                if (formHiddenType) {
                    formHiddenType.value = value;
                    formHiddenType.dispatchEvent(new Event('change'));
                }
                this.value = "";
                syncStatusBarCards(value);
            });
        }

        if (formHiddenType && formHiddenType.value !== "") {
            syncStatusBarCards(formHiddenType.value);
        }

        if (formHiddenType) {
            formHiddenType.addEventListener('change', function() {
                syncStatusBarCards(this.value);
            });
        }
    });
</script>