<?php
$active_page = 'reports';
$page_title = 'Reports & Analytics';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-chart-bar text-primary me-2"></i>Reports & Analytics</h4>
        <p class="text-muted mb-0">Generate reports and analyze school data</p>
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

<!-- Report Categories -->
<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Student Reports</h5>
                <p class="card-text">Generate reports on student enrollment, demographics, and performance.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="generateReport('students')">Student List</button>
                    <button class="btn btn-outline-primary" onclick="generateReport('student-demographics')">Demographics</button>
                    <button class="btn btn-outline-primary" onclick="generateReport('student-performance')">Performance</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                <h5 class="card-title">Financial Reports</h5>
                <p class="card-text">View fee collection, outstanding payments, and financial summaries.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-success" onclick="generateReport('fee-collection')">Fee Collection</button>
                    <button class="btn btn-outline-success" onclick="generateReport('outstanding-fees')">Outstanding Fees</button>
                    <button class="btn btn-outline-success" onclick="generateReport('financial-summary')">Financial Summary</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-calendar-check fa-3x text-info mb-3"></i>
                <h5 class="card-title">Attendance Reports</h5>
                <p class="card-text">Analyze attendance patterns and generate attendance reports.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-info" onclick="generateReport('attendance-summary')">Attendance Summary</button>
                    <button class="btn btn-outline-info" onclick="generateReport('attendance-trends')">Attendance Trends</button>
                    <button class="btn btn-outline-info" onclick="generateReport('absentee-report')">Absentee Report</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-file-alt fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Academic Reports</h5>
                <p class="card-text">Generate exam results, grade reports, and academic analytics.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-warning" onclick="generateReport('exam-results')">Exam Results</button>
                    <button class="btn btn-outline-warning" onclick="generateReport('grade-distribution')">Grade Distribution</button>
                    <button class="btn btn-outline-warning" onclick="generateReport('academic-performance')">Academic Performance</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Generation Form -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Custom Report Generation</h5>
    </div>
    <div class="card-body">
        <form id="reportForm">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="reportType" class="form-label">Report Type</label>
                    <select class="form-select" id="reportType" required>
                        <option value="">Select report type...</option>
                        <option value="students">Students</option>
                        <option value="fees">Fees</option>
                        <option value="attendance">Attendance</option>
                        <option value="exams">Exams</option>
                        <option value="events">Events</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="dateFrom" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="dateFrom">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="dateTo" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="dateTo">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="format" class="form-label">Export Format</label>
                    <select class="form-select" id="format">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-download me-2"></i>Generate & Download Report
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function generateReport(reportType) {
    // Parse report type to determine category and specific type
    let category, type;

    if (reportType.includes('-')) {
        const parts = reportType.split('-');
        category = parts[0];
        type = parts.slice(1).join('-');
    } else {
        category = reportType;
        type = 'list'; // default for students
    }

    // Show loading message
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
    button.disabled = true;

    // Create form and submit
    const form = document.createElement('form');
    form.method = 'GET';
    form.style.display = 'none';

    let url;
    switch (category) {
        case 'students':
            url = '/admin/generate-student-report';
            break;
        case 'student':
            url = '/admin/generate-student-report';
            category = 'students';
            break;
        case 'fee':
            url = '/admin/generate-financial-report';
            category = 'fees';
            break;
        case 'outstanding':
            url = '/admin/generate-financial-report';
            type = 'outstanding-fees';
            category = 'fees';
            break;
        case 'financial':
            url = '/admin/generate-financial-report';
            type = 'financial-summary';
            category = 'fees';
            break;
        case 'attendance':
            url = '/admin/generate-attendance-report';
            break;
        case 'absentee':
            url = '/admin/generate-attendance-report';
            type = 'absentee-report';
            break;
        case 'exam':
            url = '/admin/generate-academic-report';
            category = 'exams';
            break;
        case 'grade':
            url = '/admin/generate-academic-report';
            type = 'grade-distribution';
            category = 'exams';
            break;
        case 'academic':
            url = '/admin/generate-academic-report';
            type = 'academic-performance';
            category = 'exams';
            break;
        default:
            url = '/admin/generate-student-report';
            type = 'list';
    }

    form.action = url;

    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = type;
    form.appendChild(typeInput);

    document.body.appendChild(form);
    form.submit();

    // Reset button (in case form submission fails)
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 2000);
}

document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const reportType = document.getElementById('reportType').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const format = document.getElementById('format').value;

    if (!reportType) {
        alert('Please select a report type');
        return;
    }

    // Show loading
    const submitBtn = document.querySelector('#reportForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
    submitBtn.disabled = true;

    // Create form data
    const formData = new FormData();
    formData.append('reportType', reportType);
    if (dateFrom) formData.append('dateFrom', dateFrom);
    if (dateTo) formData.append('dateTo', dateTo);
    formData.append('format', format);

    // Send request
    fetch('/admin/generate-custom-report', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Trigger download or show success message
            alert('Report generated successfully! The download should start automatically.');
        } else {
            alert('Error: ' + (data.message || 'Failed to generate report'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating the report');
    })
    .finally(() => {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>