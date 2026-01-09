<?php
$active_page = 'exams';
$page_title = 'Generate Admit Cards';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-id-card text-success me-2"></i><?php echo $page_title; ?></h4>
        <p class="text-muted mb-0">Generate and print admit cards for exam students</p>
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
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Generate Admit Cards</h5>
            </div>
            <div class="card-body">
                <form id="admitCardForm" action="/admin/exams/generate-admit-cards" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <!-- Exam Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="exam_id" class="form-label">Select Exam <span class="text-danger">*</span></label>
                            <select class="form-select" id="exam_id" name="exam_id" required>
                                <option value="">Choose an exam...</option>
                                <?php foreach ($exams as $exam): ?>
                                    <option value="<?php echo $exam['id']; ?>"><?php echo htmlspecialchars($exam['exam_name']); ?> (<?php echo ucfirst($exam['exam_type']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Include Options</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_photos" name="include_photos" checked>
                                <label class="form-check-label" for="include_photos">
                                    Include Student Photos
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_signatures" name="include_signatures" checked>
                                <label class="form-check-label" for="include_signatures">
                                    Include Signature Boxes
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Class Selection -->
                    <div id="classSelection" style="display: none;">
                        <label class="form-label">Select Classes <span class="text-danger">*</span></label>
                        <div id="classCheckboxes" class="mb-3">
                            <p class="text-muted">Select an exam to load available classes.</p>
                        </div>
                        <div id="selectedClasses" class="mt-2"></div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="generateBtn" class="btn btn-success" disabled>
                                <i class="fas fa-cogs me-2"></i>Generate Admit Cards
                            </button>
                            <button type="button" id="printBtn" class="btn btn-primary ms-2" disabled>
                                <i class="fas fa-print me-2"></i>Print Admit Cards
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Instructions</h5>
            </div>
            <div class="card-body">
                <h6>How to Generate Admit Cards:</h6>
                <ol class="small">
                    <li>Select an exam from the dropdown</li>
                    <li>Choose which classes to generate cards for</li>
                    <li>Check options for photos and signatures</li>
                    <li>Click "Generate Admit Cards" to preview</li>
                    <li>Use "Print Admit Cards" for direct printing</li>
                </ol>
                <hr>
                <p class="small text-muted">
                    Admit cards will be generated for all students in the selected classes who have subjects scheduled for the selected exam.
                </p>
            </div>
        </div>

        <?php if (empty($exams)): ?>
            <div class="card mt-3">
                <div class="card-body text-center">
                    <i class="fas fa-id-card fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Exams Available</h6>
                    <p class="text-muted small">Create an exam first to generate admit cards.</p>
                    <a href="/admin/exams/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Create Exam
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
let selectedClasses = [];

document.getElementById('exam_id').addEventListener('change', function() {
    const examId = this.value;
    if (examId) {
        loadExamClasses(examId);
    } else {
        document.getElementById('classSelection').style.display = 'none';
        document.getElementById('generateBtn').disabled = true;
        document.getElementById('printBtn').disabled = true;
    }
});

function loadExamClasses(examId) {
    fetch('/admin/exams/get-exam-classes?exam_id=' + examId)
        .then(response => response.json())
        .then(data => {
            renderClassCheckboxes(data.classes);
            document.getElementById('classSelection').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading classes:', error);
            alert('Error loading classes. Please try again.');
        });
}

function renderClassCheckboxes(classes) {
    const container = document.getElementById('classCheckboxes');
    container.innerHTML = '';

    if (classes.length === 0) {
        container.innerHTML = '<p class="text-muted">No classes found for this exam.</p>';
        return;
    }

    classes.forEach(cls => {
        const div = document.createElement('div');
        div.className = 'form-check form-check-inline';
        div.innerHTML = `
            <input class="form-check-input class-checkbox" type="checkbox" id="class_${cls.id}" name="class_ids[]" value="${cls.id}" checked>
            <label class="form-check-label" for="class_${cls.id}">
                ${cls.class_name} ${cls.section}
            </label>
        `;
        container.appendChild(div);
    });

    // Add event listeners
    document.querySelectorAll('.class-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedClasses);
    });

    updateSelectedClasses();
}

function updateSelectedClasses() {
    selectedClasses = Array.from(document.querySelectorAll('.class-checkbox:checked')).map(cb => cb.value);
    const selectedClassesDiv = document.getElementById('selectedClasses');

    if (selectedClasses.length > 0) {
        let html = '<strong>Selected Classes:</strong> ';
        selectedClasses.forEach(classId => {
            const checkbox = document.getElementById('class_' + classId);
            const label = checkbox.nextElementSibling.textContent.trim();
            html += '<span class="badge bg-success me-1">' + label + '</span>';
        });
        selectedClassesDiv.innerHTML = html;
        document.getElementById('generateBtn').disabled = false;
        document.getElementById('printBtn').disabled = false;
    } else {
        selectedClassesDiv.innerHTML = '';
        document.getElementById('generateBtn').disabled = true;
        document.getElementById('printBtn').disabled = true;
    }
}

document.getElementById('admitCardForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const selectedClassesCount = document.querySelectorAll('.class-checkbox:checked').length;

    if (selectedClassesCount === 0) {
        alert('Please select at least one class.');
        return;
    }

    // Show loading
    const btn = document.getElementById('generateBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
    btn.disabled = true;

    fetch('/admin/exams/generate-admit-cards', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Open in new window
            window.open(data.html_url, '_blank');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

document.getElementById('printBtn').addEventListener('click', function() {
    const formData = new FormData(document.getElementById('admitCardForm'));
    const selectedClassesCount = document.querySelectorAll('.class-checkbox:checked').length;

    if (selectedClassesCount === 0) {
        alert('Please select at least one class.');
        return;
    }

    // For printing, we'll generate and open in new window
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Preparing...';
    btn.disabled = true;

    fetch('/admin/exams/generate-admit-cards', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const printWindow = window.open(data.html_url, '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>

<script>
// Auto-refresh the page if coming back from print
if (window.location.hash === '#printed') {
    window.location.hash = '';
    location.reload();
}
</script>

<?php
unset($_SESSION['flash']['old'], $_SESSION['flash']['errors']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>