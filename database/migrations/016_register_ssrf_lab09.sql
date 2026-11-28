USE breachlab;

INSERT INTO labs (
    id, category, title, description, difficulty, points,
    is_enabled, expected_flag_hash, expected_flag_hash_algorithm
)
VALUES (
    9, 'SSRF', 'SSRF with Blacklist-Based Input Filter',
    'Explore how URL deny-lists can be bypassed in a simulated internal stock service.',
    'Hard', 200, 1,
    '54bb07236975e3827324030f5a23b311fc7a0b82cfa87c66cff75f0d46511638',
    'sha256'
)
ON DUPLICATE KEY UPDATE
    category = VALUES(category), title = VALUES(title),
    description = VALUES(description), difficulty = VALUES(difficulty), points = VALUES(points),
    is_enabled = VALUES(is_enabled), expected_flag_hash = VALUES(expected_flag_hash),
    expected_flag_hash_algorithm = VALUES(expected_flag_hash_algorithm);
