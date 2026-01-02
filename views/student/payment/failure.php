<?php
$active_page = 'fees';
$page_title = 'Payment Failed';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Payment Failed</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Payment Failed</h4>
                    <p class="text-muted">Unfortunately, your payment could not be processed.</p>
                </div>

                <?php if (!empty($reason)): ?>
                <div class="alert alert-warning mb-4">
                    <strong>Reason:</strong> <?php echo htmlspecialchars($reason); ?>
                </div>
                <?php endif; ?>

                <div class="d-grid gap-2">
                    <a href="/student/payment/initiate" class="btn btn-primary">
                        <i class="fas fa-redo me-2"></i>Retry Payment
                    </a>
                    <a href="mailto:support@school.com?subject=Payment Failure Support" class="btn btn-outline-secondary">
                        <i class="fas fa-envelope me-2"></i>Contact Support
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