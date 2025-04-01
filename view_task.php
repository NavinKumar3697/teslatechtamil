<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $task_completion_description = $_POST['task_completion_description'];
    $task_status = $_POST['task_status'];

    $stmt = $pdo->prepare("UPDATE task_manage SET task_completion_description = CONCAT(task_completion_description, :task_completion_description), task_status = :task_status, task_updated_on = NOW() WHERE task_id = :task_id");
    // Bind the parameters
    $stmt->bindParam(':task_completion_description', $task_completion_description, PDO::PARAM_STR);
    $stmt->bindParam(':task_status', $task_status, PDO::PARAM_STR);
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->execute();
    header('location:view_task.php?id='.$task_id.'');
}

// Fetch task details if id is set
if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    $sql = "
    SELECT task_manage.*, task_department.department_name, task_user.user_first_name, task_user.user_last_name, task_user.user_image  
    FROM task_manage 
    JOIN task_department ON task_manage.task_department_id = task_department.department_id 
    JOIN task_user ON task_manage.task_user_to = task_user.user_id 
    WHERE task_id = ? AND task_manage.task_assign_date <= ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$task_id, date('Y-m-d')]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if($task['task_status'] === 'Pending'){
        if(isset($_SESSION['user_logged_in'])){
            $stmt = $pdo->prepare("UPDATE task_manage SET task_status = ?, task_updated_on = NOW() WHERE task_id = ? AND task_user_to = ?");
            $stmt->execute(['Viewed', $task_id, $_SESSION['user_id']]);
            $task['task_status'] = 'Viewed';
        }
    }

    if (!$task) {
        $message = "Task not found!";
        exit;
    }
} else {
    $message = "Invalid task ID!";
    exit;
}



include('header.php');

?>

<h1 class="mt-4">Task Management</h1>
<ol class="breadcrumb mb-4">
    <?php
    if(isset($_SESSION['admin_logged_in'])){
    ?>
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <?php
    }
    ?>
    <li class="breadcrumb-item"><a href="task.php">Task Management</a></li>
    <li class="breadcrumb-item active">Task Details</li>
</ol>
<?php
if($message !== ''){
    echo '<div class="alert alert-danger">'.$message.'</div>';
} else {
?>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col col-10"><b><?php echo $task['task_title']; ?></b></div>
            <div class="col col-2">
                <?php 
                if(isset($_SESSION['user_logged_in'])){
                    if(isset($_GET["action"]) && $_GET["action"] === 'add_comment'){
                ?>
                <a href="view_task.php?id=<?php echo $task_id; ?>" class="btn btn-primary btn-sm float-end">View Task</a>
                <?php
                    } else {
                        if($task['task_status'] === 'Viewed' || $task['task_status'] === 'In Progress'){
                ?>
                <a href="view_task.php?id=<?php echo $task_id; ?>&action=add_comment" class="btn btn-primary btn-sm float-end">Add Comment</a>
                <?php
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <p><b>Department Name - </b><?php echo $task['department_name']; ?></p>
        <p><b>User Name - </b><?php echo $task['user_first_name'] . ' ' . $task['user_last_name']; ?></p>
        <p><b>Task Start Date - </b><?php echo $task['task_assign_date']; ?></p>
        <p><b>Task End Date - </b><?php echo $task['task_end_date']; ?></p>
        <p><b>Task Status - </b><?php echo formatTaskStatus($task['task_status']); ?></p>
        <b>Task Details - </b><?php echo $task['task_creator_description']; ?>
        <br />
        <br />
        <b><img src="<?php echo $task['user_image']; ?>" width="40" class="rounded-circle" /> <?php echo $task['user_first_name'] . ' ' . $task['user_last_name'] . ' comment - '?></b>
        <?php echo $task['task_completion_description'] ?? 'N/A'; ?>
        <br />
        <br />
        <p><b>Task Last Updated - </b><?php echo $task['task_updated_on']; ?></p>
        <?php
        if(isset($_GET["action"]) && $_GET["action"] === 'add_comment' && ($task['task_status'] === 'Viewed' || $task['task_status'] === 'In Progress')){
        ?>
        <form id="addTaskForm" method="POST" action="view_task.php?id=<?php echo htmlspecialchars($task_id); ?>">
            <div class="mb-3">
                <label for="task_completion_description"><b>Task Comment</b></label>
                <textarea name="task_completion_description" id="task_completion_description" class="summernote"></textarea>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="task_status">Task Status</label>
                    <select name="task_status" id="task_status" class="form-select">
                        <option value="Viewed">Viewed</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 text-center">
                <input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
                <button type="submit" class="btn btn-primary">Update Task</button>
            </div>
        </form>
        <?php
        }
        ?>
    </div>
</div>

<?php
}
?>

<?php
include('footer.php');
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>

<script>
$(document).ready(function() {
    $('.summernote').summernote({
        height: 200
    });

});
</script>