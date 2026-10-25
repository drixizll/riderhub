import './bootstrap';
import 'leaflet/dist/leaflet.css';

import L from 'leaflet';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.L = L;

Alpine.start();

document.documentElement.classList.add('has-motion');

function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'dark' ? '#0b1120' : '#4f46e5');

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
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) return;

        const preview = document.querySelector('[data-profile-photo-preview]');
        if (!preview) return;

        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = 'Selected profile photo preview';
        image.className = 'h-20 w-20 rounded-2xl border-2 border-white/40 object-cover shadow-lg sm:h-24 sm:w-24';
        image.addEventListener('load', () => URL.revokeObjectURL(image.src), { once: true });
        preview.replaceWith(image);
        image.dataset.profilePhotoPreview = '';
        input.closest('form')?.querySelector('button[type="submit"]')?.removeAttribute('disabled');
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
