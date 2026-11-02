/* Galeri beranda: filter MI/SMP (URL ikut berubah: ?unit=mi#galeri) dan penampil foto. Tanpa JS, tautan filter tetap bekerja lewat server. */
(function () {
  var root = document.getElementById('galeri');
  if (!root) return;

  var tabs = root.querySelectorAll('.gl-filter a');
  var items = root.querySelectorAll('.gl-item');
  var jumlah = root.querySelector('[data-gl-jumlah]');
  var kosong = root.querySelector('.gl-empty');

  function terlihat() {
    return Array.prototype.filter.call(items, function (li) { return !li.hidden; });
  }

  function terapkan(unit, ubahUrl) {
    Array.prototype.forEach.call(items, function (li) {
      li.hidden = !!unit && li.dataset.unit !== '' && li.dataset.unit !== unit;
    });
    Array.prototype.forEach.call(tabs, function (a) {
      if (a.dataset.unit === unit) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
    });
    var n = terlihat().length;
    if (jumlah) jumlah.textContent = n;
    if (kosong) kosong.hidden = n > 0;
    if (ubahUrl) history.replaceState(null, '', unit ? '?unit=' + unit.toLowerCase() : location.pathname);
  }

  Array.prototype.forEach.call(tabs, function (a) {
    a.addEventListener('click', function (e) {
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return; // buka di tab baru tetap normal
      e.preventDefault();
      terapkan(a.dataset.unit, true);
    });
  });

  // ---- Penampil foto
  var dlg = document.getElementById('gl-lb');
  var gambar = dlg.querySelector('.gl-lb-img');
  var judul = dlg.querySelector('.gl-lb-judul');
  var ket = dlg.querySelector('.gl-lb-ket');
  var nomor = dlg.querySelector('.gl-lb-no');
  var daftar = [];
  var indeks = 0;

  function tampil(i) {
    indeks = (i + daftar.length) % daftar.length;
    var b = daftar[indeks];
    gambar.src = b.dataset.src;
    gambar.alt = b.querySelector('img').alt;
    judul.textContent = b.dataset.judul;
    ket.textContent = b.dataset.ket || '';
    nomor.textContent = (indeks + 1) + ' dari ' + daftar.length;
    new Image().src = daftar[(indeks + 1) % daftar.length].dataset.src; // muat foto berikutnya lebih dulu
  }

  function buka(tombol) {
    daftar = terlihat().map(function (li) { return li.querySelector('.gl-open'); });
    document.documentElement.classList.add('gl-lock');
    dlg.showModal();
    tampil(daftar.indexOf(tombol));
    dlg.querySelector('[data-gl="tutup"]').focus();
  }

  root.addEventListener('click', function (e) {
    var b = e.target.closest('.gl-open');
    if (!b) return;
    if (typeof dlg.showModal !== 'function') { window.open(b.dataset.src, '_blank', 'noopener'); return; }
    buka(b);
    dlg._pemicu = b;
  });

  dlg.addEventListener('click', function (e) {
    var aksi = e.target.closest('[data-gl]');
    if (aksi) {
      if (aksi.dataset.gl === 'tutup') dlg.close();
      else tampil(indeks + (aksi.dataset.gl === 'berikutnya' ? 1 : -1));
    } else if (e.target === dlg) {
      dlg.close(); // klik di luar foto
    }
  });

  dlg.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowRight') tampil(indeks + 1);
    else if (e.key === 'ArrowLeft') tampil(indeks - 1);
  });

  // Geser jari kiri/kanan sebagai pelengkap (tombol tetap tersedia)
  var awalX = null;
  dlg.addEventListener('touchstart', function (e) { awalX = e.changedTouches[0].clientX; }, { passive: true });
  dlg.addEventListener('touchend', function (e) {
    if (awalX === null) return;
    var dx = e.changedTouches[0].clientX - awalX;
    awalX = null;
    if (Math.abs(dx) > 60) tampil(indeks + (dx < 0 ? 1 : -1));
  }, { passive: true });

  dlg.addEventListener('close', function () {
    document.documentElement.classList.remove('gl-lock');
    gambar.removeAttribute('src');
    if (dlg._pemicu) dlg._pemicu.focus();
  });
})();
