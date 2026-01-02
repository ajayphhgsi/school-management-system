<?php
$active_page = 'students';
$page_title = 'Bulk Import Students';
ob_start();
?>

<style>
.progress-container {
    display: none;
    margin-top: 20px;
}

.progress-bar {
    width: 0%;
    height: 20px;
    background-color: #007bff;
    transition: width 0.3s ease;
}

.csv-template {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    margin: 20px 0;
}

.template-table {
    font-size: 0.875rem;
}

.template-table th {
    background: #e9ecef;
    font-weight: 600;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-upload text-primary me-2"></i>Bulk Import Students</h4>
        <p class="text-muted mb-0">Import multiple students from CSV file</p>
    </div>
    <a href="/admin/students" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Students
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

<?php if (isset($_SESSION['flash']['errors'])): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Import Errors:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($_SESSION['flash']['errors'] as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']['errors']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <!-- Import Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Upload CSV File</h5>
            </div>
            <div class="card-body">
                <form id="importForm" action="/admin/students/process-bulk-import" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                        <div class="form-text">Select a CSV file containing student data. Maximum file size: 10MB</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="skip_duplicates" name="skip_duplicates" checked>
                            <label class="form-check-label" for="skip_duplicates">
                                Skip duplicate entries (based on scholar number or admission number)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="importBtn">
                            <i class="fas fa-upload me-2"></i>Import Students
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="downloadTemplate()">
                            <i class="fas fa-download me-2"></i>Download Template
                        </button>
                    </div>
                </form>

                <!-- Progress Bar -->
                <div class="progress-container" id="progressContainer">
                    <div class="progress">
                        <div class="progress-bar" id="progressBar" role="progressbar"></div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted" id="progressText">Processing...</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Instructions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Instructions</h5>
            </div>
            <div class="card-body">
                <h6>CSV Format Requirements:</h6>
                <ul class="small">
                    <li>First row must contain column headers</li>
                    <li>Required columns: scholar_number, admission_number, first_name, last_name, gender, mobile, class_name</li>
                    <li>Optional columns: middle_name, date_of_birth, caste_category, etc.</li>
                    <li>Class must exist in the system</li>
                    <li>Use exact column names as shown in template</li>
                </ul>

                <h6 class="mt-3">Data Validation:</h6>
                <ul class="small">
                    <li>Scholar number and admission number must be unique</li>
                    <li>Mobile number must be 10-15 digits</li>
                    <li>Gender must be: male, female, or other</li>
                    <li>Email must be valid format (if provided)</li>
                </ul>
            </div>
        </div>

        <!-- CSV Template -->
        <div class="csv-template">
            <h6>CSV Template Structure:</h6>
            <div class="table-responsive">
                <table class="table table-sm template-table">
                    <thead>
                        <tr>
                            <th>Column Name</th>
                            <th>Required</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>scholar_number</td><td>Yes</td><td>SCH001</td></tr>
                        <tr><td>admission_number</td><td>Yes</td><td>ADM001</td></tr>
                        <tr><td>first_name</td><td>Yes</td><td>John</td></tr>
                        <tr><td>middle_name</td><td>No</td><td>Robert</td></tr>
                        <tr><td>last_name</td><td>Yes</td><td>Doe</td></tr>
                        <tr><td>date_of_birth</td><td>No</td><td>2000-01-15</td></tr>
                        <tr><td>gender</td><td>Yes</td><td>male</td></tr>
                        <tr><td>mobile</td><td>Yes</td><td>9876543210</td></tr>
                        <tr><td>email</td><td>No</td><td>john@example.com</td></tr>
                        <tr><td>class_name</td><td>Yes</td><td>Grade 1</td></tr>
                        <tr><td>section</td><td>No</td><td>A</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const importBtn = document.getElementById('importBtn');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    // Show progress
    progressContainer.style.display = 'block';
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Importing...';

    // Simulate progress (in real implementation, this would be handled server-side)
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 10;
        if (progress <= 90) {
            progressBar.style.width = progress + '%';
            progressText.textContent = 'Processing... ' + progress + '%';
        }
    }, 500);

    fetch('/admin/students/process-bulk-import', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        progressText.textContent = 'Complete!';

        setTimeout(() => {
            if (data.success) {
                window.location.href = '/admin/students';
            } else {
                progressContainer.style.display = 'none';
                importBtn.disabled = false;
                importBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Import Students';
                alert('Import failed: ' + data.message);
            }
        }, 1000);
    })
    .catch(error => {
        clearInterval(progressInterval);
        progressContainer.style.display = 'none';
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Import Students';
        alert('Import failed: ' + error.message);
    });
});

function downloadTemplate() {
    // Create a sample CSV content
    const csvContent = `scholar_number,admission_number,admission_date,first_name,middle_name,last_name,date_of_birth,gender,caste_category,nationality,religion,blood_group,village,address,permanent_address,mobile,email,aadhar_number,samagra_number,apaar_id,pan_number,previous_school,medical_conditions,father_name,mother_name,guardian_name,guardian_contact,class_name,section
SCH001,ADM001,2024-04-01,John,,Doe,2000-01-15,male,General,Indian,Hindu,O+,Village Name,Address Line 1,Permanent Address,9876543210,john@example.com,123456789012,123456789,APAAR123,PAN123,Previous School,None,Father Name,Mother Name,Guardian Name,9876543211,Grade 1,A
SCH002,ADM002,2024-04-01,Jane,,Smith,2000-02-20,female,OBC,Indian,Hindu,A+,Village Name,Address Line 2,Permanent Address,9876543211,jane@example.com,123456789013,123456790,APAAR124,PAN124,Previous School,None,Father Name,Mother Name,Guardian Name,9876543212,Grade 1,A`;

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'student_import_template.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>