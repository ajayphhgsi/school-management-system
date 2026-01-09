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
$router->get('/admin/dashboard', 'admin/DashboardController@dashboard');
$router->get('/admin/students', 'admin/StudentController@students');
$router->get('/admin/students/print', 'admin/StudentController@printStudents');
$router->get('/admin/students/create', 'admin/StudentController@createStudent');
$router->get('/admin/students/add', 'admin/StudentController@addStudent');
$router->get('/admin/students/add/{id}', 'admin/StudentController@addStudent');
$router->post('/admin/students', 'admin/StudentController@storeStudent');
$router->get('/admin/students/view/{id}', 'admin/StudentController@viewStudent');
$router->get('/admin/students/edit/{id}', 'admin/StudentController@editStudent');
$router->post('/admin/students/update/{id}', 'admin/StudentController@updateStudent');
$router->get('/admin/students/delete/{id}', 'admin/StudentController@deleteStudent');
$router->get('/admin/students/bulk-import', 'admin/StudentController@bulkImportStudents');
$router->post('/admin/students/process-bulk-import', 'admin/StudentController@processBulkImportStudents');
$router->get('/admin/students/bulk-export', 'admin/StudentController@bulkExportStudents');
$router->get('/admin/get-next-scholar-number', 'admin/StudentController@getNextScholarNumber');
$router->get('/admin/classes', 'admin/ClassController@classes');
$router->get('/admin/classes/create', 'admin/ClassController@createClass');
$router->post('/admin/classes', 'admin/ClassController@storeClass');
$router->get('/admin/classes/edit/{id}', 'admin/ClassController@editClass');
$router->post('/admin/classes/update/{id}', 'admin/ClassController@updateClass');
$router->get('/admin/classes/delete/{id}', 'admin/ClassController@deleteClass');
$router->get('/admin/classes/{id}/stats', 'admin/ClassController@getClassStats');
$router->get('/admin/classes/promote', 'admin/ClassController@promoteStudents');
$router->post('/admin/classes/promote', 'admin/ClassController@processPromotion');
$router->get('/admin/subjects', 'admin/ClassController@subjects');
$router->get('/admin/subjects/create', 'admin/ClassController@createSubject');
$router->post('/admin/subjects', 'admin/ClassController@storeSubject');
$router->get('/admin/subjects/edit/{id}', 'admin/ClassController@editSubject');
$router->post('/admin/subjects/update/{id}', 'admin/ClassController@updateSubject');
$router->get('/admin/subjects/delete/{id}', 'admin/ClassController@deleteSubject');
$router->get('/admin/attendance', 'admin/AttendanceController@attendance');
$router->get('/admin/attendance/data', 'admin/AttendanceController@attendanceData');
$router->post('/admin/attendance/save', 'admin/AttendanceController@saveAttendance');
$router->get('/admin/attendance/export', 'admin/AttendanceController@exportAttendance');

// Certificate Management
$router->get('/admin/certificates', 'admin/CertificateController@certificates');
$router->get('/admin/certificates/students', 'admin/CertificateController@getCertificateStudents');
$router->post('/admin/certificates/generate', 'admin/CertificateController@generateCertificate');
$router->get('/admin/certificates/tc', 'admin/CertificateController@tcCertificates');
$router->get('/admin/certificates/character', 'admin/CertificateController@characterCertificates');
$router->get('/admin/certificates/bonafide', 'admin/CertificateController@bonafideCertificates');
$router->get('/admin/certificates/view/{id}', 'admin/CertificateController@viewCertificate');
$router->get('/admin/certificates/print/{id}', 'admin/CertificateController@printCertificate');
$router->post('/admin/certificates/re-administer/{id}', 'admin/CertificateController@reAdministerStudent');
$router->get('/admin/certificates/print-tc', 'admin/CertificateController@printTC');

$router->get('/admin/fees', 'admin/FeeController@fees');
$router->get('/admin/fees/export', 'admin/FeeController@exportFees');
$router->get('/admin/fees/create', 'admin/FeeController@createFee');
$router->post('/admin/fees/store', 'admin/FeeController@storeFee');
$router->get('/admin/fees/students', 'admin/FeeController@getStudentsForFees');
$router->post('/admin/fees/bulk-assign', 'admin/FeeController@bulkAssignFees');
$router->post('/admin/payments/initiate', 'admin/PaymentController@initiatePayment');
$router->post('/admin/payments/process', 'admin/PaymentController@processPayment');
$router->post('/admin/payments/refund', 'admin/PaymentController@refundPayment');
$router->get('/admin/payments/status', 'admin/PaymentController@getPaymentStatus');

// Expense Management
$router->get('/admin/expenses', 'admin/ExpenseController@expenses');
$router->get('/admin/expenses/create', 'admin/ExpenseController@createExpense');
$router->post('/admin/expenses/store', 'admin/ExpenseController@storeExpense');
$router->get('/admin/expenses/export', 'admin/ExpenseController@exportExpenses');

// Notifications
$router->get('/admin/notifications', 'admin/NotificationController@notifications');
$router->get('/admin/notifications/view', 'admin/NotificationController@viewNotifications');
$router->post('/admin/notifications/mark-read', 'admin/NotificationController@markNotificationRead');
$router->get('/admin/notifications/get-students-for-notifications', 'admin/NotificationController@getStudentsForNotifications');
$router->post('/admin/notifications/send', 'admin/NotificationController@sendNotification');

$router->get('/admin/events', 'admin/EventController@events');
$router->get('/admin/gallery', 'admin/GalleryController@gallery');
$router->get('/admin/exams', 'admin/ExamController@index');
$router->get('/admin/exams/create', 'admin/ExamController@createExam');
$router->post('/admin/exams/store', 'admin/ExamController@storeExam');
$router->get('/admin/exams/get-class-subjects', 'admin/ExamController@getClassSubjects');
$router->get('/admin/exams/get-exam-classes', 'admin/ExamController@getExamClasses');
$router->get('/admin/exams/view/{id}', 'admin/ExamController@viewExam');
$router->get('/admin/exams/edit/{id}', 'admin/ExamController@editExam');
$router->post('/admin/exams/update/{id}', 'admin/ExamController@updateExam');
$router->get('/admin/exams/delete/{id}', 'admin/ExamController@deleteExam');
$router->get('/admin/exams/results/{id}', 'admin/ExamController@enterResults');
$router->post('/admin/exams/save-results', 'admin/ExamController@saveResults');
$router->get('/admin/exams/admit-cards', 'admin/ExamController@admitCards');
$router->get('/admin/exams/admit-cards/{id}', 'admin/ExamController@printAdmitCards');
$router->get('/admin/exams/print-admit-card/{examId}/{studentId}', 'admin/ExamController@printAdmitCard');
$router->post('/admin/exams/generate-admit-cards', 'admin/ExamController@generateAdmitCards');
$router->get('/admin/exams/marksheets', 'admin/ExamController@marksheets');
$router->get('/admin/exams/marksheets/{id}', 'admin/ExamController@printMarksheets');
$router->post('/admin/exams/generate-marksheets', 'admin/ExamController@generateMarksheets');
$router->get('/admin/reports', 'admin/ReportController@reports');

// Report Generation Routes
$router->get('/admin/generate-student-report', 'admin/ReportController@generateStudentReport');
$router->get('/admin/generate-financial-report', 'admin/ReportController@generateFinancialReport');
$router->get('/admin/generate-attendance-report', 'admin/ReportController@generateAttendanceReport');
$router->get('/admin/generate-academic-report', 'admin/ReportController@generateAcademicReport');
$router->post('/admin/generate-custom-report', 'admin/ReportController@generateCustomReport');

$router->get('/admin/settings', 'admin/SettingsController@settings');
$router->post('/admin/settings', 'admin/SettingsController@saveSettings');

// Homepage Management
$router->get('/admin/homepage', 'admin/HomepageController@homepage');
$router->get('/admin/homepage/carousel', 'admin/HomepageController@homepageCarousel');
$router->post('/admin/homepage/carousel', 'admin/HomepageController@saveHomepageCarousel');
$router->get('/admin/homepage/about', 'admin/HomepageController@homepageAbout');
$router->post('/admin/homepage/about', 'admin/HomepageController@saveHomepageAbout');

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
$router->get('/admin/students/print-application/{id}', 'admin/StudentController@printStudentApplication');

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