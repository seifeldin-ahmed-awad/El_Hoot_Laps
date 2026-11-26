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
- `target/shop/` contains the separate Kanso Goods storefront, its assets, pages, catalog/cart logic, and Lab 01 training target.
- `database/migrations/` contains additive schema updates for existing installations.

The local development connection defaults to MySQL at `127.0.0.1:3306`, database `breachlab`, and the default XAMPP `root` account with an empty password. For any non-local use, create a dedicated MySQL account and set `BREACHLAB_DB_HOST`, `BREACHLAB_DB_PORT`, `BREACHLAB_DB_NAME`, `BREACHLAB_DB_USER`, and `BREACHLAB_DB_PASSWORD` in the Apache environment. Do not use the development defaults on a network-accessible server.
The application permits the development defaults only for loopback clients; non-local requests fail closed unless all five database environment variables are set.

## Lab configuration

Labs are seeded as **Not Configured**. A lab only accepts submissions when its `challenge_route` and `expected_flag_hash` are configured. SHA-256 is used by default; Lab 01 uses MD5 as declared by its migration. The raw flag is never stored in MySQL or in submission history. Until a lab is configured, its flag input and submit button remain disabled.

Use an absolute `http://` or `https://` challenge URL or a site-relative path for `challenge_route`. Generate the digest locally with PHP, without putting the flag in application source:

```powershell
& 'C:\xampp\php\php.exe' -r "echo hash('sha256', 'PASTE_REAL_FLAG_HERE'), PHP_EOL;"
```

Set the returned digest and the route for that lab in the `labs` table. Configure only challenge routes that point to an authorized, isolated target. Labs without routes and flags remain unavailable.

## Progress and authentication

Operator passwords are stored with PHP `password_hash()` and checked with `password_verify()`. Sessions identify the operator; completion rows are unique per operator and lab. Leaderboard points are calculated from those completion rows, not from seed data. Failed submissions store only a digest of the submitted value.

The Kanso Goods storefront is a separate, local target application. It has a database-backed product catalog, session-persisted bag, stock-aware demo orders, and no real payment or shipping integration. It does not collect customer details. Run `database/migrations/004_shop_catalog.sql` after the base schema to create its tables and seed the four initial products.

Lab 01 is an intentionally vulnerable PostgreSQL-backed target. Follow the setup instructions in `target/shop/modules/labs/lab01/db.php`, apply `database/migrations/005_lab01_pgsql.sql` to the separate `lab01_sqli` database, then apply MySQL migrations `006_lab_enabled_and_hash_algo.sql` and `007_lab01_register.sql` to `breachlab`. Keep the target isolated and use it only for authorized training. Opening a configured challenge records that an authenticated operator has started it.

The Scoreboard has one shared flag field. The submitted flag is matched to its unique configured hash to identify the lab; duplicate hash values are prevented by a unique database index. On an existing installation created before the central submission form, import `database/migrations/002_central_flag_submission.sql` once, followed by `database/migrations/003_operator_lab_progress.sql`.

## SQL injection lab database setup

Lab 02 and Lab 04 use isolated PostgreSQL databases, separate from the platform MySQL database:

- Lab 02: `lab02_sqli`, role `lab02_user`, environment variables `LAB02_PG_HOST`, `LAB02_PG_PORT`, `LAB02_PG_DATABASE`, `LAB02_PG_USER`, `LAB02_PG_PASSWORD`.
- Lab 04: `lab04_sqli`, role `lab04_user`, environment variables `LAB04_PG_HOST`, `LAB04_PG_PORT`, `LAB04_PG_DATABASE`, `LAB04_PG_USER`, `LAB04_PG_PASSWORD`.

Create each database and restricted login role as a PostgreSQL administrator, grant the role CONNECT on its database, then run the matching Lab 02 or Lab 04 migration inside that database. The local default passwords in `target/shop/modules/labs/shared.php` are for local training only; change them before exposing the server.

Lab 03 runs against the main `breachlab` MySQL/MariaDB database through the ordinary Kanso Goods global search box. On an existing installation, apply `database/migrations/010_lab03_account_hash_mysql.sql` to add or refresh the target account; the migration leaves existing tables untouched. On a fresh installation, import the updated `database/schema.sql`. The target is stored in the simple `users` table, which has exactly two columns: `username` (`mariam`) and `password` (the MD5 digest of the challenge flag). The plaintext flag is not stored in the target table. Lab 02 and Lab 04 continue to use their separate PostgreSQL databases. Flags are submitted through the existing scoreboard.

Example one-time PostgreSQL provisioning for Lab 02 or Lab 04 (repeat with the matching database, role, and migration):

```sql
CREATE DATABASE lab02_sqli;
CREATE ROLE lab02_user LOGIN PASSWORD 'lab02_localdev';
GRANT CONNECT ON DATABASE lab02_sqli TO lab02_user;
```

Run `008_lab02_pgsql.sql` while connected to `lab02_sqli` as the database owner/admin, and repeat with the Lab 04 database and migration. PostgreSQL roles are cluster-wide, so create each role only once.


### Lab 03 from the storefront search

Open Lab 03 directly at `target/shop/index.php?page=products&lab=3`. Ordinary storefront searches omit `lab` and continue to use the parameterized product query.
