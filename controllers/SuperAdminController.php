<?php
/**
 * SuperAdmin Controller - SuperAdmin Panel Management
 */

class SuperAdminController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('superadmin');
    }

    public function dashboard() {
        // Get dashboard statistics for superadmin
        $stats = [
            'total_admins' => $this->db->selectOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND is_active = 1")['count'],
            'total_superadmins' => $this->db->selectOne("SELECT COUNT(*) as count FROM users WHERE role = 'superadmin' AND is_active = 1")['count'],
            'total_academic_years' => $this->db->selectOne("SELECT COUNT(*) as count FROM academic_years WHERE is_active = 1")['count'],
            'active_academic_year' => $this->db->selectOne("SELECT year_name FROM academic_years WHERE is_active = 1 ORDER BY start_date DESC LIMIT 1")['year_name'] ?? 'None'
        ];

        $this->render('superadmin/dashboard', ['stats' => $stats]);
    }

    public function manageAdmins() {
        $admins = $this->db->select("SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC");
        $csrfToken = $this->csrfToken();
        $this->render('superadmin/admins/index', ['admins' => $admins, 'csrf_token' => $csrfToken]);
    }

    public function createAdmin() {
        $csrfToken = $this->csrfToken();
        $this->render('superadmin/admins/create', ['csrf_token' => $csrfToken]);
    }

    public function storeAdmin() {
        $data = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/superadmin/admins/create');
        }

        $rules = [
            'username' => 'required|unique:users,username|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'phone' => 'regex:/^[0-9+\-\s()]{10,15}$/'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/superadmin/admins/create');
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $adminData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => 'admin',
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'is_active' => 1
        ];

        $adminId = $this->db->insert('users', $adminData);

        if ($adminId) {
            $this->session->setFlash('success', 'Admin created successfully');
            $this->redirect('/superadmin/admins');
        } else {
            $this->session->setFlash('error', 'Failed to create admin');
            $this->redirect('/superadmin/admins/create');
        }
    }

    public function editAdmin($id) {
        $admin = $this->db->selectOne("SELECT * FROM users WHERE id = ? AND role = 'admin'", [$id]);
        if (!$admin) {
            $this->session->setFlash('error', 'Admin not found');
            $this->redirect('/superadmin/admins');
        }

        $csrfToken = $this->csrfToken();
        $this->render('superadmin/admins/edit', ['admin' => $admin, 'csrf_token' => $csrfToken]);
    }

    public function updateAdmin($id) {
        $admin = $this->db->selectOne("SELECT * FROM users WHERE id = ? AND role = 'admin'", [$id]);
        if (!$admin) {
            $this->session->setFlash('error', 'Admin not found');
            $this->redirect('/superadmin/admins');
        }

        $data = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/superadmin/admins/edit/' . $id);
        }

        $rules = [
            'username' => 'required|unique:users,username,' . $id . '|min:3|max:50',
            'email' => 'required|email|unique:users,email,' . $id,
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'phone' => 'regex:/^[0-9+\-\s()]{10,15}$/'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/superadmin/admins/edit/' . $id);
        }

        $adminData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'is_active' => $data['is_active']
        ];

        $updated = $this->db->update('users', $adminData, 'id = ?', [$id]);

        if ($updated) {
            $this->session->setFlash('success', 'Admin updated successfully');
            $this->redirect('/superadmin/admins');
        } else {
            $this->session->setFlash('error', 'Failed to update admin');
            $this->redirect('/superadmin/admins/edit/' . $id);
        }
    }

    public function deleteAdmin($id) {
        $admin = $this->db->selectOne("SELECT * FROM users WHERE id = ? AND role = 'admin'", [$id]);
        if (!$admin) {
            $this->session->setFlash('error', 'Admin not found');
            $this->redirect('/superadmin/admins');
        }

        $deleted = $this->db->delete('users', 'id = ?', [$id]);

        if ($deleted) {
            $this->session->setFlash('success', 'Admin deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete admin');
        }

        $this->redirect('/superadmin/admins');
    }

    public function manageAcademicYears() {
        $academicYears = $this->db->select("SELECT * FROM academic_years ORDER BY start_date DESC");
        $csrfToken = $this->csrfToken();
        $this->render('superadmin/academic-years/index', ['academic_years' => $academicYears, 'csrf_token' => $csrfToken]);
    }

    public function createAcademicYear() {
        $csrfToken = $this->csrfToken();
        $this->render('superadmin/academic-years/create', ['csrf_token' => $csrfToken]);
    }

    public function storeAcademicYear() {
        $data = [
            'year_name' => $_POST['year_name'] ?? '',
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/superadmin/academic-years/create');
        }

        $rules = [
            'year_name' => 'required|unique:academic_years,year_name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/superadmin/academic-years/create');
        }

        $academicYearData = [
            'year_name' => $data['year_name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => 0
        ];

        $academicYearId = $this->db->insert('academic_years', $academicYearData);

        if ($academicYearId) {
            $this->session->setFlash('success', 'Academic year created successfully');
            $this->redirect('/superadmin/academic-years');
        } else {
            $this->session->setFlash('error', 'Failed to create academic year');
            $this->redirect('/superadmin/academic-years/create');
        }
    }

    public function editAcademicYear($id) {
        $academicYear = $this->db->selectOne("SELECT * FROM academic_years WHERE id = ?", [$id]);
        if (!$academicYear) {
            $this->session->setFlash('error', 'Academic year not found');
            $this->redirect('/superadmin/academic-years');
        }

        $csrfToken = $this->csrfToken();
        $this->render('superadmin/academic-years/edit', ['academic_year' => $academicYear, 'csrf_token' => $csrfToken]);
    }

    public function updateAcademicYear($id) {
        $academicYear = $this->db->selectOne("SELECT * FROM academic_years WHERE id = ?", [$id]);
        if (!$academicYear) {
            $this->session->setFlash('error', 'Academic year not found');
            $this->redirect('/superadmin/academic-years');
        }

        $data = [
            'year_name' => $_POST['year_name'] ?? '',
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/superadmin/academic-years/edit/' . $id);
        }

        $rules = [
            'year_name' => 'required|unique:academic_years,year_name,' . $id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/superadmin/academic-years/edit/' . $id);
        }

        // If setting as active, deactivate others
        if ($data['is_active']) {
            $this->db->update('academic_years', ['is_active' => 0], 'is_active = 1');
        }

        $academicYearData = [
            'year_name' => $data['year_name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => $data['is_active']
        ];

        $updated = $this->db->update('academic_years', $academicYearData, 'id = ?', [$id]);

        if ($updated) {
            $this->session->setFlash('success', 'Academic year updated successfully');
            $this->redirect('/superadmin/academic-years');
        } else {
            $this->session->setFlash('error', 'Failed to update academic year');
            $this->redirect('/superadmin/academic-years/edit/' . $id);
        }
    }

    public function deleteAcademicYear($id) {
        $academicYear = $this->db->selectOne("SELECT * FROM academic_years WHERE id = ?", [$id]);
        if (!$academicYear) {
            $this->session->setFlash('error', 'Academic year not found');
            $this->redirect('/superadmin/academic-years');
        }

        // Check if academic year is being used
        $usageCount = $this->db->selectOne("
            SELECT COUNT(*) as count FROM (
                SELECT id FROM classes WHERE academic_year_id = ?
                UNION ALL
                SELECT id FROM fees WHERE academic_year_id = ?
                UNION ALL
                SELECT id FROM exams WHERE academic_year_id = ?
            ) as usage
        ", [$id, $id, $id])['count'];

        if ($usageCount > 0) {
            $this->session->setFlash('error', 'Cannot delete academic year as it is being used by existing records');
            $this->redirect('/superadmin/academic-years');
        }

        $deleted = $this->db->delete('academic_years', 'id = ?', [$id]);

        if ($deleted) {
            $this->session->setFlash('success', 'Academic year deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete academic year');
        }

        $this->redirect('/superadmin/academic-years');
    }
}