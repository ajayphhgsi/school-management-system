<?php
$active_page = 'certificates';
$page_title = 'Character Certificates';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-user-check text-info me-2"></i>Character Certificates</h4>
        <p class="text-muted mb-0">Manage issued character certificates</p>
    </div>
    <a href="/admin/certificates" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Certificates
    </a>
</div>

<?php if (!empty($certificates)): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Certificate No.</th>
                            <th>Student Name</th>
                            <th>Scholar No.</th>
                            <th>Issue Date</th>
                            <th>Purpose</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $cert): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cert['certificate_number']); ?></td>
                                <td><?php echo htmlspecialchars($cert['first_name'] . ' ' . $cert['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($cert['scholar_number']); ?></td>
                                <td><?php echo date('d M Y', strtotime($cert['issue_date'])); ?></td>
                                <td><?php echo htmlspecialchars($cert['purpose'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="/admin/certificates/view/<?php echo $cert['id']; ?>" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/temp/<?php echo basename($cert['pdf_path']); ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Download PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-user-check fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">No Character Certificates Found</h4>
        <p class="text-muted">Character certificates will appear here once issued.</p>
        <a href="/admin/certificates" class="btn btn-primary">Issue New Certificate</a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>