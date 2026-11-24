'use strict';

document.querySelectorAll('[data-auto-submit]').forEach((control) => {
  control.addEventListener('change', () => control.form?.requestSubmit());
});
