<?php
session_start();
include '../include/config.php';


if (isset($_SESSION['student_id'])) {
    $session_st_code = mysqli_real_escape_string($connect1, $_SESSION['student_id']);
    $today = date('Y-m-d');
    $sql_ban = "SELECT code_student FROM tb_ban WHERE code_student = '$session_st_code' AND '$today' BETWEEN date_start AND date_end LIMIT 1";
    $res_ban = mysqli_query($connect1, $sql_ban);
    if ($res_ban && mysqli_num_rows($res_ban) > 0) {
        session_destroy();
        header("Location: ../root/index.php");
        exit();
    }
    $sql_check = "SELECT st_activate, st_type FROM tb_student WHERE st_code = '$session_st_code'";
    $result_check = mysqli_query($connect1, $sql_check);
    if ($result_check && mysqli_num_rows($result_check) > 0) {
        $row_check = mysqli_fetch_assoc($result_check);
        if ($row_check['st_activate'] == 1) {
            header("Location: ../student/apply_form.php");
        } elseif ($row_check['st_type'] == 0) {
            header("Location: ../student/regis.php");
        } else {
            header("Location: ../root/index.php");
        }
        exit();
    }
}
if (isset($_SESSION['id_teacher'])) {
    header("Location: ../admin/advisors/teacher.php");
    exit();
}
if (isset($_SESSION['id_admin'])) {
    header("Location: ../admin/system/news.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบสมัครทุนการศึกษา</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/bg/head_01.png">
    <link rel="stylesheet" href="../assets/css/global2.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body.login-combined {
            background: linear-gradient(135deg, #003c71 0%, #1a2a3a 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Prompt', sans-serif;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }

        .login-header {
            padding: 30px 20px 10px 20px;
            text-align: center;
        }

        .login-header img {
            width: 380px;
            margin-bottom: 10px;
        }

        .login-tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .tab-btn {
            flex: 1;
            padding: 15px 5px;
            border: none;
            background: none;
            cursor: pointer;
            font-family: 'Prompt';
            font-size: 14px;
            font-weight: 600;
            color: #999;
            transition: 0.3s;
            border-bottom: 3px solid transparent;
        }

        .tab-btn.active {
            color: #003c71;
            border-bottom: 3px solid #003c71;
            background: white;
        }

        .login-body {
            padding: 30px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: 'Prompt';
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: #003c71;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #002a50;
            transform: translateY(-2px);
        }

        .error-msg {
            color: #d9534f;
            background: #f2dede;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #ebccd1;
        }

        .login-form {
            display: none;
        }

        .login-form.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .regis-link {
            display: block;
            margin-top: 25px;
            color: #003c71;
            text-decoration: none;
            font-size: 14px;
            text-align: center;
        }

        @media (max-width: 1024px) {
            .login-card {
                max-width: 480px !important;
                border-radius: 20px !important;
            }

            .login-header img {
                width: 380px !important;
            }

            .login-header h2 {
                font-size: 26px !important;
            }

            .tab-btn {
                padding: 20px 5px !important;
                font-size: 16px !important;
            }

            .login-body {
                padding: 40px !important;
            }

            .btn-login {
                padding: 16px !important;
                font-size: 18px !important;
            }
        }

        @media (max-width: 576px) {
            .login-card {
                max-width: 100% !important;
                margin: 0 !important;
            }

            .login-header img {
                width: 250px !important;
                max-width: 90% !important;
            }

            .login-header h2 {
                font-size: 1.1rem !important;
            }

            .tab-btn {
                font-size: 13px !important;
            }
        }
    </style>
</head>

<body class="login-combined">

    <div class="login-card">
        <div class="login-header">
            <img src="../assets/images/bg/update-09.png" alt="Logo">
            <h2 style="font-size: 1.3rem; color: #333;">ระบบรับสมัครทุนการศึกษา</h2>
        </div>

        <div class="login-tabs">
            <button class="tab-btn active" onclick="switchTab('student', this)">นักศึกษา</button>
            <button class="tab-btn" onclick="switchTab('teacher', this)">อาจารย์/กรรมการ</button>
            <button class="tab-btn" onclick="switchTab('admin', this)">ผู้ดูแลระบบ</button>
        </div>

        <div class="login-body">
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="error-msg"><?php echo $_SESSION['login_error'];
                                        unset($_SESSION['login_error']); ?></div>
            <?php endif; ?>

            <div id="student-form" class="login-form active">
                <form action="check_login_temp.php" method="POST">
                    <div class="form-group">
                        <label>รหัสนักศึกษา</label>
                        <input type="text" name="st_code" placeholder="เช่น 6411110xxx" required>
                    </div>
                    <div class="form-group">
                        <label>รหัสผ่าน</label>
                        <input type="password" name="st_pass" placeholder="รหัสผ่าน" required>
                    </div>
                    <button type="submit" class="btn-login" style="font-family: prompt;">เข้าสู่ระบบนักศึกษา</button>
                </form>
            </div>

            <div id="teacher-form" class="login-form">
                <form action="check_login_temp.php" method="POST">
                    <div class="form-group">
                        <label>Username อาจารย์</label>
                        <input type="text" name="tc_user" placeholder="username" required>
                    </div>
                    <div class="form-group">
                        <label>รหัสผ่าน</label>
                        <input type="password" name="tc_pass" placeholder="รหัสผ่าน" required>
                    </div>
                    <button type="submit" class="btn-login" style="background: #1a2a3a; font-family: prompt;">เข้าสู่ระบบอาจารย์</button>
                </form>
            </div>

            <div id="admin-form" class="login-form">
                <form action="check_login_temp.php" method="POST">
                    <div class="form-group">
                        <label>Username ผู้ดูแลระบบ</label>
                        <input type="text" name="ad_user" placeholder="username" required>
                    </div>
                    <div class="form-group">
                        <label>รหัสผ่าน</label>
                        <input type="password" name="ad_pass" placeholder="รหัสผ่าน" required>
                    </div>
                    <button type="submit" class="btn-login" style="background: #333; font-family: prompt;">เข้าสู่ระบบแอดมิน</button>
                </form>
            </div>

            <div style="margin-top: 25px; text-align: center; font-size: 14px; color: #666;">
                หากพบปัญหาการใช้งานระบบ แจ้งปัญหาได้ <a href="javascript:void(0);" onclick="window.location.href='../admin/issues/report_issue.php';" style="color: #003c71; font-weight: bold; text-decoration: underline;">ที่นี่</a>
            </div>
        </div>
    </div>

    <script>
        function switchTab(mode, btn) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.login-form').forEach(f => f.classList.remove('active'));
            document.getElementById(mode + '-form').classList.add('active');
        }
    </script>
</body>

</html>