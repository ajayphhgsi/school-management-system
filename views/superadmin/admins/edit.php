<?php
$active_page = 'admins';
$page_title = 'Edit Admin';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-edit text-primary me-2"></i>
                    <h5 class="mb-0">Edit Administrator</h5>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['flash'])): ?>
                    <?php if ($_SESSION['flash']['type'] === 'error'): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['flash']['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger">
                        <h6>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <form method="POST" action="/superadmin/admins/update/<?php echo $admin['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                   value="<?php echo $_SESSION['old']['first_name'] ?? $admin['first_name']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                   value="<?php echo $_SESSION['old']['last_name'] ?? $admin['last_name']; ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?php echo $_SESSION['old']['username'] ?? $admin['username']; ?>" required>
                            <div class="form-text">Unique username for login</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo $_SESSION['old']['email'] ?? $admin['email']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                               value="<?php echo $_SESSION['old']['phone'] ?? $admin['phone']; ?>">
                        <div class="form-text">Optional contact number</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   <?php echo ($_SESSION['old']['is_active'] ?? $admin['is_active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Active Account
                            </label>
                        </div>
                        <div class="form-text">Inactive admins cannot log in to the system</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/superadmin/admins" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Admins
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
unset($_SESSION['old']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>