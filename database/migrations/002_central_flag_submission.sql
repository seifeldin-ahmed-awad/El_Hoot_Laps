USE breachlab;

ALTER TABLE flag_submissions
    MODIFY lab_id TINYINT UNSIGNED NULL DEFAULT NULL;

SET @index_exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'labs'
      AND index_name = 'uq_labs_expected_flag_hash'
);

SET @add_index = IF(
    @index_exists = 0,
    'ALTER TABLE labs ADD UNIQUE KEY uq_labs_expected_flag_hash (expected_flag_hash)',
    'SELECT 1'
);

PREPARE migration_statement FROM @add_index;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
