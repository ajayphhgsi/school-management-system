<?php
$active_page = 'certificates';
$page_title = 'Transfer Certificates';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-contract text-primary me-2"></i>Transfer Certificates</h4>
        <p class="text-muted mb-0">Manage issued transfer certificates</p>
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
                            <th>Transfer Reason</th>
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
                                <td><?php echo htmlspecialchars($cert['transfer_reason'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="/admin/certificates/view/<?php echo $cert['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/admin/certificates/print/<?php echo $cert['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Print Certificate">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-warning" title="Re-administer Student" onclick="reAdministerStudent(<?php echo $cert['student_id']; ?>, '<?php echo htmlspecialchars($cert['first_name'] . ' ' . $cert['last_name']); ?>')">
                                        <i class="fas fa-undo"></i>
                                    </button>
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
        <i class="fas fa-file-contract fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">No Transfer Certificates Found</h4>
        <p class="text-muted">Transfer certificates will appear here once issued.</p>
        <a href="/admin/certificates" class="btn btn-primary">Issue New Certificate</a>
    </div>
<?php endif; ?>

<script>
function reAdministerStudent(studentId, studentName) {
    if (confirm(`Are you sure you want to re-administer ${studentName}? This will make the student active again and remove the TC issued status.`)) {
        fetch(`/admin/certificates/re-administer/${studentId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                _token: '<?php echo $csrf_token ?? ''; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Student re-administered successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to re-administer student'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while re-administering the student');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>