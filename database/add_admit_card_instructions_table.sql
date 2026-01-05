-- Add admit card instructions table
CREATE TABLE admit_card_instructions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    instruction_text TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default instructions
INSERT INTO admit_card_instructions (instruction_text, is_active) VALUES
('1. Bring this admit card to the examination hall.', 1),
('2. Arrive at least 30 minutes before the exam starts.', 1),
('3. Carry valid ID proof along with this card.', 1),
('4. Mobile phones and electronic devices are not allowed in the exam hall.', 1),
('5. Follow all instructions given by the invigilator.', 1);