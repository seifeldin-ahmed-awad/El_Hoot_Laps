USE breachlab;

SET @challenge_route_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'labs'
      AND COLUMN_NAME = 'challenge_route'
);

SET @drop_challenge_route = IF(
    @challenge_route_exists > 0,
    'ALTER TABLE labs DROP COLUMN challenge_route',
    'SELECT 1'
);
PREPARE _stmt FROM @drop_challenge_route;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

DROP TABLE IF EXISTS operator_lab_progress;
