<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

$message = '';
$success = false;
$user_id = $_SESSION['user_id'] ?? '';
$remaining_leave = 0;

if (!empty($user_id)) {
    $stmt = $pdo->prepare("SELECT user_leave FROM task_user WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $remaining_leave = $user['user_leave'];
    } else {
        $message = 'User data not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leave_reason = trim($_POST['leave_reason']);
    $leave_type = $_POST['leave_type'];
    $leave_priority = $_POST['leave_priority'];
    $leave_duration = $_POST['leave_duration'];

    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $leave_days = $interval->days + 1; // Including start date

    if ($leave_duration == 'Half-Day') {
        $leave_days = 0.5;
    }

    if ($leave_days <= 0) {
        $message = 'Invalid leave duration.';
    } elseif ($leave_days > $remaining_leave) {
        $message = 'Insufficient leave balance. You have only ' . $remaining_leave . ' days left.';
    } elseif (empty($leave_reason)) {
        $message = 'Leave reason is required.';
    } else {
        $new_remaining_leave = $remaining_leave - $leave_days;

        $file_path = '';
        if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] == 0) {
            $upload_dir = "uploads/";
            $file_name = time() . "_" . basename($_FILES["supporting_document"]["name"]); // Unique filename
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES["supporting_document"]["tmp_name"], $target_file)) {
                $supporting_document = $file_name; // Store only the filename in DB, not "uploads/"
            } else {
                $supporting_document = NULL;
            }
        } else {
            $supporting_document = NULL;
        }
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE task_user SET user_leave = :new_leave WHERE user_id = :user_id");
            $stmt->execute(['new_leave' => $new_remaining_leave, 'user_id' => $user_id]);

            $stmt = $pdo->prepare("INSERT INTO leave_requests (user_id, start_date, end_date, leave_days, leave_reason, leave_type, leave_priority, leave_duration, supporting_document, status, applied_on) 
            VALUES (:user_id, :start_date, :end_date, :leave_days, :leave_reason, :leave_type, :leave_priority, :leave_duration, :supporting_document, 'Pending', NOW())");

            $stmt->execute([
                'user_id' => $user_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'leave_days' => $leave_days,
                'leave_reason' => $leave_reason,
                'leave_type' => $leave_type,
                'leave_priority' => $leave_priority,
                'leave_duration' => $leave_duration,
                'supporting_document' => $supporting_document
            ]);


            $pdo->commit();
            header("Location: apply_leave.php?success=1");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'Database error: ' . $e->getMessage();
        }
    }
}

include('header.php');
?>

<h1 class="mt-4">Apply Leave</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Apply Leave</li>
</ol>

<?php if (!empty($message)) : ?>
    <div class="alert alert-danger"><?= $message ?></div>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] == 1) : ?>
    <div class="alert alert-success">Leave applied successfully</div>
    <script>
        setTimeout(() => {
            window.location.href = 'apply_leave.php';
        }, 3000);
    </script>
<?php endif; ?>

<div class="card">
    <div class="card-header"><b>Leave Application Form</b></div>
    <div class="card-body">
        <form method="post" action="apply_leave.php" enctype="multipart/form-data">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="remaining_leave">Remaining Leaves:</label>
                    <input type="text" id="remaining_leave" class="form-control" value="<?= $remaining_leave ?>" disabled>
                </div>
                <div class="col-md-4">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="total_days">Total Days:</label>
                    <input type="text" id="total_days" name="total_days" class="form-control" readonly>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="leave_type">Leave Type:</label>
                    <select id="leave_type" name="leave_type" class="form-select">
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Annual Leave">Annual Leave</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="leave_priority">Leave Priority:</label>
                    <select id="leave_priority" name="leave_priority" class="form-select">
                        <option value="Normal">Normal</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="leave_duration">Leave Duration:</label>
                    <select id="leave_duration" name="leave_duration" class="form-select">
                        <option value="Full-Day">Full Day</option>
                        <option value="Half-Day">Half Day</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-8">
                    <label for="leave_reason">Leave Reason:</label>
                    <textarea id="leave_reason" name="leave_reason" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-8">
                    <label for="supporting_document">Supporting Document (Optional):</label>
                    <input type="file" id="supporting_document" name="supporting_document" class="form-control" accept=".pdf,.jpg,.png,.docx">
                </div>
            </div>
            <div class="mt-2 text-center">
                <input type="submit" value="Apply Leave" class="btn btn-primary">
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById("start_date").addEventListener("change", calculateTotalDays);
    document.getElementById("end_date").addEventListener("change", calculateTotalDays);
    document.getElementById("leave_duration").addEventListener("change", calculateTotalDays);

    function calculateTotalDays() {
        let startDate = new Date(document.getElementById("start_date").value);
        let endDate = new Date(document.getElementById("end_date").value);
        let leaveDuration = document.getElementById("leave_duration").value;
        let totalDaysField = document.getElementById("total_days");

        totalDaysField.value = (leaveDuration === "Half-Day") ? 0.5 : Math.max(0, (endDate - startDate) / (1000 * 60 * 60 * 24) + 1);
    }
</script>

<?php include('footer.php'); ?>