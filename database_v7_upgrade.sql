-- DevSprint V7 Database Upgrade
-- Adds contact message support to the messages table
USE devsprint;

-- Add subject and sender_name columns to messages table for contact-form messages
ALTER TABLE messages
    ADD COLUMN IF NOT EXISTS subject VARCHAR(255) NULL AFTER message,
    ADD COLUMN IF NOT EXISTS sender_name VARCHAR(150) NULL AFTER subject,
    ADD COLUMN IF NOT EXISTS sender_email VARCHAR(150) NULL AFTER sender_name,
    ADD COLUMN IF NOT EXISTS message_type ENUM('chat','contact') NOT NULL DEFAULT 'chat' AFTER sender_email;

-- Allow receiver_id to be NULL (NULL means the message is sent to admin via contact form)
ALTER TABLE messages
    MODIFY COLUMN receiver_id INT NULL;
