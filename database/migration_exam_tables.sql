-- Migration: Add exam-related tables to existing database

-- Exams table
CREATE TABLE IF NOT EXISTS exams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_name VARCHAR(255) NOT NULL,
    exam_type ENUM('quarterly', 'halfyearly', 'annually', 'custom') DEFAULT 'custom',
    class_id INTEGER, -- For backward compatibility, can be null for multi-class exams
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    academic_year_id INTEGER,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Exam subjects table (junction table for exam-subject-class relationships)
CREATE TABLE IF NOT EXISTS exam_subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL, -- Which class this subject exam is for
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
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    marks_obtained DECIMAL(5,2),
    max_marks DECIMAL(5,2),
    grade VARCHAR(5),
    percentage DECIMAL(5,2),
    academic_year_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Admit card instructions table
CREATE TABLE IF NOT EXISTS admit_card_instructions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    instruction_text TEXT NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admit card instructions
INSERT OR IGNORE INTO admit_card_instructions (instruction_text) VALUES
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