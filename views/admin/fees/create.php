<?php
$active_page = 'fees';
$page_title = 'Record Fee Payment';
ob_start();
?>

<style>
.fee-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.fee-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.student-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.student-card:hover {
    background: #e9ecef;
    border-color: #0d6efd;
}

.student-card.selected {
    background: #e7f3ff;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.payment-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 20px;
}

.receipt-preview {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    font-size: 0.875rem;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-money-bill-wave text-success me-2"></i>Record Fee Payment</h4>
        <p class="text-muted mb-0">Collect fees with advanced filtering and receipt generation</p>
    </div>
    <a href="/admin/fees" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Fees
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

<form id="feeForm" method="POST" action="/admin/fees/store" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <div class="row">
        <!-- Student Selection -->
        <div class="col-lg-8 mb-4">
            <div class="card fee-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users text-primary me-2"></i>Student Selection</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="classFilter" class="form-label">Class</label>
                            <select class="form-select" id="classFilter" name="class_filter">
                                <option value="">All Classes</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name'] . ' ' . $class['section']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="villageFilter" class="form-label">Village (Optional)</label>
                            <input type="text" class="form-control" id="villageFilter" name="village_filter" placeholder="Filter by village">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-primary" onclick="loadStudents()">
                                    <i class="fas fa-search me-1"></i>Search Students
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Student List -->
                    <div id="studentList" class="mb-3" style="max-height: 300px; overflow-y: auto;">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <p>Select class and click "Search Students" to load student list</p>
                        </div>
                    </div>

                    <!-- Selected Student Info -->
                    <div id="selectedStudentInfo" class="d-none">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-user-check me-2"></i>Selected Student</h6>
                            <div id="studentDetails"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee Details & Payment -->
        <div class="col-lg-4 mb-4">
            <div class="card fee-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calculator text-success me-2"></i>Fee Details</h5>
                </div>
                <div class="card-body">
                    <!-- Fee Type -->
                    <div class="mb-3">
                        <label for="feeType" class="form-label">Fee Type *</label>
                        <select class="form-select" id="feeType" name="fee_type" required>
                            <option value="">Select Fee Type</option>
                            <option value="tuition">Tuition Fee</option>
                            <option value="exam">Exam Fee</option>
                            <option value="transport">Transport Fee</option>
                            <option value="library">Library Fee</option>
                            <option value="sports">Sports Fee</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Total Fee -->
                    <div class="mb-3">
                        <label for="totalFee" class="form-label">Total Fee Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="totalFee" name="total_fee" step="0.01" min="0" required>
                        </div>
                    </div>

                    <!-- Discount/Scholarship -->
                    <div class="mb-3">
                        <label for="discount" class="form-label">Discount/Scholarship (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="discount" name="discount" step="0.01" min="0" value="0">
                        </div>
                    </div>

                    <!-- Net Amount (Calculated) -->
                    <div class="mb-3">
                        <label class="form-label">Net Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="netAmount" name="net_amount" readonly>
                        </div>
                    </div>

                    <!-- Receipt Number -->
                    <div class="mb-3">
                        <label for="receiptNumber" class="form-label">Receipt Number *</label>
                        <input type="text" class="form-control" id="receiptNumber" name="receipt_number" required>
                        <div class="form-text">Auto-generated or enter custom</div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="card fee-card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-credit-card text-info me-2"></i>Payment Information</h5>
                </div>
                <div class="card-body">
                    <!-- Payment Mode -->
                    <div class="mb-3">
                        <label for="paymentMode" class="form-label">Payment Mode *</label>
                        <select class="form-select" id="paymentMode" name="payment_mode" required>
                            <option value="">Select Payment Mode</option>
                            <option value="cash">Cash</option>
                            <option value="online">Online Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>

                    <!-- Payment Date -->
                    <div class="mb-3">
                        <label for="paymentDate" class="form-label">Payment Date *</label>
                        <input type="date" class="form-control" id="paymentDate" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <!-- Transaction/Cheque Details -->
                    <div id="transactionDetails" class="d-none">
                        <div class="mb-3">
                            <label for="transactionId" class="form-label">Transaction/Cheque Number</label>
                            <input type="text" class="form-control" id="transactionId" name="transaction_id">
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks (Optional)</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Summary & Receipt Preview -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="payment-summary">
                <h5 class="mb-3"><i class="fas fa-receipt me-2"></i>Payment Summary</h5>
                <div class="row">
                    <div class="col-6">
                        <div class="mb-2">
                            <small>Total Fee:</small>
                            <div class="h6 mb-0" id="summaryTotalFee">$0.00</div>
                        </div>
                        <div class="mb-2">
                            <small>Discount:</small>
                            <div class="h6 mb-0" id="summaryDiscount">$0.00</div>
                        </div>
                        <div class="mb-2">
                            <small>Net Amount:</small>
                            <div class="h6 mb-0 fw-bold" id="summaryNetAmount">$0.00</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-2">
                            <small>Student:</small>
                            <div class="h6 mb-0" id="summaryStudent">-</div>
                        </div>
                        <div class="mb-2">
                            <small>Fee Type:</small>
                            <div class="h6 mb-0" id="summaryFeeType">-</div>
                        </div>
                        <div class="mb-2">
                            <small>Payment Mode:</small>
                            <div class="h6 mb-0" id="summaryPaymentMode">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-print me-2"></i>Receipt Preview</h5>
                </div>
                <div class="card-body">
                    <div class="receipt-preview">
                        <div class="text-center mb-3">
                            <h6>School Management System</h6>
                            <small>Fee Receipt</small>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Receipt #:</strong> <span id="receiptPreviewNumber">-</span></div>
                            <div class="col-6"><strong>Date:</strong> <span id="receiptPreviewDate">-</span></div>
                        </div>
                        <div class="mb-2">
                            <strong>Student:</strong> <span id="receiptPreviewStudent">-</span>
                        </div>
                        <div class="mb-2">
                            <strong>Fee Type:</strong> <span id="receiptPreviewFeeType">-</span>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Amount:</strong> $<span id="receiptPreviewAmount">0.00</span></div>
                            <div class="col-6"><strong>Payment:</strong> <span id="receiptPreviewPayment">-</span></div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <small>Triple Copy: School/Student/Accounts</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between align-items-center">
        <a href="/admin/fees" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i>Cancel
        </a>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" onclick="previewReceipt()">
                <i class="fas fa-eye me-1"></i>Preview Receipt
            </button>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save me-2"></i>Record Payment & Generate Receipt
            </button>
        </div>
    </div>
</form>

<script>
// Global variables
let selectedStudent = null;

// Load students based on filters
function loadStudents() {
    const classId = document.getElementById('classFilter').value;
    const village = document.getElementById('villageFilter').value;

    if (!classId) {
        alert('Please select a class first');
        return;
    }

    // Show loading
    document.getElementById('studentList').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
            <p>Loading students...</p>
        </div>
    `;

    // AJAX request to load students
    fetch(`/admin/fees/students?class_id=${classId}&village=${encodeURIComponent(village)}`)
        .then(response => response.json())
        .then(data => {
            displayStudents(data.students);
        })
        .catch(error => {
            console.error('Error loading students:', error);
            document.getElementById('studentList').innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>Error loading students. Please try again.</p>
                </div>
            `;
        });
}

function displayStudents(students) {
    const studentList = document.getElementById('studentList');

    if (students.length === 0) {
        studentList.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-users fa-2x mb-2"></i>
                <p>No students found matching the criteria.</p>
            </div>
        `;
        return;
    }

    studentList.innerHTML = students.map(student => `
        <div class="student-card" onclick="selectStudent(${student.id}, '${student.first_name} ${student.last_name}', '${student.scholar_number}', '${student.class_name} ${student.section}')">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    ${student.photo ? `<img src="/uploads/${student.photo}" class="rounded-circle" width="40" height="40" alt="Photo">` : '<div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-user text-white"></i></div>'}
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${student.first_name} ${student.last_name}</h6>
                    <small class="text-muted">Scholar No: ${student.scholar_number} | Class: ${student.class_name} ${student.section}</small>
                    ${student.village ? `<br><small class="text-muted">Village: ${student.village}</small>` : ''}
                </div>
                <div class="flex-shrink-0">
                    <i class="fas fa-chevron-right text-muted"></i>
                </div>
            </div>
        </div>
    `).join('');
}

function selectStudent(id, name, scholarNumber, classInfo) {
    selectedStudent = { id, name, scholarNumber, classInfo };

    // Update UI
    document.querySelectorAll('.student-card').forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    // Show selected student info
    document.getElementById('selectedStudentInfo').classList.remove('d-none');
    document.getElementById('studentDetails').innerHTML = `
        <strong>${name}</strong><br>
        <small>Scholar Number: ${scholarNumber} | Class: ${classInfo}</small>
    `;

    // Update summary
    document.getElementById('summaryStudent').textContent = name;

    // Add hidden input for student ID
    let studentInput = document.getElementById('selectedStudentId');
    if (!studentInput) {
        studentInput = document.createElement('input');
        studentInput.type = 'hidden';
        studentInput.id = 'selectedStudentId';
        studentInput.name = 'student_id';
        document.getElementById('feeForm').appendChild(studentInput);
    }
    studentInput.value = id;

    updateReceiptPreview();
}

// Calculate net amount
function calculateNetAmount() {
    const totalFee = parseFloat(document.getElementById('totalFee').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const netAmount = Math.max(0, totalFee - discount);

    document.getElementById('netAmount').value = netAmount.toFixed(2);
    document.getElementById('summaryTotalFee').textContent = '$' + totalFee.toFixed(2);
    document.getElementById('summaryDiscount').textContent = '$' + discount.toFixed(2);
    document.getElementById('summaryNetAmount').textContent = '$' + netAmount.toFixed(2);

    updateReceiptPreview();
}

// Update payment mode details
function updatePaymentMode() {
    const paymentMode = document.getElementById('paymentMode').value;
    const transactionDetails = document.getElementById('transactionDetails');

    if (['online', 'cheque', 'upi'].includes(paymentMode)) {
        transactionDetails.classList.remove('d-none');
        document.getElementById('transactionId').required = true;
        document.getElementById('transactionId').placeholder =
            paymentMode === 'cheque' ? 'Enter cheque number' : 'Enter transaction ID';
    } else {
        transactionDetails.classList.add('d-none');
        document.getElementById('transactionId').required = false;
    }

    document.getElementById('summaryPaymentMode').textContent = paymentMode.charAt(0).toUpperCase() + paymentMode.slice(1);
    updateReceiptPreview();
}

// Update receipt preview
function updateReceiptPreview() {
    const receiptNumber = document.getElementById('receiptNumber').value;
    const paymentDate = document.getElementById('paymentDate').value;
    const netAmount = document.getElementById('netAmount').value;
    const feeType = document.getElementById('feeType').options[document.getElementById('feeType').selectedIndex].text;
    const paymentMode = document.getElementById('paymentMode').options[document.getElementById('paymentMode').selectedIndex].text;

    document.getElementById('receiptPreviewNumber').textContent = receiptNumber || 'AUTO-GENERATED';
    document.getElementById('receiptPreviewDate').textContent = paymentDate ? new Date(paymentDate).toLocaleDateString() : '-';
    document.getElementById('receiptPreviewStudent').textContent = selectedStudent ? selectedStudent.name : '-';
    document.getElementById('receiptPreviewFeeType').textContent = feeType || '-';
    document.getElementById('receiptPreviewAmount').textContent = netAmount || '0.00';
    document.getElementById('receiptPreviewPayment').textContent = paymentMode || '-';

    document.getElementById('summaryFeeType').textContent = feeType || '-';
}

// Preview receipt
function previewReceipt() {
    if (!selectedStudent) {
        alert('Please select a student first');
        return;
    }

    // This would open a modal with full receipt preview
    alert('Receipt preview functionality - To be implemented with modal');
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('totalFee').addEventListener('input', calculateNetAmount);
    document.getElementById('discount').addEventListener('input', calculateNetAmount);
    document.getElementById('feeType').addEventListener('change', function() {
        document.getElementById('summaryFeeType').textContent = this.options[this.selectedIndex].text;
        updateReceiptPreview();
    });
    document.getElementById('paymentMode').addEventListener('change', updatePaymentMode);
    document.getElementById('receiptNumber').addEventListener('input', updateReceiptPreview);
    document.getElementById('paymentDate').addEventListener('change', updateReceiptPreview);

    // Auto-generate receipt number
    const timestamp = Date.now();
    document.getElementById('receiptNumber').value = 'RCP-' + timestamp.toString().slice(-8);
    updateReceiptPreview();
});

// Form validation
document.getElementById('feeForm').addEventListener('submit', function(e) {
    if (!selectedStudent) {
        e.preventDefault();
        alert('Please select a student');
        return;
    }

    const netAmount = parseFloat(document.getElementById('netAmount').value);
    if (netAmount <= 0) {
        e.preventDefault();
        alert('Net amount must be greater than 0');
        return;
    }
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>