<?php
session_start();
include '../../include/config.php';

$page_title = "รายละเอียดข้อมูลคะแนน";
$student_name = "ไม่พบข้อมูลนักศึกษา";
$scores_by_committee = [];
$total_score = 0;
$average_score = 0;
$percentage_score = 0;
$committee_count = 0;

$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$back_to_type = isset($_GET['type']) ? (int)$_GET['type'] : 1;


$limit = 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_save_all'])) {
    if (isset($_POST['scores_update']) && is_array($_POST['scores_update'])) {
        foreach ($_POST['scores_update'] as $sco_id => $new_score) {
            $sco_id = (int)$sco_id;
            $new_score = (float)$new_score;
            mysqli_query($connect1, "UPDATE tb_scores SET scores = '$new_score' WHERE sco_id = '$sco_id'");
        }

        $sql_sync = "UPDATE tb_student s
                     SET 
                        s.sum_score = (SELECT SUM(scores) FROM tb_scores WHERE st_id = '$student_id'),
                        s.st_average = (SELECT AVG(scores) FROM tb_scores WHERE st_id = '$student_id')
                     WHERE s.st_id = '$student_id'";
        mysqli_query($connect1, $sql_sync);

        $_SESSION['msg_success'] = "บันทึกการแก้ไขคะแนนเรียบร้อยแล้ว";
        header("Location: view_score_details.php?id=" . $student_id . "&type=" . $back_to_type . "&p=" . $page);
        exit();
    }
}

if (isset($_GET['del_id'])) {
    $del_id = (int)$_GET['del_id'];
    mysqli_query($connect1, "DELETE FROM tb_scores WHERE sco_id = '$del_id'");

    $sql_sync = "UPDATE tb_student s
                 SET 
                    s.sum_score = (SELECT IFNULL(SUM(scores), 0) FROM tb_scores WHERE st_id = '$student_id'),
                    s.st_average = (SELECT IFNULL(AVG(scores), 0) FROM tb_scores WHERE st_id = '$student_id')
                 WHERE s.st_id = '$student_id'";
    mysqli_query($connect1, $sql_sync);

    $_SESSION['msg_success'] = "ลบข้อมูลคะแนนเรียบร้อยแล้ว";
    header("Location: view_score_details.php?id=" . $student_id . "&type=" . $back_to_type . "&p=" . $page);
    exit();
}

if ($student_id > 0) {
    $sql_student = "SELECT st_firstname, st_lastname FROM tb_student WHERE st_id = '$student_id'";
    $result_student = mysqli_query($connect1, $sql_student);
    if ($result_student && mysqli_num_rows($result_student) > 0) {
        $student_data = mysqli_fetch_assoc($result_student);
        $student_name = $student_data['st_firstname'] . ' ' . $student_data['st_lastname'];
    }

    $sql_count = "SELECT COUNT(*) FROM tb_scores WHERE st_id = '$student_id'";
    $res_count = mysqli_query($connect1, $sql_count);
    $total_records = mysqli_fetch_array($res_count)[0];
    $total_pages = ceil($total_records / $limit);

    $sql_scores = "SELECT s.sco_id, t.tc_name, s.scores, s.sco_comment 
                   FROM tb_scores AS s
                   JOIN tb_teacher AS t ON s.tc_id = t.tc_id
                   WHERE s.st_id = '$student_id'
                   ORDER BY t.tc_name ASC
                   LIMIT $limit OFFSET $offset";

    $result_scores = mysqli_query($connect1, $sql_scores);
    if ($result_scores) {
        while ($row = mysqli_fetch_assoc($result_scores)) {
            $scores_by_committee[] = [
                'sco_id' => $row['sco_id'],
                'cm_name' => $row['tc_name'],
                'score' => $row['scores'],
                'comment' => $row['sco_comment']
            ];
        }
    }

    $sql_summary = "SELECT SUM(scores) as total, COUNT(sco_id) as cnt, AVG(scores) as average 
                    FROM tb_scores WHERE st_id = '$student_id'";
    $res_summary = mysqli_query($connect1, $sql_summary);
    $summary = mysqli_fetch_assoc($res_summary);
    $total_score = $summary['total'] ?? 0;
    $committee_count = $summary['cnt'] ?? 0;
    $average_score = $summary['average'] ?? 0;

    $percentage_score = ($average_score > 0) ? ($average_score / 4) * 100 : 0;
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
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .btn-icon-link {
            text-decoration: none !important;
            border: none !important;
            outline: none !important;
            display: inline-block;
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
                    <div class="content-header mb-4 pb-3 border-bottom">
                        <h1 class="m-0 fw-bold" style="font-size: 20px; color: #333;">
                            <?php echo htmlspecialchars($page_title . ' : ' . $student_name); ?>
                        </h1>
                        <a href="scholarship_scores.php?type=<?php echo $back_to_type; ?>" class="btn btn-secondary rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-arrow-left me-2"></i> ย้อนกลับ
                        </a>
                    </div>

                    <?php if (isset($_SESSION['msg_success'])): ?>
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ',
                                text: '<?php echo $_SESSION['msg_success']; ?>',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        </script>
                        <?php unset($_SESSION['msg_success']); ?>
                    <?php endif; ?>

                    <form action="" method="POST" id="form-scores">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ชื่อ-สกุล คณะกรรมการ</th>
                                        <th>ความเห็น</th>
                                        <th class="text-center" style="width: 150px;">คะแนน</th>
                                        <th class="text-center" style="width: 120px;">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($scores_by_committee)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted fw-medium">
                                                <?php echo ($student_id == 0) ? 'กรุณาระบุ ID ของนักศึกษา' : 'ไม่พบข้อมูลคะแนนจากกรรมการ'; ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($scores_by_committee as $score): ?>
                                            <tr>
                                                <td class="fw-medium"><?php echo htmlspecialchars($score['cm_name']); ?></td>
                                                <td class="text-muted small"><?php echo nl2br(htmlspecialchars($score['comment'])); ?></td>
                                                <td class="text-center">
                                                    <span class="score-text fw-bold text-primary"><?php echo htmlspecialchars(number_format($score['score'], 2)); ?></span>
                                                    <input type="number" step="0.01" name="scores_update[<?php echo $score['sco_id']; ?>]"
                                                        class="form-control edit-score-input mx-auto" value="<?php echo $score['score']; ?>" style="display: none;">
                                                </td>
                                                <td class="text-center">
                                                    <a href="javascript:void(0)" onclick="toggleEditMode(true)" class="text-warning fs-5 me-2 btn-icon-edit btn-icon-link" title="แก้ไขคะแนนทั้งหมด">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $score['sco_id']; ?>)" class="text-danger fs-5 btn-icon-link" title="ลบคะแนน">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($scores_by_committee)): ?>
                                    <tfoot>
                                        <tr class="summary-row">
                                            <td colspan="2" class="ps-4">คะแนนรวม (จากกรรมการทั้งหมด <?php echo $committee_count; ?> ท่าน)</td>
                                            <td class="text-center fw-bold fs-5"><?php echo htmlspecialchars(number_format($total_score, 2)); ?></td>
                                            <td></td>
                                        </tr>
                                        <tr class="summary-row">
                                            <td colspan="2" class="ps-4">คะแนนร้อยละ (%)</td>
                                            <td class="text-center fw-bold fs-4 text-primary"><?php echo htmlspecialchars(number_format($percentage_score)); ?>%</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                            <ul class="pagination mt-4">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?id=<?php echo $student_id; ?>&type=<?php echo $back_to_type; ?>&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        <?php endif; ?>

                        <div class="action-footer-center mt-5">
                            <?php if (!empty($scores_by_committee)): ?>
                                <button type="submit" name="btn_save_all" id="btn-save-all" class="btn btn-success btn-mode-green px-5 shadow-sm" style="display: none;">
                                    <i class="fas fa-save me-2"></i> บันทึกข้อมูลทั้งหมด
                                </button>
                                <button type="button" id="btn-cancel-all" class="btn btn-secondary btn-mode-gray px-5 shadow-sm" onclick="toggleEditMode(false)" style="display: none;">
                                    ยกเลิก
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </main>
            </div>
        </div>
    </div>

    <?php include '../../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'ยืนยันการลบคะแนน?',
                text: "ข้อมูลคะแนนนี้จะถูกลบออก และกรรมการท่านนี้จะสามารถให้คะแนนใหม่ได้",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `view_score_details.php?id=<?php echo $student_id; ?>&type=<?php echo $back_to_type; ?>&p=<?php echo $page; ?>&del_id=${id}`;
                }
            })
        }

        function toggleEditMode(isEdit) {
            const scoreTexts = document.querySelectorAll('.score-text');
            const scoreInputs = document.querySelectorAll('.edit-score-input');
            const btnIconsEdit = document.querySelectorAll('.btn-icon-edit');
            const btnSave = document.getElementById('btn-save-all');
            const btnCancel = document.getElementById('btn-cancel-all');

            if (isEdit) {
                scoreTexts.forEach(el => el.style.setProperty('display', 'none', 'important'));
                scoreInputs.forEach(el => el.style.setProperty('display', 'block', 'important'));
                btnIconsEdit.forEach(el => el.style.setProperty('display', 'none', 'important'));
                btnSave.style.display = 'inline-block';
                btnCancel.style.display = 'inline-block';
            } else {
                scoreTexts.forEach(el => el.style.setProperty('display', 'inline', 'important'));
                scoreInputs.forEach(el => el.style.setProperty('display', 'none', 'important'));
                btnIconsEdit.forEach(el => el.style.setProperty('display', 'inline-block', 'important'));
                btnSave.style.display = 'none';
                btnCancel.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const menuHeader = document.querySelector('.sidebar .menu-header');
            if (menuHeader) {
                menuHeader.addEventListener('click', () => {
                    if (window.innerWidth <= 1024) sidebar.classList.toggle('is-open');
                });
            }
        });
    </script>
</body>

</html>