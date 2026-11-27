<section class="listing-page account-page">
  <p class="eyebrow">WELCOME BACK</p>
  <div class="section-heading listing-heading"><h1>Sign in</h1></div>
  <p class="account-note">Sign in to view your customer account, saved session, and recent studio orders.</p>
  <form method="post" action="index.php?page=login" class="account-email-form account-auth-form">
    <input type="hidden" name="action" value="customer-login">
    <?= csrf_field() ?>
    <div class="account-field">
      <label for="customer-email">Email address</label>
      <input id="customer-email" name="email" type="email" maxlength="190" autocomplete="email" required>
    </div>
    <div class="account-field">
      <label for="customer-password">Password</label>
      <input id="customer-password" name="password" type="password" autocomplete="current-password" required>
    </div>
    <button class="shop-button account-submit" type="submit">Sign in <span aria-hidden="true">→</span></button>
  </form>
  <p class="account-switch">New here? <a class="text-link" href="<?= shop_e(shop_url('register')) ?>">Create a customer account</a></p>
</section>
