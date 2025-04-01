<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

$user_id = $_SESSION['user_id'] ?? '';

if (empty($user_id)) {
    die('User not logged in.');
}

$stmt = $pdo->prepare("SELECT sd.fixed_salary, sd.allowance, sd.borrowings, sd.total_salary, sd.salary_date, td.department_name FROM salary_details sd JOIN task_department td ON sd.department_id = td.department_id WHERE sd.user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<h1 class="mt-4">Salary Details</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="task.php">Task</a></li>
    <li class="breadcrumb-item active">Salary Details</li>
</ol>

<div class="card">
    <div class="card-header"><b>Your Salary Details</b></div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Department Name</th>
                    <th>Fixed Salary</th>
                    <th>Allowance</th>
                    <th>Borrowings</th>
                    <th>Total Salary</th>
                    <th>Salary Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($salaries)) { ?>
                    <tr><td colspan="6" class="text-center">No salary records found.</td></tr>
                <?php } else { ?>
                    <?php foreach ($salaries as $salary) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($salary['department_name']); ?></td>
                            <td><?php echo htmlspecialchars($salary['fixed_salary']); ?></td>
                            <td><?php echo htmlspecialchars($salary['allowance']); ?></td>
                            <td><?php echo htmlspecialchars($salary['borrowings']); ?></td>
                            <td><?php echo htmlspecialchars($salary['total_salary']); ?></td>
                            <td><?php echo htmlspecialchars($salary['salary_date']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>


