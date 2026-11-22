/**
 * Client-Side Caching, Service Worker Registration, and Instant Link Prefetching
 */

// Register Service Worker
export function initServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js', { scope: '/' })
            .then((registration) => {
                // If there's an updated worker waiting, skip waiting
                if (registration.waiting) {
                    registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                }

                registration.addEventListener('updatefound', () => {
                    const installingWorker = registration.installing;
                    if (installingWorker) {
                        installingWorker.addEventListener('statechange', () => {
                            if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                installingWorker.postMessage({ type: 'SKIP_WAITING' });
                            }
                        });
                    }
                });
            })
            .catch((err) => {
                console.debug('[SW] Registration failed:', err);
            });
    });

    // Clear stale cached HTML pages on logout forms
    document.querySelectorAll('form[action*="logout"]').forEach((form) => {
        form.addEventListener('submit', () => {
            if (navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_PAGES_CACHE' });
            }
        });
    });
}

// Modern Speculation Rules API for instant Chrome/Edge prerender/prefetch
export function initSpeculationRules() {
    if (!HTMLScriptElement.supports || !HTMLScriptElement.supports('speculationrules')) {
        return;
    }

    const script = document.createElement('script');
    script.type = 'speculationrules';
    script.textContent = JSON.stringify({
        prefetch: [
            {
                where: {
                    and: [
                        { href_matches: "/*" },
                        { not: { href_matches: "/admin/*" } },
                        { not: { href_matches: "/login" } },
                        { not: { href_matches: "/register" } },
                        { not: { href_matches: "/logout" } },
                        { not: { href_matches: "/watchlist" } }
                    ]
                },
                eagerness: "moderate"
            }
        ]
    });
    document.head.appendChild(script);
}

// Hover/Touch Intent Link Prefetcher for all browsers
export function initIntentPrefetcher() {
    // Check if user prefers reduced data
    if (
        navigator.connection &&
        (navigator.connection.saveData || (navigator.connection.effectiveType && navigator.connection.effectiveType.includes('2g')))
    ) {
        return;
    }

    const prefetchedUrls = new Set();
    const currentOrigin = window.location.origin;

    function isPrefetchable(url) {
        if (!url || prefetchedUrls.has(url)) {
            return false;
        }

        try {
            const parsed = new URL(url, currentOrigin);
            if (parsed.origin !== currentOrigin) {
                return false;
            }

            const path = parsed.pathname;
            // Exclude non-page or non-read paths
            if (
                path.startsWith('/admin') ||
                path.startsWith('/login') ||
                path.startsWith('/register') ||
                path.startsWith('/logout') ||
                path === '/watchlist' ||
                path === window.location.pathname ||
                parsed.hash && parsed.pathname === window.location.pathname ||
                /\.(pdf|zip|rar|mp4|webm|m3u8)$/i.test(path)
            ) {
                return false;
            }

            return true;
        } catch {
            return false;
        }
    }

    function prefetchUrl(url) {
        if (!isPrefetchable(url)) {
            return;
        }

        prefetchedUrls.add(url);

        // Use low-priority fetch so it doesn't block critical page resources
        if (window.fetch) {
            fetch(url, {
                priority: 'low',
                credentials: 'same-origin',
                headers: { 'X-Purpose': 'preview' }
            }).catch(() => {});
        } else {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
        }
    }

    let hoverTimer = null;

    function closestLinkFromEvent(event) {
        return event.target instanceof Element ? event.target.closest('a') : null;
    }

    function handlePointerEnter(event) {
        const link = closestLinkFromEvent(event);
        if (!link || !link.href) {
            return;
        }

        clearTimeout(hoverTimer);
        // Wait 65ms hover dwell to confirm user intent
        hoverTimer = setTimeout(() => {
            prefetchUrl(link.href);
        }, 65);
    }

    function handlePointerLeave() {
        clearTimeout(hoverTimer);
    }

    function handleTouchStart(event) {
        const link = closestLinkFromEvent(event);
        if (link && link.href) {
            prefetchUrl(link.href);
        }
    }

    document.addEventListener('pointerenter', handlePointerEnter, { passive: true, capture: true });
    document.addEventListener('pointerleave', handlePointerLeave, { passive: true, capture: true });
    document.addEventListener('touchstart', handleTouchStart, { passive: true });
}

// Keyboard shortcut `/` to focus search
export function initSearchKeyboardShortcut() {
    window.addEventListener('keydown', (e) => {
        if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
}

// Click outside to close dropdowns
export function initDropdownOutsideClick() {
    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-menu-toggle]') && !e.target.closest('#genre-dropdown')) {
            const dropdown = document.getElementById('genre-dropdown');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        }
    });
}

// Auto-initialize
if (typeof window !== 'undefined') {
    initServiceWorker();
    initSpeculationRules();
    initIntentPrefetcher();
    initSearchKeyboardShortcut();
    initDropdownOutsideClick();
}
