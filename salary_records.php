<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $department_id = $_POST['department_id'] ?? '';
        $user_id = $_POST['user_id'] ?? '';
        $user_join_date = $_POST['user_join_date'] ?? '';
        $bank_name = $_POST['bank_name'] ?? '';
        $account_number = $_POST['account_number'] ?? '';
        $ifsc_code = $_POST['ifsc_code'] ?? '';
        $basic_salary = $_POST['basic_salary'] ?? 0;
        $bonus = $_POST['bonus'] ?? 0;
        $overtime_hours = $_POST['overtime_hours'] ?? 0;
        $borrowings = $_POST['borrowings'] ?? 0;
        $leave_deduction = $_POST['leave_deduction'] ?? 0;
        $tax_deduction = $_POST['tax_deduction'] ?? 0;
        $basic_salary = $_POST['basic_salary'] ?? 0;
        $bonus = $_POST['bonus'] ?? 0;
        $overtime_hours = $_POST['overtime_hours'] ?? 0;
        $borrowings = $_POST['borrowings'] ?? 0;
        $leave_deduction = $_POST['leave_deduction'] ?? 0;
        $tax_deduction = $_POST['tax_deduction'] ?? 0;

        $final_salary = ($basic_salary + $bonus + ($overtime_hours * 100)) - ($borrowings + ($leave_deduction*500)  + $tax_deduction);

        if (empty($department_id) || empty($user_id) || empty($user_join_date) || empty($bank_name) || empty($account_number) || empty($ifsc_code)) {
            throw new Exception('All fields are required.');
        }

        $stmt = $pdo->prepare("INSERT INTO salary_records (department_id, user_id, user_join_date, bank_name, account_number, ifsc_code, basic_salary, bonus, overtime_hours, borrowings, leave_deduction, tax_deduction, final_salary) VALUES (:department_id, :user_id, :user_join_date, :bank_name, :account_number, :ifsc_code, :basic_salary, :bonus, :overtime_hours, :borrowings, :leave_deduction, :tax_deduction, :final_salary)");

        $stmt->execute([
            'department_id' => $department_id,
            'user_id' => $user_id,
            'user_join_date' => $user_join_date,
            'bank_name' => $bank_name,
            'account_number' => $account_number,
            'ifsc_code' => $ifsc_code,
            'basic_salary' => $basic_salary,
            'bonus' => $bonus,
            'overtime_hours' => $overtime_hours,
            'borrowings' => $borrowings,
            'leave_deduction' => $leave_deduction,
            'tax_deduction' => $tax_deduction,
            'final_salary' => $final_salary
        ]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Salary record saved successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while saving the record.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?> 