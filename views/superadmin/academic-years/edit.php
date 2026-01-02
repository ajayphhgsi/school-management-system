<?php
$active_page = 'academic_years';
$page_title = 'Edit Academic Year';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex align-items-center">
                    <i class="fas fa-calendar-edit text-primary me-2"></i>
                    <h5 class="mb-0">Edit Academic Year</h5>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['flash'])): ?>
                    <?php if ($_SESSION['flash']['type'] === 'error'): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['flash']['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger">
                        <h6>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <form method="POST" action="/superadmin/academic-years/update/<?php echo $academic_year['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="mb-3">
                        <label for="year_name" class="form-label">Academic Year Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="year_name" name="year_name"
                               value="<?php echo $_SESSION['old']['year_name'] ?? $academic_year['year_name']; ?>" required>
                        <div class="form-text">Unique name for the academic year</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                   value="<?php echo $_SESSION['old']['start_date'] ?? $academic_year['start_date']; ?>" required>
                            <div class="form-text">When the academic year begins</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="<?php echo $_SESSION['old']['end_date'] ?? $academic_year['end_date']; ?>" required>
                            <div class="form-text">When the academic year ends</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   <?php echo ($_SESSION['old']['is_active'] ?? $academic_year['is_active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Active Academic Year
                            </label>
                        </div>
                        <div class="form-text">
                            Only one academic year can be active at a time. Activating this year will deactivate all others.
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/superadmin/academic-years" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Years
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Year
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Validate that end date is after start date
document.getElementById('start_date').addEventListener('change', validateDates);
document.getElementById('end_date').addEventListener('change', validateDates);

function validateDates() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const endDateInput = document.getElementById('end_date');

    if (startDate && endDate) {
        if (new Date(endDate) <= new Date(startDate)) {
            endDateInput.setCustomValidity('End date must be after start date');
        } else {
            endDateInput.setCustomValidity('');
        }
    }
}
</script>

<?php
unset($_SESSION['old']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>