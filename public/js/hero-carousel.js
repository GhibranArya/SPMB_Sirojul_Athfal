// Carousel foto hero beranda (#heroCarousel). Otomatis geser tiap 5 detik,
// berhenti saat kursor/fokus di atasnya, dan tidak jalan otomatis bila
// pengguna meminta gerak berkurang (prefers-reduced-motion).
(function () {
    const root = document.getElementById('heroCarousel');
    if (!root) return;

    const slides = root.querySelectorAll('.hc-slide');
    const dots = root.querySelectorAll('.hc-dot');
    const caption = root.querySelector('.hc-caption');
    const thumbs = root.querySelector('.hc-thumbs');
    const toggle = root.querySelector('.hc-toggle');
    const INTERVAL = 5000;

    let index = 0;
    let timer = null;
    let userPaused = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let hovering = false;

    function show(n) {
        index = (n + slides.length) % slides.length;
        slides.forEach((s, i) => {
            s.classList.toggle('is-active', i === index);
            s.setAttribute('aria-hidden', i === index ? 'false' : 'true');
        });
        dots.forEach((d, i) => {
            d.classList.toggle('is-active', i === index);
            if (i === index) d.setAttribute('aria-current', 'true');
            else d.removeAttribute('aria-current');
        });
        caption.textContent = slides[index].dataset.caption;

        // Geser deretan thumbnail (bukan halaman) supaya thumbnail aktif tetap terlihat.
        const d = dots[index];
        if (thumbs && d) {
            thumbs.scrollTo({ left: d.offsetLeft - (thumbs.clientWidth - d.offsetWidth) / 2, behavior: 'smooth' });
        }
    }

    function stop() {
        clearInterval(timer);
        timer = null;
    }

    function start() {
        stop();
        if (userPaused || hovering || document.hidden) return;
        timer = setInterval(() => show(index + 1), INTERVAL);
    }

    function go(n) {
        show(n);
        start();
    }

    function renderToggle() {
        toggle.innerHTML = '<i class="fa-solid ' + (userPaused ? 'fa-play' : 'fa-pause') + '"></i>';
        toggle.setAttribute('aria-label', userPaused ? 'Putar geser otomatis' : 'Jeda geser otomatis');
    }

    root.querySelector('.hc-prev').addEventListener('click', () => go(index - 1));
    root.querySelector('.hc-next').addEventListener('click', () => go(index + 1));
    dots.forEach((d, i) => d.addEventListener('click', () => go(i)));
    toggle.addEventListener('click', () => {
        userPaused = !userPaused;
        renderToggle();
        start();
    });

    root.addEventListener('mouseenter', () => { hovering = true; stop(); });
    root.addEventListener('mouseleave', () => { hovering = false; start(); });
    root.addEventListener('focusin', () => { hovering = true; stop(); });
    root.addEventListener('focusout', () => { hovering = false; start(); });
    document.addEventListener('visibilitychange', start);

    root.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') go(index - 1);
        else if (e.key === 'ArrowRight') go(index + 1);
    });

    // Geser jari / seret
    let startX = null;
    root.addEventListener('pointerdown', (e) => { startX = e.clientX; });
    root.addEventListener('pointerup', (e) => {
        if (startX === null) return;
        const dx = e.clientX - startX;
        startX = null;
        if (Math.abs(dx) > 40) go(index + (dx < 0 ? 1 : -1));
    });
    root.addEventListener('pointercancel', () => { startX = null; });

    renderToggle();
    start();
})();
