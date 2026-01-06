<?php
$active_page = 'classes';
$page_title = 'Edit Class';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><?php echo $page_title; ?></h4>
        <p class="text-muted mb-0">Update class information</p>
    </div>
    <div>
        <a href="/admin/classes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Classes
        </a>
    </div>
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

<?php if (isset($_SESSION['flash']['errors'])): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($_SESSION['flash']['errors'] as $error): ?>
                <li><?php echo $error[0]; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/classes/update/<?php echo $class['id']; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="class_name" class="form-label">Class Name *</label>
                    <input type="text" class="form-control" id="class_name" name="class_name"
                           value="<?php echo $_SESSION['flash']['old']['class_name'] ?? $class['class_name']; ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="section" class="form-label">Section *</label>
                    <input type="text" class="form-control" id="section" name="section"
                           value="<?php echo $_SESSION['flash']['old']['section'] ?? $class['section']; ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo ($_SESSION['flash']['old']['is_active'] ?? $class['is_active']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>

            <!-- Subject Assignment -->
            <div class="mb-4">
                <h5 class="mb-3">Assign Subjects</h5>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Available Subjects</label>
                        <select multiple class="form-select" id="available_subjects" size="8">
                            <?php foreach ($subjects as $subject): ?>
                                <?php if (!$subject['assigned']): ?>
                                    <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['subject_name'] . ' (' . $subject['subject_code'] . ')'); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assigned Subjects</label>
                        <select multiple class="form-select" id="assigned_subjects" name="assigned_subjects[]" size="8">
                            <?php foreach ($subjects as $subject): ?>
                                <?php if ($subject['assigned']): ?>
                                    <option value="<?php echo $subject['id']; ?>" selected><?php echo htmlspecialchars($subject['subject_name'] . ' (' . $subject['subject_code'] . ')'); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-success btn-sm me-2" onclick="assignSubjects()">
                        <i class="fas fa-arrow-right me-1"></i>Assign
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="unassignSubjects()">
                        <i class="fas fa-arrow-left me-1"></i>Unassign
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="/admin/classes" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Class</button>
            </div>
        </form>
    </div>
</div>

<script>
function assignSubjects() {
    const available = document.getElementById('available_subjects');
    const assigned = document.getElementById('assigned_subjects');

    // Move selected options from available to assigned
    Array.from(available.selectedOptions).forEach(option => {
        assigned.appendChild(option);
        option.selected = true; // Keep selected for form submission
    });
}

function unassignSubjects() {
    const available = document.getElementById('available_subjects');
    const assigned = document.getElementById('assigned_subjects');

    // Move selected options from assigned to available
    Array.from(assigned.selectedOptions).forEach(option => {
        available.appendChild(option);
        option.selected = false; // Remove from selection
    });
    adjustSelectSizes();
}

// Double-click to assign/unassign
document.getElementById('available_subjects').addEventListener('dblclick', assignSubjects);
document.getElementById('assigned_subjects').addEventListener('dblclick', unassignSubjects);

// Auto-select all assigned subjects before form submission
document.querySelector('form').addEventListener('submit', function() {
    const assigned = document.getElementById('assigned_subjects');
    Array.from(assigned.options).forEach(option => {
        option.selected = true;
    });
});

function adjustSelectSizes() {
    const available = document.getElementById('available_subjects');
    const assigned = document.getElementById('assigned_subjects');
    const maxSize = Math.max(available.options.length, assigned.options.length);
    const size = Math.max(5, Math.min(maxSize, 10));
    available.size = size;
    assigned.size = size;
}

window.addEventListener('load', adjustSelectSizes);
</script>

<?php
unset($_SESSION['flash']['old'], $_SESSION['flash']['errors']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>