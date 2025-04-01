<?php
require_once 'db_connect.php'; // Database connection
require_once 'auth_function.php'; // Admin login check

checkAdminLogin(); // Ensure admin is logged in

    // Handle remove user (using AJAX)
    if (isset($_GET['delete_id'])) {
        $user_id = $_GET['delete_id'];

        try {
            // Start transaction to handle deletion of related records and user
            $pdo->beginTransaction();

            // Step 1: Delete related records in task_manage table
            $delete_tasks_sql = "DELETE FROM task_manage WHERE task_user_to = ?";
            $stmt = $pdo->prepare($delete_tasks_sql);
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // Step 2: Delete the user from task_user table
            $delete_user_sql = "DELETE FROM task_user WHERE user_id = ?";
            $stmt = $pdo->prepare($delete_user_sql);
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // Commit transaction
            $pdo->commit();

            echo 'success'; // Return success response for AJAX
            exit();

        } catch (PDOException $e) {
            // Rollback the transaction in case of error
            $pdo->rollBack();
            error_log("PDO Error: " . $e->getMessage()); // Log error message
            echo 'error'; // Return error response for AJAX
            exit();
        }
    }

// Check if status message exists
if (isset($_GET['status'])) {
    $status_message = ($_GET['status'] == 'success') ? 'User removed successfully!' : 'There was an error removing the user. Please try again.';
    $status_class = ($_GET['status'] == 'success') ? 'alert-success' : 'alert-danger';
}

include('header.php');
?>

<h1 class="mt-4">User Management</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">User Management</li>
</ol>

<?php if (isset($status_message)): ?>
    <div class="alert <?php echo $status_class; ?>"><?php echo $status_message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col col-md-6"><b>User List</b></div>
            <div class="col col-md-6">
                <a href="add_user.php" class="btn btn-success btn-sm float-end">Add</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="userTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>ID</th>
                    <th>Department</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Contact No.</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch user data with department name
                $sql = "SELECT u.*, d.department_name FROM task_user u 
                        LEFT JOIN task_department d ON u.department_id = d.department_id";
                $result = $pdo->query($sql);

                if ($result && $result->rowCount() > 0) {
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                ?>
                        <tr id="user-<?php echo $row['user_id']; ?>">
                            <td><img src="<?php echo $row['user_image']; ?>" width="50" /></td>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo $row['department_name']; ?></td>
                            <td><?php echo $row['user_first_name']; ?></td>
                            <td><?php echo $row['user_last_name']; ?></td>
                            <td><?php echo $row['user_email_address']; ?></td>
                            <td><?php echo $row['user_contact_no']; ?></td>
                            <td>
                                <?php echo ($row['user_status'] === 'Enable') ?
                                    '<span class="badge bg-success">Enable</span>' :
                                    '<span class="badge bg-danger">Disable</span>'; ?>
                            </td>
                            <td>
                                <div class="text-center">
                                    <a href="view_user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-primary btn-sm">View</a>&nbsp;
                                    <a href="edit_user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <button class="btn btn-danger btn-sm remove-user" data-id="<?php echo $row['user_id']; ?>">Remove</button>
                                </div>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
    $(document).ready(function() {
        $('#userTable').DataTable();

        // Handle remove user
        $('.remove-user').click(function() {
            var userId = $(this).data('id');

            console.log('Deleting user with ID:', userId); // Log user ID to confirm it's being passed

            if (confirm('Are you sure you want to remove this user?')) {
                $.ajax({
                    url: '', // The same page
                    type: 'GET',
                    data: {
                        delete_id: userId
                    },
                    success: function(response) {
                        console.log('Response from server:', response); // Log server response for debugging

                        if (response === 'success') {
                            $('#user-' + userId).remove(); // Remove the user row from the table
                        } else {
                            alert('Error removing user. Please try again.');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred while processing your request.');
                        console.error('AJAX Error:', status, error); // Log any AJAX error
                    }
                });
            }
        });
    });
</script>