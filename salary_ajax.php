<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['department_id'])) {
        $department_id = $_POST['department_id'];
        $stmt = $pdo->prepare("SELECT user_id, user_first_name, user_last_name FROM task_user WHERE department_id = ?");
        $stmt->execute([$department_id]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['type' => 'users', 'data' => $users]);
        exit;
    }

    if (isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        $stmt = $pdo->prepare("SELECT user_BankName, user_Account_No, user_IFSC, user_salary, user_leave,user_join_date FROM task_user WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            echo json_encode(['type' => 'salary', 'data' => $userData]);
        } else {
            echo json_encode(['type' => 'error', 'message' => 'User not found']);
        }
        exit;
    }
}

echo json_encode(['type' => 'error', 'message' => 'Invalid request']);
?>
