USE `emmanuel-bissa-portfolio`;

ALTER TABLE informations
    ADD COLUMN tiktok_link VARCHAR(255) NULL AFTER twitter_link,
    ADD COLUMN youtube_link VARCHAR(255) NULL AFTER tiktok_link;
