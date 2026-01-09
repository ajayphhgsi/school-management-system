-- School Management System Database Schema
-- Version 1.0.0

CREATE DATABASE IF NOT EXISTS school_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_management;

-- User roles and permissions
CREATE TABLE user_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    avatar VARCHAR(255),
    remember_token VARCHAR(255),
    2fa_secret VARCHAR(255),
    2fa_enabled BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES user_roles(id)
);

-- Permissions table
CREATE TABLE permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    permission_name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Role permissions junction
CREATE TABLE role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES user_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Classes table
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(50) NOT NULL,
    section VARCHAR(10),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subjects table
CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Class subjects junction
CREATE TABLE class_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT,
    subject_id INT,
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    scholar_number VARCHAR(20) UNIQUE,
    admission_number VARCHAR(20) UNIQUE,
    admission_date DATE,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    caste_category VARCHAR(50),
    nationality VARCHAR(50) DEFAULT 'Indian',
    religion VARCHAR(50),
    blood_group VARCHAR(10),
    village VARCHAR(100),
    address TEXT,
    permanent_address TEXT,
    mobile VARCHAR(20),
    email VARCHAR(100),
    aadhar_number VARCHAR(20),
    samagra_number VARCHAR(20),
    apaar_id VARCHAR(20),
    pan_number VARCHAR(20),
    previous_school VARCHAR(100),
    medical_conditions TEXT,
    photo VARCHAR(255),
    father_name VARCHAR(100),
    mother_name VARCHAR(100),
    guardian_name VARCHAR(100),
    guardian_contact VARCHAR(20),
    class_id INT,
    roll_number INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Attendance table
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    class_id INT,
    attendance_date DATE,
    status ENUM('present', 'absent', 'late') DEFAULT 'present',
    marked_by INT,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (marked_by) REFERENCES users(id),
    UNIQUE KEY unique_attendance (student_id, attendance_date)
);

-- Grading scales table
CREATE TABLE grading_scales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    grade_name VARCHAR(5) NOT NULL, -- 'A+', 'A', 'B'
    min_percentage DECIMAL(5,2),    -- 90.00
    max_percentage DECIMAL(5,2),    -- 100.00
    grade_point DECIMAL(4,2),       -- 10.0 (For CGPA calculations)
    description VARCHAR(100)        -- 'Outstanding'
);

-- Academic years table
CREATE TABLE academic_years (
    id INT PRIMARY KEY AUTO_INCREMENT,
    year_name VARCHAR(20) UNIQUE NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fee structures table
CREATE TABLE fee_structures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT,
    fee_head_name VARCHAR(100), -- e.g. "Tuition Fee", "Bus Fee"
    amount DECIMAL(10,2),
    academic_year_id INT,
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Fees table (assigned fees to students)
CREATE TABLE fees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    fee_type VARCHAR(100),
    amount DECIMAL(10,2),
    due_date DATE,
    is_paid BOOLEAN DEFAULT FALSE,
    academic_year_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Student fee collections table
CREATE TABLE student_fee_collections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    fee_structure_id INT,
    amount_paid DECIMAL(10,2),
    payment_date DATE,
    payment_mode ENUM('cash', 'online', 'cheque', 'upi'),
    transaction_id VARCHAR(100),
    payment_gateway VARCHAR(100),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded'),
    refund_amount DECIMAL(10,2),
    cheque_number VARCHAR(50),
    remarks TEXT,
    collected_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_structure_id) REFERENCES fee_structures(id) ON DELETE CASCADE,
    FOREIGN KEY (collected_by) REFERENCES users(id)
);

-- Fee payments table (for online payments)
CREATE TABLE fee_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fee_id INT,
    amount_paid DECIMAL(10,2),
    payment_date DATE,
    payment_mode ENUM('cash', 'online', 'cheque', 'upi'),
    payment_gateway VARCHAR(100),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded'),
    transaction_id VARCHAR(100),
    refund_amount DECIMAL(10,2),
    collected_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fee_id) REFERENCES fees(id),
    FOREIGN KEY (collected_by) REFERENCES users(id)
);

-- Expenses table
CREATE TABLE expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expense_date DATE,
    reason VARCHAR(255),
    category ENUM('diesel', 'staff', 'bus', 'maintenance', 'misc', 'custom'),
    amount DECIMAL(10,2),
    payment_mode ENUM('cash', 'online', 'cheque'),
    remarks TEXT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- Events table
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE,
    event_time TIME,
    venue VARCHAR(255),
    organizer VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- News table
CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    summary TEXT,
    image_path VARCHAR(255),
    published_date DATE,
    author_id INT,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- Gallery table
CREATE TABLE gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    image_path VARCHAR(255),
    category VARCHAR(100),
    description TEXT,
    uploaded_by INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Homepage content table
CREATE TABLE homepage_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section VARCHAR(50),
    title VARCHAR(255),
    content TEXT,
    image_path VARCHAR(255),
    link VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'string',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Certificates table
CREATE TABLE certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    certificate_type ENUM('transfer', 'character', 'bonafide') NOT NULL,
    certificate_number VARCHAR(50) UNIQUE,
    student_id INT,
    issue_date DATE,
    transfer_reason VARCHAR(255),
    conduct VARCHAR(50),
    remarks TEXT,
    generated_by INT,
    pdf_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(id)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    icon VARCHAR(50) DEFAULT 'fas fa-bell',
    is_read BOOLEAN DEFAULT FALSE,
    related_table VARCHAR(100),
    related_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Audit logs table
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255),
    table_name VARCHAR(100),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Books table
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    isbn VARCHAR(20),
    author VARCHAR(100),
    quantity INT DEFAULT 1,
    shelf_number VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Book issues table
CREATE TABLE book_issues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT,
    user_id INT, -- Can be student or teacher
    issue_date DATE,
    due_date DATE,
    return_date DATE,
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Exams table
CREATE TABLE IF NOT EXISTS exams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exam_name VARCHAR(255) NOT NULL,
    exam_type ENUM('quarterly', 'halfyearly', 'annually', 'custom') DEFAULT 'custom',
    class_id INT, -- For backward compatibility, can be null for multi-class exams
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    academic_year_id INT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Exam subjects table (junction table for exam-subject-class relationships)
CREATE TABLE IF NOT EXISTS exam_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL, -- Which class this subject exam is for
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_marks DECIMAL(5,2) DEFAULT 100.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Exam results table
CREATE TABLE IF NOT EXISTS exam_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    marks_obtained DECIMAL(5,2),
    max_marks DECIMAL(5,2),
    grade VARCHAR(5),
    percentage DECIMAL(5,2),
    academic_year_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Admit card instructions table
CREATE TABLE IF NOT EXISTS admit_card_instructions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    instruction_text TEXT NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admit card instructions
INSERT IGNORE INTO admit_card_instructions (instruction_text) VALUES
('Candidates must reach the examination center 30 minutes before the scheduled time.'),
('Bring this admit card and a valid photo ID proof to the examination center.'),
('Electronic devices like mobile phones, calculators are not allowed in the examination hall.'),
('Candidates must follow all instructions given by the invigilator.'),
('Any attempt to cheat or misconduct will result in disqualification.');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_exams_academic_year ON exams(academic_year_id);
CREATE INDEX IF NOT EXISTS idx_exams_active ON exams(is_active);
CREATE INDEX IF NOT EXISTS idx_exam_subjects_exam ON exam_subjects(exam_id);
CREATE INDEX IF NOT EXISTS idx_exam_subjects_class ON exam_subjects(class_id);
CREATE INDEX IF NOT EXISTS idx_exam_subjects_date ON exam_subjects(exam_date);
CREATE INDEX IF NOT EXISTS idx_exam_results_exam ON exam_results(exam_id);
CREATE INDEX IF NOT EXISTS idx_exam_results_student ON exam_results(student_id);
CREATE INDEX IF NOT EXISTS idx_exam_results_subject ON exam_results(subject_id);

-- Insert default data
INSERT INTO user_roles (role_name, description) VALUES
('admin', 'Administrator with full access'),
('student', 'Student with limited access');

INSERT INTO permissions (permission_name, description) VALUES
('manage_users', 'Create, edit, delete users'),
('manage_students', 'Manage student records'),
('manage_classes', 'Manage classes and subjects'),
('manage_attendance', 'Mark and view attendance'),
('manage_fees', 'Manage fee structure and payments'),
('manage_events', 'Manage school events'),
('manage_gallery', 'Manage photo gallery'),
('view_reports', 'View reports and analytics'),
('manage_settings', 'Manage system settings'),
('manage_academic_years', 'Manage academic years');

INSERT INTO role_permissions (role_id, permission_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10);

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

-- Assign all permissions to superadmin
INSERT INTO role_permissions (role_id, permission_id) SELECT (SELECT id FROM user_roles WHERE role_name = 'superadmin'), id FROM permissions;

-- Student optional subjects table
CREATE TABLE student_optional_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    academic_year_id INT,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);






-- Add academic_year_id to classes
ALTER TABLE classes ADD COLUMN academic_year_id INT, ADD FOREIGN KEY (academic_year_id) REFERENCES academic_years(id);

-- Add academic_year_id to expenses
ALTER TABLE expenses ADD COLUMN academic_year_id INT, ADD FOREIGN KEY (academic_year_id) REFERENCES academic_years(id);

-- Add academic_year_id to attendance
ALTER TABLE attendance ADD COLUMN academic_year_id INT, ADD FOREIGN KEY (academic_year_id) REFERENCES academic_years(id);


-- Add academic_year_id to events
ALTER TABLE events ADD COLUMN academic_year_id INT, ADD FOREIGN KEY (academic_year_id) REFERENCES academic_years(id);

-- Add academic_year_id to certificates
ALTER TABLE certificates ADD COLUMN academic_year_id INT, ADD FOREIGN KEY (academic_year_id) REFERENCES academic_years(id);

-- Add tc_issued field to students table for TC system
ALTER TABLE students ADD COLUMN tc_issued BOOLEAN DEFAULT FALSE;

-- Add academic_year_id to gallery
ALTER TABLE gallery ADD COLUMN academic_year_id INT, ADD FOREIGN KEY (academic_year_id) REFERENCES academic_years(id);

-- Insert default academic year
INSERT INTO academic_years (year_name, start_date, end_date) VALUES ('2024-2025', '2024-04-01', '2025-03-31');

-- Insert sample classes
INSERT INTO classes (class_name, section, academic_year_id) VALUES
('Class 1', 'A', 1),
('Class 2', 'A', 1),
('Class 3', 'A', 1),
('Class 4', 'A', 1),
('Class 5', 'A', 1),
('Class 6', 'A', 1),
('Class 7', 'A', 1),
('Class 8', 'A', 1),
('Class 9', 'A', 1),
('Class 10', 'A', 1);

-- Insert sample subjects
INSERT INTO subjects (subject_name, subject_code) VALUES
('Mathematics', 'MATH'),
('English', 'ENG'),
('Hindi', 'HIN'),
('Science', 'SCI'),
('Social Science', 'SST'),
('Computer Science', 'CS'),
('Physical Education', 'PE'),
('Art', 'ART'),
('Music', 'MUS');

-- Insert class subjects (assign subjects to classes)
-- Assuming class IDs start from 1
INSERT INTO class_subjects (class_id, subject_id, teacher_id) VALUES
-- Class 1A (id=1)
(1, 1, 1), -- Math
(1, 2, 1), -- English
(1, 3, 1), -- Hindi
(1, 4, 1), -- Science
(1, 5, 1), -- SST
-- Class 2A (id=2)
(2, 1, 1),
(2, 2, 1),
(2, 3, 1),
(2, 4, 1),
(2, 5, 1),
-- Class 3A (id=3)
(3, 1, 1),
(3, 2, 1),
(3, 3, 1),
(3, 4, 1),
(3, 5, 1),
-- Class 4A (id=4)
(4, 1, 1),
(4, 2, 1),
(4, 3, 1),
(4, 4, 1),
(4, 5, 1),
-- Class 5A (id=5)
(5, 1, 1),
(5, 2, 1),
(5, 3, 1),
(5, 4, 1),
(5, 5, 1),
-- Class 6A (id=6)
(6, 1, 1),
(6, 2, 1),
(6, 3, 1),
(6, 4, 1),
(6, 5, 1),
(6, 6, 1), -- CS
-- Class 7A (id=7)
(7, 1, 1),
(7, 2, 1),
(7, 3, 1),
(7, 4, 1),
(7, 5, 1),
(7, 6, 1),
-- Class 8A (id=8)
(8, 1, 1),
(8, 2, 1),
(8, 3, 1),
(8, 4, 1),
(8, 5, 1),
(8, 6, 1),
-- Class 9A (id=9)
(9, 1, 1),
(9, 2, 1),
(9, 3, 1),
(9, 4, 1),
(9, 5, 1),
(9, 6, 1),
-- Class 10A (id=10)
(10, 1, 1),
(10, 2, 1),
(10, 3, 1),
(10, 4, 1),
(10, 5, 1),
(10, 6, 1);

-- Add 2FA columns to users (already added in table definition)
-- ALTER TABLE users ADD COLUMN 2fa_secret VARCHAR(255), ADD COLUMN 2fa_enabled BOOLEAN DEFAULT FALSE;