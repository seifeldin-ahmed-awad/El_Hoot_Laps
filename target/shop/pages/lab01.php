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

<style>
.lab01-info-page h1 {
  margin: 0;
  color: var(--espresso);
  font-family: Yrsa, Georgia, "Times New Roman", serif;
  font-size: clamp(44px, 6vw, 66px);
  font-weight: 400;
  letter-spacing: -.045em;
  line-height: 1;
}
.lab01-meta {
  padding: 24px 0 32px;
  border-top: 1px solid var(--soft-border);
  border-bottom: 1px solid var(--soft-border);
  margin-bottom: 36px;
}
.lab01-meta-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
  margin: 0;
}
.lab01-meta-item dt {
  color: var(--terracotta);
  font-family: "Red Hat Mono", Consolas, monospace;
  font-size: 9px;
  letter-spacing: .1em;
  margin-bottom: 4px;
}
.lab01-meta-item dd {
  margin: 0;
  color: var(--espresso);
  font-size: 13px;
  font-weight: 600;
}
.lab01-difficulty-apprentice { color: var(--success); }
.lab01-status-enabled { color: var(--success); font-family: "Red Hat Mono", Consolas, monospace; font-size: 11px; }

.lab01-description, .lab01-instructions, .lab01-cta {
  margin-bottom: 36px;
}
.lab01-description h2, .lab01-instructions h2 {
  margin: 0 0 14px;
  color: var(--espresso);
  font-family: Yrsa, Georgia, serif;
  font-size: 24px;
  font-weight: 400;
}
.lab01-description p {
  color: var(--muted);
  font-size: 14px;
  max-width: 760px;
  margin: 0 0 10px;
}
.lab01-description code, .lab01-instructions code {
  font-family: "Red Hat Mono", Consolas, monospace;
  font-size: 12px;
  background: var(--surface);
  padding: 1px 5px;
  border-radius: 2px;
}
.lab01-steps {
  color: var(--muted);
  font-size: 14px;
  max-width: 760px;
  margin: 0 0 20px;
  padding-left: 22px;
}
.lab01-steps li { margin-bottom: 8px; line-height: 1.6; }
.lab01-hint-box {
  max-width: 760px;
  padding: 16px 20px;
  border-left: 3px solid var(--terracotta);
  background: var(--paper);
}
.lab01-hint-box .eyebrow { margin-bottom: 6px; }
.lab01-hint-box p { margin: 0; color: var(--muted); font-size: 13px; }
.lab01-cta { padding-top: 8px; }
.lab01-cta-note {
  margin: 14px 0 0;
  color: #766d64;
  font-size: 11px;
  max-width: 640px;
}
@media (max-width: 700px) {
  .lab01-meta-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .lab01-info-page h1 { font-size: 44px; }
}
</style>
