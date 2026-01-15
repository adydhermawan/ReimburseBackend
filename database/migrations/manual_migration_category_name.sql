-- Migration: Add category_name to reimbursements table
-- Run this SQL in TiDB Cloud Console

-- Step 1: Add category_name column
ALTER TABLE reimbursements 
ADD COLUMN category_name VARCHAR(255) NULL AFTER category_id;

-- Step 2: Modify category_id to be nullable
-- Note: TiDB might require dropping and recreating the foreign key
ALTER TABLE reimbursements 
MODIFY COLUMN category_id BIGINT UNSIGNED NULL;

-- Verify the changes
DESCRIBE reimbursements;
