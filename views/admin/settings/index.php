<?php
$active_page = 'settings';
$page_title = 'System Settings';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-cog text-primary me-2"></i>System Settings</h4>
        <p class="text-muted mb-0">Configure system preferences and settings</p>
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

<!-- Settings Form -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">System Settings</h5>
    </div>
    <div class="card-body">
        <form method="post" action="/admin/settings" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- General Settings -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-primary"><i class="fas fa-globe me-2"></i>General Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'School Management System') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select class="form-select" id="timezone" name="timezone">
                                <option value="UTC" <?= ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                <option value="Asia/Kolkata" <?= ($settings['timezone'] ?? '') === 'Asia/Kolkata' ? 'selected' : '' ?>>Asia/Kolkata</option>
                                <option value="America/New_York" <?= ($settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>Eastern Time</option>
                                <option value="America/Chicago" <?= ($settings['timezone'] ?? '') === 'America/Chicago' ? 'selected' : '' ?>>Central Time</option>
                                <option value="America/Denver" <?= ($settings['timezone'] ?? '') === 'America/Denver' ? 'selected' : '' ?>>Mountain Time</option>
                                <option value="America/Los_Angeles" <?= ($settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="language" class="form-label">Default Language</label>
                            <select class="form-select" id="language" name="language">
                                <option value="en" <?= ($settings['language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                                <option value="es" <?= ($settings['language'] ?? '') === 'es' ? 'selected' : '' ?>>Spanish</option>
                                <option value="fr" <?= ($settings['language'] ?? '') === 'fr' ? 'selected' : '' ?>>French</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_format" class="form-label">Date Format</label>
                            <select class="form-select" id="date_format" name="date_format">
                                <option value="Y-m-d" <?= ($settings['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                <option value="m/d/Y" <?= ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                <option value="d/m/Y" <?= ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="school_logo" class="form-label">School Logo</label>
                        <input type="file" class="form-control" id="school_logo" name="school_logo" accept="image/*">
                        <small class="form-text text-muted">Upload a new school logo (PNG, JPG, GIF). Max size: 2MB</small>
                        <?php if (!empty($settings['school_logo'])): ?>
                            <div class="mt-2">
                                <img src="/uploads/<?= htmlspecialchars($settings['school_logo']) ?>" alt="Current Logo" style="max-height: 50px;">
                                <small class="text-muted">Current logo</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- School Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-primary"><i class="fas fa-school me-2"></i>School Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="school_name" class="form-label">School Name</label>
                            <input type="text" class="form-control" id="school_name" name="school_name" value="<?= htmlspecialchars($settings['school_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="school_code" class="form-label">School Code</label>
                            <input type="text" class="form-control" id="school_code" name="school_code" value="<?= htmlspecialchars($settings['school_code'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="school_address" class="form-label">Address</label>
                        <textarea class="form-control" id="school_address" name="school_address" rows="3"><?= htmlspecialchars($settings['school_address'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="school_phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="school_phone" name="school_phone" value="<?= htmlspecialchars($settings['school_phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="school_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="school_email" name="school_email" value="<?= htmlspecialchars($settings['school_email'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-primary"><i class="fas fa-envelope me-2"></i>Email Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="smtp_host" class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="smtp_port" class="form-label">SMTP Port</label>
                            <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="smtp_user" class="form-label">SMTP Username</label>
                            <input type="text" class="form-control" id="smtp_user" name="smtp_user" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="smtp_pass" class="form-label">SMTP Password</label>
                            <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="from_email" class="form-label">From Email</label>
                        <input type="email" class="form-control" id="from_email" name="from_email" value="<?= htmlspecialchars($settings['from_email'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-primary"><i class="fas fa-shield-alt me-2"></i>Security Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                            <input type="number" class="form-control" id="session_timeout" name="session_timeout" value="<?= htmlspecialchars($settings['session_timeout'] ?? '60') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_min_length" class="form-label">Minimum Password Length</label>
                            <input type="number" class="form-control" id="password_min_length" name="password_min_length" value="<?= htmlspecialchars($settings['password_min_length'] ?? '8') ?>">
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="two_factor_auth" name="two_factor_auth" value="1" <?= ($settings['two_factor_auth'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="two_factor_auth">Enable Two-Factor Authentication</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="password_expiry" name="password_expiry" value="1" <?= ($settings['password_expiry'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="password_expiry">Enable Password Expiry (90 days)</label>
                    </div>
                </div>
            </div>

            <!-- Automation Settings -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-primary"><i class="fas fa-robot me-2"></i>Automation Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="scholar_auto_generate" name="scholar_auto_generate" value="1" <?= ($settings['scholar_auto_generate'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="scholar_auto_generate">Enable Automatic Scholar Number Generation</label>
                        <small class="form-text text-muted">When enabled, scholar numbers will be auto-generated based on class type (N for Nursery/UKG, P for Primary, S for Secondary).</small>
                    </div>
                </div>
            </div>

            <!-- Certificate Settings -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-primary"><i class="fas fa-certificate me-2"></i>Certificate Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tc_prefix" class="form-label">Transfer Certificate Prefix</label>
                            <input type="text" class="form-control" id="tc_prefix" name="tc_prefix" value="<?= htmlspecialchars($settings['tc_prefix'] ?? 'TC') ?>" maxlength="10">
                            <small class="form-text text-muted">Prefix for transfer certificate numbers (e.g., TC, TRANS)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tc_start_number" class="form-label">TC Starting Number</label>
                            <input type="number" class="form-control" id="tc_start_number" name="tc_start_number" value="<?= htmlspecialchars($settings['tc_start_number'] ?? '1') ?>" min="1">
                            <small class="form-text text-muted">Starting number for transfer certificates</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save All Settings</button>
            </div>
        </form>

        <!-- Two-Factor Authentication Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Two-Factor Authentication</h6>
            </div>
            <div class="card-body">
                <?php if (isset($user['2fa_enabled']) && $user['2fa_enabled']): ?>
                    <p>2FA is enabled for your account.</p>
                    <form method="post" action="/disable-2fa">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="btn btn-danger">Disable 2FA</button>
                    </form>
                <?php else: ?>
                    <p>2FA is not enabled. <a href="/setup-2fa" class="btn btn-primary">Setup 2FA</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>