<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once 'config.php';

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_folder = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
$base_path = "/scholar/";
$scholarship_names_menu = [];
if (isset($connect1)) {
    $sql_menu = "SELECT st_name_1, st_1, st_name_2, st_2, st_name_3, st_3 FROM tb_year WHERE y_id = 1";
    $result_menu = mysqli_query($connect1, $sql_menu);
    if ($result_menu) {
        $data_menu = mysqli_fetch_assoc($result_menu);
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($data_menu["st_name_$i"])) {
                $scholarship_names_menu[$i] = $data_menu["st_name_$i"];
            }
        }
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
$currentType = isset($_GET['type']) ? $_GET['type'] : null;
?>

<style>
    :root {
        --primary-blue: #003c71;
        --light-blue: #eef2ff;
        --border-color: #f0f0f0;
    }

    .sidebar {
        flex: 0 0 250px;
        background-color: #ffffff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        position: sticky;
        top: 140px;
        align-self: flex-start;
        z-index: 100;
    }

    .sidebar .menu-header {
        background-color: #003c71;
        color: white;
        padding: 15px 20px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 8px 8px 0 0;
        cursor: pointer;
    }

    .sidebar .menu-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .sidebar .menu-list>li {
        position: relative;
        border-bottom: 1px solid var(--border-color);
    }

    .sidebar .menu-list li:last-child {
        border-bottom: none;
    }

    .sidebar .menu-list li a {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        color: #333;
        text-decoration: none;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
        font-size: 0.95rem;
    }

    .sidebar .menu-list li a:hover {
        background-color: #f8f9fa;
        color: var(--primary-blue);
    }

    .sidebar .menu-list li.active>a {
        background-color: var(--light-blue);
        color: var(--primary-blue);
        font-weight: 600;
        border-left-color: var(--primary-blue);
    }

    .sidebar .menu-list .submenu {
        display: none;
        position: absolute;
        left: 100%;
        top: 0;
        width: 260px;
        background-color: #ffffff;
        box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.1);
        list-style: none;
        padding: 5px 0;
        margin: 0;
        border-radius: 0 8px 8px 8px;
        border: 1px solid var(--border-color);
        z-index: 1000;
    }

    @media (min-width: 1025px) {
        .sidebar .menu-list li.has-submenu:hover>.submenu {
            display: block;
        }

        .sidebar .menu-list .has-submenu>a::after {
            content: '\f105';
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 14px;
        }
    }

    .sidebar .menu-list .submenu li {
        border-bottom: none;
    }

    .sidebar .menu-list .submenu li a {
        padding: 12px 20px;
        font-size: 14px;
        border-left: none;
        justify-content: flex-start;
    }

    .sidebar .menu-list .submenu li.active-submenu a {
        color: var(--primary-blue);
        background-color: var(--light-blue);
        font-weight: 600;
    }

    @media (max-width: 1024px) {
        .sidebar {
            position: static;
            width: 100%;
            margin-bottom: 20px;
        }

        .sidebar .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar .menu-header::after {
            content: '\f107';
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 14px;
            transition: 0.3s;
        }

        .sidebar.is-open .menu-header::after {
            content: '\f106';
        }

        .sidebar .menu-list>li {
            display: none;
        }

        .sidebar.is-open .menu-list>li {
            display: block;
        }

        .sidebar .menu-list .submenu {
            position: static;
            width: 100%;
            box-shadow: none;
            background-color: #f8f9fa;
            border-radius: 0;
            border: none;
            border-top: 1px solid #ddd;
        }

        .sidebar .menu-list li.has-submenu.submenu-open .submenu {
            display: block !important;
        }

        .sidebar .menu-list .submenu li {
            display: block !important;
        }

        .sidebar .menu-list .has-submenu>a::after {
            content: '\f107';
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            transition: 0.3s;
        }

        .sidebar .menu-list li.has-submenu.submenu-open>a::after {
            transform: rotate(180deg);
        }
    }
</style>

<aside class="sidebar">
    <div class="menu-header">Main Menu</div>
    <ul class="menu-list">
        <li class="<?php if ($currentPage == 'admin.php') echo 'active'; ?>">
            <a href="<?php echo $base_path; ?>admin/system/admin.php"> ข้อมูลส่วนตัว</a>
        </li>
        <li class="<?php if (in_array($currentPage, ['news.php', 'add_news.php'])) echo 'active'; ?>">
            <a href="<?php echo $base_path; ?>admin/news/news.php"> ข่าวประชาสัมพันธ์</a>
        </li>
        <li class="<?php if (in_array($currentPage, ['issue.php', 'issue_view.php'])) echo 'active'; ?>">
            <a href="<?php echo $base_path; ?>admin/issues/issue.php"> แจ้งปัญหาการใช้งาน</a>
        </li>
        <li class="<?php if ($currentPage == 'scholarship_types.php') echo 'active'; ?>">
            <a href="<?php echo $base_path; ?>admin/settings/scholarship_types.php"> ข้อมูลชื่อทุนการศึกษา</a>
        </li>
        <li class="<?php if (in_array($currentPage, ['majors.php', 'add_major.php', 'edit_major.php'])) echo 'active'; ?>">
            <a href="<?php echo $base_path; ?>admin/settings/majors.php"> ข้อมูลสาขาวิชา</a>
        </li>
        <li class="<?php if (in_array($currentPage, ['advisors.php', 'add_advisor.php', 'edit_advisor.php'])) echo 'active'; ?>">
            <a href="<?php echo $base_path; ?>admin/advisors/advisors.php"> ข้อมูลอาจารย์ที่ปรึกษา</a>
        </li>
        <li class="<?php if (in_array($currentPage, ['committees.php', 'add_committee.php', 'edit_committee.php'])) echo 'active'; ?>">
            <a href="<?php echo $base_path; ?>admin/committees/committees.php"> ข้อมูลคณะกรรมการ</a>
        </li>
        <li class="<?php if (in_array($currentPage, ['susp_std.php', 'susp_std_add.php'])) echo 'active'; ?>">
            <a href="<?php echo $base_path; ?>student/susp_std.php"> รายชื่อนักศึกษาที่ถูกระงับ</a>
        </li>

        <?php if (!empty($scholarship_names_menu)): ?>
            <li class="has-submenu <?php if ($currentPage == 'student_data.php') echo 'active'; ?>">
                <a href="javascript:void(0)"><span> ข้อมูลนักศึกษา</span></a>
                <ul class="submenu">
                    <?php foreach ($scholarship_names_menu as $type_id => $type_name): ?>
                        <li class="<?php if ($currentPage == 'student_data.php' && $currentType == $type_id) echo 'active-submenu'; ?>">
                            <a href="<?php echo $base_path; ?>admin/students/student_data.php?type=<?php echo $type_id; ?>"><?php echo htmlspecialchars($type_name); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <li class="has-submenu <?php if ($currentPage == 'scholarship_scores.php') echo 'active'; ?>">
                <a href="javascript:void(0)"><span> คะแนนทุน</span></a>
                <ul class="submenu">
                    <?php foreach ($scholarship_names_menu as $type_id => $type_name): ?>
                        <li class="<?php if ($currentPage == 'scholarship_scores.php' && $currentType == $type_id) echo 'active-submenu'; ?>">
                            <a href="<?php echo $base_path; ?>admin/scores/scholarship_scores.php?type=<?php echo $type_id; ?>"><?php echo htmlspecialchars($type_name); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endif; ?>

        <li class="<?php if ($currentPage == 'clear_data.php') echo 'active'; ?>">
            <a href="<?php echo $base_path; ?>admin/system/clear_data.php"> ล้างข้อมูลเว็บไซต์</a>
        </li>
        <li><a href="<?php echo $base_path; ?>admin/root/index.php" style="color: #d9534f;"> ออกจากระบบ</a></li>
    </ul>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const menuHeader = document.querySelector('.sidebar .menu-header');
        const hasSubmenuItems = sidebar.querySelectorAll('.has-submenu');

        if (menuHeader) {
            menuHeader.addEventListener('click', function() {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.toggle('is-open');
                }
            });
        }

        hasSubmenuItems.forEach(item => {
            const link = item.querySelector('a');
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 1024) {
                    e.preventDefault();
                    e.stopPropagation();
                    item.classList.toggle('submenu-open');
                }
            });
        });
    });
</script>