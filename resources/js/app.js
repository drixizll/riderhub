import './bootstrap';
import 'leaflet/dist/leaflet.css';

import L from 'leaflet';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.L = L;
window.Chart = Chart;

Alpine.start();

document.documentElement.classList.add('has-motion');

function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'dark' ? '#0b1120' : '#7c3aed');

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const label = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        button.setAttribute('aria-label', label);
        button.querySelector('[data-theme-label]')?.replaceChildren(document.createTextNode(theme === 'dark' ? 'Light mode' : 'Dark mode'));
        button.querySelector('[data-theme-sun]')?.classList.toggle('hidden', theme === 'dark');
        button.querySelector('[data-theme-moon]')?.classList.toggle('hidden', theme !== 'dark');
    });
}

applyTheme(document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light');

const colorSchemePreference = window.matchMedia('(prefers-color-scheme: dark)');
colorSchemePreference.addEventListener('change', (event) => {
    try {
        if (localStorage.getItem('riderhub-theme')) return;
    } catch (error) {
        console.warn('RiderHub could not read the saved theme preference.', error);
    }

    applyTheme(event.matches ? 'dark' : 'light');
});

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
        try {
            localStorage.setItem('riderhub-theme', nextTheme);
        } catch (error) {
            console.warn('RiderHub could not save the theme preference in this browser.', error);
        }
    });
});

const passwordInput = document.querySelector('[data-password-input]');
const strengthPanel = document.querySelector('[data-password-strength]');
if (passwordInput && strengthPanel) {
    const strengthLabel = strengthPanel.querySelector('[data-strength-label]');
    const strengthSegments = [...strengthPanel.querySelectorAll('[data-strength-segment]')];
    const passwordRequirements = {
        length: (value) => value.length >= 8,
        lowercase: (value) => /[a-z]/.test(value),
        uppercase: (value) => /[A-Z]/.test(value),
        number: (value) => /\d/.test(value),
        symbol: (value) => /[^A-Za-z0-9]/.test(value),
    };
    const strengthColors = {
        weak: 'bg-rose-500',
        medium: 'bg-amber-500',
        strong: 'bg-emerald-500',
    };

    const updatePasswordStrength = () => {
        const value = passwordInput.value;
        const score = Object.entries(passwordRequirements).reduce((total, [requirement, passes]) => {
            const matched = passes(value);
            strengthPanel.querySelector(`[data-password-check="${requirement}"]`)?.classList.toggle('password-check-passed', matched);
            return total + Number(matched);
        }, 0);
        const strength = score === 0 ? null : score <= 2 ? 'weak' : score <= 4 ? 'medium' : 'strong';

        strengthLabel.textContent = !value ? 'Enter a password' : `${strength[0].toUpperCase()}${strength.slice(1)} password`;
        strengthLabel.dataset.strength = strength ?? 'empty';
        strengthSegments.forEach((segment, index) => {
            segment.classList.remove('bg-slate-200', ...Object.values(strengthColors));
            segment.classList.add(strength && index < score ? strengthColors[strength] : 'bg-slate-200');
        });
    };

    passwordInput.addEventListener('input', updatePasswordStrength);
    updatePasswordStrength();
}

document.querySelectorAll('[data-profile-photo-input]').forEach((input) => {
    input.addEventListener('change', async () => {
        const file = input.files?.[0];
        if (!file) return;

        const form = input.closest('form');
        const label = form?.querySelector('[data-profile-photo-label]');
        if (label) label.textContent = 'Preparing photo…';

        let finalFile = file;
        try {
            // Compress & resize large iOS/Android camera photos in-browser so uploads never exceed server limits
            const bitmap = await new Promise((resolve, reject) => {
                const img = new Image();
                const objUrl = URL.createObjectURL(file);
                img.onload = () => {
                    URL.revokeObjectURL(objUrl);
                    resolve(img);
                };
                img.onerror = (err) => {
                    URL.revokeObjectURL(objUrl);
                    reject(err);
                };
                img.src = objUrl;
            });

            const maxDim = 900;
            const scale = Math.min(1, maxDim / Math.max(bitmap.naturalWidth || 1, bitmap.naturalHeight || 1));
            const targetW = Math.max(1, Math.round((bitmap.naturalWidth || maxDim) * scale));
            const targetH = Math.max(1, Math.round((bitmap.naturalHeight || maxDim) * scale));

            const canvas = document.createElement('canvas');
            canvas.width = targetW;
            canvas.height = targetH;
            const ctx = canvas.getContext('2d');
            if (ctx) {
                ctx.drawImage(bitmap, 0, 0, targetW, targetH);
                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
                if (blob && typeof DataTransfer !== 'undefined') {
                    const compressed = new File([blob], 'profile-photo.jpg', { type: 'image/jpeg', lastModified: Date.now() });
                    const dt = new DataTransfer();
                    dt.items.add(compressed);
                    input.files = dt.files;
                    finalFile = compressed;
                }
            }
        } catch {
            // Keep original file if browser cannot decode/re-encode
        }

        const preview = document.querySelector('[data-profile-photo-preview]');
        if (preview) {
            const image = document.createElement('img');
            image.src = URL.createObjectURL(finalFile);
            image.alt = 'Selected profile photo preview';
            image.className = 'h-20 w-20 rounded-2xl border-2 border-white/40 object-cover shadow-xl sm:h-24 sm:w-24';
            image.addEventListener('load', () => URL.revokeObjectURL(image.src), { once: true });
            preview.replaceWith(image);
            image.dataset.profilePhotoPreview = '';
        }

        if (label) label.textContent = 'Photo ready ✓';
        form?.querySelector('button[type="submit"]')?.removeAttribute('disabled');
    });
});

document.querySelectorAll('[data-rating-choices]').forEach((group) => {
    const choices = [...group.querySelectorAll('.rating-choice')];
    const updateRatingStars = (selectedScore) => {
        choices.forEach((choice, index) => choice.classList.toggle('is-selected', index < selectedScore));
    };

    group.addEventListener('change', (event) => {
        if (event.target instanceof HTMLInputElement && event.target.name === 'score') {
            updateRatingStars(Number(event.target.value));
        }
    });
    group.addEventListener('mouseover', (event) => {
        const choice = event.target.closest('.rating-choice');
        if (choice) updateRatingStars(choices.indexOf(choice) + 1);
    });
    group.addEventListener('mouseleave', () => {
        const selected = group.querySelector('input[name="score"]:checked');
        if (selected) updateRatingStars(Number(selected.value));
    });
});

const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
if ('IntersectionObserver' in window && !motionPreference.matches) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.dataset.revealed = 'true';
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -32px 0px' });

    document.querySelectorAll('.reveal-on-scroll').forEach((element) => revealObserver.observe(element));
} else {
    document.querySelectorAll('.reveal-on-scroll').forEach((element) => {
        element.dataset.revealed = 'true';
    });
}

document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    const link = event.target.closest('a[href]');
    if (!link || link.target === '_blank' || link.hasAttribute('download')) return;

    const destination = new URL(link.href, window.location.href);
    if (destination.origin !== window.location.origin || destination.pathname === window.location.pathname && destination.hash) return;

    document.body.classList.add('is-navigating');
    window.setTimeout(() => document.body.classList.remove('is-navigating'), 10000);
});

document.addEventListener('submit', (event) => {
    if (event.defaultPrevented) return;

    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.dataset.noLoading === 'true') return;

    document.body.classList.add('is-navigating');
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');

        if (button instanceof HTMLButtonElement && !button.querySelector('.loading-spinner')) {
            const spinner = document.createElement('span');
            spinner.className = 'loading-spinner inline-block h-4 w-4 rounded-full border-2 border-current border-r-transparent align-middle';
            spinner.setAttribute('aria-hidden', 'true');
            button.prepend(spinner);
            button.classList.add('gap-2');
        }
    });
});

window.addEventListener('pageshow', () => document.body.classList.remove('is-navigating'));

let deferredInstallPrompt = null;
window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    document.querySelectorAll('[data-install-app]').forEach((button) => {
        button.hidden = false;
    });
});

document.querySelectorAll('[data-install-app]').forEach((button) => {
    button.addEventListener('click', async () => {
        if (!deferredInstallPrompt) return;

        try {
            await deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
        } catch (error) {
            console.error('RiderHub could not open the browser installation prompt.', error);
        } finally {
            document.querySelectorAll('[data-install-app]').forEach((installButton) => {
                installButton.hidden = true;
            });
            deferredInstallPrompt = null;
        }
    });
});

window.addEventListener('appinstalled', () => {
    document.querySelectorAll('[data-install-app]').forEach((button) => {
        button.hidden = true;
    });
});

if ('serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .catch((error) => console.error('RiderHub could not register its offline app shell.', error));
    });
}

// Global 60fps Interactive RiderHub Mobility Network Background Canvas
const techCanvas = document.querySelector('[data-riderhub-canvas]');
if (techCanvas instanceof HTMLCanvasElement && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const ctx = techCanvas.getContext('2d');
    let width = 0;
    let height = 0;
    let nodes = [];
    let pointer = { x: -999, y: -999 };

    const initNodes = (force = false) => {
        const host = techCanvas.parentElement || document.body;
        const rect = host.getBoundingClientRect();
        const nextWidth = Math.max(window.innerWidth, rect.width || window.innerWidth);
        const nextHeight = Math.max(window.innerHeight, rect.height || window.innerHeight);

        // Ignore tiny iOS Safari URL-bar show/hide height jitters during vertical scroll
        if (!force && width > 0 && Math.abs(nextWidth - width) < 20 && Math.abs(nextHeight - height) < 120) {
            return;
        }

        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        width = nextWidth;
        height = nextHeight;
        techCanvas.width = Math.floor(width * dpr);
        techCanvas.height = Math.floor(height * dpr);
        if (ctx) {
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }

        const isMobile = width < 640;
        const count = isMobile
            ? Math.min(26, Math.max(16, Math.floor((width * height) / 26000)))
            : Math.min(54, Math.max(24, Math.floor((width * height) / 24000)));
        nodes = Array.from({ length: count }, () => ({
            x: Math.random() * width,
            y: Math.random() * height,
            vx: (Math.random() - 0.5) * 0.58,
            vy: (Math.random() - 0.5) * 0.58,
            r: Math.random() * 2.2 + 1.4,
            pulse: Math.random() * Math.PI * 2,
        }));
    };

    window.addEventListener('mousemove', (event) => {
        const rect = techCanvas.getBoundingClientRect();
        pointer = { x: event.clientX - rect.left, y: event.clientY - rect.top };
    }, { passive: true });
    window.addEventListener('mouseleave', () => {
        pointer = { x: -999, y: -999 };
    });
    window.addEventListener('resize', () => initNodes(false), { passive: true });
    initNodes(true);

    const render = () => {
        if (!document.hidden && ctx) {
            ctx.clearRect(0, 0, width, height);
            const isDark = document.documentElement.dataset.theme === 'dark';
            const lineRgb = isDark ? '167, 139, 250' : '124, 58, 237';
            const pulseRgb = isDark ? '232, 121, 249' : '139, 92, 246';

            for (let i = 0; i < nodes.length; i++) {
                const a = nodes[i];
                a.x += a.vx;
                a.y += a.vy;
                a.pulse += 0.03;
                if (a.x < 0 || a.x > width) a.vx *= -1;
                if (a.y < 0 || a.y > height) a.vy *= -1;

                const mdist = Math.hypot(a.x - pointer.x, a.y - pointer.y);
                if (mdist < 160) {
                    ctx.strokeStyle = `rgba(${pulseRgb}, ${(1 - mdist / 160) * (isDark ? 0.42 : 0.32)})`;
                    ctx.lineWidth = 1.25;
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(pointer.x, pointer.y);
                    ctx.stroke();
                }

                for (let j = i + 1; j < nodes.length; j++) {
                    const b = nodes[j];
                    const dist = Math.hypot(a.x - b.x, a.y - b.y);
                    if (dist < 155) {
                        const alpha = (1 - dist / 155) * (isDark ? 0.28 : 0.2);
                        ctx.strokeStyle = `rgba(${lineRgb}, ${alpha})`;
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(a.x, a.y);
                        ctx.lineTo(b.x, b.y);
                        ctx.stroke();
                    }
                }

                const glowR = a.r + Math.sin(a.pulse) * 0.55;
                ctx.fillStyle = isDark ? 'rgba(196, 181, 253, 0.78)' : 'rgba(109, 40, 217, 0.52)';
                ctx.beginPath();
                ctx.arc(a.x, a.y, Math.max(1, glowR), 0, Math.PI * 2);
                ctx.fill();
            }
        }
        window.requestAnimationFrame(render);
    };
    render();
}
