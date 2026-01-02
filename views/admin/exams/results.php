<?php
$active_page = 'exams';
$page_title = 'Enter Exam Results';
ob_start();
?>

<style>
.results-table {
    font-size: 0.875rem;
}

.results-table th {
    background: #f8f9fa;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
}

.results-table td {
    text-align: center;
    vertical-align: middle;
}

.student-name {
    font-weight: 500;
    text-align: left !important;
}

.marks-input {
    width: 80px;
    text-align: center;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 4px 8px;
}

.marks-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.grade-display {
    font-weight: bold;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
}

.grade-A { background-color: #d4edda; color: #155724; }
.grade-B { background-color: #fff3cd; color: #856404; }
.grade-C { background-color: #ffeaa7; color: #d68910; }
.grade-D { background-color: #d1ecf1; color: #0c5460; }
.grade-F { background-color: #f8d7da; color: #721c24; }

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-chart-line text-success me-2"></i>Enter Exam Results</h4>
        <p class="text-muted mb-0">Record marks for <?php echo $exam['exam_name']; ?> - <?php echo $exam['class_name']; ?> <?php echo $exam['section']; ?></p>
    </div>
    <div>
        <a href="/admin/exams" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i>Back to Exams
        </a>
        <button class="btn btn-success" onclick="saveAllResults()">
            <i class="fas fa-save me-2"></i>Save All Results
        </button>
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

<!-- Exam Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <h5><?php echo count($students); ?></h5>
                <p class="mb-0 opacity-75">Total Students</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <h5><?php echo count($exam_subjects); ?></h5>
                <p class="mb-0 opacity-75">Subjects</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <h5 id="resultsEntered">0</h5>
                <p class="mb-0 opacity-75">Results Entered</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <h5 id="completionPercent">0%</h5>
                <p class="mb-0 opacity-75">Completion</p>
            </div>
        </div>
    </div>
</div>

<!-- Results Entry Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Mark Entry Table</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($students) && !empty($exam_subjects)): ?>
            <div class="table-responsive">
                <table class="table table-bordered results-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="min-width: 200px;">Student Name</th>
                            <th rowspan="2">Roll No</th>
                            <?php foreach ($exam_subjects as $subject): ?>
                                <th colspan="2"><?php echo $subject['subject_name']; ?><br><small><?php echo $subject['max_marks']; ?> marks</small></th>
                            <?php endforeach; ?>
                            <th rowspan="2">Total</th>
                            <th rowspan="2">Percentage</th>
                            <th rowspan="2">Grade</th>
                        </tr>
                        <tr>
                            <?php foreach ($exam_subjects as $subject): ?>
                                <th>Marks</th>
                                <th>Grade</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr data-student-id="<?php echo $student['id']; ?>">
                                <td class="student-name">
                                    <div class="d-flex align-items-center">
                                        <?php if ($student['photo']): ?>
                                            <img src="/uploads/<?php echo $student['photo']; ?>" class="rounded-circle me-2" width="30" height="30" alt="Photo">
                                        <?php endif; ?>
                                        <div>
                                            <div><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></div>
                                            <small class="text-muted"><?php echo $student['scholar_number']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $student['roll_number'] ?? 'N/A'; ?></td>
                                <?php foreach ($exam_subjects as $subject): ?>
                                    <td>
                                        <input type="number"
                                               class="form-control marks-input"
                                               data-student-id="<?php echo $student['id']; ?>"
                                               data-subject-id="<?php echo $subject['subject_id']; ?>"
                                               data-max-marks="<?php echo $subject['max_marks']; ?>"
                                               min="0"
                                               max="<?php echo $subject['max_marks']; ?>"
                                               step="0.5"
                                               placeholder="0">
                                    </td>
                                    <td>
                                        <span class="grade-display" data-student-id="<?php echo $student['id']; ?>" data-subject-id="<?php echo $subject['subject_id']; ?>">-</span>
                                    </td>
                                <?php endforeach; ?>
                                <td><span class="fw-bold" data-student-id="<?php echo $student['id']; ?>" data-type="total">0</span></td>
                                <td><span class="fw-bold" data-student-id="<?php echo $student['id']; ?>" data-type="percentage">0%</span></td>
                                <td><span class="grade-display fw-bold" data-student-id="<?php echo $student['id']; ?>" data-type="overall-grade">-</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-outline-primary me-2" onclick="loadExistingResults()">
                        <i class="fas fa-download me-1"></i>Load Existing Results
                    </button>
                    <button class="btn btn-outline-warning" onclick="clearAllResults()">
                        <i class="fas fa-eraser me-1"></i>Clear All
                    </button>
                </div>
                <div>
                    <button class="btn btn-success btn-lg" onclick="saveAllResults()">
                        <i class="fas fa-save me-2"></i>Save All Results
                    </button>
                </div>
            </div>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Data Available</h4>
                <p class="text-muted">No students or subjects found for this exam.</p>
                <a href="/admin/exams" class="btn btn-primary">Back to Exams</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
let examData = {
    examId: <?php echo $exam['id']; ?>,
    subjects: <?php echo json_encode($exam_subjects); ?>,
    students: <?php echo json_encode($students); ?>
};

// Load existing results on page load
document.addEventListener('DOMContentLoaded', function() {
    loadExistingResults();
});

// Load existing exam results
function loadExistingResults() {
    fetch(`/admin/exams/${examData.examId}/existing-results`)
        .then(response => response.json())
        .then(data => {
            if (data.results) {
                populateResults(data.results);
            }
        })
        .catch(error => {
            console.error('Error loading existing results:', error);
        });
}

// Populate results in the table
function populateResults(results) {
    // Clear all inputs first
    document.querySelectorAll('.marks-input').forEach(input => {
        input.value = '';
    });

    // Populate with existing results
    results.forEach(result => {
        const input = document.querySelector(`input[data-student-id="${result.student_id}"][data-subject-id="${result.subject_id}"]`);
        if (input) {
            input.value = result.marks_obtained;
            updateGrade(input);
        }
    });

    // Update all calculations
    updateAllCalculations();
}

// Update grade when marks change
function updateGrade(input) {
    const studentId = input.dataset.studentId;
    const subjectId = input.dataset.subjectId;
    const marks = parseFloat(input.value) || 0;
    const maxMarks = parseFloat(input.dataset.maxMarks);

    const gradeElement = document.querySelector(`.grade-display[data-student-id="${studentId}"][data-subject-id="${subjectId}"]`);
    if (gradeElement) {
        const grade = calculateGrade(marks, maxMarks);
        gradeElement.textContent = grade;
        gradeElement.className = `grade-display grade-${grade}`;
    }
}

// Calculate grade based on marks
function calculateGrade(marks, maxMarks) {
    if (maxMarks === 0) return 'N/A';

    const percentage = (marks / maxMarks) * 100;

    if (percentage >= 90) return 'A';
    if (percentage >= 80) return 'B';
    if (percentage >= 70) return 'C';
    if (percentage >= 60) return 'D';
    return 'F';
}

// Update all calculations for a student
function updateStudentCalculations(studentId) {
    const subjectInputs = document.querySelectorAll(`input[data-student-id="${studentId}"]`);
    let totalMarks = 0;
    let totalMaxMarks = 0;

    subjectInputs.forEach(input => {
        const marks = parseFloat(input.value) || 0;
        const maxMarks = parseFloat(input.dataset.maxMarks);
        totalMarks += marks;
        totalMaxMarks += maxMarks;
    });

    const percentage = totalMaxMarks > 0 ? ((totalMarks / totalMaxMarks) * 100).toFixed(2) : 0;
    const overallGrade = calculateGrade(totalMarks, totalMaxMarks);

    // Update display
    const totalElement = document.querySelector(`[data-student-id="${studentId}"][data-type="total"]`);
    const percentageElement = document.querySelector(`[data-student-id="${studentId}"][data-type="percentage"]`);
    const gradeElement = document.querySelector(`.grade-display[data-student-id="${studentId}"][data-type="overall-grade"]`);

    if (totalElement) totalElement.textContent = totalMarks;
    if (percentageElement) percentageElement.textContent = percentage + '%';
    if (gradeElement) {
        gradeElement.textContent = overallGrade;
        gradeElement.className = `grade-display fw-bold grade-${overallGrade}`;
    }
}

// Update all calculations
function updateAllCalculations() {
    const studentIds = [...new Set(Array.from(document.querySelectorAll('[data-student-id]')).map(el => el.dataset.studentId))];

    studentIds.forEach(studentId => {
        updateStudentCalculations(studentId);
    });

    updateStats();
}

// Update statistics
function updateStats() {
    const totalCells = document.querySelectorAll('[data-type="total"]');
    const filledCells = Array.from(totalCells).filter(cell => parseFloat(cell.textContent) > 0);
    const completionPercent = totalCells.length > 0 ? Math.round((filledCells.length / totalCells.length) * 100) : 0;

    document.getElementById('resultsEntered').textContent = filledCells.length;
    document.getElementById('completionPercent').textContent = completionPercent + '%';
}

// Save all results
function saveAllResults() {
    const results = [];
    const inputs = document.querySelectorAll('.marks-input');

    inputs.forEach(input => {
        const marks = parseFloat(input.value);
        if (!isNaN(marks) && marks >= 0) {
            results.push({
                student_id: parseInt(input.dataset.studentId),
                subject_id: parseInt(input.dataset.subjectId),
                marks_obtained: marks,
                max_marks: parseFloat(input.dataset.maxMarks)
            });
        }
    });

    if (results.length === 0) {
        alert('No results to save. Please enter some marks first.');
        return;
    }

    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    btn.disabled = true;

    // AJAX request to save results
    fetch('/admin/exams/save-results', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?php echo $csrf_token; ?>'
        },
        body: JSON.stringify({
            exam_id: examData.examId,
            results: results
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Results saved successfully!');
            updateStats();
        } else {
            alert('Error saving results: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error saving results:', error);
        alert('Error saving results. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Clear all results
function clearAllResults() {
    if (confirm('Are you sure you want to clear all entered results? This action cannot be undone.')) {
        document.querySelectorAll('.marks-input').forEach(input => {
            input.value = '';
            updateGrade(input);
        });
        updateAllCalculations();
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Update grade and calculations when marks change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('marks-input')) {
            updateGrade(e.target);
            updateStudentCalculations(e.target.dataset.studentId);
            updateStats();
        }
    });

    // Validate marks don't exceed max
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('marks-input')) {
            const marks = parseFloat(e.target.value) || 0;
            const maxMarks = parseFloat(e.target.dataset.maxMarks);

            if (marks > maxMarks) {
                alert(`Marks cannot exceed maximum marks (${maxMarks})`);
                e.target.value = maxMarks;
                updateGrade(e.target);
                updateStudentCalculations(e.target.dataset.studentId);
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>