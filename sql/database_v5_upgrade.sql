-- DevSprint V3 Database Upgrade
USE devsprint;

-- 1. Indexing Strategy Improvements
-- Add indexes to users for AI matchmaking
ALTER TABLE users ADD FULLTEXT INDEX IF NOT EXISTS ft_skills (skills);
ALTER TABLE users ADD FULLTEXT INDEX IF NOT EXISTS ft_bio (bio);

-- Add index to applications for faster lookups
CREATE INDEX idx_applications_user ON applications(user_id);
CREATE INDEX idx_applications_status ON applications(status);

-- Add indexes to teams if teams table exists (it should, as referenced in matchmaking.php)
-- Let's make sure teams table has proper indexes
CREATE INDEX idx_teams_leader ON teams(leader_id);

-- 2. New Tables for Features

-- Internal Messaging/Chat System
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    team_id INT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Real-time Notification System
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- AI User Scores Cache (optional, but good for performance)
CREATE TABLE IF NOT EXISTS user_scores (
    user_a INT NOT NULL,
    user_b INT NOT NULL,
    score DECIMAL(5, 2) NOT NULL,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_a, user_b),
    FOREIGN KEY (user_a) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_b) REFERENCES users(id) ON DELETE CASCADE
);
