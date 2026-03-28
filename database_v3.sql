USE devsprint;

ALTER TABLE hackathons 
ADD COLUMN application_type ENUM('Individual', 'Team', 'Both') DEFAULT 'Both' AFTER description;
