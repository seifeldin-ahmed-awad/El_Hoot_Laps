/* Lab 07: reveal the flag after the stored onclick payload triggers alert. */
(function () {
  const section = document.querySelector('.reviews-section[data-lab07]');
  if (!section) return;

  const originalAlert = window.alert.bind(window);
  let revealed = false;

  window.alert = function (message) {
    originalAlert(message);
    if (revealed) return;
    revealed = true;

    let flag = document.getElementById('lab07-flag');
    if (!flag) {
      flag = document.createElement('p');
      flag.id = 'lab07-flag';
      flag.className = 'shop-flash is-success';
      flag.setAttribute('role', 'status');
      flag.setAttribute('aria-live', 'polite');
      flag.textContent = 'Lab 07 flag: Flag{ePeQnCgVgFFn}';
      section.prepend(flag);
    }

    window.setTimeout(function () {
      window.location.reload();
    }, 30000);
  };
})();
