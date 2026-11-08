USE `emmanuel-bissa-portfolio`;

SET @hire_column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'informations'
      AND column_name = 'hire_link'
);
SET @add_hire_sql = IF(
    @hire_column_exists = 0,
    'ALTER TABLE informations ADD COLUMN hire_link VARCHAR(255) NULL AFTER youtube_link',
    'SELECT 1'
);
PREPARE add_hire_statement FROM @add_hire_sql;
EXECUTE add_hire_statement;
DEALLOCATE PREPARE add_hire_statement;

SET @contact_column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'informations'
      AND column_name = 'contact_link'
);
SET @drop_contact_sql = IF(
    @contact_column_exists > 0,
    'ALTER TABLE informations DROP COLUMN contact_link',
    'SELECT 1'
);
PREPARE drop_contact_statement FROM @drop_contact_sql;
EXECUTE drop_contact_statement;
DEALLOCATE PREPARE drop_contact_statement;
