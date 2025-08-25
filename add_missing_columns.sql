-- Add missing columns to the games table for score saving
ALTER TABLE games ADD COLUMN IF NOT EXISTS completion_time INTEGER;
ALTER TABLE games ADD COLUMN IF NOT EXISTS completed_at TIMESTAMP;
ALTER TABLE games ADD COLUMN IF NOT EXISTS words_found INTEGER DEFAULT 0;
ALTER TABLE games ADD COLUMN IF NOT EXISTS total_words INTEGER DEFAULT 0;

-- Check if the columns were added successfully
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'games' 
AND column_name IN ('completion_time', 'completed_at', 'words_found', 'total_words')
ORDER BY column_name;
