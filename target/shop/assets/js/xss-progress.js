/* Report an executed XSS alert and show the awarded flag in the page afterward. */
(function () {
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  const originalAlert = window.alert.bind(window);
  let reported = false;

  function showFlagInPage(labId, flag) {
    const panel = document.createElement('div');
    panel.setAttribute('role', 'status');
    panel.style.cssText = 'margin:16px;padding:14px;border:2px solid #333;background:#fff8d8;color:#111;font-weight:bold;';
    panel.textContent = 'Lab ' + String(labId).padStart(2, '0') + ' flag: ' + flag;
    document.body.appendChild(panel);
  }

  window.alert = function (message) {
    originalAlert(message);
    if (reported || !token) return;
    reported = true;

    fetch('index.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body: new URLSearchParams({
        action: 'record-xss-execution',
        csrf_token: token
      })
    })
      .then(function (response) { return response.json(); })
      .then(function (result) {
        if (result.status === 'awarded') showFlagInPage(result.lab_id, result.flag);
        // Repeated solves stay silent; no completion banner is shown otherwise.
      })
      .catch(function () {
        // Do not alter the normal shop UI if progress reporting fails.
      });
  };
})();
