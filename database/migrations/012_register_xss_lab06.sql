-- Register Lab 06: reflected XSS in a JavaScript string on the main shop search.
-- Shares the existing search endpoint with SQLi Lab 03; no separate shop or lab URL.
USE breachlab;

UPDATE labs
SET category = 'Cross-Site Scripting',
    title = 'Reflected XSS into a JavaScript String with Single Quote and Backslash Escaped',
    description = 'Break out of a reflected JavaScript string by terminating the script element.',
    difficulty = 'Medium',
    points = 150,
    is_enabled = 1,
    expected_flag_hash = '1cdd9642bfd7814ceaa527a5a17ff526da3bb3a40cf86a954bb9336679ea077a',
    expected_flag_hash_algorithm = 'sha256'
WHERE id = 6;
