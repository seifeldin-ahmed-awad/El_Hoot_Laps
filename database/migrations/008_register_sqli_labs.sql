-- Register Lab 02 (conditional blind), Lab 03 (schema discovery), Lab 04 (time delay).
-- Apply to the MySQL breachlab database after migration 006.
USE breachlab;
UPDATE labs SET title='Blind SQL Injection with Conditional Responses', category='SQL Injection',
 description='Infer query results from the presence or absence of the Welcome back response.', difficulty='Medium', points=150,
 is_enabled=1, challenge_route='/BreachLab/target/shop/?lab=2',
 expected_flag_hash='a78302d1b58e5e5c5771246d137c3be2a58acb328f249d7b76d6dca370ee3b4b', expected_flag_hash_algorithm='sha256' WHERE id=2;
UPDATE labs SET title='UNION-based SQL Injection', category='SQL Injection',
 description='Examine how combining query results can expose data beyond the intended view.', difficulty='Medium', points=150,
 is_enabled=1, challenge_route='/BreachLab/target/shop/?lab=3',
 expected_flag_hash='0bf9229778a6add4ed4f57a357ed5182516d1933ef0e8f49e1bde6f4adc08f0e', expected_flag_hash_algorithm='sha256' WHERE id=3;
UPDATE labs SET title='Blind SQL Injection with Time Delays', category='SQL Injection',
 description='Use a PostgreSQL time-delay expression in TrackingId and observe the response duration.', difficulty='Hard', points=200,
 is_enabled=1, challenge_route='/BreachLab/target/shop/?lab=4',
 expected_flag_hash='6192bf0a9f57fdfdb9d13a04b41f230d75c413a94e950f7d7af897a93f506873', expected_flag_hash_algorithm='sha256' WHERE id=4;
