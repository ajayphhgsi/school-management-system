<?php
$active_page = 'exams';
$page_title = 'Generate Admit Cards';
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

.admit-card-preview {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    font-size: 0.875rem;
    max-height: 400px;
    overflow-y: auto;
}

.generation-options {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-id-card text-primary me-2"></i>Generate Admit Cards</h4>
        <p class="text-muted mb-0">Create admit cards for exams with PDF export</p>
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
                                        <span class="badge bg-<?php echo (strtotime($exam['start_date']) <= time() && strtotime($exam['end_date']) >= time()) ? 'success' : 'secondary'; ?>">
                                            <?php echo (strtotime($exam['start_date']) <= time() && strtotime($exam['end_date']) >= time()) ? 'Active' : 'Upcoming'; ?>
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
                        <p class="text-muted">Create exams first to generate admit cards</p>
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
                <h5 class="mb-0">Admit Card Generation</h5>
            </div>
            <div class="card-body">
                <div class="generation-options">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Selected Exam: <span id="selectedExamName" class="text-primary"></span></h6>
                            <p class="mb-1">Class: <span id="selectedExamClass"></span></p>
                            <p class="mb-0">Students: <span id="studentCount">0</span></p>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="includePhotos" checked>
                                <label class="form-check-label" for="includePhotos">
                                    Include Student Photos
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="includeSignatures" checked>
                                <label class="form-check-label" for="includeSignatures">
                                    Include Signature Areas
                                </label>
                            </div>
                            <div class="mb-2">
                                <label for="cardsPerPage" class="form-label small">Cards per A4 page:</label>
                                <select class="form-select form-select-sm" id="cardsPerPage">
                                    <option value="2">2 cards</option>
                                    <option value="4" selected>4 cards</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6>Bulk Generation</h6>
                        <p class="text-muted small">Generate admit cards for all students in the selected exam's class</p>
                        <button class="btn btn-success btn-lg w-100" onclick="generateBulkAdmitCards()">
                            <i class="fas fa-users me-2"></i>Generate All Admit Cards
                        </button>
                    </div>
                    <div class="col-md-6">
                        <h6>Individual Generation</h6>
                        <p class="text-muted small">Generate admit card for a specific student</p>
                        <div class="input-group">
                            <select class="form-select" id="individualStudent">
                                <option value="">Select Student</option>
                            </select>
                            <button class="btn btn-primary" onclick="generateIndividualAdmitCard()">
                                <i class="fas fa-user me-1"></i>Generate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admit Card Preview -->
        <div id="previewSection" class="card d-none">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Admit Card Preview</h5>
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
                <div class="admit-card-preview">
                    <div class="text-center mb-4">
                        <h4>School Management System</h4>
                        <h5>Admit Card</h5>
                        <hr>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Exam:</strong> <span id="previewExamName">-</span>
                        </div>
                        <div class="col-6">
                            <strong>Date:</strong> <span id="previewExamDate">-</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4">
                            <strong>Roll No:</strong> <span id="previewRollNo">-</span>
                        </div>
                        <div class="col-4">
                            <strong>Class:</strong> <span id="previewClass">-</span>
                        </div>
                        <div class="col-4">
                            <strong>Scholar No:</strong> <span id="previewScholarNo">-</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Student Name:</strong> <span id="previewStudentName">-</span>
                    </div>

                    <div class="mb-4">
                        <strong>Subject Schedule:</strong>
                        <div id="previewSubjects" class="mt-2">
                            <small class="text-muted">Subject schedule will appear here</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-4 text-center">
                            <div style="border-top: 1px solid #000; margin-top: 40px; padding-top: 5px;">
                                <small>Principal</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="border-top: 1px solid #000; margin-top: 40px; padding-top: 5px;">
                                <small>Exam Controller</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="border-top: 1px solid #000; margin-top: 40px; padding-top: 5px;">
                                <small>School Seal</small>
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

function selectExam(examId, examName, examClass) {
    selectedExam = { id: examId, name: examName, class: examClass };

    // Update UI
    document.querySelectorAll('.exam-card').forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    document.getElementById('selectedExamName').textContent = examName;
    document.getElementById('selectedExamClass').textContent = examClass;
    document.getElementById('generationOptions').classList.remove('d-none');

    // Load students for this exam
    loadStudentsForExam(examId);
}

function loadStudentsForExam(examId) {
    // Show loading
    document.getElementById('studentCount').textContent = 'Loading...';

    // AJAX request to get students
    fetch(`/admin/exams/${examId}/students`)
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

function generateBulkAdmitCards() {
    if (!selectedExam) {
        alert('Please select an exam first');
        return;
    }

    if (selectedStudents.length === 0) {
        alert('No students found for this exam');
        return;
    }

    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
    btn.disabled = true;

    // AJAX request to generate bulk admit cards
    fetch('/admin/exams/generate-admit-cards', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?php echo $csrf_token; ?>'
        },
        body: JSON.stringify({
            exam_id: selectedExam.id,
            students: selectedStudents.map(s => s.id),
            include_photos: document.getElementById('includePhotos').checked,
            include_signatures: document.getElementById('includeSignatures').checked,
            cards_per_page: document.getElementById('cardsPerPage').value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Open PDF in new tab
            window.open(data.pdf_url, '_blank');
            alert('Admit cards generated successfully!');
        } else {
            alert('Error generating admit cards: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error generating admit cards:', error);
        alert('Error generating admit cards. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function generateIndividualAdmitCard() {
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

    // AJAX request to generate individual admit card
    fetch('/admin/exams/generate-admit-card', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?php echo $csrf_token; ?>'
        },
        body: JSON.stringify({
            exam_id: selectedExam.id,
            student_id: studentId,
            include_photos: document.getElementById('includePhotos').checked,
            include_signatures: document.getElementById('includeSignatures').checked
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Open PDF in new tab
            window.open(data.pdf_url, '_blank');
            // Update preview
            updatePreview(studentId);
        } else {
            alert('Error generating admit card: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error generating admit card:', error);
        alert('Error generating admit card. Please try again.');
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
    document.getElementById('previewExamDate').textContent = 'Exam Date Range';
    document.getElementById('previewRollNo').textContent = student.roll_number || 'N/A';
    document.getElementById('previewClass').textContent = selectedExam.class;
    document.getElementById('previewScholarNo').textContent = student.scholar_number;
    document.getElementById('previewStudentName').textContent = `${student.first_name} ${student.last_name}`;

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