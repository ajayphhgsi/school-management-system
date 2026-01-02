<?php
$active_page = 'expenses';
$page_title = 'Record New Expense';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-money-bill-wave text-danger me-2"></i>Record New Expense</h4>
        <p class="text-muted mb-0">Add a new expense record to track school spending</p>
    </div>
    <a href="/admin/expenses" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Expenses
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

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Expense Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/expenses/store">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="expense_date" class="form-label">Expense Date *</label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date"
                                   value="<?php echo $_SESSION['flash']['old']['expense_date'] ?? date('Y-m-d'); ?>" required>
                            <div class="form-text">When was this expense incurred?</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="diesel" <?php echo ($_SESSION['flash']['old']['category'] ?? '') === 'diesel' ? 'selected' : ''; ?>>Diesel/Fuel</option>
                                <option value="staff" <?php echo ($_SESSION['flash']['old']['category'] ?? '') === 'staff' ? 'selected' : ''; ?>>Staff Salaries</option>
                                <option value="bus" <?php echo ($_SESSION['flash']['old']['category'] ?? '') === 'bus' ? 'selected' : ''; ?>>Bus Expenses</option>
                                <option value="maintenance" <?php echo ($_SESSION['flash']['old']['category'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                <option value="misc" <?php echo ($_SESSION['flash']['old']['category'] ?? '') === 'misc' ? 'selected' : ''; ?>>Miscellaneous</option>
                                <option value="custom" <?php echo ($_SESSION['flash']['old']['category'] ?? '') === 'custom' ? 'selected' : ''; ?>>Custom</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason/Description *</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required
                                  placeholder="Describe what this expense was for..."><?php echo htmlspecialchars($_SESSION['flash']['old']['reason'] ?? ''); ?></textarea>
                        <div class="form-text">Provide a clear description of the expense</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0"
                                       value="<?php echo $_SESSION['flash']['old']['amount'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="payment_mode" class="form-label">Payment Mode *</label>
                            <select class="form-select" id="payment_mode" name="payment_mode" required>
                                <option value="">Select Payment Mode</option>
                                <option value="cash" <?php echo ($_SESSION['flash']['old']['payment_mode'] ?? '') === 'cash' ? 'selected' : ''; ?>>Cash</option>
                                <option value="online" <?php echo ($_SESSION['flash']['old']['payment_mode'] ?? '') === 'online' ? 'selected' : ''; ?>>Online Transfer</option>
                                <option value="cheque" <?php echo ($_SESSION['flash']['old']['payment_mode'] ?? '') === 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Additional Remarks (Optional)</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2"
                                  placeholder="Any additional notes or remarks..."><?php echo htmlspecialchars($_SESSION['flash']['old']['remarks'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/admin/expenses" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="fas fa-save me-2"></i>Record Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">This Month's Expenses</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-primary mb-1">$<?php echo number_format($monthly_stats['total'] ?? 0, 2); ?></div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success mb-1"><?php echo $monthly_stats['count'] ?? 0; ?></div>
                        <small class="text-muted">Records</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Guide -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Category Guide</h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="mb-2"><strong>Diesel/Fuel:</strong> Vehicle fuel, generator fuel</div>
                    <div class="mb-2"><strong>Staff Salaries:</strong> Teacher and staff payments</div>
                    <div class="mb-2"><strong>Bus Expenses:</strong> Transportation costs</div>
                    <div class="mb-2"><strong>Maintenance:</strong> Repairs, upkeep, supplies</div>
                    <div class="mb-2"><strong>Miscellaneous:</strong> Other expenses</div>
                    <div class="mb-2"><strong>Custom:</strong> Special categories as needed</div>
                </div>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Recent Expenses</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_expenses)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($recent_expenses, 0, 3) as $expense): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small"><?php echo htmlspecialchars(substr($expense['reason'], 0, 25)); ?><?php echo strlen($expense['reason']) > 25 ? '...' : ''; ?></div>
                                        <small class="text-muted"><?php echo date('M d', strtotime($expense['expense_date'])); ?> • <?php echo ucfirst($expense['category']); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-danger">$<?php echo number_format($expense['amount'], 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <small>No recent expenses</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-generate receipt number or reference
document.addEventListener('DOMContentLoaded', function() {
    // You could add any client-side validation or enhancements here
    console.log('Expense form loaded');
});
</script>

<?php
unset($_SESSION['flash']['old'], $_SESSION['flash']['errors']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>