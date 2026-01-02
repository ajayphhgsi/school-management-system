<?php
$active_page = 'fees';
$page_title = 'My Fee Details';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-money-bill-wave text-info me-2"></i>My Fee Details</h4>
        <p class="text-muted mb-0">View your fee payments and outstanding amounts</p>
    </div>
</div>

<!-- Fee Summary -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                </div>
                <h4>$<?php echo number_format($total_fees, 2); ?></h4>
                <p class="mb-0 opacity-75 small">Total Fees</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
                <h4>$<?php echo number_format($total_paid, 2); ?></h4>
                <p class="mb-0 opacity-75 small">Paid Amount</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card <?php echo $pending_amount > 0 ? 'bg-warning' : 'bg-success'; ?> text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                </div>
                <h4>$<?php echo number_format($pending_amount, 2); ?></h4>
                <p class="mb-0 opacity-75 small">Pending Amount</p>
            </div>
        </div>
    </div>
</div>

<!-- Fee Records -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Fee Payment History</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($fees)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Payment Date</th>
                            <th>Payment Mode</th>
                            <th>Status</th>
                            <th>Transaction ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fees as $fee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fee['description'] ?? 'Fee Payment'); ?></td>
                                <td>$<?php echo number_format($fee['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($fee['due_date'])); ?></td>
                                <td>
                                    <?php if ($fee['payment_date']): ?>
                                        <?php echo date('M d, Y', strtotime($fee['payment_date'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($fee['payment_mode']): ?>
                                        <span class="badge bg-secondary"><?php echo ucfirst($fee['payment_mode']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($fee['amount_paid'] > 0): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $fee['transaction_id'] ?? '-'; ?></td>
                                <td>
                                    <?php if ($fee['amount_paid'] == 0): ?>
                                        <a href="/student/payment/initiate?fee_id=<?php echo $fee['id']; ?>" class="btn btn-primary btn-sm">Pay Online</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-money-bill-wave fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Fee Records</h4>
                <p class="text-muted">Your fee payment records will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>