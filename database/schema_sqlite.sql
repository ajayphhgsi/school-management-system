-- School Management System Database Schema (SQLite)
-- Version 1.0.0

-- Users table
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'student',
    first_name TEXT,
    last_name TEXT,
    phone TEXT,
    avatar TEXT,
    remember_token TEXT,
    2fa_secret TEXT,
    2fa_enabled INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- User roles and permissions
CREATE TABLE user_roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_name TEXT UNIQUE NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Permissions table
CREATE TABLE permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    permission_name TEXT UNIQUE NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Role permissions junction
CREATE TABLE role_permissions (
    role_id INTEGER,
    permission_id INTEGER,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES user_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Students table
CREATE TABLE students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    scholar_number TEXT UNIQUE,
    admission_number TEXT UNIQUE,
    admission_date DATE,
    first_name TEXT NOT NULL,
    middle_name TEXT,
    last_name TEXT NOT NULL,
    date_of_birth DATE,
    gender TEXT,
    caste_category TEXT,
    nationality TEXT DEFAULT 'Indian',
    religion TEXT,
    blood_group TEXT,
    village TEXT,
    address TEXT,
    permanent_address TEXT,
    mobile TEXT,
    email TEXT,
    aadhar_number TEXT,
    samagra_number TEXT,
    apaar_id TEXT,
    pan_number TEXT,
    previous_school TEXT,
    medical_conditions TEXT,
    photo TEXT,
    father_name TEXT,
    mother_name TEXT,
    guardian_name TEXT,
    guardian_contact TEXT,
    class_id INTEGER,
    roll_number INTEGER,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Classes table
CREATE TABLE classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_name TEXT NOT NULL,
    section TEXT,
    academic_year TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Subjects table
CREATE TABLE subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_name TEXT NOT NULL,
    subject_code TEXT UNIQUE,
    description TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Class subjects junction
CREATE TABLE class_subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER,
    subject_id INTEGER,
    teacher_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Attendance table
CREATE TABLE attendance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER,
    class_id INTEGER,
    attendance_date DATE,
    status TEXT DEFAULT 'present',
    marked_by INTEGER,
    remarks TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (marked_by) REFERENCES users(id)
);

-- Exams table
CREATE TABLE exams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_name TEXT NOT NULL,
    exam_type TEXT DEFAULT 'custom',
    class_id INTEGER,
    start_date DATE,
    end_date DATE,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Exam results table
CREATE TABLE exam_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_id INTEGER,
    student_id INTEGER,
    subject_id INTEGER,
    marks_obtained DECIMAL(5,2),
    max_marks DECIMAL(5,2),
    grade TEXT,
    percentage DECIMAL(5,2),
    remarks TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- Fees table
CREATE TABLE fees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER,
    fee_type TEXT,
    amount DECIMAL(10,2),
    due_date DATE,
    academic_year TEXT,
    is_paid INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Fee payments table
CREATE TABLE fee_payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fee_id INTEGER,
    amount_paid DECIMAL(10,2),
    payment_date DATE,
    payment_mode TEXT,
    transaction_id TEXT,
    payment_gateway TEXT,
    payment_status TEXT,
    refund_amount DECIMAL(10,2),
    cheque_number TEXT,
    remarks TEXT,
    collected_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE CASCADE,
    FOREIGN KEY (collected_by) REFERENCES users(id)
);

-- Expenses table
CREATE TABLE expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    expense_date DATE,
    reason TEXT,
    category TEXT,
    amount DECIMAL(10,2),
    payment_mode TEXT,
    remarks TEXT,
    recorded_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- Events table
CREATE TABLE events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    event_date DATE,
    event_time TIME,
    location TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Gallery table
CREATE TABLE gallery (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    image_path TEXT,
    category TEXT,
    description TEXT,
    uploaded_by INTEGER,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- News table
CREATE TABLE news (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT,
    published_date DATE,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Homepage content table
CREATE TABLE homepage_content (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    section TEXT,
    title TEXT,
    content TEXT,
    image_path TEXT,
    link TEXT,
    sort_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type TEXT DEFAULT 'string',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Audit logs table
CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT,
    table_name TEXT,
    record_id INTEGER,
    old_values TEXT,
    new_values TEXT,
    ip_address TEXT,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default data
INSERT INTO user_roles (role_name, description) VALUES
('admin', 'Administrator with full access'),
('student', 'Student with limited access');

INSERT INTO permissions (permission_name, description) VALUES
('manage_users', 'Create, edit, delete users'),
('manage_students', 'Manage student records'),
('manage_classes', 'Manage classes and subjects'),
('manage_attendance', 'Mark and view attendance'),
('manage_exams', 'Create and manage exams'),
('manage_fees', 'Manage fee structure and payments'),
('manage_events', 'Manage school events'),
('manage_gallery', 'Manage photo gallery'),
('view_reports', 'View reports and analytics'),
('manage_settings', 'Manage system settings');

INSERT INTO role_permissions (role_id, permission_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role, first_name, last_name) VALUES
('admin', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator');

-- Insert sample settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('school_name', 'School Management System', 'string'),
('school_address', '123 School Street, City, State', 'string'),
('school_phone', '+1-234-567-8900', 'string'),
('school_email', 'info@school.com', 'string'),
('academic_year', '2024-2025', 'string');

-- Migration for superadmin panel support

-- Add superadmin role
INSERT INTO user_roles (role_name, description) VALUES ('superadmin', 'Super administrator with all permissions');

-- Add permission for managing academic years
INSERT INTO permissions (permission_name, description) VALUES ('manage_academic_years', 'Manage academic years');

-- Assign all permissions to superadmin
INSERT INTO role_permissions (role_id, permission_id) SELECT (SELECT id FROM user_roles WHERE role_name = 'superadmin'), id FROM permissions;

-- Create academic_years table
CREATE TABLE academic_years (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    year_name TEXT UNIQUE NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Update default admin to superadmin (role is TEXT, so can set to 'superadmin')
UPDATE users SET role = 'superadmin' WHERE username = 'admin';

-- Add academic_year_id to classes
ALTER TABLE classes ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to fees
ALTER TABLE fees ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to expenses
ALTER TABLE expenses ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to attendance
ALTER TABLE attendance ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to exams
ALTER TABLE exams ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to exam_results
ALTER TABLE exam_results ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add percentage column to exam_results
ALTER TABLE exam_results ADD COLUMN percentage DECIMAL(5,2);

-- Add academic_year_id to events
ALTER TABLE events ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to certificates
ALTER TABLE certificates ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Insert default academic year
INSERT INTO academic_years (year_name, start_date, end_date) VALUES ('2024-2025', '2024-04-01', '2025-03-31');

-- Add 2FA columns to users
ALTER TABLE users ADD COLUMN remember_token TEXT;
ALTER TABLE users ADD COLUMN 2fa_secret TEXT;
ALTER TABLE users ADD COLUMN 2fa_enabled INTEGER DEFAULT 0;