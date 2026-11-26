/* Lab 05: reveal the lab flag after the injected alert is shown. */
(function () {
  const params = new URLSearchParams(window.location.search);
  if (params.get('checkStock') !== '1') return;

  const originalAlert = window.alert.bind(window);
  window.alert = function (message) {
    originalAlert(message);
    let flag = document.getElementById('lab05-flag');
    if (!flag) {
      flag = document.createElement('p');
      flag.id = 'lab05-flag';
      flag.setAttribute('role', 'status');
      flag.style.cssText = 'margin:1rem 0;padding:1rem;border:2px solid #2f6b45;background:#edf8ef;color:#174b2a;font-weight:bold';
      flag.textContent = 'Lab 05 flag: Flag{nmuBi2UMWOk0}';
      (document.querySelector('#stock-check-result') || document.querySelector('main') || document.body).appendChild(flag);
      window.setTimeout(function () { window.location.replace('index.php?page=products'); }, 30000);
    }
  };
})();
