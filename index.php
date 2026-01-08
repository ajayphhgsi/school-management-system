<?php
/**
 * School Management System - Main Entry Point
 * Version 1.0.0
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Define constants
define('BASE_PATH', __DIR__ . '/');
define('APP_PATH', BASE_PATH . 'app/');
define('CORE_PATH', BASE_PATH . 'core/');
define('CONFIG_PATH', BASE_PATH . 'config/');
define('CONTROLLERS_PATH', BASE_PATH . 'controllers/');
define('MODELS_PATH', BASE_PATH . 'models/');
define('VIEWS_PATH', BASE_PATH . 'views/');
define('UPLOADS_PATH', BASE_PATH . 'uploads/');

// Include autoloader (if using Composer)
if (file_exists(BASE_PATH . 'vendor/autoload.php')) {
    require_once BASE_PATH . 'vendor/autoload.php';
}

// Include core files
require_once CORE_PATH . 'Database.php';
require_once CORE_PATH . 'Router.php';
require_once CORE_PATH . 'Security.php';
require_once CORE_PATH . 'Session.php';
require_once CORE_PATH . 'Validator.php';

// Include base controller
require_once CONTROLLERS_PATH . 'Controller.php';

// Include middleware
require_once BASE_PATH . 'middleware/Auth.php';
require_once BASE_PATH . 'middleware/Student.php';
require_once BASE_PATH . 'middleware/Admin.php';
require_once BASE_PATH . 'middleware/SuperAdmin.php';

// Load configuration
$config = require CONFIG_PATH . 'app.php';

// Initialize core components
$database = new Database();
$router = new Router();
$security = new Security();
$session = new Session();

// Define routes
// Public routes
$router->get('/', 'PublicController@index');
$router->get('/about', 'PublicController@about');
$router->get('/courses', 'PublicController@courses');
$router->get('/events', 'PublicController@events');
$router->get('/gallery', 'PublicController@gallery');
$router->get('/contact', 'PublicController@contact');
$router->post('/contact', 'PublicController@contact');

// Admin routes
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/students', 'AdminController@students');
$router->get('/admin/students/print', 'AdminController@printStudents');
$router->get('/admin/students/create', 'AdminController@createStudent');
$router->get('/admin/students/add', 'AdminController@addStudent');
$router->get('/admin/students/add/{id}', 'AdminController@addStudent');
$router->post('/admin/students', 'AdminController@storeStudent');
$router->get('/admin/students/view/{id}', 'AdminController@viewStudent');
$router->get('/admin/students/edit/{id}', 'AdminController@editStudent');
$router->post('/admin/students/update/{id}', 'AdminController@updateStudent');
$router->get('/admin/students/delete/{id}', 'AdminController@deleteStudent');
$router->get('/admin/students/bulk-import', 'AdminController@bulkImportStudents');
$router->post('/admin/students/process-bulk-import', 'AdminController@processBulkImportStudents');
$router->get('/admin/students/bulk-export', 'AdminController@bulkExportStudents');
$router->get('/admin/get-next-scholar-number', 'AdminController@getNextScholarNumber');
$router->get('/admin/classes', 'AdminController@classes');
$router->get('/admin/classes/create', 'AdminController@createClass');
$router->post('/admin/classes', 'AdminController@storeClass');
$router->get('/admin/classes/edit/{id}', 'AdminController@editClass');
$router->post('/admin/classes/update/{id}', 'AdminController@updateClass');
$router->get('/admin/classes/delete/{id}', 'AdminController@deleteClass');
$router->get('/admin/classes/{id}/stats', 'AdminController@getClassStats');
$router->get('/admin/classes/promote', 'AdminController@promoteStudents');
$router->post('/admin/classes/promote', 'AdminController@processPromotion');
$router->get('/admin/subjects', 'AdminController@classes');
$router->get('/admin/subjects/create', 'AdminController@createSubject');
$router->post('/admin/subjects', 'AdminController@storeSubject');
$router->get('/admin/subjects/edit/{id}', 'AdminController@editSubject');
$router->post('/admin/subjects/update/{id}', 'AdminController@updateSubject');
$router->get('/admin/subjects/delete/{id}', 'AdminController@deleteSubject');
$router->get('/admin/attendance', 'AdminController@attendance');
$router->get('/admin/attendance/data', 'AdminController@attendanceData');
$router->post('/admin/attendance/save', 'AdminController@saveAttendance');
$router->get('/admin/attendance/export', 'AdminController@exportAttendance');

// Certificate Management
$router->get('/admin/certificates', 'AdminController@certificates');
$router->get('/admin/certificates/students', 'AdminController@getCertificateStudents');
$router->post('/admin/certificates/generate', 'AdminController@generateCertificate');
$router->get('/admin/certificates/tc', 'AdminController@tcCertificates');
$router->get('/admin/certificates/character', 'AdminController@characterCertificates');
$router->get('/admin/certificates/bonafide', 'AdminController@bonafideCertificates');
$router->get('/admin/certificates/view/{id}', 'AdminController@viewCertificate');
$router->get('/admin/certificates/print/{id}', 'AdminController@printCertificate');
$router->post('/admin/certificates/re-administer/{id}', 'AdminController@reAdministerStudent');
$router->get('/admin/certificates/print-tc', 'AdminController@printTC');

$router->get('/admin/fees', 'AdminController@fees');
$router->get('/admin/fees/export', 'AdminController@exportFees');
$router->get('/admin/fees/create', 'AdminController@createFee');
$router->post('/admin/fees/store', 'AdminController@storeFee');
$router->get('/admin/fees/students', 'AdminController@getStudentsForFees');
$router->post('/admin/fees/bulk-assign', 'AdminController@bulkAssignFees');

// Expense Management
$router->get('/admin/expenses', 'AdminController@expenses');
$router->get('/admin/expenses/create', 'AdminController@createExpense');
$router->post('/admin/expenses/store', 'AdminController@storeExpense');
$router->get('/admin/expenses/export', 'AdminController@exportExpenses');

// Notifications
$router->get('/admin/notifications', 'AdminController@notifications');
$router->get('/admin/notifications/view', 'AdminController@viewNotifications');
$router->post('/admin/notifications/mark-read', 'AdminController@markNotificationRead');
$router->get('/admin/notifications/get-students-for-notifications', 'AdminController@getStudentsForNotifications');
$router->post('/admin/notifications/send', 'AdminController@sendNotification');

$router->get('/admin/events', 'AdminController@events');
$router->get('/admin/gallery', 'AdminController@gallery');
$router->get('/admin/reports', 'AdminController@reports');

// Report Generation Routes
$router->get('/admin/generate-student-report', 'AdminController@generateStudentReport');
$router->get('/admin/generate-financial-report', 'AdminController@generateFinancialReport');
$router->get('/admin/generate-attendance-report', 'AdminController@generateAttendanceReport');
$router->get('/admin/generate-academic-report', 'AdminController@generateAcademicReport');
$router->post('/admin/generate-custom-report', 'AdminController@generateCustomReport');

$router->get('/admin/settings', 'AdminController@settings');
$router->post('/admin/settings', 'AdminController@saveSettings');

// Homepage Management
$router->get('/admin/homepage', 'AdminController@homepage');
$router->get('/admin/homepage/carousel', 'AdminController@homepageCarousel');
$router->post('/admin/homepage/carousel', 'AdminController@saveHomepageCarousel');
$router->get('/admin/homepage/about', 'AdminController@homepageAbout');
$router->post('/admin/homepage/about', 'AdminController@saveHomepageAbout');

// SuperAdmin routes
$router->get('/superadmin/dashboard', 'SuperAdminController@dashboard');
$router->get('/superadmin/admins', 'SuperAdminController@manageAdmins');
$router->get('/superadmin/admins/create', 'SuperAdminController@createAdmin');
$router->post('/superadmin/admins/store', 'SuperAdminController@storeAdmin');
$router->get('/superadmin/admins/edit/{id}', 'SuperAdminController@editAdmin');
$router->post('/superadmin/admins/update/{id}', 'SuperAdminController@updateAdmin');
$router->get('/superadmin/admins/delete/{id}', 'SuperAdminController@deleteAdmin');
$router->get('/superadmin/academic-years', 'SuperAdminController@manageAcademicYears');
$router->get('/superadmin/academic-years/create', 'SuperAdminController@createAcademicYear');
$router->post('/superadmin/academic-years/store', 'SuperAdminController@storeAcademicYear');
$router->get('/superadmin/academic-years/edit/{id}', 'SuperAdminController@editAcademicYear');
$router->post('/superadmin/academic-years/update/{id}', 'SuperAdminController@updateAcademicYear');
$router->get('/superadmin/academic-years/delete/{id}', 'SuperAdminController@deleteAcademicYear');

// Student routes
$router->get('/student/dashboard', 'StudentController@dashboard');
$router->get('/student/profile', 'StudentController@profile');
$router->post('/student/profile', 'StudentController@updateProfile');
$router->get('/student/attendance', 'StudentController@attendance');
$router->get('/student/results', 'StudentController@results');
$router->get('/student/fees', 'StudentController@fees');
$router->get('/student/events', 'StudentController@events');
$router->get('/student/resources', 'StudentController@resources');
$router->get('/student/change-password', 'StudentController@changePassword');
$router->post('/student/change-password', 'StudentController@updatePassword');

// Admin Student routes
$router->get('/admin/students/print-application/{id}', 'AdminController@printStudentApplication');

// API routes
$router->post('/api/v1/auth/login', 'ApiController@login');
$router->get('/api/v1/students', 'ApiController@getStudents');
$router->get('/api/v1/students/{id}', 'ApiController@getStudent');
$router->get('/api/v1/fees', 'ApiController@getFees');
$router->get('/api/v1/students/{id}/fees', 'ApiController@getStudentFees');
$router->get('/api/v1/attendance', 'ApiController@getAttendance');
$router->get('/api/v1/reports', 'ApiController@getReports');

// Auth routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/select-academic-year', 'AuthController@selectAcademicYear');
$router->post('/select-academic-year', 'AuthController@selectAcademicYear');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/setup-2fa', 'AuthController@setup2FA');
$router->post('/setup-2fa', 'AuthController@setup2FA');
$router->get('/verify-2fa', 'AuthController@verify2FA');
$router->post('/verify-2fa', 'AuthController@verify2FA');
$router->post('/disable-2fa', 'AuthController@disable2FA');

// Handle routing
$router->dispatch();
?>