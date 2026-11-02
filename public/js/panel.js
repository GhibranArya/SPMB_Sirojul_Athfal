/* Interaksi ringan panel pendaftar & admin */
(function () {
  var wrap = document.createElement('div');
  wrap.className = 'toast-wrap';
  wrap.setAttribute('role', 'status');
  wrap.setAttribute('aria-live', 'polite');
  document.body.appendChild(wrap);

  window.toast = function (msg) {
    var t = document.createElement('div');
    t.className = 'toast';
    t.innerHTML = '<i class="fa-solid fa-circle-check" aria-hidden="true"></i><span></span>';
    t.querySelector('span').textContent = msg;
    wrap.appendChild(t);
    setTimeout(function () { t.remove(); }, 3800);
  };

  // Isi otomatis form modal konten: tombol membawa data-mode="baru|ubah" dan data-row (JSON baris).
  // Form memakai data-store, data-update (":id" diganti id baris), data-title-baru, data-title-ubah.
  window.isiForm = function (m, btn) {
    var f = m.querySelector("form"), baru = btn.dataset.mode === "baru";
    var row = baru ? {} : JSON.parse(btn.dataset.row);
    f.reset();
    f.action = baru ? f.dataset.store : f.dataset.update.replace(":id", row.id);
    var meth = f.querySelector("[name=_method]"); if (meth) meth.value = baru ? "POST" : "PUT";
    var rid = f.querySelector("[name=_id]"); if (rid) rid.value = baru ? "" : row.id;
    Array.prototype.forEach.call(f.elements, function (el) {
      if (!el.name || el.type === "file" || el.name === "_token" || el.name === "_method" || el.name === "_id") return;
      if (el.type === "checkbox") { el.checked = baru ? el.defaultChecked : !!row[el.name]; return; }
      if (!baru && row[el.name] !== undefined) el.value = row[el.name] == null ? "" : row[el.name];
    });
    var t = m.querySelector("[data-title]"); if (t) t.textContent = baru ? f.dataset.titleBaru : f.dataset.titleUbah;
    m.querySelectorAll(".alert").forEach(function (a) { a.remove(); });
    m.querySelectorAll("[data-hide-edit]").forEach(function (x) { x.style.display = baru ? "" : "none"; });
    m.querySelectorAll("[data-show-edit]").forEach(function (x) { x.style.display = baru ? "none" : ""; });
    m.querySelectorAll("img[data-preview]").forEach(function (im) { var s = baru ? "" : row[im.dataset.preview]; im.style.display = s ? "" : "none"; im.src = s ? im.dataset.base + s : ""; });
  };

  // Fokus modal: masuk ke dalam dialog saat dibuka, kembali ke pemicu saat ditutup
  var lastTrigger = null;
  function fokusModal(m) {
    var box = m.querySelector('.modal-box') || m;
    box.setAttribute('tabindex', '-1');
    box.focus();
  }
  function tutupModal(m) {
    m.classList.remove('open');
    if (lastTrigger && document.contains(lastTrigger)) lastTrigger.focus();
    lastTrigger = null;
  }

  // Modal: [data-open="id"] (+ data-fill → window.fillModal), [data-close]
  document.addEventListener('click', function (e) {
    var o = e.target.closest('[data-open]');
    if (o) {
      var m = document.getElementById(o.dataset.open);
      if (m) {
        lastTrigger = o;
        m.classList.add('open');
        if (o.dataset.fill && window.fillModal) window.fillModal(m, o);
        else if (o.dataset.row !== undefined || o.dataset.mode === 'baru') window.isiForm(m, o);
        fokusModal(m);
      }
    }
    if (e.target.closest('[data-close]') || e.target.classList.contains('modal')) {
      var mm = e.target.closest('.modal');
      if (mm) tutupModal(mm);
    }
    var info = e.target.closest('[data-toast]');
    if (info) { e.preventDefault(); window.toast(info.dataset.toast); }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.querySelectorAll('.modal.open').forEach(tutupModal);
    if (e.key === 'Tab') {
      var m = document.querySelector('.modal.open');
      if (!m) return;
      var f = Array.prototype.filter.call(
        m.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]):not([type=hidden]), select:not([disabled]), textarea:not([disabled]), [tabindex="0"]'),
        function (el) { return el.offsetParent !== null; }
      );
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && (document.activeElement === first || document.activeElement === m.querySelector('.modal-box'))) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  // Unggah berkas: cek ukuran (maks 2 MB) lalu kirim form otomatis
  document.querySelectorAll('input[type=file][data-autosubmit]').forEach(function (inp) {
    inp.addEventListener('change', function () {
      var f = inp.files[0];
      if (!f) return;
      if (f.size > 2 * 1024 * 1024) { window.toast('Ukuran berkas maksimal 2 MB.'); inp.value = ''; return; }
      window.toast('Mengunggah ' + f.name + '…');
      inp.form.submit();
    });
  });

  // Cegah klik ganda pada tombol submit
  document.querySelectorAll('form[data-once]').forEach(function (f) {
    f.addEventListener('submit', function () {
      f.querySelectorAll('button[type=submit]').forEach(function (b) { b.disabled = true; });
    });
  });

  // Sidebar admin (mobile)
  var mb = document.getElementById('menuBtn');
  if (mb) mb.addEventListener('click', function () {
    var buka = document.getElementById('adSide').classList.toggle('open');
    mb.setAttribute('aria-expanded', buka);
  });
})();
