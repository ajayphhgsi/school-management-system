<?php
$active_page = 'certificates';
$page_title = 'View Transfer Certificate';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-contract text-primary me-2"></i>Transfer Certificate</h4>
        <p class="text-muted mb-0">Certificate No: <?php echo htmlspecialchars($certificate['certificate_number']); ?></p>
    </div>
    <div>
        <a href="/admin/certificates/tc" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Certificate Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Certificate Number:</td>
                                <td><?php echo htmlspecialchars($certificate['certificate_number']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Student Name:</td>
                                <td><?php echo htmlspecialchars($certificate['first_name'] . ' ' . $certificate['last_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Scholar Number:</td>
                                <td><?php echo htmlspecialchars($certificate['scholar_number']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Class:</td>
                                <td><?php echo htmlspecialchars($certificate['class_name'] . ' ' . $certificate['section']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Date of Admission:</td>
                                <td><?php echo $certificate['admission_date'] ? date('d M Y', strtotime($certificate['admission_date'])) : 'N/A'; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Issue Date:</td>
                                <td><?php echo date('d M Y', strtotime($certificate['issue_date'])); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Transfer Reason:</td>
                                <td><?php echo htmlspecialchars($certificate['transfer_reason'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Conduct:</td>
                                <td><?php echo htmlspecialchars($certificate['conduct'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Remarks:</td>
                                <td><?php echo htmlspecialchars($certificate['remarks'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Generated On:</td>
                                <td><?php echo date('d M Y H:i', strtotime($certificate['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>