-- Migration: Creation of skill categories table with icons and mapping existing skills
-- File: sql/migration_skill_categories.sql

-- 1. Create skill_categories table with icon column
CREATE TABLE IF NOT EXISTS skill_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    icon VARCHAR(255) DEFAULT 'bx bx-code-alt',
    display_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insert default categories with unique icons
INSERT IGNORE INTO skill_categories (id, name, icon, display_order) VALUES
(1, 'Front-End', 'bx bx-layout', 1),
(2, 'Back-End', 'bx bx-server', 2),
(3, 'Base de données', 'bx bx-data', 3),
(4, 'Software & Game Dev', 'bx bx-joystick', 4);

-- 3. Add category_id column to skills table if not existing
SET @dropdown_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'skills' 
      AND COLUMN_NAME = 'category_id'
);

SET @sql = IF(@dropdown_exists = 0, 
    'ALTER TABLE skills ADD COLUMN category_id INT NULL AFTER name, ADD CONSTRAINT fk_skills_category_id FOREIGN KEY (category_id) REFERENCES skill_categories(id) ON DELETE SET NULL ON UPDATE CASCADE;', 
    'SELECT "category_id column already exists";'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Map existing skills to categories based on name keywords
UPDATE skills 
SET category_id = 1 
WHERE (category_id IS NULL OR category_id = 0) 
  AND (name LIKE '%HTML%' OR name LIKE '%CSS%' OR name LIKE '%BOOTSTRAP%' OR name LIKE '%REACT%' OR name LIKE '%FRONT%');

UPDATE skills 
SET category_id = 2 
WHERE (category_id IS NULL OR category_id = 0) 
  AND (name LIKE '%PHP%' OR name LIKE '%NODE%' OR name LIKE '%BACK%');

UPDATE skills 
SET category_id = 3 
WHERE (category_id IS NULL OR category_id = 0) 
  AND (name LIKE '%MYSQL%' OR name LIKE '%SQL%' OR name LIKE '%SQLITE%' OR name LIKE '%BASE%');

UPDATE skills 
SET category_id = 4 
WHERE (category_id IS NULL OR category_id = 0) 
  AND (name LIKE '%GODOT%' OR name LIKE '%GDEVELOP%' OR name LIKE '%GAME%' OR name LIKE '%SOFTWARE%');

-- Default any unmapped skills to Front-End (1)
UPDATE skills SET category_id = 1 WHERE category_id IS NULL OR category_id = 0;
