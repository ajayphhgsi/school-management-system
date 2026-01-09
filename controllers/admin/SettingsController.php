<?php
/**
 * Admin Settings Controller
 */

class SettingsController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
    }

    public function settings() {
        $settings = $this->db->select("SELECT * FROM settings ORDER BY setting_key");
        $user = $_SESSION['user'] ?? null;

        // Convert settings array to associative array for easier access
        $settingsAssoc = [];
        foreach ($settings as $setting) {
            $settingsAssoc[$setting['setting_key']] = $setting['setting_value'];
        }

        $csrfToken = $this->csrfToken();
        $this->render('admin/settings/index', ['settings' => $settingsAssoc, 'user' => $user, 'csrf_token' => $csrfToken]);
    }

    public function saveSettings() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/settings');
        }

        // Handle logo upload
        $logoPath = '';
        if (isset($_FILES['school_logo']) && $_FILES['school_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOADS_PATH . 'settings/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'school_logo_' . time() . '_' . basename($_FILES['school_logo']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['school_logo']['tmp_name'], $targetFile)) {
                $logoPath = 'settings/' . $fileName;
            } else {
                $this->session->setFlash('error', 'Failed to upload logo file.');
                $this->redirect('/admin/settings');
            }
        }

        // Define all possible settings
        $settingKeys = [
            'site_name', 'timezone', 'language', 'date_format',
            'school_name', 'school_code', 'school_address', 'school_phone', 'school_email',
            'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'from_email',
            'session_timeout', 'password_min_length', 'two_factor_auth', 'password_expiry',
            'scholar_auto_generate', 'school_logo', 'tc_prefix', 'tc_start_number'
        ];

        // Save each setting
        foreach ($settingKeys as $key) {
            $value = $data[$key] ?? '';
            if ($key === 'school_logo' && $logoPath) {
                $value = $logoPath;
            }

            // Check if setting exists
            $existing = $this->db->selectOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
            if ($existing) {
                $this->db->update('settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
            } else {
                $this->db->insert('settings', ['setting_key' => $key, 'setting_value' => $value, 'setting_type' => 'string']);
            }
        }

        $this->session->setFlash('success', 'Settings saved successfully');
        $this->redirect('/admin/settings');
    }
}