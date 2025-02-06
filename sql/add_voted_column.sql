-- Add voted column if it doesn't exist
ALTER TABLE voters ADD COLUMN IF NOT EXISTS voted TINYINT(1) NOT NULL DEFAULT 0;
