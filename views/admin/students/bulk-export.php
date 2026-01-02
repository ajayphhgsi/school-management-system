<?php
$active_page = 'students';
$page_title = 'Bulk Export Students';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-download text-primary me-2"></i>Bulk Export Students</h4>
        <p class="text-muted mb-0">Export student data to CSV file</p>
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

<div class="row">
    <div class="col-lg-8">
        <!-- Export Options -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Export Options</h5>
            </div>
            <div class="card-body">
                <form id="exportForm" action="/admin/students/bulk-export" method="GET">
                    <div class="mb-3">
                        <label class="form-label">Academic Year Filter</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="filter" id="current_year" value="current_academic_year" checked>
                            <label class="form-check-label" for="current_year">
                                Current Academic Year
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="filter" id="all_students" value="all">
                            <label class="form-check-label" for="all_students">
                                All Students (All Years)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Filters</label>
                        <div class="row">
                            <div class="col-md-6">
                                <select class="form-select" name="class_filter" id="classFilter">
                                    <option value="">All Classes</option>
                                    <?php
                                    // Classes will be loaded via AJAX or passed from controller
                                    // For now, showing placeholder
                                    ?>
                                        <option value="">Select a class...</option>
                                </select>
                                <div class="form-text">Filter by specific class</div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" name="status_filter" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="active">Active Only</option>
                                    <option value="inactive">Inactive Only</option>
                                </select>
                                <div class="form-text">Filter by student status</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="format" id="csv_format" value="csv" checked>
                            <label class="form-check-label" for="csv_format">
                                CSV (Comma Separated Values) - Compatible with Excel
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_photos" id="include_photos" checked>
                            <label class="form-check-label" for="include_photos">
                                Include photo file paths
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_guardian" id="include_guardian" checked>
                            <label class="form-check-label" for="include_guardian">
                                Include guardian information
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-download me-2"></i>Export Students
                    </button>
                </form>
            </div>
        </div>

        <!-- Export Preview -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Export Preview</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    The exported CSV will contain the following columns:
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Basic Information:</h6>
                        <ul class="small">
                            <li>Scholar Number</li>
                            <li>Admission Number</li>
                            <li>Admission Date</li>
                            <li>Full Name (First, Middle, Last)</li>
                            <li>Date of Birth</li>
                            <li>Gender</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Academic Information:</h6>
                        <ul class="small">
                            <li>Class & Section</li>
                            <li>Contact Details</li>
                            <li>Address Information</li>
                            <li>Guardian Details</li>
                            <li>Medical Information</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Export Summary</h5>
            </div>
            <div class="card-body">
                <?php
                $academicYearId = isset($_SESSION['academic_year_id']) ? $_SESSION['academic_year_id'] : null;
                $where = "";
                $params = [];
                if ($academicYearId) {
                    $where = "WHERE c.academic_year_id = ?";
                    $params = [$academicYearId];
                }
                $totalStudents = count($this->db->select("SELECT s.id FROM students s LEFT JOIN classes c ON s.class_id = c.id $where", $params));
                $activeStudents = count(array_filter($this->db->select("SELECT s.is_active FROM students s LEFT JOIN classes c ON s.class_id = c.id $where", $params), fn($s) => $s['is_active']));
                ?>
                <div class="text-center">
                    <div class="h3 text-primary mb-2"><?php echo $totalStudents; ?></div>
                    <p class="text-muted mb-1">Total Students</p>
                    <small class="text-success"><?php echo $activeStudents; ?> Active</small>
                </div>
            </div>
        </div>

        <!-- Export History -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Recent Exports</h5>
            </div>
            <div class="card-body">
                <div class="text-center text-muted">
                    <i class="fas fa-history fa-2x mb-2"></i>
                    <p>No recent exports</p>
                    <small>Export history will be shown here</small>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Tips</h5>
            </div>
            <div class="card-body">
                <ul class="small">
                    <li>Use filters to export specific data sets</li>
                    <li>CSV files can be opened in Excel or Google Sheets</li>
                    <li>Exported data respects academic year filtering</li>
                    <li>Photo paths are included for reference only</li>
                    <li>Export process may take time for large datasets</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Update form action based on selected filters
document.querySelectorAll('input[name="filter"]').forEach(radio => {
    radio.addEventListener('change', updateExportUrl);
});

document.getElementById('classFilter').addEventListener('change', updateExportUrl);
document.getElementById('statusFilter').addEventListener('change', updateExportUrl);

function updateExportUrl() {
    const form = document.getElementById('exportForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (let [key, value] of formData.entries()) {
        if (value && value !== '') {
            params.append(key, value);
        }
    }

    form.action = '/admin/students/bulk-export?' + params.toString();
}

// Auto-submit form on filter change for preview
document.querySelectorAll('input[name="filter"], #classFilter, #statusFilter').forEach(element => {
    element.addEventListener('change', function() {
        // Could add AJAX call here to update preview stats
    });
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>