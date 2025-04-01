<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$departments = $pdo->query("SELECT department_id, department_name FROM task_department")->fetchAll(PDO::FETCH_ASSOC);
include('header.php');
?>

<h1 class="mt-4">Salary Management</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Salary Management</li>
</ol>

<div class="card">
    <div class="card-header">Manage Salary</div>
    <div class="card-body">
        <form id="salaryForm">
            <div class="row">
                <!-- User Details & Bank Information -->
                <section class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">User & Bank Information</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="department_id">Select Department:</label>
                                <select id="department_id" name="department_id" class="form-select" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept) { ?>
                                        <option value="<?php echo $dept['department_id']; ?>">
                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="user_id">Select User:</label>
                                <select id="user_id" name="user_id" class="form-select" required>
                                    <option value="">Select User</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="user_join_date">User Join Date:</label>
                                <input type="date" id="user_join_date" name="user_join_date" class="form-control" readonly>
                            </div>
                            <hr>
                            <h5>Bank Information</h5>
                            <div class="mb-3">
                                <label for="bank_name">Bank Name:</label>
                                <input type="text" id="bank_name" name="bank_name" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="account_number">Account Number:</label>
                                <input type="text" id="account_number" name="account_number" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="ifsc_code">IFSC Code:</label>
                                <input type="text" id="ifsc_code" name="ifsc_code" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Salary Calculation Section -->
                <section class="col-md-6">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">Salary Calculation</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="basic_salary">Basic Salary:</label>
                                <input type="number" id="basic_salary" name="basic_salary" class="form-control" required min="0">
                            </div>
                            <div class="mb-3">
                                <label for="bonus">Bonus:</label>
                                <input type="number" id="bonus" name="bonus" class="form-control" min="0">
                            </div>
                            <div class="mb-3">
                                <label for="overtime_hours">Overtime Hours:</label>
                                <input type="number" id="overtime_hours" name="overtime_hours" class="form-control" min="0">
                            </div>
                            <div class="mb-3">
                                <label for="borrowings">Borrowings (Loans):</label>
                                <input type="number" id="borrowings" name="borrowings" class="form-control" min="0">
                            </div>
                            <div class="mb-3">
                                <label for="leave_deduction">Leave Deduction:</label>
                                <input type="number" id="leave_deduction" name="leave_deduction" class="form-control" min="0">
                            </div>
                            <div class="mb-3">
                                <label for="tax_deduction">Tax Deduction:</label>
                                <input type="number" id="tax_deduction" name="tax_deduction" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="final_salary">Final Salary:</label>
                                <input type="number" id="final_salary" name="final_salary" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="row">
                <div class="col-md-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary">Save Salary Record</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#department_id').change(function() {
            var departmentId = $(this).val();
            if (departmentId) {
                $.post('salary_ajax.php', {
                    department_id: departmentId
                }, function(response) {
                    if (response.type === 'users') {
                        var userSelect = $('#user_id').html('<option value="">Select User</option>');
                        response.data.forEach(user => {
                            userSelect.append('<option value="' + user.user_id + '">' + user.user_first_name + ' ' + user.user_last_name + '</option>');
                        });
                    }
                }, 'json');
            }
        });

        $('#user_id').change(function() {
            var userId = $(this).val();
            if (userId) {
                $.post('salary_ajax.php', {
                    user_id: userId
                }, function(response) {
                    console.log("AJAX Response:", response); // Debugging

                    if (response.type === 'salary' && response.data) {
                        // Auto-fill user details
                        $('#user_join_date').val(response.data.user_join_date || '');
                        $('#bank_name').val(response.data.user_BankName || '');
                        $('#account_number').val(response.data.user_Account_No || '');
                        $('#ifsc_code').val(response.data.user_IFSC || '');
                        $('#basic_salary').val(response.data.user_salary || 0);
                        $('#borrowings').val(response.data.user_salary_advance || 0);
                        $('#user_leave').val(response.data.user_leave || 0);
                    } else {
                        console.error("Error:", response.message);
                    }
                }, 'json').fail(function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                });
            }
        });
    });

    function calculateFinalSalary() {
        let basicSalary = parseFloat($('#basic_salary').val()) || 0;
        let bonus = parseFloat($('#bonus').val()) || 0;
        let overtime = parseFloat($('#overtime_hours').val()) || 0;
        let borrowings = parseFloat($('#borrowings').val()) || 0;
        let leaveDeduction = parseFloat($('#leave_deduction').val()) || 0;
        let taxDeduction = parseFloat($('#tax_deduction').val()) || 0;

        let finalSalary = (basicSalary + bonus + (overtime * 100)) - (borrowings + leaveDeduction + taxDeduction);
        $('#final_salary').val(finalSalary.toFixed(2));
    }

    // Trigger calculation on input change
    $('#basic_salary, #bonus, #overtime_hours, #borrowings, #leave_deduction, #tax_deduction').on('input', calculateFinalSalary);
</script>