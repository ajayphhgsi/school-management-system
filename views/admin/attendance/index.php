<?php
$active_page = 'attendance';
$page_title = 'Attendance Management';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-calendar-check text-primary me-2"></i>Attendance Management</h4>
        <p class="text-muted mb-0">Track and manage student attendance</p>
    </div>
    <button class="btn btn-primary btn-lg" onclick="markAttendance()">
        <i class="fas fa-plus me-2"></i>Mark Attendance
    </button>
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

<!-- Class Selection -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <label for="classSelect" class="form-label">Select Class</label>
                <select class="form-select" id="classSelect">
                    <option value="">Choose a class...</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name'] . ' ' . $class['section']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="dateSelect" class="form-label">Select Date</label>
                <input type="date" class="form-control" id="dateSelect" value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div id="attendanceContent" class="d-none">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Attendance for <span id="selectedClass"></span> on <span id="selectedDate"></span></h5>
            <div>
                <button class="btn btn-outline-primary btn-sm me-2" onclick="markAllPresent()">
                    <i class="fas fa-check-circle me-1"></i>Mark All Present
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="markAllAbsent()">
                    <i class="fas fa-times-circle me-1"></i>Mark All Absent
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Scholar No.</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <!-- Attendance rows will be populated here -->
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Present: <span id="presentCount">0</span> |
                        Absent: <span id="absentCount">0</span> |
                        Late: <span id="lateCount">0</span>
                    </small>
                </div>
                <div>
                    <button class="btn btn-success me-2" onclick="saveAttendance()">
                        <i class="fas fa-save me-2"></i>Save Attendance
                    </button>
                    <button class="btn btn-outline-primary" onclick="exportAttendance()">
                        <i class="fas fa-download me-2"></i>Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Placeholder when no class selected -->
<div id="noClassSelected" class="text-center py-5">
    <i class="fas fa-calendar-check fa-4x text-muted mb-3"></i>
    <h4 class="text-muted">Select a Class and Date</h4>
    <p class="text-muted">Choose a class from the dropdown above to start marking attendance.</p>
</div>

<script>
let attendanceData = [];
let students = [];

document.getElementById('classSelect').addEventListener('change', loadAttendance);
document.getElementById('dateSelect').addEventListener('change', loadAttendance);

function loadAttendance() {
    const classId = document.getElementById('classSelect').value;
    const date = document.getElementById('dateSelect').value;

    if (classId && date) {
        // Show attendance content
        document.getElementById('attendanceContent').classList.remove('d-none');
        document.getElementById('noClassSelected').classList.add('d-none');

        // Update header
        const classSelect = document.getElementById('classSelect');
        const selectedOption = classSelect.options[classSelect.selectedIndex];
        document.getElementById('selectedClass').textContent = selectedOption.text;
        document.getElementById('selectedDate').textContent = new Date(date).toLocaleDateString();

        // Load students and attendance data
        loadStudentsAndAttendance(classId, date);
    } else {
        document.getElementById('attendanceContent').classList.add('d-none');
        document.getElementById('noClassSelected').classList.remove('d-none');
    }
}

function loadStudentsAndAttendance(classId, date) {
    // Show loading
    document.getElementById('attendanceTableBody').innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted">
                <i class="fas fa-spinner fa-spin me-2"></i>Loading students...
            </td>
        </tr>
    `;

    // AJAX request to load students and existing attendance
    fetch(`/admin/attendance/data?class_id=${classId}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            students = data.students || [];
            attendanceData = data.attendance || [];

            renderAttendanceTable();
        })
        .catch(error => {
            console.error('Error loading attendance data:', error);
            document.getElementById('attendanceTableBody').innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Error loading data. Please try again.
                    </td>
                </tr>
            `;
        });
}

function renderAttendanceTable() {
    const tbody = document.getElementById('attendanceTableBody');

    if (students.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-users me-2"></i>No students found in this class.
                </td>
            </tr>
        `;
        updateCounts();
        return;
    }

    tbody.innerHTML = students.map((student, index) => {
        // Find existing attendance record
        const existingAttendance = attendanceData.find(a => a.student_id == student.id);
        const status = existingAttendance ? existingAttendance.status : 'present';

        return `
            <tr>
                <td>${index + 1}</td>
                <td>${student.scholar_number}</td>
                <td>
                    <div class="d-flex align-items-center">
                        ${student.photo ? `<img src="/uploads/${student.photo}" class="rounded-circle me-2" width="30" height="30" alt="Photo">` : ''}
                        <div>
                            <div class="fw-bold">${student.first_name} ${student.last_name}</div>
                        </div>
                    </div>
                </td>
                <td>${student.class_name} ${student.section}</td>
                <td>
                    <select class="form-select form-select-sm attendance-status" data-student-id="${student.id}" onchange="updateAttendanceStatus(${student.id}, this.value)">
                        <option value="present" ${status === 'present' ? 'selected' : ''}>Present</option>
                        <option value="absent" ${status === 'absent' ? 'selected' : ''}>Absent</option>
                        <option value="late" ${status === 'late' ? 'selected' : ''}>Late</option>
                    </select>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-info" onclick="viewStudentAttendance(${student.id})" title="View History">
                        <i class="fas fa-history"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    updateCounts();
}

function updateAttendanceStatus(studentId, status) {
    // Update local attendance data
    const existingIndex = attendanceData.findIndex(a => a.student_id == studentId);
    if (existingIndex >= 0) {
        attendanceData[existingIndex].status = status;
    } else {
        attendanceData.push({
            student_id: studentId,
            status: status,
            date: document.getElementById('dateSelect').value
        });
    }
    updateCounts();
}

function updateCounts() {
    const present = attendanceData.filter(a => a.status === 'present').length;
    const absent = attendanceData.filter(a => a.status === 'absent').length;
    const late = attendanceData.filter(a => a.status === 'late').length;

    document.getElementById('presentCount').textContent = present;
    document.getElementById('absentCount').textContent = absent;
    document.getElementById('lateCount').textContent = late;
}

function markAllPresent() {
    document.querySelectorAll('.attendance-status').forEach(select => {
        select.value = 'present';
        updateAttendanceStatus(select.dataset.studentId, 'present');
    });
}

function markAllAbsent() {
    document.querySelectorAll('.attendance-status').forEach(select => {
        select.value = 'absent';
        updateAttendanceStatus(select.dataset.studentId, 'absent');
    });
}

function saveAttendance() {
    const classId = document.getElementById('classSelect').value;
    const date = document.getElementById('dateSelect').value;

    if (!classId || !date) {
        alert('Please select a class and date');
        return;
    }

    // Show loading state
    const saveBtn = document.querySelector('button[onclick="saveAttendance()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    saveBtn.disabled = true;

    // AJAX request to save attendance
    fetch('/admin/attendance/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?php echo $csrf_token; ?>'
        },
        body: JSON.stringify({
            class_id: classId,
            date: date,
            attendance: attendanceData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Attendance saved successfully!');
            // Reload to get updated data
            loadAttendance();
        } else {
            alert('Error saving attendance: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error saving attendance:', error);
        alert('Error saving attendance. Please try again.');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function exportAttendance() {
    const classId = document.getElementById('classSelect').value;
    const date = document.getElementById('dateSelect').value;

    if (!classId || !date) {
        alert('Please select a class and date');
        return;
    }

    window.open(`/admin/attendance/export?class_id=${classId}&date=${date}`, '_blank');
}

function viewStudentAttendance(studentId) {
    // This could open a modal with student's attendance history
    window.open(`/admin/students/attendance/${studentId}`, '_blank');
}

function markAttendance() {
    // This function is called from the header button
    const classSelect = document.getElementById('classSelect');
    if (classSelect.value) {
        loadAttendance();
    } else {
        alert('Please select a class first');
    }
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>