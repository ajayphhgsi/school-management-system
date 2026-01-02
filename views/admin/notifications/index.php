<?php
$active_page = 'notifications';
$page_title = 'Notifications';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-bell text-primary me-2"></i>Notifications</h4>
        <p class="text-muted mb-0">Send emails and SMS notifications to students and parents</p>
    </div>
    <div>
        <a href="/admin/notifications/view" class="btn btn-outline-primary">
            <i class="fas fa-list me-2"></i>View Notifications
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

<!-- Notification Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Send Notification</h5>
    </div>
    <div class="card-body">
        <form id="notificationForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">

            <!-- Notification Type -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Notification Type <span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="notification_type" id="email_only" value="email" checked>
                        <label class="form-check-label" for="email_only">
                            <i class="fas fa-envelope text-primary me-1"></i>Email Only
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="notification_type" id="sms_only" value="sms">
                        <label class="form-check-label" for="sms_only">
                            <i class="fas fa-sms text-success me-1"></i>SMS Only
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="notification_type" id="both" value="both">
                        <label class="form-check-label" for="both">
                            <i class="fas fa-envelope text-primary me-1"></i><i class="fas fa-sms text-success me-1"></i>Both Email & SMS
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Recipient Selection <span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="recipient_type" id="all_students" value="all_students" checked>
                        <label class="form-check-label" for="all_students">
                            All Students
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="recipient_type" id="by_class" value="class">
                        <label class="form-check-label" for="by_class">
                            By Class
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="recipient_type" id="selected_students" value="selected">
                        <label class="form-check-label" for="selected_students">
                            Selected Students
                        </label>
                    </div>
                </div>
            </div>

            <!-- Class Selection -->
            <div class="mb-3" id="classSelection" style="display: none;">
                <label for="class_id" class="form-label">Select Class <span class="text-danger">*</span></label>
                <select class="form-select" id="class_id" name="class_id">
                    <option value="">Choose a class...</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo $class['class_name'] . ' ' . $class['section']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Student Selection -->
            <div class="mb-3" id="studentSelection" style="display: none;">
                <label class="form-label">Select Students <span class="text-danger">*</span></label>
                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                    <div id="studentList" class="row">
                        <!-- Students will be loaded here -->
                    </div>
                </div>
                <div class="form-text">Select individual students to send notifications to</div>
            </div>

            <!-- Template Selection -->
            <div class="mb-3">
                <label for="template" class="form-label">Use Template</label>
                <select class="form-select" id="template" name="template">
                    <option value="">Select a template...</option>
                    <option value="fee_reminder">Fee Payment Reminder</option>
                    <option value="exam_notification">Exam Notification</option>
                    <option value="holiday_notice">Holiday Notice</option>
                    <option value="event_invitation">Event Invitation</option>
                    <option value="custom">Custom Message</option>
                </select>
            </div>

            <!-- Subject -->
            <div class="mb-3">
                <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>

            <!-- Message -->
            <div class="mb-3">
                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                <div class="form-text">Use {student_name}, {class_name}, {scholar_number} as placeholders</div>
            </div>

            <!-- Send Button -->
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary" onclick="sendNotification()">
                    <i class="fas fa-paper-plane me-2"></i>Send Notification
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Templates -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-money-bill-wave fa-2x text-warning mb-3"></i>
                <h6 class="card-title">Fee Reminder</h6>
                <button class="btn btn-warning btn-sm" onclick="loadTemplate('fee_reminder')">
                    <i class="fas fa-file-text me-1"></i>Use Template
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-file-alt fa-2x text-info mb-3"></i>
                <h6 class="card-title">Exam Notification</h6>
                <button class="btn btn-info btn-sm" onclick="loadTemplate('exam_notification')">
                    <i class="fas fa-file-text me-1"></i>Use Template
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-calendar-alt fa-2x text-success mb-3"></i>
                <h6 class="card-title">Holiday Notice</h6>
                <button class="btn btn-success btn-sm" onclick="loadTemplate('holiday_notice')">
                    <i class="fas fa-file-text me-1"></i>Use Template
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-users fa-2x text-primary mb-3"></i>
                <h6 class="card-title">Event Invitation</h6>
                <button class="btn btn-primary btn-sm" onclick="loadTemplate('event_invitation')">
                    <i class="fas fa-file-text me-1"></i>Use Template
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function loadTemplate(templateType) {
    const templates = {
        'fee_reminder': {
            subject: 'Fee Payment Reminder - {school_name}',
            message: `Dear {student_name},

This is a reminder that your fee payment is due. Please ensure timely payment to avoid any inconvenience.

Student Details:
Name: {student_name}
Class: {class_name}
Scholar Number: {scholar_number}

Amount Due: Please check your account for the exact amount.
Due Date: As per the fee schedule

Please contact the school office for any queries.

Best regards,
{school_name}
School Administration`
        },
        'exam_notification': {
            subject: 'Exam Schedule Notification - {school_name}',
            message: `Dear {student_name},

We are pleased to inform you about the upcoming examinations.

Student Details:
Name: {student_name}
Class: {class_name}
Scholar Number: {scholar_number}

Please check the examination schedule and prepare accordingly. All the best for your exams!

Best regards,
{school_name}
School Administration`
        },
        'holiday_notice': {
            subject: 'Holiday Notice - {school_name}',
            message: `Dear {student_name},

This is to inform you about the upcoming holiday.

Student Details:
Name: {student_name}
Class: {class_name}
Scholar Number: {scholar_number}

Holiday Details: [Please specify holiday details]

School will remain closed during this period.

Best regards,
{school_name}
School Administration`
        },
        'event_invitation': {
            subject: 'Event Invitation - {school_name}',
            message: `Dear {student_name},

We cordially invite you to the upcoming school event.

Student Details:
Name: {student_name}
Class: {class_name}
Scholar Number: {scholar_number}

Event Details: [Please specify event details]

We look forward to your participation.

Best regards,
{school_name}
School Administration`
        }
    };

    if (templates[templateType]) {
        document.getElementById('template').value = templateType;
        document.getElementById('subject').value = templates[templateType].subject;
        document.getElementById('message').value = templates[templateType].message;
    }
}

// Handle recipient type changes
document.querySelectorAll('input[name="recipient_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const classSelection = document.getElementById('classSelection');
        const studentSelection = document.getElementById('studentSelection');

        if (this.value === 'class') {
            classSelection.style.display = 'block';
            studentSelection.style.display = 'none';
        } else if (this.value === 'selected') {
            classSelection.style.display = 'block';
            studentSelection.style.display = 'block';
            loadStudentsForSelection();
        } else {
            classSelection.style.display = 'none';
            studentSelection.style.display = 'none';
        }
    });
});

document.getElementById('class_id').addEventListener('change', function() {
    if (document.querySelector('input[name="recipient_type"]:checked').value === 'selected') {
        loadStudentsForSelection();
    }
});

function loadStudentsForSelection() {
    const classId = document.getElementById('class_id').value;
    if (!classId) return;

    fetch(`/admin/notifications/get-students-for-notifications?class_id=${classId}`)
        .then(response => response.json())
        .then(data => {
            const studentList = document.getElementById('studentList');
            studentList.innerHTML = '';

            if (data.students && data.students.length > 0) {
                data.students.forEach(student => {
                    const studentDiv = document.createElement('div');
                    studentDiv.className = 'col-md-6 mb-2';
                    studentDiv.innerHTML = `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="student_ids[]" value="${student.id}" id="student_${student.id}">
                            <label class="form-check-label" for="student_${student.id}">
                                ${student.first_name} ${student.last_name} (${student.scholar_number})
                            </label>
                        </div>
                    `;
                    studentList.appendChild(studentDiv);
                });
            } else {
                studentList.innerHTML = '<div class="col-12"><p class="text-muted">No students found in this class.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            document.getElementById('studentList').innerHTML = '<div class="col-12"><p class="text-danger">Error loading students.</p></div>';
        });
}

function sendNotification() {
    const form = document.getElementById('notificationForm');
    const formData = new FormData(form);

    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const recipientType = formData.get('recipient_type');
    if (recipientType === 'selected') {
        const selectedStudents = formData.getAll('student_ids[]');
        if (selectedStudents.length === 0) {
            alert('Please select at least one student.');
            return;
        }
    }

    // Show loading
    const sendBtn = document.querySelector('.btn-primary[onclick="sendNotification()"]');
    const originalText = sendBtn.innerHTML;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';

    // Prepare data
    const data = {
        notification_type: formData.get('notification_type'),
        recipient_type: recipientType,
        subject: formData.get('subject'),
        message: formData.get('message'),
        template: formData.get('template')
    };

    if (recipientType === 'class') {
        data.class_id = formData.get('class_id');
    } else if (recipientType === 'selected') {
        data.student_ids = formData.getAll('student_ids[]');
    }

    fetch('/admin/notifications/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            form.reset();
            document.getElementById('classSelection').style.display = 'none';
            document.getElementById('studentSelection').style.display = 'none';
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending notifications.');
    })
    .finally(() => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = originalText;
    });
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>