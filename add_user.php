<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$message = '';

// Fetch departments for the dropdown
$departments = [];
$stmt = $pdo->query("SELECT department_id, department_name FROM task_department WHERE department_status = 'Enable'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $departments[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_first_name = trim($_POST['user_first_name']);
    $user_last_name = trim($_POST['user_last_name']);
    $department_id = trim($_POST['department_id']);
    $user_email_address = trim($_POST['user_email_address']);
    $user_email_password = trim($_POST['user_email_password']);
    $user_contact_no = trim($_POST['user_contact_no']);
    $user_date_of_birth = trim($_POST['user_date_of_birth']);
    $user_gender = trim($_POST['user_gender']);
    $user_address = trim($_POST['user_address']);
    $user_status = trim($_POST['user_status']);
    $user_BankName = trim($_POST['user_BankName']);
    $user_Account_No = trim($_POST['user_Account_No']);
    $user_IFSC = trim($_POST['user_IFSC']);
    $user_leave = trim($_POST['user_leave']);
    $user_salary_from_date = trim($_POST['user_salary_from_date']);
    $user_join_date = trim($_POST['user_join_date']);
    $user_salary = trim($_POST['user_salary']);

    // Validate inputs
    if (empty($user_first_name) || empty($user_last_name) || empty($department_id) || empty($user_email_address) || empty($user_email_password) || empty($user_contact_no) || empty($user_date_of_birth) || empty($user_gender) || empty($user_address) || empty($user_status) || empty($user_BankName) || empty($user_Account_No) || empty($user_IFSC) || empty($user_salary_from_date) || empty($user_leave) || empty($user_join_date) || empty($user_salary)) {
        $message = 'All fields are required.';
    } elseif (!filter_var($user_email_address, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } elseif (!isset($_FILES['user_image']) || $_FILES['user_image']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Error uploading image.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM task_user WHERE user_email_address = :user_email_address");
        $stmt->execute(['user_email_address' => $user_email_address]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $message = 'Email already exists.';
        } else {
            // Hash the password
            $hashed_password = password_hash($user_email_password, PASSWORD_DEFAULT);

            // Handle image upload
            $image_path = 'uploads/' . basename($_FILES['user_image']['name']);
            if (move_uploaded_file($_FILES['user_image']['tmp_name'], $image_path)) {
                // Insert into database
                try {
                    $stmt = $pdo->prepare("INSERT INTO task_user (
                        user_first_name, user_last_name, department_id, user_email_address, user_email_password, 
                        user_contact_no, user_date_of_birth, user_gender, user_address, user_status, user_image, 
                        user_BankName, user_Account_No, user_IFSC, user_salary, user_salary_from_date, user_leave, 
                        user_join_date, user_added_on, user_updated_on) 
                    VALUES (
                        :user_first_name, :user_last_name, :department_id, :user_email_address, :user_email_password, 
                        :user_contact_no, :user_date_of_birth, :user_gender, :user_address, :user_status, :user_image, 
                        :user_BankName, :user_Account_No, :user_IFSC, :user_salary, :user_salary_from_date, :user_leave, 
                        :user_join_date, NOW(), NOW())");

                    $stmt->execute([
                        'user_first_name'       => $user_first_name,
                        'user_last_name'        => $user_last_name,
                        'department_id'         => $department_id,
                        'user_email_address'    => $user_email_address,
                        'user_email_password'   => $hashed_password,
                        'user_contact_no'       => $user_contact_no,
                        'user_date_of_birth'    => $user_date_of_birth,
                        'user_gender'           => $user_gender,
                        'user_address'          => $user_address,
                        'user_status'           => $user_status,
                        'user_image'            => $image_path,
                        'user_BankName'         => $user_BankName,
                        'user_Account_No'       => $user_Account_No,
                        'user_IFSC'             => $user_IFSC,
                        'user_salary'           => $user_salary,
                        'user_salary_from_date' => $user_salary_from_date,
                        'user_leave'            => $user_leave,
                        'user_join_date'        => $user_join_date
                    ]);

                    header('location:user.php');
                    exit();
                } catch (PDOException $e) {
                    $message = 'Database Error: ' . $e->getMessage();
                }
            } else {
                $message = 'Failed to upload image.';
            }
        }
    }
}

include('header.php');
?>


    <h1 class="mt-4">Add User</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="user.php">User Management</a></li>
        <li class="breadcrumb-item active">Add User</li>
    </ol>
    <?php
    if (isset($message) && $message !== '') {
        echo '
            <div class="alert alert-danger">
            ' . $message . '
            </div>
            ';
    }
    ?>
    <div class="card">
        <div class="card-header">Add User</div>
        <div class="card-body">
            <form method="post" action="add_user.php" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="department_id">Department:</label>
                        <select id="department_id" name="department_id" class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept) { ?>
                                <option value="<?php echo $dept['department_id']; ?>"
                                    <?php if (isset($department) && $department == $dept['department_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="user_first_name">First Name:</label>
                        <input type="text" id="user_first_name" name="user_first_name" class="form-control"
                            value="<?php echo htmlspecialchars($user_first_name ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="user_last_name">Last Name:</label>
                        <input type="text" id="user_last_name" name="user_last_name" class="form-control"
                            value="<?php echo htmlspecialchars($user_last_name ?? ''); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="user_email_address">Email:</label>
                        <input type="email" id="user_email_address" name="user_email_address" class="form-control"
                            value="<?php echo htmlspecialchars($user_email_address ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="user_email_password">Password:</label>
                        <input type="password" id="user_email_password" name="user_email_password" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="user_contact_no">Contact No:</label>
                        <input type="text" id="user_contact_no" name="user_contact_no" class="form-control"
                            value="<?php echo htmlspecialchars($user_contact_no ?? ''); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="user_date_of_birth">Date of Birth:</label>
                        <input type="date" id="user_date_of_birth" name="user_date_of_birth" class="form-control"
                            value="<?php echo htmlspecialchars($user_date_of_birth ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="user_gender">Gender:</label>
                        <select id="user_gender" name="user_gender" class="form-select">
                            <option value="Male"
                                <?php if (isset($user_gender) && $user_gender == 'Male') echo 'selected'; ?>>Male</option>
                            <option value="Female"
                                <?php if (isset($user_gender) && $user_gender == 'Female') echo 'selected'; ?>>Female
                            </option>
                            <option value="Other"
                                <?php if (isset($user_gender) && $user_gender == 'Other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="user_address">Address:</label>
                        <input type="text" id="user_address" name="user_address" class="form-control"
                            value="<?php echo htmlspecialchars($user_address ?? ''); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="user_image">User Image:</label>
                        <input type="file" id="user_image" name="user_image" accept="image/*" required>
                    </div>
                    <div class="col-md-4">
                        <label for="user_status">Status:</label>
                        <select id="user_status" name="user_status" class="form-select">
                            <option value="Enable"
                                <?php if (isset($user_status) && $user_status == 'Enable') echo 'selected'; ?>>Enable
                            </option>
                            <option value="Disable"
                                <?php if (isset($user_status) && $user_status == 'Disable') echo 'selected'; ?>>Disable
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="user_join_date">Join Date:</label>
                        <input type="date" id="user_join_date" name="user_join_date" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">Bank Information</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="Bank_Name">Bank Name:</label>
                                        <input type="text" id="Bank_Name" name="user_BankName" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="Account_No">Account Number:</label>
                                        <input type="text" id="Account_No" name="user_Account_No" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="IFSC">IFSC Code:</label>
                                        <input type="text" id="IFSC" name="user_IFSC" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="user_salary">Salary</label>
                        <input type="text" id="user_salary" name="user_salary" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="salary">Salary From</label>
                        <input type="date" id="salary_from_date" name="user_salary_from_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="leave">Leave</label>
                        <input type="text" id="leave" name="user_leave" class="form-control" required>
                    </div>
                </div>
        </div>
        <div class="mt-2 text-center">
            <input type="submit" value="Add User" class="btn btn-primary" />
        </div>
        </form>
    </div>
    </div>

    <?php
    include('footer.php');
    ?>