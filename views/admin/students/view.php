<?php
$active_page = 'students';
$page_title = 'View Student';
ob_start();
?>

<style>
@media print {
    .btn, .alert, .card-header .btn {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
        break-inside: avoid;
    }
    .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #000 !important;
    }
    body {
        font-size: 12px;
    }
    .row {
        page-break-inside: avoid;
    }
    @page {
        margin: 1in;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-user text-primary me-2"></i>Student Details</h4>
        <p class="text-muted mb-0">Complete information for <?php echo $student['first_name'] . ' ' . $student['last_name']; ?></p>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-outline-secondary me-2">
            <i class="fas fa-print me-1"></i>Print Details
        </button>
        <a href="/admin/students/edit/<?php echo $student['id']; ?>" class="btn btn-primary me-2">
            <i class="fas fa-edit me-1"></i>Edit Student
        </a>
        <a href="/admin/students" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Students
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

<div class="row">
    <!-- Student Photo and Basic Info -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <?php if ($student['photo']): ?>
                    <img src="/uploads/<?php echo $student['photo']; ?>" alt="Student Photo" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #e9ecef;">
                <?php else: ?>
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 150px; height: 150px;">
                        <i class="fas fa-user text-white fa-3x"></i>
                    </div>
                <?php endif; ?>

                <h5 class="card-title"><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></h5>
                <p class="text-muted mb-2"><?php echo $student['scholar_number']; ?></p>

                <div class="mb-3">
                    <span class="badge bg-light text-dark me-2">
                        <i class="fas fa-graduation-cap me-1"></i><?php echo $student['class_name'] ? $student['class_name'] . ' ' . $student['section'] : 'No Class'; ?>
                    </span>
                    <br>
                    <span class="badge <?php echo $student['is_active'] ? 'bg-success' : 'bg-danger'; ?> mt-2">
                        <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Personal Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Scholar Number</label>
                        <p class="mb-0"><?php echo $student['scholar_number']; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Admission Number</label>
                        <p class="mb-0"><?php echo $student['admission_number'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">First Name</label>
                        <p class="mb-0"><?php echo $student['first_name']; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Middle Name</label>
                        <p class="mb-0"><?php echo $student['middle_name'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Last Name</label>
                        <p class="mb-0"><?php echo $student['last_name']; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Date of Birth</label>
                        <p class="mb-0"><?php echo $student['date_of_birth'] ? date('d M Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Gender</label>
                        <p class="mb-0"><?php echo ucfirst($student['gender'] ?: ''); ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Admission Date</label>
                        <p class="mb-0"><?php echo $student['admission_date'] ? date('d M Y', strtotime($student['admission_date'])) : 'N/A'; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-address-book me-2"></i>Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Mobile Number</label>
                        <p class="mb-0"><?php echo $student['mobile']; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <p class="mb-0"><?php echo $student['email'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Guardian Contact</label>
                        <p class="mb-0"><?php echo $student['guardian_contact'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Village</label>
                        <p class="mb-0"><?php echo $student['village'] ?: 'N/A'; ?></p>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Address</label>
                    <p class="mb-0"><?php echo nl2br($student['address'] ?: '') ?: 'N/A'; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Permanent Address</label>
                    <p class="mb-0"><?php echo nl2br($student['permanent_address'] ?: '') ?: 'N/A'; ?></p>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Additional Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Caste/Category</label>
                        <p class="mb-0"><?php echo $student['caste_category'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Nationality</label>
                        <p class="mb-0"><?php echo $student['nationality']; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Religion</label>
                        <p class="mb-0"><?php echo $student['religion'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Blood Group</label>
                        <p class="mb-0"><?php echo $student['blood_group'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Aadhar Number</label>
                        <p class="mb-0"><?php echo $student['aadhar_number'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Samagra Number</label>
                        <p class="mb-0"><?php echo $student['samagra_number'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">PAN Number</label>
                        <p class="mb-0"><?php echo $student['pan_number'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Aapaar ID</label>
                        <p class="mb-0"><?php echo $student['apaar_id'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Previous School</label>
                        <p class="mb-0"><?php echo $student['previous_school'] ?: 'N/A'; ?></p>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Medical Conditions</label>
                    <p class="mb-0"><?php echo nl2br($student['medical_conditions'] ?: '') ?: 'None'; ?></p>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Father's Name</label>
                        <p class="mb-0"><?php echo $student['father_name'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Mother's Name</label>
                        <p class="mb-0"><?php echo $student['mother_name'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Guardian's Name</label>
                        <p class="mb-0"><?php echo $student['guardian_name'] ?: 'N/A'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>