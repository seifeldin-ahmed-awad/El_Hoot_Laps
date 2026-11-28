# BreachLab

## Local XAMPP setup

1. Start Apache and MySQL from the XAMPP Control Panel.
2. Import `database/schema.sql` using phpMyAdmin. This creates the `breachlab` database and seeds the ten supplied catalog entries.
3. Open `http://localhost/BreachLab/`.
4. Create an operator account using **[SIGN_UP]**.
5. Apply `database/migrations/004_shop_catalog.sql` to the `breachlab` database to install the local Kanso Goods training storefront.
6. Open the shop at `http://localhost/BreachLab/target/shop/`.

## Project layout

- Root PHP entry points serve the BreachLab Welcome page, authentication, and Scoreboard.
- `app/` contains shared bootstrap, configuration, database, security, and authentication helpers.
- `.env.example` lists the supported environment variables for database, PostgreSQL lab, and Lab 09 configuration. Apache/XAMPP environment values remain the runtime source; the `.env` file is not automatically loaded.
- `includes/` contains platform navigation and leaderboard presentation.
- `assets/css/` and `assets/js/` contain platform styling and the Welcome animation.
- `target/shop/` contains the Kanso Goods storefront and the integrated training targets for the active labs.
- `database/migrations/` contains additive schema updates for existing installations.

The local development connection defaults to MySQL at `127.0.0.1:3306`, database `breachlab`, and the default XAMPP `root` account with an empty password. For any non-local use, create a dedicated MySQL account and set `BREACHLAB_DB_HOST`, `BREACHLAB_DB_PORT`, `BREACHLAB_DB_NAME`, `BREACHLAB_DB_USER`, and `BREACHLAB_DB_PASSWORD` in the Apache environment. Do not use the development defaults on a network-accessible server.
The application permits the development defaults only for loopback clients; non-local requests fail closed unless all five database environment variables are set.

## Progress and authentication

Operator passwords are stored with PHP `password_hash()` and checked with `password_verify()`. Sessions identify the operator; completion rows are unique per operator and lab. Leaderboard points are calculated from those completion rows, not from seed data. Failed submissions store only a digest of the submitted value.

The Kanso Goods storefront is a separate, local target application. It has a database-backed product catalog, session-persisted bag, stock-aware demo orders, and no real payment or shipping integration. It does not collect customer details. Run `database/migrations/004_shop_catalog.sql` after the base schema to create its tables and seed the four initial products.

Lab 01 is an intentionally vulnerable PostgreSQL-backed target. Follow the setup instructions in `target/shop/modules/labs/lab01/db.php`, apply `database/migrations/005_lab01_pgsql.sql` to the separate `lab01_sqli` database, then apply MySQL migrations `006_lab_enabled_and_hash_algo.sql` and `007_lab01_register.sql` to `breachlab`. Keep the target isolated and use it only for authorized training.

The Scoreboard has one shared flag field. A submitted flag is matched to its unique hash to identify and mark its lab solved; duplicate flag hashes are prevented by a unique database index. On an existing installation, apply `database/migrations/002_central_flag_submission.sql` if needed, then apply `database/migrations/018_flag_only_progress.sql` to remove legacy target-launch data and configuration.


## CSRF Lab 08

Apply `database/migrations/014_register_csrf_lab08.sql` and `015_shop_customer_accounts.sql` after the base schema and shop catalog migration. Register/sign in using the shop customer account pages (separate from operator accounts). The normal account email change uses POST with a CSRF token; the intentionally vulnerable GET route is demonstrated by the separate local attacker page in `attacker-csrf/index.html`. Serve that page from `http://localhost:8081` with `php -S localhost:8081` from the `attacker-csrf` directory, using the same browser profile as the signed-in shop.


## SSRF Lab 09

Apply `database/migrations/016_register_ssrf_lab09.sql` after the existing migrations. Lab 09 uses the product page's Check stock request and the `stockApi` parameter. Its internal admin service is simulated within the shop; it does not make arbitrary network requests. Use Burp Repeater to change `stockApi` and observe the blacklist behavior. The lab's target-user deletion is simulated and returns `flag{kr9BOrumo2ff}`.
