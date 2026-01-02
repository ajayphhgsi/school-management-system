<?php
$active_page = 'exams';
$page_title = 'Create New Exam';
ob_start();
?>

<style>
.exam-section {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.subject-row {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
}

.subject-row.removing {
    opacity: 0.5;
    background: #f5c6cb;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-plus-circle text-primary me-2"></i>Create New Exam</h4>
        <p class="text-muted mb-0">Set up exam with subjects, schedule, and result entry</p>
    </div>
    <a href="/admin/exams" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Exams
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

<form id="examForm" method="POST" action="/admin/exams/store">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <!-- Basic Exam Information -->
    <div class="exam-section">
        <h5 class="mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Exam Information</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="exam_name" class="form-label">Exam Name *</label>
                <input type="text" class="form-control" id="exam_name" name="exam_name"
                       value="<?php echo htmlspecialchars($_SESSION['flash']['old']['exam_name'] ?? ''); ?>" required>
                <div class="form-text">e.g., "Mid-Term Examination 2024", "Final Exam"</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="exam_type" class="form-label">Exam Type *</label>
                <select class="form-select" id="exam_type" name="exam_type" required>
                    <option value="">Select Exam Type</option>
                    <option value="mid-term" <?php echo ($_SESSION['flash']['old']['exam_type'] ?? '') === 'mid-term' ? 'selected' : ''; ?>>Mid-Term</option>
                    <option value="final" <?php echo ($_SESSION['flash']['old']['exam_type'] ?? '') === 'final' ? 'selected' : ''; ?>>Final</option>
                    <option value="custom" <?php echo ($_SESSION['flash']['old']['exam_type'] ?? '') === 'custom' ? 'selected' : ''; ?>>Custom</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="class_id" class="form-label">Class *</label>
                <select class="form-select" id="class_id" name="class_id" required>
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"
                                <?php echo ($_SESSION['flash']['old']['class_id'] ?? '') == $class['id'] ? 'selected' : ''; ?>>
                            <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="academic_year" class="form-label">Academic Year</label>
                <input type="text" class="form-control" id="academic_year" name="academic_year"
                       value="<?php echo htmlspecialchars($_SESSION['flash']['old']['academic_year'] ?? date('Y') . '-' . (date('Y') + 1)); ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="start_date" class="form-label">Start Date *</label>
                <input type="date" class="form-control" id="start_date" name="start_date"
                       value="<?php echo htmlspecialchars($_SESSION['flash']['old']['start_date'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="end_date" class="form-label">End Date *</label>
                <input type="date" class="form-control" id="end_date" name="end_date"
                       value="<?php echo htmlspecialchars($_SESSION['flash']['old']['end_date'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                       <?php echo ($_SESSION['flash']['old']['is_active'] ?? 1) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_active">
                    Exam is active and visible to students
                </label>
            </div>
        </div>
    </div>

    <!-- Subject Scheduling -->
    <div class="exam-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-calendar-alt text-success me-2"></i>Subject Scheduling</h5>
            <button type="button" class="btn btn-success btn-sm" onclick="addSubject()">
                <i class="fas fa-plus me-1"></i>Add Subject
            </button>
        </div>

        <div id="subjectsContainer">
            <!-- Subject rows will be added here -->
        </div>

        <div class="text-muted small mt-3">
            <i class="fas fa-info-circle me-1"></i>
            Add subjects for this exam with their respective dates, times, and maximum marks.
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between align-items-center">
        <a href="/admin/exams" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i>Cancel
        </a>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" onclick="previewExam()">
                <i class="fas fa-eye me-1"></i>Preview
            </button>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save me-2"></i>Create Exam
            </button>
        </div>
    </div>
</form>

<!-- Subject Template (Hidden) -->
<template id="subjectTemplate">
    <div class="subject-row" data-subject-id="">
        <div class="row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="form-label">Subject *</label>
                <select class="form-select subject-select" name="subjects[][subject_id]" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>"><?php echo $subject['subject_name']; ?> (<?php echo $subject['subject_code']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Exam Date *</label>
                <input type="date" class="form-control exam-date" name="subjects[][exam_date]" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Start Time *</label>
                <input type="time" class="form-control start-time" name="subjects[][start_time]" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">End Time *</label>
                <input type="time" class="form-control end-time" name="subjects[][end_time]" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Max Marks *</label>
                <input type="number" class="form-control max-marks" name="subjects[][max_marks]" min="1" max="100" value="100" required>
            </div>
            <div class="col-md-1 mb-2">
                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeSubject(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
let subjectCounter = 0;

// Add new subject row
function addSubject() {
    const template = document.getElementById('subjectTemplate');
    const container = document.getElementById('subjectsContainer');
    const clone = template.content.cloneNode(true);

    // Set unique IDs and update counter
    subjectCounter++;
    const subjectRow = clone.querySelector('.subject-row');
    subjectRow.dataset.subjectId = subjectCounter;

    container.appendChild(clone);

    // Set default exam date to exam start date
    const examDate = document.getElementById('start_date').value;
    if (examDate) {
        subjectRow.querySelector('.exam-date').value = examDate;
    }
}

// Remove subject row
function removeSubject(button) {
    const subjectRow = button.closest('.subject-row');
    subjectRow.classList.add('removing');

    setTimeout(() => {
        subjectRow.remove();
    }, 300);
}

// Preview exam
function previewExam() {
    const examName = document.getElementById('exam_name').value;
    const examType = document.getElementById('exam_type').value;
    const classSelect = document.getElementById('class_id');
    const className = classSelect.options[classSelect.selectedIndex]?.text || '';
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;

    let previewText = `Exam: ${examName || 'Not set'}\n`;
    previewText += `Type: ${examType || 'Not set'}\n`;
    previewText += `Class: ${className || 'Not set'}\n`;
    previewText += `Duration: ${startDate || 'Not set'} to ${endDate || 'Not set'}\n\n`;

    const subjectRows = document.querySelectorAll('.subject-row');
    if (subjectRows.length > 0) {
        previewText += 'Subjects:\n';
        subjectRows.forEach((row, index) => {
            const subjectSelect = row.querySelector('.subject-select');
            const subjectText = subjectSelect.options[subjectSelect.selectedIndex]?.text || 'Not selected';
            const examDate = row.querySelector('.exam-date').value;
            const startTime = row.querySelector('.start-time').value;
            const endTime = row.querySelector('.end-time').value;
            const maxMarks = row.querySelector('.max-marks').value;

            previewText += `${index + 1}. ${subjectText} - ${examDate} (${startTime} - ${endTime}) - ${maxMarks} marks\n`;
        });
    } else {
        previewText += 'No subjects added yet.\n';
    }

    alert(previewText);
}

// Form validation
document.getElementById('examForm').addEventListener('submit', function(e) {
    const subjectRows = document.querySelectorAll('.subject-row');
    if (subjectRows.length === 0) {
        e.preventDefault();
        alert('Please add at least one subject to the exam.');
        return;
    }

    // Validate date range
    const startDate = new Date(document.getElementById('start_date').value);
    const endDate = new Date(document.getElementById('end_date').value);

    if (endDate < startDate) {
        e.preventDefault();
        alert('End date cannot be before start date.');
        return;
    }

    // Validate subject dates are within exam date range
    let hasInvalidDate = false;
    subjectRows.forEach(row => {
        const examDate = new Date(row.querySelector('.exam-date').value);
        if (examDate < startDate || examDate > endDate) {
            hasInvalidDate = true;
        }
    });

    if (hasInvalidDate) {
        e.preventDefault();
        alert('All subject exam dates must be within the exam start and end dates.');
        return;
    }
});

// Auto-update subject dates when exam dates change
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = this.value;
    document.querySelectorAll('.exam-date').forEach(input => {
        if (!input.value) {
            input.value = startDate;
        }
    });
});

// Load subjects when class changes
document.getElementById('class_id').addEventListener('change', function() {
    const classId = this.value;
    if (classId) {
        loadClassSubjects(classId);
    }
});

function loadClassSubjects(classId) {
    // This would load subjects specific to the class
    // For now, we'll keep all subjects available
    console.log('Loading subjects for class:', classId);
}
</script>

<?php
unset($_SESSION['flash']['old'], $_SESSION['flash']['errors']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>