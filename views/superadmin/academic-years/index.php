<?php
$active_page = 'academic_years';
$page_title = 'Academic Years';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Academic Year Management</h4>
        <p class="text-muted mb-0">Configure and manage academic years for the school system</p>
    </div>
    <a href="/superadmin/academic-years/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Academic Year
    </a>
</div>

<?php if (isset($_SESSION['flash'])): ?>
    <?php if ($_SESSION['flash']['type'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['flash']['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_SESSION['flash']['type'] === 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['flash']['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (!empty($academic_years)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Academic Year</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($academic_years as $year): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="fas fa-calendar text-white"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($year['year_name']); ?></h6>
                                            <small class="text-muted">Academic Year</small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($year['start_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($year['end_date'])); ?></td>
                                <td>
                                    <?php if ($year['is_active']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Active
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-pause me-1"></i>Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($year['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/superadmin/academic-years/edit/<?php echo $year['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Year">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (!$year['is_active']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Year"
                                                    onclick="confirmDelete(<?php echo $year['id']; ?>, '<?php echo htmlspecialchars($year['year_name']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Academic Years Found</h5>
                <p class="text-muted">Start by adding your first academic year to the system.</p>
                <a href="/superadmin/academic-years/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add First Year
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the academic year "<strong id="yearName"></strong>"?</p>
                <p class="text-danger mb-0">This action cannot be undone and will affect all related records.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="btn btn-danger">Delete Year</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(yearId, yearName) {
    document.getElementById('yearName').textContent = yearName;
    document.getElementById('deleteForm').action = '/superadmin/academic-years/delete/' + yearId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>