<?php
session_start();
include '../../include/config.php';

$edit_mode = false;
$page_title = "เพิ่มข่าวประชาสัมพันธ์";
$news_title = "";
$news_details = "";
$news_file_name = "";
$news_file_actual = "";
$news_id = null;


if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_mode = true;
    $page_title = "แก้ไขข่าวประชาสัมพันธ์";

    $news_id = mysqli_real_escape_string($connect1, $_GET['id']);

    $sql_news = "SELECT titlenews, detailnews FROM tb_news WHERE idnews = '$news_id'";
    $result_news = mysqli_query($connect1, $sql_news);
    $news_data = mysqli_fetch_assoc($result_news);

    $sql_file = "SELECT namefile, filenab FROM tb_files WHERE idnews = '$news_id' LIMIT 1";
    $result_file = mysqli_query($connect1, $sql_file);
    $file_data = mysqli_fetch_assoc($result_file);

    if ($news_data) {
        $news_title = $news_data['titlenews'];
        $news_details = $news_data['detailnews'];

        if ($file_data) {
            $news_file_name = $file_data['namefile'];
            $news_file_actual = $file_data['filenab'];
        }
    } else {
        die("ไม่พบข้อมูลข่าวที่ต้องการแก้ไข");
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

        .btn-choose-file-custom {
            background-color: #efefef;
            border: 1px solid #767676;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            color: black;
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
                        <h1 class="m-0 fw-bold" style="font-size: 22px; color: #333;"><?php echo $page_title; ?></h1>
                    </div>

                    <form id="newsForm" action="<?php echo $edit_mode ? '../news/update_news.php' : '../news/submit_news.php'; ?>" method="post" enctype="multipart/form-data">

                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="news_id" value="<?php echo $news_id; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="news-title" class="fw-bold mb-1">หัวข้อ:</label>
                            <input type="text" class="form-control" id="news-title" name="news_title" value="<?php echo htmlspecialchars($news_title); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="news-details" class="fw-bold mb-1">รายละเอียด:</label>
                            <textarea class="form-control" id="news-details" name="news_details" rows="6" required><?php echo htmlspecialchars($news_details); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="news-file-name" class="fw-bold mb-1">ชื่อเอกสาร:</label>
                            <input type="text" class="form-control" id="news-file-name" name="news_file_name" value="<?php echo htmlspecialchars($news_file_name); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold mb-1">แนบไฟล์:</label>
                            <div class="d-flex align-items-center">
                                <label for="news-file" class="btn-choose-file-custom">Choose File</label>
                                <span id="file-name-text" class="ms-3 text-muted" style="font-size: 14px;">
                                    <?php if ($edit_mode && !empty($news_file_actual)): ?>
                                        <a href="../uploads/<?php echo htmlspecialchars($news_file_actual); ?>" target="_blank" class="text-primary text-decoration-underline">
                                            <?php echo htmlspecialchars($news_file_actual); ?>
                                        </a>
                                    <?php else: ?>
                                        No file chosen
                                    <?php endif; ?>
                                </span>
                                <input type="file" id="news-file" name="news_file" accept=".pdf" style="display: none;" onchange="updateFileName(this)">
                            </div>
                            <p class="text-muted small mt-2">เป็น .pdf เท่านั้น (หากไม่ต้องการเปลี่ยนไฟล์ ไม่ต้องอัปโหลดใหม่)</p>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                            <a href="../news/news.php" class="btn btn-secondary rounded-pill px-5">ยกเลิก</a>
                            <button type="submit" id="btn-submit" class="btn btn-save rounded-pill" disabled>บันทึก</button>
                        </div>
                    </form>
                </main>
            </div>
        </div>
    </div>

    <?php include '../../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function updateFileName(input) {
            const fileNameText = document.getElementById('file-name-text');
            if (input.files && input.files[0]) {
                fileNameText.innerHTML = '<span class="text-dark">' + input.files[0].name + '</span>';
            } else {
                fileNameText.innerHTML = 'No file chosen';
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

            const titleInput = document.getElementById('news-title');
            const detailsInput = document.getElementById('news-details');
            const docNameInput = document.getElementById('news-file-name'); // นำกลับมาเพื่อเช็คการเปลี่ยนแปลง
            const fileInput = document.getElementById('news-file'); // นำกลับมาเพื่อเช็คการเลือกไฟล์ใหม่
            const submitBtn = document.getElementById('btn-submit');

            const initialValues = {
                title: titleInput.value.trim(),
                details: detailsInput.value.trim(),
                docName: docNameInput ? docNameInput.value.trim() : ''
            };

            const isEditMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;

            function validateForm() {
                const currentTitle = titleInput.value.trim();
                const currentDetails = detailsInput.value.trim();
                const currentDocName = docNameInput ? docNameInput.value.trim() : '';
                const fileSelected = fileInput && fileInput.files.length > 0;

                const isMandatoryFilled = (currentTitle !== "" && currentDetails !== "");

                let hasChanged = true;
                if (isEditMode) {
                    hasChanged = (
                        currentTitle !== initialValues.title ||
                        currentDetails !== initialValues.details ||
                        currentDocName !== initialValues.docName ||
                        fileSelected
                    );
                }

                submitBtn.disabled = !(isMandatoryFilled && hasChanged);
            }

            [titleInput, detailsInput, docNameInput].forEach(el => {
                if (el) el.addEventListener('input', validateForm);
            });
            if (fileInput) {
                fileInput.addEventListener('change', validateForm);
            }

            validateForm();
        });
    </script>
</body>

</html>