-- Register Lab 05 against the existing main storefront product pages.
-- No lab selector parameter is added to product URLs.
USE breachlab;

UPDATE labs
SET is_enabled = 1,
    expected_flag_hash = 'cf82084966113a66c7f3966c50888baa778bd144d30919ea3df435cf6ede8ee7',
    expected_flag_hash_algorithm = 'sha256'
WHERE id = 5;
