/**
 * TOC dinamica per l'override article/default.php.
 * Estrae H2 e H3 da .richtext-wrapper.lora e li inserisce nel page-index
 * prima della voce "Informazioni articolo".
 * H3 sono visualmente indentati tramite la classe Bootstrap ps-3.
 */
(() => {
    'use strict';

    const contentSection = document.querySelector('.richtext-wrapper.lora');
    const tocList = document.querySelector('[data-element="page-index"]');

    if (!contentSection || !tocList) return;

    // --- 1. Iniezione heading dinamici ---
    const headings = contentSection.querySelectorAll('h2, h3');

    if (headings.length) {
        const infoLink = tocList.querySelector('a[href="#informazioni-articolo"]');
        const infoLi   = infoLink ? infoLink.closest('li') : null;

        // Garantisce ID unici: se l'editor genera ID duplicati per heading con stesso testo,
        // li sovrascrive con un indice univoco.
        const usedIds = new Set();
        headings.forEach((heading, index) => {
            if (!heading.id || usedIds.has(heading.id)) {
                heading.id = 'toc-h-' + index;
            }
            usedIds.add(heading.id);
        });

        headings.forEach(heading => {
            const li   = document.createElement('li');
            li.className = 'nav-item';

            const a    = document.createElement('a');
            a.className = 'nav-link';
            a.href = '#' + heading.id;
            if (heading.tagName === 'H3') a.style.paddingLeft = '2.5rem';

            const span = document.createElement('span');
            span.textContent = heading.textContent.trim();

            a.appendChild(span);
            li.appendChild(a);

            if (infoLi) {
                tocList.insertBefore(li, infoLi);
            } else {
                tocList.appendChild(li);
            }
        });
    }

    // --- 2. scroll-margin-top su tutti i target per header sticky ---
    tocList.querySelectorAll('a[href^="#"]').forEach(link => {
        const el = document.getElementById(link.getAttribute('href').substring(1));
        if (el) el.style.scrollMarginTop = '40px';
    });

    // --- 3. Smooth scroll + aggiornamento hash URL ---
    tocList.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const href   = this.getAttribute('href');
            const target = document.querySelector(href);
            if (!target) return;
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.pushState(null, null, href);
            tocList.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // --- 4. Disabilita NavScroll di Bootstrap Italia ---
    // BI registra un proprio scroll listener che sovrascrive la classe active sulle voci
    // originali (solo #descrizione e #informazioni-articolo), interferendo con il nostro spy.
    const navEl = document.querySelector('.it-navscroll-wrapper[data-bs-navscroll]');
    if (navEl && typeof window.bootstrap !== 'undefined') {
        try {
            window.bootstrap.NavScroll?.getInstance?.(navEl)?.dispose?.();
        } catch (_) {}
    }

    // --- 5. Progress bar (reimplementata dopo dispose di BI) ---
    const progressBar = document.querySelector('.it-navscroll-progressbar');
    if (progressBar) {
        const updateProgress = () => {
            const scrollable = document.documentElement.scrollHeight - window.innerHeight;
            const pct = scrollable > 0 ? Math.min(100, (window.scrollY / scrollable) * 100) : 0;
            progressBar.style.width = pct + '%';
            progressBar.setAttribute('aria-valuenow', Math.round(pct));
        };
        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    }

    // --- 6. Scroll-spy: evidenzia la voce TOC corrispondente alla sezione corrente ---
    // Raccoglie i target in ordine DOM, senza duplicati (gestisce ID uguali dopo la fix al punto 1).
    const tocTargets = [];
    tocList.querySelectorAll('a[href^="#"]').forEach(link => {
        const el = document.getElementById(link.getAttribute('href').substring(1));
        if (el && !tocTargets.includes(el)) tocTargets.push(el);
    });

    const OFFSET = 50;
    let rafId = null;

    const updateActive = () => {
        let current = tocTargets[0]?.id ?? '';
        for (const el of tocTargets) {
            if (el.getBoundingClientRect().top <= OFFSET) {
                current = el.id;
            }
        }
        tocList.querySelectorAll('.nav-link').forEach(link => {
            link.classList.toggle('active', link.getAttribute('href') === '#' + current);
        });
    };

    window.addEventListener('scroll', () => {
        if (rafId) cancelAnimationFrame(rafId);
        rafId = requestAnimationFrame(updateActive);
    }, { passive: true });

    updateActive();
})();
