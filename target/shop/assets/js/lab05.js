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
      flag.className = 'shop-flash is-success';
      flag.setAttribute('role', 'status');
      flag.textContent = 'Lab 05 flag MD5: c2da4a1c8260ff0c8c66db7e2b49524d';
      (document.querySelector('#stock-check-result') || document.querySelector('main') || document.body).appendChild(flag);
      window.setTimeout(function () { window.location.replace('index.php?page=products'); }, 30000);
    }
  };
})();
