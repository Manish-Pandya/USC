-- Add room type column to Room
ALTER TABLE room ADD COLUMN `room_type` varchar(24) DEFAULT 'ResearchLab';
