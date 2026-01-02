<?php
$active_page = 'fees';
$page_title = 'Payment Success';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Payment Successful</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Payment Completed Successfully!</h4>
                    <p class="text-muted">Your payment has been processed successfully.</p>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-6">
                        <strong>Payment ID:</strong><br>
                        <?php echo htmlspecialchars($payment_id ?? 'N/A'); ?>
                    </div>
                    <div class="col-sm-6">
                        <strong>Amount:</strong><br>
                        $<?php echo number_format($amount ?? 0, 2); ?>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-6">
                        <strong>Gateway:</strong><br>
                        <?php echo htmlspecialchars(ucfirst($gateway ?? 'N/A')); ?>
                    </div>
                    <div class="col-sm-6">
                        <strong>Date:</strong><br>
                        <?php echo date('M d, Y H:i', strtotime($payment_date ?? 'now')); ?>
                    </div>
                </div>

                <div class="d-grid">
                    <a href="/student/fees" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Fees
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>