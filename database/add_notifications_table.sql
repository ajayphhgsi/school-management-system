-- Add notifications table to existing database
-- Run this SQL to add the notifications functionality

CREATE TABLE IF NOT EXISTS notifications (
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

-- Insert some sample notifications for testing
INSERT INTO notifications (user_id, title, message, type, icon, is_read) VALUES
(1, 'Welcome to School Management System', 'Your account has been set up successfully. You can now manage students, classes, and more.', 'success', 'fas fa-check-circle', FALSE),
(1, 'New Student Registration', 'John Doe has been registered in Class 10A with Scholar Number S001.', 'info', 'fas fa-user-plus', FALSE),
(1, 'Fee Payment Reminder', '3 students have pending fee payments due this month.', 'warning', 'fas fa-exclamation-triangle', FALSE),
(1, 'Exam Schedule Updated', 'Mid-term examination schedule has been published for Class 10.', 'info', 'fas fa-calendar-alt', TRUE),
(1, 'System Maintenance', 'Scheduled maintenance will occur tonight from 11 PM to 1 AM.', 'warning', 'fas fa-cog', TRUE);