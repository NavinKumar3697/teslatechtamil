<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

$message = '';
$success = false;

// Handle Leave Approval or Rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['leave_id'], $_POST['status'])) {
    $leave_id = intval($_POST['leave_id']);
    $new_status = ($_POST['status'] === 'Approved') ? 'Approved' : 'Rejected';

    try {
        $stmt = $pdo->prepare("UPDATE leave_requests SET status = :status WHERE id = :leave_id");
        $stmt->execute(['status' => $new_status, 'leave_id' => $leave_id]);

        $message = "Leave request has been " . strtolower($new_status) . " successfully.";
        $success = true;
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
}

// Handle Payment Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['leave_id'], $_POST['payment_status'])) {
    $leave_id = intval($_POST['leave_id']);
    $payment_status = ($_POST['payment_status'] === 'Paid') ? 'Paid' : 'Unpaid';

    try {
        $stmt = $pdo->prepare("UPDATE leave_requests SET leave_compensation = :payment_status WHERE id = :leave_id");
        $stmt->execute(['payment_status' => $payment_status, 'leave_id' => $leave_id]);
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
}

// Fetch Leave Requests with Department Name
$query = "SELECT lr.id, lr.user_id, 
       CONCAT(tu.user_first_name, ' ', tu.user_last_name) AS name, 
       tu.user_email_address, 
       td.department_name, 
       lr.start_date, lr.end_date, 
       lr.leave_days, lr.leave_reason, 
       lr.leave_type, lr.leave_priority, 
       lr.leave_duration, lr.supporting_document, 
       lr.status, lr.applied_on, lr.leave_compensation 
        FROM leave_requests lr
        JOIN task_user tu ON lr.user_id = tu.user_id 
        JOIN task_department td ON tu.department_id = td.department_id 
        ORDER BY lr.applied_on DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$leave_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<h1 class="mt-4">Manage Leave Requests</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Leave Management</li>
</ol>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><b>Leave Requests</b></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-normal">
                    <tr>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Total Days</th>
                        <th>Leave Type</th>
                        <th>Priority</th>
                        <th>Duration</th>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leave_requests as $leave): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($leave['name']); ?></td>
                            <td><?php echo htmlspecialchars($leave['user_email_address']); ?></td>
                            <td><?php echo htmlspecialchars($leave['department_name']); ?></td>
                            <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($leave['leave_days']); ?></td>
                            <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                            <td><?php echo htmlspecialchars($leave['leave_priority']); ?></td>
                            <td><?php echo htmlspecialchars($leave['leave_duration']); ?></td>
                            <td>
                                <?php if (!empty($leave['supporting_document'])) {
                                    $file_path = ltrim($leave['supporting_document'], '/'); 
                                ?>
                                    <a href="uploads/<?php echo htmlspecialchars($file_path); ?>" target="_blank">View</a>
                                <?php } else {
                                    echo 'No Document Available';
                                } ?>
                            </td>
                            <td>
                                <b><?php echo htmlspecialchars($leave['status']); ?></b>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="leave_id" value="<?php echo intval($leave['id']); ?>">
                                    <select name="payment_status" class="form-control" onchange="this.form.submit()">
                                        <option value="Paid" <?php echo ($leave['leave_compensation'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="Unpaid" <?php echo ($leave['leave_compensation'] === 'Unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
