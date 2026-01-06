-- School Management System Database Schema
-- Version 1.0.0

-- User roles and permissions
CREATE TABLE user_roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_name TEXT UNIQUE NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role_id INTEGER NOT NULL,
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
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES user_roles(id)
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

-- Classes table
CREATE TABLE classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_name TEXT NOT NULL,
    section TEXT,
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
    FOREIGN KEY (marked_by) REFERENCES users(id),
    UNIQUE (student_id, attendance_date)
);

-- Grading scales table
CREATE TABLE grading_scales (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    grade_name TEXT NOT NULL, -- 'A+', 'A', 'B'
    min_percentage REAL,    -- 90.00
    max_percentage REAL,    -- 100.00
    grade_point REAL,       -- 10.0 (For CGPA calculations)
    description TEXT        -- 'Outstanding'
);

-- Academic years table
CREATE TABLE academic_years (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    year_name TEXT UNIQUE NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Fee structures table
CREATE TABLE fee_structures (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER,
    fee_head_name TEXT, -- e.g. "Tuition Fee", "Bus Fee"
    amount REAL,
    academic_year_id INTEGER,
    due_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Fees table (assigned fees to students)
CREATE TABLE fees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER,
    fee_type TEXT,
    amount REAL,
    due_date DATE,
    is_paid INTEGER DEFAULT 0,
    academic_year_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Student fee collections table
CREATE TABLE student_fee_collections (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER,
    fee_structure_id INTEGER,
    amount_paid REAL,
    payment_date DATE,
    payment_mode TEXT,
    transaction_id TEXT,
    payment_gateway TEXT,
    payment_status TEXT,
    refund_amount REAL,
    cheque_number TEXT,
    remarks TEXT,
    collected_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_structure_id) REFERENCES fee_structures(id) ON DELETE CASCADE,
    FOREIGN KEY (collected_by) REFERENCES users(id)
);

-- Expenses table
CREATE TABLE expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    expense_date DATE,
    reason TEXT,
    category TEXT,
    amount REAL,
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
    venue TEXT,
    organizer TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- News table
CREATE TABLE news (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT,
    summary TEXT,
    image_path TEXT,
    published_date DATE,
    author_id INTEGER,
    is_featured INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
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

-- Certificates table
CREATE TABLE certificates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    certificate_type TEXT NOT NULL,
    certificate_number TEXT UNIQUE,
    student_id INTEGER,
    issue_date DATE,
    transfer_reason TEXT,
    conduct TEXT,
    remarks TEXT,
    generated_by INTEGER,
    pdf_path TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(id)
);

-- Notifications table
CREATE TABLE notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    title TEXT NOT NULL,
    message TEXT,
    type TEXT DEFAULT 'info',
    icon TEXT DEFAULT 'fas fa-bell',
    is_read INTEGER DEFAULT 0,
    related_table TEXT,
    related_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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

-- Books table
CREATE TABLE books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    isbn TEXT,
    author TEXT,
    quantity INTEGER DEFAULT 1,
    shelf_number TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Book issues table
CREATE TABLE book_issues (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    book_id INTEGER,
    user_id INTEGER, -- Can be student or teacher
    issue_date DATE,
    due_date DATE,
    return_date DATE,
    fine_amount REAL DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id),
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
INSERT INTO users (username, email, password, role_id, first_name, last_name) VALUES
('admin', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'System', 'Administrator');

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

-- Student optional subjects table
CREATE TABLE student_optional_subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    academic_year_id INTEGER,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Exam types table (replaces exams for better hierarchy)
CREATE TABLE exam_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL, -- e.g., "Half Yearly Examination 2025-26"
    exam_type TEXT DEFAULT 'custom',
    academic_year_id INTEGER,
    start_date DATE,
    end_date DATE,
    is_active INTEGER DEFAULT 1,
    is_published INTEGER DEFAULT 0, -- Controls if students can see results
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Exams table (for backward compatibility)
CREATE TABLE exams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_name TEXT NOT NULL,
    exam_type TEXT DEFAULT 'custom',
    class_id INTEGER,
    start_date DATE,
    end_date DATE,
    is_active INTEGER DEFAULT 1,
    academic_year_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Exam subjects table (now exam schedules linking exam types to classes and subjects)
CREATE TABLE exam_subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_type_id INTEGER,
    subject_id INTEGER,
    class_id INTEGER NOT NULL,
    exam_date DATE,
    exam_day TEXT,
    start_time TIME,
    end_time TIME,
    max_marks REAL,
    pass_marks REAL DEFAULT 33.00,
    max_marks_theory REAL DEFAULT 0,
    max_marks_practical REAL DEFAULT 0,
    pass_marks_theory REAL DEFAULT 0,
    pass_marks_practical REAL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_type_id) REFERENCES exam_types(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

CREATE INDEX idx_exam_subjects_class_id ON exam_subjects(class_id);

-- Exam results table
CREATE TABLE exam_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_type_id INTEGER,
    student_id INTEGER,
    subject_id INTEGER,
    marks_obtained REAL,
    percentage REAL,
    grade TEXT,
    remarks TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_type_id) REFERENCES exam_types(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);


-- Add academic_year_id to classes
ALTER TABLE classes ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to expenses
ALTER TABLE expenses ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to attendance
ALTER TABLE attendance ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to exam_results
ALTER TABLE exam_results ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to events
ALTER TABLE events ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add academic_year_id to certificates
ALTER TABLE certificates ADD COLUMN academic_year_id INTEGER REFERENCES academic_years(id);

-- Add tc_issued field to students table for TC system
ALTER TABLE students ADD COLUMN tc_issued INTEGER DEFAULT 0;

-- Insert default academic year
INSERT INTO academic_years (year_name, start_date, end_date) VALUES ('2024-2025', '2024-04-01', '2025-03-31');

-- Add 2FA columns to users (already added in table definition)
-- ALTER TABLE users ADD COLUMN 2fa_secret TEXT, ADD COLUMN 2fa_enabled INTEGER DEFAULT 0;