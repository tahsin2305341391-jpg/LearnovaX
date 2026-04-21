// ============================================
// LearnovaX – Developers Page JS
// assets/js/developers.js
// ============================================

document.addEventListener('DOMContentLoaded', function () {

    // ── Scroll reveal for member cards ───────
    const cards = document.querySelectorAll(
        '.member-card, .roadmap-card, .stack-card'
    );

    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity   = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, i * 100);
                cardObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        card.style.opacity   = '0';
        card.style.transform = 'translateY(24px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        cardObserver.observe(card);
    });

    // ── Roadmap line animate ─────────────────
    const roadmapLines = document.querySelectorAll('.roadmap-line.show-line');
    const lineObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.height = 'calc(100% - 12px)';
                lineObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    roadmapLines.forEach(line => {
        line.style.height     = '0';
        line.style.transition = 'height 0.8s ease';
        lineObserver.observe(line);
    });

    // ── Highlight member card on URL hash ────
    const hash = window.location.hash;
    if (hash) {
        const target = document.querySelector(hash);
        if (target) {
            setTimeout(() => {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.style.boxShadow = '0 0 0 3px #6c63ff44';
                setTimeout(() => {
                    target.style.boxShadow = '';
                }, 2000);
            }, 400);
        }
    }
});
