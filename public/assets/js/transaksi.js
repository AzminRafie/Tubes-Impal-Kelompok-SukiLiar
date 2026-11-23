// Interaksi UI Checkout & Pembayaran - RentAMobil
(() => {
  const MAX_BYTES = 5 * 1024 * 1024; // 5 MB
  const ALLOWED_TYPES = new Set(['image/jpeg', 'image/png']);
  const ALLOWED_EXT = /\.(jpe?g|png)$/i;

  function formatSize(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
  }

  // 1. Area upload gambar (KTP & Bukti Transfer) dengan preview & validasi sisi klien
  document.querySelectorAll('[data-upload-zone]').forEach((zone) => {
    const input = zone.querySelector('.rm-upload-input');
    const placeholder = zone.querySelector('.rm-upload-placeholder');
    const previewBox = zone.querySelector('.rm-upload-preview');
    const previewImg = zone.querySelector('.rm-preview-img');
    const nameEl = zone.querySelector('.rm-file-name');
    const sizeEl = zone.querySelector('.rm-file-size');
    const errorEl = zone.parentElement.querySelector('.rm-upload-error');

    function setError(msg) {
      if (!errorEl) return;
      if (msg) {
        errorEl.textContent = msg;
        errorEl.classList.remove('d-none');
      } else {
        errorEl.textContent = '';
        errorEl.classList.add('d-none');
      }
    }

    function resetPreview() {
      placeholder.classList.remove('d-none');
      previewBox.classList.add('d-none');
      previewImg.removeAttribute('src');
      nameEl.textContent = '';
      sizeEl.textContent = '';
    }

    function handleFile(file) {
      setError('');
      if (!file) {
        resetPreview();
        return;
      }
      if (!ALLOWED_EXT.test(file.name) || (file.type && !ALLOWED_TYPES.has(file.type))) {
        input.value = '';
        resetPreview();
        setError('Format file tidak valid. Hanya gambar JPG, JPEG, atau PNG yang diperbolehkan.');
        return;
      }
      if (file.size <= 0 || file.size > MAX_BYTES) {
        input.value = '';
        resetPreview();
        setError('Ukuran file melebihi batas maksimal 5 MB.');
        return;
      }

      nameEl.textContent = file.name;
      sizeEl.textContent = formatSize(file.size);
      const reader = new FileReader();
      reader.onload = (ev) => {
        previewImg.src = ev.target.result;
        placeholder.classList.add('d-none');
        previewBox.classList.remove('d-none');
      };
      reader.readAsDataURL(file);
    }

    zone.querySelectorAll('.rm-upload-trigger').forEach((btn) => {
      btn.addEventListener('click', () => input.click());
    });

    input.addEventListener('change', () => {
      handleFile(input.files && input.files[0] ? input.files[0] : null);
    });

    zone.addEventListener('dragover', (e) => {
      e.preventDefault();
      zone.classList.add('is-dragover');
    });
    zone.addEventListener('dragleave', () => zone.classList.remove('is-dragover'));
    zone.addEventListener('drop', (e) => {
      e.preventDefault();
      zone.classList.remove('is-dragover');
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        const dt = new DataTransfer();
        dt.items.add(e.dataTransfer.files[0]);
        input.files = dt.files;
        handleFile(e.dataTransfer.files[0]);
      }
    });
  });

  // 2. Pilihan metode pembayaran interaktif (Transfer BCA / Mandiri / BRI)
  const methodRadios = document.querySelectorAll('.rm-method-radio');
  const bankPanels = document.querySelectorAll('[data-bank-panel]');
  const selectedMethodLabel = document.getElementById('lblSelectedMethod');

  function syncBankPanel() {
    const active = document.querySelector('.rm-method-radio:checked');
    if (!active) return;
    const val = active.value;
    if (selectedMethodLabel) selectedMethodLabel.textContent = val;
    bankPanels.forEach((panel) => {
      panel.classList.toggle('d-none', panel.getAttribute('data-bank-panel') !== val);
    });
  }

  methodRadios.forEach((radio) => radio.addEventListener('change', syncBankPanel));
  syncBankPanel();

  // 3. Tombol salin nomor rekening / nominal
  document.querySelectorAll('.rm-copy-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const text = btn.getAttribute('data-copy') || '';
      if (!text) return;
      const originalHtml = btn.innerHTML;
      try {
        await navigator.clipboard.writeText(text);
        btn.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(() => { btn.innerHTML = originalHtml; }, 1500);
      } catch {
        // Fallback diam-diam jika browser membatasi clipboard API
      }
    });
  });

  // 4. Validasi form & feedback loading saat submit
  document.querySelectorAll('.rm-submit-form').forEach((form) => {
    form.addEventListener('submit', (e) => {
      const fileInput = form.querySelector('.rm-upload-input');
      const errorEl = form.querySelector('.rm-upload-error');

      if (fileInput && fileInput.required && (!fileInput.files || fileInput.files.length === 0)) {
        e.preventDefault();
        if (errorEl) {
          errorEl.textContent = 'Silakan pilih file gambar terlebih dahulu sebelum melanjutkan.';
          errorEl.classList.remove('d-none');
        }
        return;
      }

      const alamatInput = form.querySelector('#txtAlamat');
      if (alamatInput && alamatInput.value.trim().length < 5) {
        e.preventDefault();
        alamatInput.focus();
        alamatInput.classList.add('is-invalid');
        return;
      }

      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        const loadingText = submitBtn.getAttribute('data-loading-text') || 'Memproses...';
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${loadingText}`;
      }
    });
  });
})();
