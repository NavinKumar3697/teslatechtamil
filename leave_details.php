<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

// Fetch Departments
$dept_stmt = $pdo->prepare("SELECT * FROM task_department");
$dept_stmt->execute();
$departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Users Based on Selected Department
$users = [];
if (!empty($_POST['department_id'])) {
    $user_stmt = $pdo->prepare("SELECT * FROM task_user WHERE department_id = :dept_id");
    $user_stmt->execute(['dept_id' => $_POST['department_id']]);
    $users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
}

include('header.php');
?>

<h1 class="mt-4">Leave Details</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Leave Details</li>
</ol>

<div class="card">
    <div class="card-header"><b>Filter Leave Requests</b></div>
    <div class="card-body">
        <form method="post" id="filterForm">
            <div class="row">
                <div class="col-md-6">
                    <label for="department_id" class="form-label">Select Department</label>
                    <select name="department_id" id="department_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo $department['department_id']; ?>"
                                <?php echo (!empty($_POST['department_id']) && $_POST['department_id'] == $department['department_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($department['department_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="user_id" class="form-label">Select User</label>
                    <select name="user_id" id="user_id" class="form-control">
                        <option value="">-- Select User --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>">
                                <?php echo htmlspecialchars($user['user_first_name'] . ' ' . $user['user_last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>
