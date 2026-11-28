<section class="listing-page account-page">
  <p class="eyebrow">JOIN THE STUDIO</p>
  <div class="section-heading listing-heading"><h1>Create account</h1></div>
  <p class="account-note">Create a customer account. These credentials are separate from BreachLab operator accounts.</p>
  <form method="post" action="index.php?page=register" class="account-email-form account-auth-form">
    <input type="hidden" name="action" value="customer-register">
    <?= csrf_field() ?>
    <div class="account-field">
      <label for="customer-email">Email address</label>
      <input id="customer-email" name="email" type="email" maxlength="190" autocomplete="email" required>
    </div>
    <div class="account-field">
      <label for="customer-password">Password <span>(at least 6 characters)</span></label>
      <input id="customer-password" name="password" type="password" minlength="6" autocomplete="new-password" required>
    </div>
    <button class="shop-button account-submit" type="submit">Create account <span aria-hidden="true">→</span></button>
  </form>
  <p class="account-switch">Already registered? <a class="text-link" href="<?= shop_e(shop_url('login')) ?>">Sign in</a></p>
</section>
