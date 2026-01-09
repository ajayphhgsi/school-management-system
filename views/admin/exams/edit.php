<?php
$active_page = 'exams';
$page_title = 'Edit Exam';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><?php echo $page_title; ?></h4>
        <p class="text-muted mb-0">Edit exam details and subject schedule</p>
    </div>
    <div>
        <a href="/admin/exams" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Exams
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

<?php if (isset($_SESSION['flash']['errors'])): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($_SESSION['flash']['errors'] as $error): ?>
                <li><?php echo $error[0]; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <style>
            .subject-row { margin-bottom: 15px; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; }
            .subject-header { background-color: #f8f9fa; padding: 10px; margin: -15px -15px 15px -15px; border-radius: 5px 5px 0 0; }
            .auto-fill-btn { margin-top: 10px; }
            .class-badge { display: inline-block; margin: 2px; padding: 4px 8px; background-color: #007bff; color: white; border-radius: 3px; font-size: 0.8rem; }
            .time-input { width: 120px; }
            .date-input { width: 140px; }
        </style>
        <form id="examForm" action="/admin/exams/update/<?php echo $exam['id']; ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <!-- Basic Exam Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="exam_name" class="form-label">Exam Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="exam_name" name="exam_name" value="<?php echo htmlspecialchars($exam['exam_name']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="exam_type" class="form-label">Exam Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="exam_type" name="exam_type" required>
                        <option value="">Select Exam Type</option>
                        <option value="quarterly" <?php echo ($exam['exam_type'] == 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                        <option value="halfyearly" <?php echo ($exam['exam_type'] == 'halfyearly') ? 'selected' : ''; ?>>Half Yearly</option>
                        <option value="annually" <?php echo ($exam['exam_type'] == 'annually') ? 'selected' : ''; ?>>Annually</option>
                        <option value="custom" <?php echo ($exam['exam_type'] == 'custom') ? 'selected' : ''; ?>>Custom</option>
                    </select>
                </div>
            </div>

            <!-- Class Selection -->
            <div class="row mb-4">
                <div class="col-12">
                    <label class="form-label">Select Classes <span class="text-danger">*</span></label>
                    <div id="classSelection">
                        <?php foreach ($classes as $class): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input class-checkbox" type="checkbox" id="class_<?php echo $class['id']; ?>" name="class_ids[]" value="<?php echo $class['id']; ?>" <?php echo in_array($class['id'], array_column($exam_subjects, 'class_id')) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="class_<?php echo $class['id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="selectedClasses" class="mt-2"></div>
                </div>
            </div>

            <!-- Date Range -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $exam['start_date']; ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $exam['end_date']; ?>" required>
                </div>
            </div>

            <!-- Academic Year (Optional) -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="academic_year_id" class="form-label">Academic Year</label>
                    <select class="form-select" id="academic_year_id" name="academic_year_id">
                        <option value="">Select Academic Year</option>
                        <?php foreach ($academic_years as $year): ?>
                            <option value="<?php echo $year['id']; ?>" <?php echo ($exam['academic_year_id'] == $year['id'] || ($year['is_active'] && !$exam['academic_year_id'])) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($year['year_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Active Status</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $exam['is_active'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Exam is Active
                        </label>
                    </div>
                </div>
            </div>

            <!-- Subjects Section -->
            <div id="subjectsSection" style="display: <?php echo !empty($exam_subjects) ? 'block' : 'none'; ?>;">
                <h5 class="mb-3">Subject Schedule</h5>
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary" id="autoFillBtn">
                        <i class="fas fa-magic"></i> Auto Fill Dates & Times
                    </button>
                    <small class="text-muted ms-2">Set dates and times for all subjects at once</small>
                </div>
                <div id="subjectsContainer">
                    <?php
                    // Group existing exam subjects by class
                    $subjectsByClass = [];
                    foreach ($exam_subjects as $es) {
                        $classKey = $es['class_id'];
                        if (!isset($subjectsByClass[$classKey])) {
                            $subjectsByClass[$classKey] = [
                                'class_name' => $es['class_name'] . ' ' . $es['section'],
                                'subjects' => []
                            ];
                        }
                        $subjectsByClass[$classKey]['subjects'][] = $es;
                    }

                    $index = 0;
                    foreach ($subjectsByClass as $classId => $classData):
                    ?>
                    <div class="subject-row">
                        <div class="subject-header">
                            <strong><?php echo htmlspecialchars($classData['class_name']); ?></strong>
                        </div>
                        <?php foreach ($classData['subjects'] as $subject): ?>
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" readonly>
                                <input type="hidden" name="subjects[<?php echo $index; ?>][subject_id]" value="<?php echo $subject['subject_id']; ?>">
                                <input type="hidden" name="subjects[<?php echo $index; ?>][class_id]" value="<?php echo $subject['class_id']; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control date-input subject-date" name="subjects[<?php echo $index; ?>][exam_date]" value="<?php echo $subject['exam_date']; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Start Time</label>
                                <input type="time" class="form-control time-input subject-start-time" name="subjects[<?php echo $index; ?>][start_time]" value="<?php echo $subject['start_time']; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-control time-input subject-end-time" name="subjects[<?php echo $index; ?>][end_time]" value="<?php echo $subject['end_time']; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Max Marks</label>
                                <input type="number" class="form-control subject-max-marks" name="subjects[<?php echo $index; ?>][max_marks]" value="<?php echo $subject['max_marks']; ?>" min="1">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-secondary btn-sm clear-subject" title="Clear this subject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <?php $index++; endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="row mt-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Exam
                    </button>
                    <a href="/admin/exams" class="btn btn-secondary ms-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let subjectsData = [];
    let selectedClasses = [];

    // Initialize selected classes from checked checkboxes
    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedClasses();
    });

    // Handle class selection
    document.querySelectorAll('.class-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedClasses();
            loadSubjectsForClasses();
        });
    });

    function updateSelectedClasses() {
        selectedClasses = Array.from(document.querySelectorAll('.class-checkbox:checked')).map(cb => cb.value);
        const selectedClassesDiv = document.getElementById('selectedClasses');

        if (selectedClasses.length > 0) {
            let html = '<strong>Selected Classes:</strong> ';
            selectedClasses.forEach(classId => {
                const checkbox = document.getElementById('class_' + classId);
                const label = checkbox.nextElementSibling.textContent.trim();
                html += '<span class="class-badge">' + label + '</span>';
            });
            selectedClassesDiv.innerHTML = html;
        } else {
            selectedClassesDiv.innerHTML = '';
        }
    }

    function loadSubjectsForClasses() {
        if (selectedClasses.length === 0) {
            document.getElementById('subjectsSection').style.display = 'none';
            return;
        }

        fetch('/admin/exams/get-class-subjects?class_ids=' + selectedClasses.join(','))
            .then(response => response.json())
            .then(data => {
                subjectsData = data.subjects;
                renderSubjects();
                document.getElementById('subjectsSection').style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading subjects:', error);
                alert('Error loading subjects. Please try again.');
            });
    }

    function renderSubjects() {
        const container = document.getElementById('subjectsContainer');
        const existingClassHeaders = Array.from(container.querySelectorAll('.subject-header')).map(header => header.textContent.trim());

        // Group subjects by class
        const subjectsByClass = {};
        subjectsData.forEach(subject => {
            const classKey = subject.class_id;
            if (!subjectsByClass[classKey]) {
                subjectsByClass[classKey] = {
                    class_name: subject.class_name + ' ' + subject.section,
                    subjects: []
                };
            }
            subjectsByClass[classKey].subjects.push(subject);
        });

        // Render each class's subjects if not already existing
        Object.keys(subjectsByClass).forEach(classId => {
            const classData = subjectsByClass[classId];
            if (!existingClassHeaders.includes(classData.class_name)) {
                // Add new class row
                const classDiv = document.createElement('div');
                classDiv.className = 'subject-row';
                classDiv.innerHTML = `
                    <div class="subject-header">
                        <strong>${classData.class_name}</strong>
                    </div>
                    ${classData.subjects.map((subject, index) => `
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" value="${subject.subject_name}" readonly>
                                <input type="hidden" name="subjects[${Date.now() + index}][subject_id]" value="${subject.id}">
                                <input type="hidden" name="subjects[${Date.now() + index}][class_id]" value="${subject.class_id}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control date-input subject-date" name="subjects[${Date.now() + index}][exam_date]">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Start Time</label>
                                <input type="time" class="form-control time-input subject-start-time" name="subjects[${Date.now() + index}][start_time]">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-control time-input subject-end-time" name="subjects[${Date.now() + index}][end_time]">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Max Marks</label>
                                <input type="number" class="form-control subject-max-marks" name="subjects[${Date.now() + index}][max_marks]" value="100" min="1">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-secondary btn-sm clear-subject" title="Clear this subject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                `;
                container.appendChild(classDiv);
            }
        });

        // Add event listeners for clear buttons
        document.querySelectorAll('.clear-subject').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('.row');
                row.querySelectorAll('input[type="date"], input[type="time"], input[type="number"]').forEach(input => {
                    input.value = '';
                });
            });
        });
    }

    // Auto fill functionality
    document.getElementById('autoFillBtn').addEventListener('click', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        if (!startDate || !endDate) {
            alert('Please set start and end dates first.');
            return;
        }

        // Simple auto-fill: spread subjects across available dates
        const subjectDates = document.querySelectorAll('.subject-date');
        const subjectStartTimes = document.querySelectorAll('.subject-start-time');
        const subjectEndTimes = document.querySelectorAll('.subject-end-time');

        if (subjectDates.length === 0) return;

        // Calculate date range
        const start = new Date(startDate);
        const end = new Date(endDate);
        const totalDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

        // Distribute subjects across dates
        subjectDates.forEach((dateInput, index) => {
            const daysToAdd = Math.floor((index / subjectDates.length) * totalDays);
            const subjectDate = new Date(start);
            subjectDate.setDate(start.getDate() + daysToAdd);

            if (subjectDate <= end) {
                dateInput.value = subjectDate.toISOString().split('T')[0];
            } else {
                dateInput.value = endDate;
            }
        });

        // Set default times (can be customized)
        subjectStartTimes.forEach(timeInput => {
            timeInput.value = '10:00';
        });

        subjectEndTimes.forEach(timeInput => {
            timeInput.value = '11:30';
        });
    });

    // Form validation
    document.getElementById('examForm').addEventListener('submit', function(e) {
        const selectedClassesCount = document.querySelectorAll('.class-checkbox:checked').length;
        if (selectedClassesCount === 0) {
            e.preventDefault();
            alert('Please select at least one class.');
            return;
        }

        // Check if at least one subject is configured
        const subjectDates = document.querySelectorAll('.subject-date');
        let hasValidSubject = false;
        subjectDates.forEach(dateInput => {
            if (dateInput.value.trim() !== '') {
                hasValidSubject = true;
            }
        });

        if (!hasValidSubject) {
            e.preventDefault();
            alert('Please configure at least one subject with date and time.');
            return;
        }
    });
</script>

<?php
unset($_SESSION['flash']['old'], $_SESSION['flash']['errors']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>