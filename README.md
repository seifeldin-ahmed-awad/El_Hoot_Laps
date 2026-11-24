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
- `app/` contains shared bootstrap, database, security, authentication, and challenge-route validation.
- `includes/` contains platform navigation and leaderboard presentation.
- `assets/css/` and `assets/js/` contain platform styling and the Welcome animation.
- `target/shop/` contains the separate Kanso Goods storefront, its assets, pages, catalog/cart logic, and informational lab registry.
- `database/migrations/` contains additive schema updates for existing installations.

The local development connection defaults to MySQL at `127.0.0.1:3306`, database `breachlab`, and the default XAMPP `root` account with an empty password. For any non-local use, create a dedicated MySQL account and set `BREACHLAB_DB_HOST`, `BREACHLAB_DB_PORT`, `BREACHLAB_DB_NAME`, `BREACHLAB_DB_USER`, and `BREACHLAB_DB_PASSWORD` in the Apache environment. Do not use the development defaults on a network-accessible server.
The application permits the development defaults only for loopback clients; non-local requests fail closed unless all five database environment variables are set.

## Lab configuration

All ten labs are seeded as **Not Configured**. A lab only accepts submissions when both `challenge_route` and `expected_flag_hash` are set in the `labs` table. The expected flag is stored as a lowercase SHA-256 digest; the raw flag is never stored in the database or in submission history. Until both fields are configured, its flag input and submit button remain disabled.

Use an absolute `http://` or `https://` challenge URL or a site-relative path for `challenge_route`. Generate the digest locally with PHP, without putting the flag in application source:

```powershell
& 'C:\xampp\php\php.exe' -r "echo hash('sha256', 'PASTE_REAL_FLAG_HERE'), PHP_EOL;"
```

Set the returned digest and the route for that lab in the `labs` table. Configure only challenge routes that point to an authorized, isolated target. Labs without routes and flags remain unavailable.

## Progress and authentication

Operator passwords are stored with PHP `password_hash()` and checked with `password_verify()`. Sessions identify the operator; completion rows are unique per operator and lab. Leaderboard points are calculated from those completion rows, not from seed data. Failed submissions store only a digest of the submitted value.

The Kanso Goods storefront is a separate, local target application. It has a database-backed product catalog, session-persisted bag, stock-aware demo orders, and no real payment or shipping integration. It does not collect customer details. Run `database/migrations/004_shop_catalog.sql` after the base schema to create its tables and seed the four initial products.

The shop's lab registry is informational and deliberately has no vulnerable challenge handlers. Do not configure a Scoreboard challenge route or expected flag until an authorized, isolated lab implementation is ready. Opening a configured challenge records that an authenticated operator has started it.

The Scoreboard has one shared flag field. The submitted flag is matched to its unique configured hash to identify the lab; duplicate hash values are prevented by a unique database index. On an existing installation created before the central submission form, import `database/migrations/002_central_flag_submission.sql` once, followed by `database/migrations/003_operator_lab_progress.sql`.
