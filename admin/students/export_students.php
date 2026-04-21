<?php
session_start();
include '../../include/config.php';

$scholarship_id = isset($_GET['type']) ? (int)$_GET['type'] : 0;

$scholarship_title = "รายชื่อนักศึกษา";
if ($scholarship_id >= 1 && $scholarship_id <= 3) {
    $column_name = "st_name_" . $scholarship_id;
    $sql_title = "SELECT `$column_name` FROM tb_year WHERE y_id = 1";
    $result_title = mysqli_query($connect1, $sql_title);
    if ($result_title && mysqli_num_rows($result_title) > 0) {
        $data_title = mysqli_fetch_row($result_title);
        $scholarship_title = !empty($data_title[0]) ? $data_title[0] : "ทุนประเภทที่ " . $scholarship_id;
    }
}

$sql_export = "SELECT s.st_code, s.st_firstname, s.st_lastname, p.g_program, s.st_activate, s.st_confirm
               FROM tb_student s
               LEFT JOIN tb_program p ON s.st_program = p.g_id";

if ($scholarship_id > 0) {
    $sql_export .= " WHERE s.st_type = '$scholarship_id'";
}
$sql_export .= " ORDER BY s.st_code ASC";

$result = mysqli_query($connect1, $sql_export);

$filename = "รายชื่อนักศึกษา_" . date('Ymd_His') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        body,
        table,
        td,
        th {
            font-family: "TH Sarabun New", "TH Sarabun PSK", serif;
        }

        th {
            background-color: #3b7ddd;
            color: #ffffff;
            border: 0.5pt solid #000000;
            padding: 5px;
            font-weight: bold;
            font-size: 14pt;
        }

        td {
            border: 0.5pt solid #000000;
            padding: 4px;
            font-size: 14pt;
        }
    </style>
</head>

<body>
    <table border="1">
        <tr>
            <th colspan="5" style="font-size: 16pt; font-weight: bold; height: 30px;">
                รายชื่อนักศึกษาผู้สมัครรับทุน: <?php echo $scholarship_title; ?>
            </th>
        </tr>
        <thead>
            <tr>
                <th width="120">รหัสนักศึกษา</th>
                <th width="200">ชื่อ</th>
                <th width="200">นามสกุล</th>
                <th width="250">สาขาวิชา</th>
                <th width="120">สถานะการอนุมัติ</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td style="text-align: center;"><?php echo $row['st_code']; ?></td>
                    <td><?php echo $row['st_firstname']; ?></td>
                    <td><?php echo $row['st_lastname']; ?></td>
                    <td><?php echo $row['g_program']; ?></td>
                    <td style="text-align: center;">
                        <?php echo ($row['st_confirm'] == 1) ? "อนุมัติแล้ว" : "รอดำเนินการ"; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>