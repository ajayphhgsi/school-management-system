<?php
$active_page = 'students';
$page_title = isset($student) ? 'Edit Student' : 'Add New Student';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><?php echo $page_title; ?></h4>
        <?php if (isset($student)): ?>
            <p class="text-muted mb-0">Update student information</p>
        <?php else: ?>
            <p class="text-muted mb-0">Add a new student to the system</p>
        <?php endif; ?>
    </div>
    <div>
        <?php if (isset($student)): ?>
            <a href="/admin/students/view/<?php echo $student['id']; ?>" class="btn btn-info me-2">
                <i class="fas fa-eye me-1"></i>View Student
            </a>
        <?php endif; ?>
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
        <form method="POST" action="<?php echo isset($student) ? '/admin/students/update/' . $student['id'] : '/admin/students'; ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="scholar_number" class="form-label">Scholar Number *</label>
                    <input type="text" class="form-control" id="scholar_number" name="scholar_number"
                           value="<?php echo $_SESSION['flash']['old']['scholar_number'] ?? (isset($student) ? $student['scholar_number'] : ''); ?>" <?php echo isset($student) ? '' : 'disabled'; ?> required>
                    <div class="form-text"><?php echo isset($student) ? '' : 'Scholar number is auto-generated based on class selection.'; ?></div>
                    <?php if (isset($_SESSION['flash']['errors']['scholar_number'])): ?>
                        <div class="text-danger small"><?php echo $_SESSION['flash']['errors']['scholar_number'][0]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="admission_number" class="form-label">Admission Number *</label>
                    <input type="text" class="form-control" id="admission_number" name="admission_number"
                           value="<?php echo $_SESSION['flash']['old']['admission_number'] ?? (isset($student) ? $student['admission_number'] : ''); ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="admission_date" class="form-label">Admission Date</label>
                    <input type="date" class="form-control" id="admission_date" name="admission_date"
                           value="<?php echo $_SESSION['flash']['old']['admission_date'] ?? (isset($student) ? $student['admission_date'] : ''); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name"
                           value="<?php echo $_SESSION['flash']['old']['first_name'] ?? (isset($student) ? $student['first_name'] : ''); ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name"
                           value="<?php echo $_SESSION['flash']['old']['middle_name'] ?? (isset($student) ? $student['middle_name'] : ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name"
                           value="<?php echo $_SESSION['flash']['old']['last_name'] ?? (isset($student) ? $student['last_name'] : ''); ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth *</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                           value="<?php echo $_SESSION['flash']['old']['date_of_birth'] ?? (isset($student) ? $student['date_of_birth'] : ''); ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="gender" class="form-label">Gender *</label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo (($_SESSION['flash']['old']['gender'] ?? (isset($student) ? $student['gender'] : '')) === 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo (($_SESSION['flash']['old']['gender'] ?? (isset($student) ? $student['gender'] : '')) === 'female') ? 'selected' : ''; ?>>Female</option>
                        <option value="other" <?php echo (($_SESSION['flash']['old']['gender'] ?? (isset($student) ? $student['gender'] : '')) === 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="caste_category" class="form-label">Caste/Category</label>
                    <input type="text" class="form-control" id="caste_category" name="caste_category"
                           value="<?php echo $_SESSION['flash']['old']['caste_category'] ?? (isset($student) ? $student['caste_category'] : ''); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="nationality" class="form-label">Nationality</label>
                    <input type="text" class="form-control" id="nationality" name="nationality"
                           value="<?php echo $_SESSION['flash']['old']['nationality'] ?? (isset($student) ? $student['nationality'] : 'Indian'); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="religion" class="form-label">Religion</label>
                    <input type="text" class="form-control" id="religion" name="religion"
                           value="<?php echo $_SESSION['flash']['old']['religion'] ?? (isset($student) ? $student['religion'] : ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="blood_group" class="form-label">Blood Group</label>
                    <select class="form-control" id="blood_group" name="blood_group">
                        <option value="">Select Blood Group</option>
                        <option value="A+" <?php echo (($_SESSION['flash']['old']['blood_group'] ?? (isset($student) ? $student['blood_group'] : '')) === 'A+') ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo (($_SESSION['flash']['old']['blood_group'] ?? (isset($student) ? $student['blood_group'] : '')) === 'A-') ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo (($_SESSION['flash']['old']['blood_group'] ?? (isset($student) ? $student['blood_group'] : '')) === 'B+') ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo (($_SESSION['flash']['old']['blood_group'] ?? (isset($student) ? $student['blood_group'] : '')) === 'B-') ? 'selected' : ''; ?>>B-</option>
                        <option value="AB+" <?php echo (($_SESSION['flash']['old']['blood_group'] ?? (isset($student) ? $student['blood_group'] : '')) === 'AB+') ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo (($_SESSION['flash']['old']['blood_group'] ?? (isset($student) ? $student['blood_group'] : '')) === 'AB-') ? 'selected' : ''; ?>>AB-</option>
                        <option value="O+" <?php echo (($_SESSION['flash']['old']['blood_group'] ?? (isset($student) ? $student['blood_group'] : '')) === 'O+') ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo (($_SESSION['flash']['old']['blood_group'] ?? (isset($student) ? $student['blood_group'] : '')) === 'O-') ? 'selected' : ''; ?>>O-</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="village" class="form-label">Village</label>
                    <input type="text" class="form-control" id="village" name="village"
                           value="<?php echo $_SESSION['flash']['old']['village'] ?? (isset($student) ? $student['village'] : ''); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="mobile" class="form-label">Mobile Number *</label>
                    <input type="tel" class="form-control" id="mobile" name="mobile"
                           value="<?php echo $_SESSION['flash']['old']['mobile'] ?? (isset($student) ? $student['mobile'] : ''); ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo $_SESSION['flash']['old']['email'] ?? (isset($student) ? $student['email'] : ''); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="class_id" class="form-label">Class & Section *</label>
                    <select class="form-control" id="class_id" name="class_id" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"
                                    <?php echo (($_SESSION['flash']['old']['class_id'] ?? (isset($student) ? $student['class_id'] : '')) == $class['id']) ? 'selected' : ''; ?>>
                                <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?php echo $_SESSION['flash']['old']['address'] ?? (isset($student) ? $student['address'] : ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="permanent_address" class="form-label">Permanent Address</label>
                <textarea class="form-control" id="permanent_address" name="permanent_address" rows="3"><?php echo $_SESSION['flash']['old']['permanent_address'] ?? (isset($student) ? $student['permanent_address'] : ''); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="aadhar_number" class="form-label">Aadhar Number</label>
                    <input type="text" class="form-control" id="aadhar_number" name="aadhar_number"
                           value="<?php echo $_SESSION['flash']['old']['aadhar_number'] ?? (isset($student) ? $student['aadhar_number'] : ''); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="samagra_number" class="form-label">Samagra Number</label>
                    <input type="text" class="form-control" id="samagra_number" name="samagra_number"
                           value="<?php echo $_SESSION['flash']['old']['samagra_number'] ?? (isset($student) ? $student['samagra_number'] : ''); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="apaar_id" class="form-label">Aapaar ID</label>
                    <input type="text" class="form-control" id="apaar_id" name="apaar_id"
                           value="<?php echo $_SESSION['flash']['old']['apaar_id'] ?? (isset($student) ? $student['apaar_id'] : ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pan_number" class="form-label">PAN Number</label>
                    <input type="text" class="form-control" id="pan_number" name="pan_number"
                           value="<?php echo $_SESSION['flash']['old']['pan_number'] ?? (isset($student) ? $student['pan_number'] : ''); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="previous_school" class="form-label">Previous School Name</label>
                    <input type="text" class="form-control" id="previous_school" name="previous_school"
                           value="<?php echo $_SESSION['flash']['old']['previous_school'] ?? (isset($student) ? $student['previous_school'] : ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="father_name" class="form-label">Father's Name</label>
                    <input type="text" class="form-control" id="father_name" name="father_name"
                           value="<?php echo $_SESSION['flash']['old']['father_name'] ?? (isset($student) ? $student['father_name'] : ''); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="mother_name" class="form-label">Mother's Name</label>
                    <input type="text" class="form-control" id="mother_name" name="mother_name"
                           value="<?php echo $_SESSION['flash']['old']['mother_name'] ?? (isset($student) ? $student['mother_name'] : ''); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="guardian_name" class="form-label">Guardian's Name</label>
                    <input type="text" class="form-control" id="guardian_name" name="guardian_name"
                           value="<?php echo $_SESSION['flash']['old']['guardian_name'] ?? (isset($student) ? $student['guardian_name'] : ''); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="guardian_contact" class="form-label">Guardian's Contact Number</label>
                <input type="tel" class="form-control" id="guardian_contact" name="guardian_contact"
                       value="<?php echo $_SESSION['flash']['old']['guardian_contact'] ?? (isset($student) ? $student['guardian_contact'] : ''); ?>">
            </div>

            <div class="mb-3">
                <label for="medical_conditions" class="form-label">Medical Conditions</label>
                <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="3"><?php echo $_SESSION['flash']['old']['medical_conditions'] ?? (isset($student) ? $student['medical_conditions'] : ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="photo" class="form-label">Student Photo</label>
                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                <div class="form-text">Upload a passport-size photo (JPG, PNG, GIF). <?php echo isset($student) ? 'Leave empty to keep current photo.' : ''; ?></div>
                <?php if (isset($student) && $student['photo']): ?>
                    <div class="mt-2">
                        <small class="text-muted">Current photo:</small><br>
                        <img src="/uploads/<?php echo $student['photo']; ?>" alt="Current Photo" class="mt-1" style="max-width: 100px; max-height: 100px; border: 1px solid #dee2e6; border-radius: 4px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-end">
                <a href="/admin/students" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <?php echo isset($student) ? 'Update Student' : 'Save Student'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
<?php if (!isset($student)): ?>
document.getElementById('class_id').addEventListener('change', function() {
    const classId = this.value;
    if (classId) {
        fetch('/admin/get-next-scholar-number?class_id=' + classId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('scholar_number').value = data.scholar_number;
                }
            })
            .catch(error => console.error('Error:', error));
    } else {
        document.getElementById('scholar_number').value = '';
    }
});
<?php endif; ?>
</script>

<?php
unset($_SESSION['flash']['old'], $_SESSION['flash']['errors']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>