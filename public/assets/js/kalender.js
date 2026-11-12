// Kalender ketersediaan (AvailabilityCalendar) - Usecase #6
(() => {
  const cfg = JSON.parse(document.getElementById('calData').textContent);
  const blocked = new Set(cfg.blocked);
  const BULAN = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  const SHORT = BULAN.map((b) => b.slice(0, 3));
  const $ = (id) => document.getElementById(id);

  const pad = (n) => String(n).padStart(2, '0');
  const key = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
  const parse = (k) => { const [y, m, d] = k.split('-').map(Number); return new Date(y, m - 1, d); };
  const fmt = (k) => { const d = parse(k); return `${d.getDate()} ${SHORT[d.getMonth()]} ${d.getFullYear()}, 09:00`; };
  const rp = (n) => 'Rp' + n.toLocaleString('id-ID');

  const [ty, tm] = cfg.today.split('-').map(Number);
  let viewY = ty, viewM = tm - 1;
  let pickup = null, ret = null;

  function rangeBlocked(a, b) {
    for (let d = parse(a), end = parse(b); d <= end; d.setDate(d.getDate() + 1)) {
      if (blocked.has(key(d.getFullYear(), d.getMonth(), d.getDate()))) return true;
    }
    return false;
  }

  function showError(text) {
    $('msgDateBooked').textContent = text;
    bootstrap.Toast.getOrCreateInstance($('toastError')).show();
  }

  function render() {
    $('lblMonth').textContent = `${BULAN[viewM]} ${viewY}`;
    $('btnPrevMonth').disabled = viewY === ty && viewM === tm - 1;

    const grid = $('calGrid');
    grid.innerHTML = '';
    const first = new Date(viewY, viewM, 1).getDay();
    const days = new Date(viewY, viewM + 1, 0).getDate();

    for (let i = 0; i < first; i++) grid.appendChild(document.createElement('span'));

    for (let d = 1; d <= days; d++) {
      const k = key(viewY, viewM, d);
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'rm-day';
      btn.textContent = d;

      const inRange = pickup && k >= pickup && k <= (ret || pickup);
      if (k < cfg.today) { btn.classList.add('past'); btn.disabled = true; }
      else if (blocked.has(k)) { btn.classList.add('blocked'); btn.setAttribute('aria-label', `${d} (booked)`); }
      else if (inRange) btn.classList.add('selected');

      if (ret && k === ret) {
        const tag = document.createElement('small');
        tag.textContent = 'Return';
        btn.appendChild(tag);
      }
      btn.addEventListener('click', () => pick(k));
      grid.appendChild(btn);
    }
    updateSummary();
  }

  function pick(k) {
    $('msgDateBooked').textContent = '';
    if (blocked.has(k)) return showError('This date is already booked.');

    if (!pickup || ret || k < pickup) {
      pickup = k;
      ret = null;
    } else if (k === pickup) {
      return showError('Sewa minimal 24 jam. Tanggal Return harus minimal 1 hari setelah tanggal Pickup.');
    } else if (rangeBlocked(pickup, k)) {
      return showError('This date is already booked.');
    } else {
      ret = k;
    }
    render();
  }

  function updateSummary() {
    $('txtPickupDate').value = pickup ? fmt(pickup) : '';
    $('txtReturnDate').value = ret ? fmt(ret) : '';

    if (!pickup || !ret) {
      $('lblDuration').textContent = '-';
      $('lblRateCalc').textContent = '-';
      $('lblTotalRental').textContent = 'Rp0';
      return;
    }
    // Hitungan sewa 24 jam: selisih hari (Return minimal H+1)
    const days = Math.max(1, Math.round((parse(ret) - parse(pickup)) / 86400000));
    $('lblDuration').textContent = `${days} day${days > 1 ? 's' : ''} (${days * 24} hours)`;
    $('lblRateCalc').textContent = `${days} × ${rp(cfg.rate)}`;
    $('lblTotalRental').textContent = rp(days * cfg.rate);
  }

  $('btnPrevMonth').addEventListener('click', () => { if (--viewM < 0) { viewM = 11; viewY--; } render(); });
  $('btnNextMonth').addEventListener('click', () => { if (++viewM > 11) { viewM = 0; viewY++; } render(); });

  $('btnProceedCheckout').addEventListener('click', () => {
    if (!pickup || !ret) return showError('Pilih tanggal Pickup dan Return terlebih dahulu.');
    if (pickup === ret) return showError('Sewa minimal 24 jam. Tanggal Return harus minimal 1 hari setelah tanggal Pickup.');
    window.location.href = `${cfg.checkoutUrl}&mulai=${pickup}&selesai=${ret}`;
  });

  render();
})();
