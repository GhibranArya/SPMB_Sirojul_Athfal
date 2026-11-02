// Carousel ekstrakurikuler beranda (#eksCarousel). Geser dengan panah, titik, papan ketik, atau sentuhan.
(function () {
    const root = document.getElementById('eksCarousel');
    const dotsBox = document.getElementById('eksDots');
    if (!root) return;

    const track = root.querySelector('.eks-track');
    const slides = track.querySelectorAll('.eks-slide');
    const prev = root.querySelector('.eks-prev');
    const next = root.querySelector('.eks-next');
    let dots = [];

    // Jarak satu langkah = lebar kartu + celah antar kartu.
    const gap = () => parseFloat(getComputedStyle(track).columnGap) || 0;
    const step = () => slides[0].offsetWidth + gap();
    // Jumlah kartu yang terlihat penuh sekaligus (3 di desktop, 2 di tablet, 1 di ponsel).
    // Toleransi 0.1 karena lebar kartu berupa pecahan piksel (mis. 371.33 x 3 + 2 celah = 1160).
    const perView = () => Math.max(1, Math.floor((track.clientWidth + gap()) / step() + 0.1));
    const pages = () => Math.max(1, slides.length - perView() + 1);

    function current() {
        const atEnd = track.scrollLeft >= track.scrollWidth - track.clientWidth - 2;
        return atEnd ? pages() - 1 : Math.min(pages() - 1, Math.round(track.scrollLeft / step()));
    }

    function goTo(i) {
        track.scrollTo({ left: Math.max(0, Math.min(pages() - 1, i)) * step() });
    }

    function buildDots() {
        dotsBox.innerHTML = '';
        dots = [];
        if (pages() < 2) return;
        for (let i = 0; i < pages(); i++) {
            const b = document.createElement('button');
            b.type = 'button';
            b.setAttribute('aria-label', 'Tampilkan ekstrakurikuler halaman ' + (i + 1));
            b.addEventListener('click', () => goTo(i));
            dotsBox.appendChild(b);
            dots.push(b);
        }
    }

    function update() {
        const i = current();
        dots.forEach((d, k) => {
            d.classList.toggle('is-active', k === i);
            if (k === i) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current');
        });
        prev.disabled = i <= 0;
        next.disabled = i >= pages() - 1;
    }

    prev.addEventListener('click', () => goTo(current() - 1));
    next.addEventListener('click', () => goTo(current() + 1));
    track.addEventListener('scroll', update, { passive: true });
    track.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') { e.preventDefault(); goTo(current() - 1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); goTo(current() + 1); }
    });
    window.addEventListener('resize', () => { buildDots(); update(); });

    buildDots();
    update();
})();
