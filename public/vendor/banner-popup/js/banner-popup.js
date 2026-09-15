/**
 * inudev/banner-popup — Frontend module
 *
 * Fetches active banners from the server, respects show_frequency via cookies,
 * and renders/triggers each popup at the right moment.
 *
 * Usage: include this script on every public page.
 * The x-banner-popup Blade component does this automatically.
 */

(function () {
    'use strict';

    const COOKIE_PREFIX = window.BannerPopupConfig?.cookiePrefix ?? 'bp_';
    const ENDPOINT      = window.BannerPopupConfig?.endpoint    ?? '/banner-popup/active';
    const SEEN_ENDPOINT = window.BannerPopupConfig?.seenEndpoint ?? '/banner-popup';
    const CURRENT_PAGE  = window.BannerPopupConfig?.currentPage  ?? '';

    // -------------------------------------------------------------------------
    // Cookie helpers
    // -------------------------------------------------------------------------

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function setCookie(name, value, days) {
        let expires = '';
        if (days === -1) {
            // Session cookie — removed when browser closes
            expires = '';
        } else if (days > 0) {
            const d = new Date();
            d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
            expires = '; expires=' + d.toUTCString();
        }
        document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
    }

    // -------------------------------------------------------------------------
    // Frequency guard
    // -------------------------------------------------------------------------

    /**
     * Returns true if the banner should be shown based on show_frequency.
     *   0  → always show
     *   N  → show only if last seen was more than N days ago
     *  -1  → show only once (session cookie)
     */
    function shouldShow(banner) {
        const freq = banner.show_frequency;
        if (freq === 0) return true;

        const cookieName = COOKIE_PREFIX + banner.id;
        const lastSeen   = getCookie(cookieName);

        if (!lastSeen) return true;

        if (freq === -1) {
            // "Once" — cookie exists → already shown
            return false;
        }

        const diffMs   = Date.now() - parseInt(lastSeen, 10);
        const diffDays = diffMs / (1000 * 60 * 60 * 24);
        return diffDays >= freq;
    }

    function recordSeen(banner) {
        const freq       = banner.show_frequency;
        const cookieName = COOKIE_PREFIX + banner.id;

        if (freq === 0) return; // no tracking needed

        const value = freq === -1
            ? '1'                   // session cookie, value irrelevant
            : Date.now().toString();

        setCookie(cookieName, value, freq === -1 ? undefined : freq + 1);

        // Notify the server (fire-and-forget)
        fetch(`${SEEN_ENDPOINT}/${banner.id}/seen`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
        }).catch(() => {});
    }

    // -------------------------------------------------------------------------
    // Responsive image selection
    // -------------------------------------------------------------------------

    function getBestImage(images) {
        const w = window.innerWidth;
        if (w <= 640 && images.mobile)  return images.mobile;
        if (w <= 1024 && images.tablet) return images.tablet;
        return images.desktop ?? images.tablet ?? images.mobile ?? null;
    }

    // -------------------------------------------------------------------------
    // DOM builder
    // -------------------------------------------------------------------------

    function buildPopup(banner) {
        const imgSrc = getBestImage(banner.images);
        if (!imgSrc) return null;

        const overlay = document.createElement('div');
        overlay.className = 'bp-overlay';
        overlay.dataset.bannerId = banner.id;
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-label', 'Popup banner');

        const box = document.createElement('div');
        box.className = 'bp-box';

        const closeBtn = document.createElement('button');
        closeBtn.className = 'bp-close';
        closeBtn.setAttribute('aria-label', 'Cerrar popup');
        closeBtn.innerHTML = '&times;';

        const img = document.createElement('img');
        img.className = 'bp-img';
        img.src = imgSrc;
        img.alt = '';
        img.loading = 'eager';

        // Update image on resize
        window.addEventListener('resize', () => {
            const newSrc = getBestImage(banner.images);
            if (newSrc && img.src !== newSrc) img.src = newSrc;
        });

        if (banner.link_url) {
            const link = document.createElement('a');
            link.href   = banner.link_url;
            link.target = banner.link_target ?? '_self';
            link.setAttribute('rel', banner.link_target === '_blank' ? 'noopener noreferrer' : '');
            link.appendChild(img);
            box.appendChild(link);
        } else {
            box.appendChild(img);
        }

        box.appendChild(closeBtn);
        overlay.appendChild(box);

        function dismiss() {
            overlay.classList.remove('bp-visible');
            setTimeout(() => overlay.remove(), 300);
        }

        closeBtn.addEventListener('click', dismiss);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) dismiss();
        });
        document.addEventListener('keydown', function onKey(e) {
            if (e.key === 'Escape') { dismiss(); document.removeEventListener('keydown', onKey); }
        });

        return overlay;
    }

    function show(banner) {
        const el = buildPopup(banner);
        if (!el) return;

        document.body.appendChild(el);
        // Force reflow before adding visible class for CSS transition
        requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add('bp-visible')));

        recordSeen(banner);
    }

    // -------------------------------------------------------------------------
    // Trigger handlers
    // -------------------------------------------------------------------------

    function scheduleBanner(banner) {
        if (!shouldShow(banner)) return;

        switch (banner.trigger) {
            case 'page_load':
                show(banner);
                break;

            case 'delay': {
                const seconds = banner.trigger_delay ?? 3;
                setTimeout(() => show(banner), seconds * 1000);
                break;
            }

            case 'exit_intent': {
                let triggered = false;
                function onMouseLeave(e) {
                    if (triggered || e.clientY > 10) return;
                    triggered = true;
                    document.removeEventListener('mouseleave', onMouseLeave);
                    show(banner);
                }
                document.addEventListener('mouseleave', onMouseLeave);
                break;
            }
        }
    }

    // -------------------------------------------------------------------------
    // Bootstrap
    // -------------------------------------------------------------------------

    async function init() {
        const url = CURRENT_PAGE
            ? `${ENDPOINT}?page=${encodeURIComponent(CURRENT_PAGE)}`
            : ENDPOINT;

        let banners;
        try {
            const res = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            banners = await res.json();
        } catch {
            return; // Network error — fail silently
        }

        if (!Array.isArray(banners) || banners.length === 0) return;

        banners.forEach(scheduleBanner);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
