<?php
$active_page = 'certificates';
$page_title = 'Certificate Management';
ob_start();
?>

<style>
.certificate-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.certificate-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.certificate-card.selected {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.student-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.student-card:hover {
    background: #e9ecef;
    border-color: #0d6efd;
}

.student-card.selected {
    background: #e7f3ff;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.certificate-preview {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    font-size: 0.875rem;
    max-height: 500px;
    overflow-y: auto;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-certificate text-primary me-2"></i>Certificate Management</h4>
        <p class="text-muted mb-0">Generate transfer certificates and other student certificates</p>
    </div>
    <div>
        <a href="/admin/certificates/tc" class="btn btn-outline-primary">View Transfer Certificates</a>
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

<!-- Certificate Types -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Certificate Types</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="certificate-card card text-center h-100" onclick="selectCertificateType('transfer')">
                            <div class="card-body">
                                <div class="mb-3">
                                    <i class="fas fa-file-contract fa-3x text-success"></i>
                                </div>
                                <h5 class="card-title">Transfer Certificate</h5>
                                <p class="card-text text-muted">Generate transfer certificates for students</p>
                                <span class="badge bg-success">Available</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="certificate-card card text-center h-100" onclick="selectCertificateType('character')">
                            <div class="card-body">
                                <div class="mb-3">
                                    <i class="fas fa-user-check fa-3x text-info"></i>
                                </div>
                                <h5 class="card-title">Character Certificate</h5>
                                <p class="card-text text-muted">Generate character certificates</p>
                                <span class="badge bg-secondary">Coming Soon</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="certificate-card card text-center h-100" onclick="selectCertificateType('bonafide')">
                            <div class="card-body">
                                <div class="mb-3">
                                    <i class="fas fa-stamp fa-3x text-warning"></i>
                                </div>
                                <h5 class="card-title">Bonafide Certificate</h5>
                                <p class="card-text text-muted">Generate bonafide certificates</p>
                                <span class="badge bg-secondary">Coming Soon</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Certificate Generation -->
<div id="transferCertificateSection" class="row d-none">
    <!-- Student Selection -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Select Student</h5>
            </div>
            <div class="card-body">
                <!-- Class Filter -->
                <div class="mb-3">
                    <label for="classFilter" class="form-label">Filter by Class</label>
                    <select class="form-select" id="classFilter">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name'] . ' ' . $class['section']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search -->
                <div class="mb-3">
                    <label for="studentSearch" class="form-label">Search Students</label>
                    <input type="text" class="form-control" id="studentSearch" placeholder="Search by name or scholar number">
                </div>

                <!-- Student List -->
                <div id="studentList" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p>Select class to load students</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Certificate Details -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Transfer Certificate Details</h5>
            </div>
            <div class="card-body">
                <form id="certificateForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <!-- Certificate Details -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="transferReason" class="form-label">Reason for Transfer *</label>
                            <select class="form-select" id="transferReason" name="transfer_reason" required>
                                <option value="">Select Reason</option>
                                <option value="parent_transfer">Parent Transfer</option>
                                <option value="better_opportunity">Better Educational Opportunity</option>
                                <option value="family_moved">Family Moved</option>
                                <option value="personal">Personal Reasons</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="issueDate" class="form-label">Issue Date *</label>
                            <input type="date" class="form-control" id="issueDate" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="conduct" class="form-label">Conduct & Character</label>
                        <select class="form-select" id="conduct" name="conduct">
                            <option value="good">Good</option>
                            <option value="satisfactory">Satisfactory</option>
                            <option value="excellent">Excellent</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Additional Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Any additional remarks or notes..."></textarea>
                    </div>

                    <!-- Certificate Preview -->
                    <div class="mt-4">
                        <h6>Certificate Preview</h6>
                        <div class="certificate-preview">
                            <div class="text-center mb-4">
                                <h3>School Management System</h3>
                                <h4>TRANSFER CERTIFICATE</h4>
                                <hr>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>Certificate No:</strong> <span id="previewCertNo">-</span>
                                </div>
                                <div class="col-6">
                                    <strong>Issue Date:</strong> <span id="previewIssueDate">-</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <strong>This is to certify that</strong> <span id="previewStudentName">-</span>
                            </div>

                            <div class="mb-3">
                                <strong>Scholar Number:</strong> <span id="previewScholarNo">-</span><br>
                                <strong>Class:</strong> <span id="previewClass">-</span><br>
                                <strong>Date of Admission:</strong> <span id="previewAdmissionDate">-</span>
                            </div>

                            <div class="mb-3">
                                <strong>Academic Record:</strong><br>
                                <span id="previewAcademicRecord">-</span>
                            </div>

                            <div class="mb-3">
                                <strong>Reason for Leaving:</strong> <span id="previewReason">-</span>
                            </div>

                            <div class="mb-3">
                                <strong>Conduct:</strong> <span id="previewConduct">-</span>
                            </div>

                            <div class="row mt-5">
                                <div class="col-4 text-center">
                                    <div style="border-top: 1px solid #000; padding-top: 10px;">
                                        <small>Class Teacher</small>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div style="border-top: 1px solid #000; padding-top: 10px;">
                                        <small>Principal</small>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div style="border-top: 1px solid #000; padding-top: 10px;">
                                        <small>School Seal</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-secondary" onclick="resetCertificateForm()">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <div>
                            <button type="button" class="btn btn-outline-primary me-2" onclick="previewCertificate()">
                                <i class="fas fa-eye me-1"></i>Preview
                            </button>
                            <button type="button" class="btn btn-success" onclick="generateCertificate()">
                                <i class="fas fa-download me-2"></i>Generate Certificate
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let selectedStudent = null;
let selectedCertificateType = null;

// Certificate type selection
function selectCertificateType(type) {
    document.querySelectorAll('.certificate-card').forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    selectedCertificateType = type;

    if (type === 'transfer') {
        document.getElementById('transferCertificateSection').classList.remove('d-none');
        loadStudents();
    } else {
        alert('This certificate type is not yet implemented');
    }
}

// Load students based on class filter
function loadStudents() {
    const classId = document.getElementById('classFilter').value;

    document.getElementById('studentList').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
            <p>Loading students...</p>
        </div>
    `;

    let url = '/admin/certificates/students';
    if (classId) {
        url += `?class_id=${classId}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            displayStudents(data.students);
        })
        .catch(error => {
            console.error('Error loading students:', error);
            document.getElementById('studentList').innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>Error loading students. Please try again.</p>
                </div>
            `;
        });
}

// Display students
function displayStudents(students) {
    const studentList = document.getElementById('studentList');

    if (students.length === 0) {
        studentList.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-users fa-2x mb-2"></i>
                <p>No students found.</p>
            </div>
        `;
        return;
    }

    studentList.innerHTML = students.map(student => `
        <div class="student-card" onclick="selectStudent(${student.id}, '${student.first_name} ${student.last_name}', '${student.scholar_number}', '${student.class_name} ${student.section}', '${student.admission_date}')">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    ${student.photo ? `<img src="/uploads/${student.photo}" class="rounded-circle" width="40" height="40" alt="Photo">` : '<div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-user text-white"></i></div>'}
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${student.first_name} ${student.last_name}</h6>
                    <small class="text-muted">Scholar No: ${student.scholar_number} | Class: ${student.class_name} ${student.section}</small>
                </div>
                <div class="flex-shrink-0">
                    <i class="fas fa-chevron-right text-muted"></i>
                </div>
            </div>
        </div>
    `).join('');
}

// Select student
function selectStudent(id, name, scholarNumber, classInfo, admissionDate) {
    selectedStudent = { id, name, scholarNumber, classInfo, admissionDate };

    document.querySelectorAll('.student-card').forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    updateCertificatePreview();
}

// Update certificate preview
function updateCertificatePreview() {
    if (!selectedStudent) return;

    const certNo = 'TC-' + Date.now().toString().slice(-8);
    const issueDate = document.getElementById('issueDate').value;
    const reason = document.getElementById('transferReason').options[document.getElementById('transferReason').selectedIndex].text;
    const conduct = document.getElementById('conduct').options[document.getElementById('conduct').selectedIndex].text;

    document.getElementById('previewCertNo').textContent = certNo;
    document.getElementById('previewIssueDate').textContent = issueDate ? new Date(issueDate).toLocaleDateString() : '-';
    document.getElementById('previewStudentName').textContent = selectedStudent.name;
    document.getElementById('previewScholarNo').textContent = selectedStudent.scholarNumber;
    document.getElementById('previewClass').textContent = selectedStudent.classInfo;
    document.getElementById('previewAdmissionDate').textContent = selectedStudent.admissionDate ? new Date(selectedStudent.admissionDate).toLocaleDateString() : '-';
    document.getElementById('previewAcademicRecord').textContent = 'Academic record will be populated from student results';
    document.getElementById('previewReason').textContent = reason || '-';
    document.getElementById('previewConduct').textContent = conduct || '-';
}

// Generate certificate
function generateCertificate() {
    if (!selectedStudent) {
        alert('Please select a student first');
        return;
    }

    const formData = new FormData(document.getElementById('certificateForm'));
    const data = Object.fromEntries(formData);

    if (!data.transfer_reason || !data.issue_date) {
        alert('Please fill in all required fields');
        return;
    }

    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
    btn.disabled = true;

    // AJAX request to generate certificate
    fetch('/admin/certificates/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': data.csrf_token
        },
        body: JSON.stringify({
            student_id: selectedStudent.id,
            certificate_type: 'transfer',
            transfer_reason: data.transfer_reason,
            issue_date: data.issue_date,
            conduct: data.conduct,
            remarks: data.remarks
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.open(data.certificate_url, '_blank');
            alert('Transfer certificate generated successfully!');
        } else {
            alert('Error generating certificate: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error generating certificate:', error);
        alert('Error generating certificate. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Preview certificate
function previewCertificate() {
    updateCertificatePreview();
    document.getElementById('previewCertNo').scrollIntoView({ behavior: 'smooth' });
}

// Reset form
function resetCertificateForm() {
    selectedStudent = null;
    document.querySelectorAll('.student-card').forEach(card => card.classList.remove('selected'));
    document.getElementById('certificateForm').reset();
    updateCertificatePreview();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('classFilter').addEventListener('change', loadStudents);
    document.getElementById('studentSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.student-card').forEach(card => {
            const name = card.textContent.toLowerCase();
            card.style.display = name.includes(searchTerm) ? '' : 'none';
        });
    });

    // Update preview when form changes
    ['transferReason', 'issueDate', 'conduct'].forEach(id => {
        document.getElementById(id).addEventListener('change', updateCertificatePreview);
    });
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>