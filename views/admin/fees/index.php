<?php
$active_page = 'fees';
$page_title = 'Fees Management';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-money-bill-wave text-primary me-2"></i>Fees Management</h4>
        <p class="text-muted mb-0">Handle fee collection, payments, and financial records</p>
    </div>
    <a href="/admin/fees/create" class="btn btn-primary btn-lg">
        <i class="fas fa-plus me-2"></i>Record Payment
    </a>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash']['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['flash']['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Fee Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Collected</h5>
                <h3>$<?php echo number_format($stats['total_collected'] ?? 0, 2); ?></h3>
                <small>This month</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Pending</h5>
                <h3>$<?php echo number_format($stats['total_pending'] ?? 0, 2); ?></h3>
                <small>Outstanding</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">This Month</h5>
                <h3>$<?php echo number_format($stats['monthly_target'] ?? 0, 2); ?></h3>
                <small>Target</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Overdue</h5>
                <h3>$<?php echo number_format($stats['overdue_amount'] ?? 0, 2); ?></h3>
                <small>Payments</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/fees" class="row align-items-end">
            <div class="col-md-2">
                <label for="start_date" class="form-label">From Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $_GET['start_date'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">To Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $_GET['end_date'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <label for="month" class="form-label">Month</label>
                <input type="month" class="form-control" id="month" name="month" value="<?php echo $_GET['month'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <label for="year" class="form-label">Year</label>
                <input type="number" class="form-control" id="year" name="year" value="<?php echo $_GET['year'] ?? ''; ?>" placeholder="e.g., 2024" min="2000" max="2030">
            </div>
            <div class="col-md-2">
                <label for="search" class="form-label">Search Student</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo $_GET['search'] ?? ''; ?>" placeholder="Name or Scholar No.">
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-filter me-1"></i>Apply Filters
                    </button>
                    <a href="/admin/fees" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-plus-circle fa-2x text-primary mb-3"></i>
                <h6 class="card-title">Record Payment</h6>
                <a href="/admin/fees/create" class="btn btn-primary btn-sm">New Payment</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-users-cog fa-2x text-secondary mb-3"></i>
                <h6 class="card-title">Bulk Assign Fees</h6>
                <button class="btn btn-secondary btn-sm" onclick="showBulkAssignModal()">
                    <i class="fas fa-tasks me-1"></i>Assign
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-file-invoice-dollar fa-2x text-success mb-3"></i>
                <h6 class="card-title">Generate Receipt</h6>
                <a href="/admin/fees/receipts" class="btn btn-success btn-sm">Print Receipts</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-download fa-2x text-warning mb-3"></i>
                <h6 class="card-title">Export Data</h6>
                <button class="btn btn-warning btn-sm" onclick="exportFees()">
                    <i class="fas fa-file-excel me-1"></i>Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Assign Fees Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Assign Fees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkAssignForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">

                    <div class="mb-3">
                        <label class="form-label">Assignment Type <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="assignment_type" id="assign_class" value="class" checked>
                            <label class="form-check-label" for="assign_class">
                                Assign to entire class
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="assignment_type" id="assign_selected" value="selected">
                            <label class="form-check-label" for="assign_selected">
                                Assign to selected students
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="classSelection">
                        <label for="class_id" class="form-label">Select Class <span class="text-danger">*</span></label>
                        <select class="form-select" id="class_id" name="class_id" required>
                            <option value="">Choose a class...</option>
                            <?php
                            $academicYearId = isset($_SESSION['academic_year_id']) ? $_SESSION['academic_year_id'] : null;
                            $where = "WHERE is_active = 1";
                            $params = [];
                            if ($academicYearId) {
                                $where .= " AND academic_year_id = ?";
                                $params = [$academicYearId];
                            }
                            $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);
                            foreach ($classes as $class):
                            ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name'] . ' ' . $class['section']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="studentSelection" style="display: none;">
                        <label class="form-label">Select Students <span class="text-danger">*</span></label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div id="studentList" class="row">
                                <!-- Students will be loaded here -->
                            </div>
                        </div>
                        <div class="form-text">Select individual students to assign fees to</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fee_type" class="form-label">Fee Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="fee_type" name="fee_type" required>
                                    <option value="">Select fee type...</option>
                                    <option value="tuition">Tuition Fee</option>
                                    <option value="transport">Transport Fee</option>
                                    <option value="exam">Exam Fee</option>
                                    <option value="library">Library Fee</option>
                                    <option value="sports">Sports Fee</option>
                                    <option value="misc">Miscellaneous</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="academic_year" class="form-label">Academic Year</label>
                                <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo date('Y') . '-' . (date('Y') + 1); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Optional remarks..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignFees()">
                    <i class="fas fa-tasks me-2"></i>Assign Fees
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Fees Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Payment Records</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($fees)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Scholar No.</th>
                            <th>Fee Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fees as $fee): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($fee['created_at'])); ?></td>
                                <td><?php echo $fee['first_name'] . ' ' . $fee['last_name']; ?></td>
                                <td><?php echo $fee['scholar_number']; ?></td>
                                <td><?php echo $fee['fee_type'] ?? 'Tuition'; ?></td>
                                <td>$<?php echo number_format($fee['amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-success">Paid</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/admin/fees/view/<?php echo $fee['id']; ?>" class="btn btn-sm btn-outline-info" title="View Receipt">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/fees/edit/<?php echo $fee['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteFee(<?php echo $fee['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-money-bill-wave fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Fee Records Found</h4>
                <p class="text-muted">Start recording fee payments.</p>
                <a href="/admin/fees/create" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Record First Payment
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteFee(feeId) {
    if (confirm('Are you sure you want to delete this fee record?')) {
        // TODO: Implement delete functionality
        alert('Delete fee record: ' + feeId);
    }
}

function exportFees() {
    const params = new URLSearchParams(window.location.search);
    let url = '/admin/fees/export?';
    for (let [key, value] of params) {
        url += `${key}=${encodeURIComponent(value)}&`;
    }
    window.open(url, '_blank');
}

function showBulkAssignModal() {
    const modal = new bootstrap.Modal(document.getElementById('bulkAssignModal'));
    modal.show();
}

document.querySelectorAll('input[name="assignment_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const classSelection = document.getElementById('classSelection');
        const studentSelection = document.getElementById('studentSelection');

        if (this.value === 'class') {
            classSelection.style.display = 'block';
            studentSelection.style.display = 'none';
            document.getElementById('class_id').required = true;
        } else {
            classSelection.style.display = 'none';
            studentSelection.style.display = 'block';
            document.getElementById('class_id').required = false;
            loadStudentsForSelection();
        }
    });
});

document.getElementById('class_id').addEventListener('change', function() {
    if (document.querySelector('input[name="assignment_type"]:checked').value === 'selected') {
        loadStudentsForSelection();
    }
});

function loadStudentsForSelection() {
    const classId = document.getElementById('class_id').value;
    if (!classId) return;

    fetch(`/admin/fees/get-students-for-fees?class_id=${classId}`)
        .then(response => response.json())
        .then(data => {
            const studentList = document.getElementById('studentList');
            studentList.innerHTML = '';

            if (data.students && data.students.length > 0) {
                data.students.forEach(student => {
                    const studentDiv = document.createElement('div');
                    studentDiv.className = 'col-md-6 mb-2';
                    studentDiv.innerHTML = `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="student_ids[]" value="${student.id}" id="student_${student.id}">
                            <label class="form-check-label" for="student_${student.id}">
                                ${student.first_name} ${student.last_name} (${student.scholar_number})
                            </label>
                        </div>
                    `;
                    studentList.appendChild(studentDiv);
                });
            } else {
                studentList.innerHTML = '<div class="col-12"><p class="text-muted">No students found in this class.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            document.getElementById('studentList').innerHTML = '<div class="col-12"><p class="text-danger">Error loading students.</p></div>';
        });
}

function assignFees() {
    const form = document.getElementById('bulkAssignForm');
    const formData = new FormData(form);

    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const assignmentType = formData.get('assignment_type');
    if (assignmentType === 'selected') {
        const selectedStudents = formData.getAll('student_ids[]');
        if (selectedStudents.length === 0) {
            alert('Please select at least one student.');
            return;
        }
    }

    // Show loading
    const assignBtn = document.querySelector('#bulkAssignModal .btn-primary');
    const originalText = assignBtn.innerHTML;
    assignBtn.disabled = true;
    assignBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Assigning...';

    // Prepare data
    const data = {
        assignment_type: assignmentType,
        fee_type: formData.get('fee_type'),
        amount: parseFloat(formData.get('amount')),
        due_date: formData.get('due_date'),
        academic_year: formData.get('academic_year'),
        remarks: formData.get('remarks')
    };

    if (assignmentType === 'class') {
        data.class_id = formData.get('class_id');
    } else {
        data.student_ids = formData.getAll('student_ids[]');
    }

    fetch('/admin/fees/bulk-assign', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            bootstrap.Modal.getInstance(document.getElementById('bulkAssignModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while assigning fees.');
    })
    .finally(() => {
        assignBtn.disabled = false;
        assignBtn.innerHTML = originalText;
    });
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>