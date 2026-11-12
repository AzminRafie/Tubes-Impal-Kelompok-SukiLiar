// Kirim ulang form saat filter/urutan berubah, dan saat user berhenti mengetik (debounce).
(() => {
  const form = document.getElementById('filterForm');
  if (!form) return;

  ['dropTransmission', 'dropAvailability', 'dropSort'].forEach((id) => {
    document.getElementById(id)?.addEventListener('change', () => form.submit());
  });

  const search = document.getElementById('txtSearchCar');
  let timer;
  search?.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => form.submit(), 500);
  });

  // Kembalikan fokus ke kolom pencarian setelah halaman dimuat ulang
  if (search && search.value) {
    search.focus();
    search.setSelectionRange(search.value.length, search.value.length);
  }
})();
