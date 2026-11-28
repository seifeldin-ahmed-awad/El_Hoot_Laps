-- Lab 08: CSRF token validation depends on request method.
-- The normal account form uses POST + CSRF token; the GET route intentionally skips validation.
USE breachlab;

UPDATE labs
SET category = 'CSRF',
    title = 'CSRF Where Token Validation Depends on Request Method',
    description = 'Compare the protected POST account update with the vulnerable GET account update.',
    difficulty = 'Medium',
    points = 150,
    is_enabled = 1,
    expected_flag_hash = 'df8001b0b1911075e5fc09bd79ea593636cdb229993d899b70940bb06e662a41',
    expected_flag_hash_algorithm = 'sha256'
WHERE id = 8;
