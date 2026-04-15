USE devsprint;

ALTER TABLE applications
ADD COLUMN team_id INT NULL DEFAULT NULL AFTER hackathon_id,
ADD FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE;
