USE `emmanuel-bissa-portfolio`;

SET @tiktok_column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'informations'
      AND column_name = 'tiktok_link'
);
SET @add_tiktok_sql = IF(
    @tiktok_column_exists = 0,
    'ALTER TABLE informations ADD COLUMN tiktok_link VARCHAR(255) NULL AFTER twitter_link',
    'SELECT 1'
);
PREPARE add_tiktok_statement FROM @add_tiktok_sql;
EXECUTE add_tiktok_statement;
DEALLOCATE PREPARE add_tiktok_statement;

SET @youtube_column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'informations'
      AND column_name = 'youtube_link'
);
SET @add_youtube_sql = IF(
    @youtube_column_exists = 0,
    'ALTER TABLE informations ADD COLUMN youtube_link VARCHAR(255) NULL AFTER tiktok_link',
    'SELECT 1'
);
PREPARE add_youtube_statement FROM @add_youtube_sql;
EXECUTE add_youtube_statement;
DEALLOCATE PREPARE add_youtube_statement;
