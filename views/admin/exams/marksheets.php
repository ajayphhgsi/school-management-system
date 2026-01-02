<?php
$active_page = 'exams';
$page_title = 'Generate Marksheets';
ob_start();
?>

<style>
.exam-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.exam-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.exam-card.selected {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.marksheet-preview {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    font-size: 0.875rem;
    max-height: 500px;
    overflow-y: auto;
}

.marks-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
}

.marks-table th,
.marks-table td {
    border: 1px solid #dee2e6;
    padding: 8px;
    text-align: center;
}

.marks-table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.grade-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-alt text-warning me-2"></i>Generate Marksheets</h4>
        <p class="text-muted mb-0">Create marksheets with subject marks, grades, and rankings</p>
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

<div class="row">
    <!-- Exam Selection -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Select Exam</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($exams)): ?>
                    <div class="list-group">
                        <?php foreach ($exams as $exam): ?>
                            <div class="list-group-item exam-card" onclick="selectExam(<?php echo $exam['id']; ?>, '<?php echo addslashes($exam['exam_name']); ?>', '<?php echo addslashes($exam['class_name']); ?>')">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo $exam['exam_name']; ?></h6>
                                        <p class="mb-1 text-muted small"><?php echo $exam['class_name']; ?> • <?php echo ucfirst($exam['exam_type']); ?></p>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($exam['start_date'])); ?> - <?php echo date('M d, Y', strtotime($exam['end_date'])); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?php echo (strtotime($exam['end_date']) < time()) ? 'success' : 'warning'; ?>">
                                            <?php echo (strtotime($exam['end_date']) < time()) ? 'Completed' : 'Ongoing'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Exams Available</h5>
                        <p class="text-muted">Create exams first to generate marksheets</p>
                        <a href="/admin/exams/create" class="btn btn-primary">Create Exam</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Generation Options -->
    <div class="col-lg-8 mb-4">
        <div id="generationOptions" class="card d-none">
            <div class="card-header">
                <h5 class="mb-0">Marksheet Generation</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Selected Exam: <span id="selectedExamName" class="text-primary"></span></h6>
                        <p class="mb-1">Class: <span id="selectedExamClass"></span></p>
                        <p class="mb-0">Students with Results: <span id="studentCount">0</span></p>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="includePhotos" checked>
                            <label class="form-check-label" for="includePhotos">
                                Include Student Photos
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="includeGrades" checked>
                            <label class="form-check-label" for="includeGrades">
                                Include Grade Calculation
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="includeRankings" checked>
                            <label class="form-check-label" for="includeRankings">
                                Include Class Rankings
                            </label>
                        </div>
                        <div class="mb-2">
                            <label for="marksheetsPerPage" class="form-label small">Marksheets per A4 page:</label>
                            <select class="form-select form-select-sm" id="marksheetsPerPage">
                                <option value="1">1 marksheet</option>
                                <option value="2" selected>2 marksheets</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6>Class Marksheet</h6>
                        <p class="text-muted small">Generate marksheets for all students in the selected exam's class</p>
                        <button class="btn btn-warning btn-lg w-100" onclick="generateClassMarksheets()">
                            <i class="fas fa-users me-2"></i>Generate Class Marksheets
                        </button>
                    </div>
                    <div class="col-md-6">
                        <h6>Individual Marksheet</h6>
                        <p class="text-muted small">Generate marksheet for a specific student</p>
                        <div class="input-group">
                            <select class="form-select" id="individualStudent">
                                <option value="">Select Student</option>
                            </select>
                            <button class="btn btn-warning" onclick="generateIndividualMarksheet()">
                                <i class="fas fa-user me-1"></i>Generate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marksheet Preview -->
        <div id="previewSection" class="card d-none">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Marksheet Preview</h5>
                <div>
                    <button class="btn btn-outline-primary btn-sm me-2" onclick="refreshPreview()">
                        <i class="fas fa-sync me-1"></i>Refresh
                    </button>
                    <button class="btn btn-success btn-sm" onclick="downloadPDF()">
                        <i class="fas fa-download me-1"></i>Download PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="marksheet-preview">
                    <div class="text-center mb-4">
                        <h3>School Management System</h3>
                        <h4>MARKSHEET</h4>
                        <h5 id="previewExamName">-</h5>
                        <hr>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Student Name:</strong> <span id="previewStudentName">-</span>
                        </div>
                        <div class="col-6">
                            <strong>Roll Number:</strong> <span id="previewRollNo">-</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Class:</strong> <span id="previewClass">-</span>
                        </div>
                        <div class="col-6">
                            <strong>Scholar Number:</strong> <span id="previewScholarNo">-</span>
                        </div>
                    </div>

                    <table class="marks-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Max Marks</th>
                                <th>Marks Obtained</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody id="previewSubjects">
                            <tr>
                                <td colspan="4" class="text-center text-muted">Subject marks will appear here</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">Total</th>
                                <th id="previewTotalMarks">-</th>
                                <th id="previewOverallGrade">-</th>
                            </tr>
                            <tr>
                                <th colspan="2">Percentage</th>
                                <th colspan="2" id="previewPercentage">-</th>
                            </tr>
                            <tr>
                                <th colspan="2">Rank</th>
                                <th colspan="2" id="previewRank">-</th>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="row mt-4">
                        <div class="col-4 text-center">
                            <div style="border-top: 1px solid #000; margin-top: 40px; padding-top: 5px;">
                                <small>Class Teacher</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="border-top: 1px solid #000; margin-top: 40px; padding-top: 5px;">
                                <small>Exam Controller</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="border-top: 1px solid #000; margin-top: 40px; padding-top: 5px;">
                                <small>Principal</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedExam = null;
let selectedStudents = [];

// Exam selection
function selectExam(examId, examName, examClass) {
    selectedExam = { id: examId, name: examName, class: examClass };

    // Update UI
    document.querySelectorAll('.exam-card').forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    document.getElementById('selectedExamName').textContent = examName;
    document.getElementById('selectedExamClass').textContent = examClass;
    document.getElementById('generationOptions').classList.remove('d-none');

    // Load students with results for this exam
    loadStudentsWithResults(examId);
}

function loadStudentsWithResults(examId) {
    // Show loading
    document.getElementById('studentCount').textContent = 'Loading...';

    // AJAX request to get students with exam results
    fetch(`/admin/exams/${examId}/results/students`)
        .then(response => response.json())
        .then(data => {
            selectedStudents = data.students || [];
            document.getElementById('studentCount').textContent = selectedStudents.length;

            // Populate individual student dropdown
            const dropdown = document.getElementById('individualStudent');
            dropdown.innerHTML = '<option value="">Select Student</option>';
            selectedStudents.forEach(student => {
                const option = document.createElement('option');
                option.value = student.id;
                option.textContent = `${student.first_name} ${student.last_name} (${student.scholar_number})`;
                dropdown.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading students:', error);
            document.getElementById('studentCount').textContent = 'Error';
        });
}

function generateClassMarksheets() {
    if (!selectedExam) {
        alert('Please select an exam first');
        return;
    }

    if (selectedStudents.length === 0) {
        alert('No students with results found for this exam');
        return;
    }

    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
    btn.disabled = true;

    // AJAX request to generate class marksheets
    fetch('/admin/exams/generate-marksheets', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?php echo $csrf_token; ?>'
        },
        body: JSON.stringify({
            exam_id: selectedExam.id,
            students: selectedStudents.map(s => s.id),
            include_photos: document.getElementById('includePhotos').checked,
            include_grades: document.getElementById('includeGrades').checked,
            include_rankings: document.getElementById('includeRankings').checked,
            marksheets_per_page: document.getElementById('marksheetsPerPage').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.open(data.marksheet_url, '_blank');
            alert('Marksheets generated successfully!');
        } else {
            alert('Error generating marksheets: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error generating marksheets:', error);
        alert('Error generating marksheets. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function generateIndividualMarksheet() {
    const studentId = document.getElementById('individualStudent').value;
    if (!studentId) {
        alert('Please select a student');
        return;
    }

    if (!selectedExam) {
        alert('Please select an exam first');
        return;
    }

    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>';
    btn.disabled = true;

    // AJAX request to generate individual marksheet
    fetch('/admin/exams/generate-marksheet', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?php echo $csrf_token; ?>'
        },
        body: JSON.stringify({
            exam_id: selectedExam.id,
            student_id: studentId,
            include_photos: document.getElementById('includePhotos').checked,
            include_grades: document.getElementById('includeGrades').checked,
            include_rankings: document.getElementById('includeRankings').checked
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.open(data.marksheet_url, '_blank');
            // Update preview
            updatePreview(studentId);
        } else {
            alert('Error generating marksheet: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error generating marksheet:', error);
        alert('Error generating marksheet. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function updatePreview(studentId = null) {
    if (!selectedExam) return;

    const student = studentId ? selectedStudents.find(s => s.id == studentId) : selectedStudents[0];
    if (!student) return;

    document.getElementById('previewExamName').textContent = selectedExam.name;
    document.getElementById('previewStudentName').textContent = `${student.first_name} ${student.last_name}`;
    document.getElementById('previewRollNo').textContent = student.roll_number || 'N/A';
    document.getElementById('previewClass').textContent = selectedExam.class;
    document.getElementById('previewScholarNo').textContent = student.scholar_number;

    // Show preview section
    document.getElementById('previewSection').classList.remove('d-none');
}

function refreshPreview() {
    const studentId = document.getElementById('individualStudent').value;
    updatePreview(studentId);
}

function downloadPDF() {
    // This would trigger PDF download
    alert('PDF download functionality - To be implemented');
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>