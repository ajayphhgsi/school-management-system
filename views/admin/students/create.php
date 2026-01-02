<?php
$active_page = 'students';
$page_title = 'Add New Student';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Add New Student</h4>
    <a href="/admin/students" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Students
    </a>
</div>

<?php if (isset($_SESSION['flash']['error'])): ?>
    <div class="alert alert-danger">
        <?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/students" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="scholar_number" class="form-label">Scholar Number *</label>
                    <input type="text" class="form-control" id="scholar_number" name="scholar_number"
                            value="<?php echo $_SESSION['flash']['old']['scholar_number'] ?? ''; ?>" readonly required>
                    <div class="form-text">Auto-filled when class is selected</div>
                    <?php if (isset($_SESSION['flash']['errors']['scholar_number'])): ?>
                        <div class="text-danger small"><?php echo $_SESSION['flash']['errors']['scholar_number'][0]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="admission_number" class="form-label">Admission Number</label>
                    <input type="text" class="form-control" id="admission_number" name="admission_number"
                            value="Auto-generated" readonly>
                    <div class="form-text">Will be auto-generated using database ID</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="admission_date" class="form-label">Admission Date</label>
                    <input type="date" class="form-control" id="admission_date" name="admission_date"
                           value="<?php echo $_SESSION['flash']['old']['admission_date'] ?? ''; ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name"
                           value="<?php echo $_SESSION['flash']['old']['first_name'] ?? ''; ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name"
                           value="<?php echo $_SESSION['flash']['old']['middle_name'] ?? ''; ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name"
                           value="<?php echo $_SESSION['flash']['old']['last_name'] ?? ''; ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth *</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                           value="<?php echo $_SESSION['flash']['old']['date_of_birth'] ?? ''; ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="gender" class="form-label">Gender *</label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo ($_SESSION['flash']['old']['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($_SESSION['flash']['old']['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                        <option value="other" <?php echo ($_SESSION['flash']['old']['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="caste_category" class="form-label">Caste/Category</label>
                    <input type="text" class="form-control" id="caste_category" name="caste_category"
                           value="<?php echo $_SESSION['flash']['old']['caste_category'] ?? ''; ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="nationality" class="form-label">Nationality</label>
                    <input type="text" class="form-control" id="nationality" name="nationality"
                           value="<?php echo $_SESSION['flash']['old']['nationality'] ?? 'Indian'; ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="religion" class="form-label">Religion</label>
                    <input type="text" class="form-control" id="religion" name="religion"
                           value="<?php echo $_SESSION['flash']['old']['religion'] ?? ''; ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="blood_group" class="form-label">Blood Group</label>
                    <select class="form-control" id="blood_group" name="blood_group">
                        <option value="">Select Blood Group</option>
                        <option value="A+" <?php echo ($_SESSION['flash']['old']['blood_group'] ?? '') === 'A+' ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo ($_SESSION['flash']['old']['blood_group'] ?? '') === 'A-' ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo ($_SESSION['flash']['old']['blood_group'] ?? '') === 'B+' ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo ($_SESSION['flash']['old']['blood_group'] ?? '') === 'B-' ? 'selected' : ''; ?>>B-</option>
                        <option value="AB+" <?php echo ($_SESSION['flash']['old']['blood_group'] ?? '') === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo ($_SESSION['flash']['old']['blood_group'] ?? '') === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                        <option value="O+" <?php echo ($_SESSION['flash']['old']['blood_group'] ?? '') === 'O+' ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo ($_SESSION['flash']['old']['blood_group'] ?? '') === 'O-' ? 'selected' : ''; ?>>O-</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="village" class="form-label">Village</label>
                    <input type="text" class="form-control" id="village" name="village"
                           value="<?php echo $_SESSION['flash']['old']['village'] ?? ''; ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="mobile" class="form-label">Mobile Number *</label>
                    <input type="tel" class="form-control" id="mobile" name="mobile"
                           value="<?php echo $_SESSION['flash']['old']['mobile'] ?? ''; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo $_SESSION['flash']['old']['email'] ?? ''; ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="class_id" class="form-label">Class & Section *</label>
                    <select class="form-control" id="class_id" name="class_id" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"
                                    <?php echo ($_SESSION['flash']['old']['class_id'] ?? '') == $class['id'] ? 'selected' : ''; ?>>
                                <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?php echo $_SESSION['flash']['old']['address'] ?? ''; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="permanent_address" class="form-label">Permanent Address</label>
                <textarea class="form-control" id="permanent_address" name="permanent_address" rows="3"><?php echo $_SESSION['flash']['old']['permanent_address'] ?? ''; ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="aadhar_number" class="form-label">Aadhar Number</label>
                    <input type="text" class="form-control" id="aadhar_number" name="aadhar_number"
                           value="<?php echo $_SESSION['flash']['old']['aadhar_number'] ?? ''; ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="samagra_number" class="form-label">Samagra Number</label>
                    <input type="text" class="form-control" id="samagra_number" name="samagra_number"
                           value="<?php echo $_SESSION['flash']['old']['samagra_number'] ?? ''; ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="apaar_id" class="form-label">Aapaar ID</label>
                    <input type="text" class="form-control" id="apaar_id" name="apaar_id"
                           value="<?php echo $_SESSION['flash']['old']['apaar_id'] ?? ''; ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pan_number" class="form-label">PAN Number</label>
                    <input type="text" class="form-control" id="pan_number" name="pan_number"
                           value="<?php echo $_SESSION['flash']['old']['pan_number'] ?? ''; ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="previous_school" class="form-label">Previous School Name</label>
                    <input type="text" class="form-control" id="previous_school" name="previous_school"
                           value="<?php echo $_SESSION['flash']['old']['previous_school'] ?? ''; ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="father_name" class="form-label">Father's Name</label>
                    <input type="text" class="form-control" id="father_name" name="father_name"
                           value="<?php echo $_SESSION['flash']['old']['father_name'] ?? ''; ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="mother_name" class="form-label">Mother's Name</label>
                    <input type="text" class="form-control" id="mother_name" name="mother_name"
                           value="<?php echo $_SESSION['flash']['old']['mother_name'] ?? ''; ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="guardian_name" class="form-label">Guardian's Name</label>
                    <input type="text" class="form-control" id="guardian_name" name="guardian_name"
                           value="<?php echo $_SESSION['flash']['old']['guardian_name'] ?? ''; ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="guardian_contact" class="form-label">Guardian's Contact Number</label>
                <input type="tel" class="form-control" id="guardian_contact" name="guardian_contact"
                       value="<?php echo $_SESSION['flash']['old']['guardian_contact'] ?? ''; ?>">
            </div>

            <div class="mb-3">
                <label for="medical_conditions" class="form-label">Medical Conditions</label>
                <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="3"><?php echo $_SESSION['flash']['old']['medical_conditions'] ?? ''; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="photo" class="form-label">Student Photo</label>
                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                <div class="form-text">Upload a passport-size photo (JPG, PNG, GIF)</div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="/admin/students" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Student</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('class_id').addEventListener('change', function() {
    const classId = this.value;
    if (classId) {
        fetch(`/admin/get-next-scholar-number?class_id=${classId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('scholar_number').value = data.scholar_number;
                }
            })
            .catch(error => console.error('Error fetching scholar number:', error));
    } else {
        document.getElementById('scholar_number').value = '';
    }
});
</script>

<?php
unset($_SESSION['flash']['old'], $_SESSION['flash']['errors']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>