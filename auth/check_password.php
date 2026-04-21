<?php
session_start();
include '../include/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'])) {

    $admin_id = 1;
    $input_password = $_POST['current_password'];

    $sql = "SELECT ad_pass FROM tb_admin WHERE ad_id = '$admin_id'";
    $result = mysqli_query($connect1, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $admin_data = mysqli_fetch_assoc($result);
        $correct_password = $admin_data['ad_pass'];

        if ($input_password === $correct_password) {
            echo json_encode(['correct' => true]);
        } else {
            echo json_encode(['correct' => false]);
        }
    } else {
        echo json_encode(['correct' => false, 'error' => 'Admin not found']);
    }
} else {
    echo json_encode(['correct' => false, 'error' => 'Invalid request']);
}

exit();
