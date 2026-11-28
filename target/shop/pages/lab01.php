<section class="listing-page lab01-info-page">
  <p class="eyebrow">BREACHLAB TRAINING MODULE — LAB_01</p>
  <h1>Visible Error-Based SQL Injection</h1>
</section>

<section class="lab01-meta">
  <dl class="lab01-meta-grid">
    <div class="lab01-meta-item">
      <dt>Category</dt>
      <dd>SQL Injection</dd>
    </div>
    <div class="lab01-meta-item">
      <dt>Difficulty</dt>
      <dd class="lab01-difficulty-apprentice">Apprentice</dd>
    </div>
    <div class="lab01-meta-item">
      <dt>Points</dt>
      <dd>100</dd>
    </div>
    <div class="lab01-meta-item">
      <dt>Technology</dt>
      <dd>PHP / PostgreSQL</dd>
    </div>
    <div class="lab01-meta-item">
      <dt>Vulnerability</dt>
      <dd>Error-based SQL injection</dd>
    </div>
    <div class="lab01-meta-item">
      <dt>Status</dt>
      <dd class="lab01-status-enabled">● ENABLED</dd>
    </div>
  </dl>
</section>

<section class="lab01-description">
  <h2>Description</h2>
  <p>The application uses a <code>TrackingId</code> cookie to identify visitors. The cookie value is included in a database query. The application exposes detailed database error messages in its responses.</p>
  <p>Your objective is to exploit this behavior to discover the sales_manager's credentials and successfully authenticate as the sales_manager.</p>
</section>

<section class="lab01-instructions">
  <h2>Instructions</h2>
  <ol class="lab01-steps">
    <li>Open the shop using the link below. The shop issues a <code>TrackingId</code> cookie when you visit the staff login page.</li>
    <li>Modify the <code>TrackingId</code> cookie value and observe how the application responds to unexpected input. Look for database error messages in the page.</li>
    <li>Set <code>TrackingId=ogAZZfxtOKUELbuJ' AND CAST((SELECT 1) AS int)--</code> and send the request. Observe the database error indicating that the <code>AND</code> condition must be a boolean expression.</li>
    <li>Use the error messages to understand the query structure and the database engine in use.</li>
    <li>Craft injection payloads that cause the database to return data inside the error message text.</li>
    <li>Retrieve the target account's password from the <code>users</code> table.</li>
    <li>Log in with the extracted credentials on the staff login page to receive the challenge flag.</li>
    <li>Submit the flag through the <a class="text-link" href="/BreachLab/scoreboard.php">BreachLab Scoreboard</a>.</li>
  </ol>

  <div class="lab01-hint-box">
    <p class="eyebrow">TECHNIQUE HINT</p>
    <p>This challenge uses <strong>error-based SQL injection</strong> against a PostgreSQL database. The database exposes type-conversion errors verbatim. No blind timing or boolean-difference techniques are needed.</p>
  </div>
</section>

<section class="lab01-cta">
  <a class="shop-button" href="modules/labs/lab01/login.php" target="_blank" rel="noopener">
    Open the target lab login page <span aria-hidden="true">→</span>
  </a>
  <p class="lab01-cta-note">The target opens in a new tab. The <code>TrackingId</code> cookie is active on that domain. Use a browser tool (DevTools, Burp Suite, or curl) to modify the cookie value and observe the response.</p>
</section>
