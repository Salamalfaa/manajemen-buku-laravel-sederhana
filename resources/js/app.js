import './bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../css/app.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const preferenceKey = 'manajemen-buku-preferences';
const fontOptions = {
    inter: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
    system: '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
    serif: 'Georgia, "Times New Roman", serif',
    mono: '"SFMono-Regular", Consolas, "Liberation Mono", monospace',
};
const clockFontOptions = {
    mono: '"SFMono-Regular", Consolas, "Liberation Mono", monospace',
    inter: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
    serif: 'Georgia, "Times New Roman", serif',
};

function readPreferences() {
    let saved = {};
    const storedPreferences = localStorage.getItem(preferenceKey);

    if (storedPreferences) {
        try {
            const parsedPreferences = JSON.parse(storedPreferences);

            if (parsedPreferences && typeof parsedPreferences === 'object' && !Array.isArray(parsedPreferences)) {
                saved = parsedPreferences;
            } else {
                console.warn('Preferensi Ruang Baca tidak valid dan telah direset.');
                localStorage.removeItem(preferenceKey);
            }
        } catch (error) {
            if (!(error instanceof SyntaxError)) throw error;

            console.warn('Preferensi Ruang Baca tidak dapat dibaca dan telah direset.', error);
            localStorage.removeItem(preferenceKey);
        }
    }

    const scale = Number(saved.scale);
    const clockSize = Number(saved.clockSize);

    return {
        theme: saved.theme === 'dark' ? 'dark' : 'light',
        accent: /^#[\da-f]{6}$/i.test(saved.accent || '') ? saved.accent : '#5279f7',
        scale: Number.isFinite(scale) ? Math.min(130, Math.max(75, scale)) : 100,
        font: Object.hasOwn(fontOptions, saved.font) ? saved.font : 'inter',
        widgetVisible: saved.widgetVisible !== false,
        clockFont: Object.hasOwn(clockFontOptions, saved.clockFont) ? saved.clockFont : 'mono',
        clockSize: Number.isFinite(clockSize) ? Math.min(52, Math.max(24, clockSize)) : 34,
    };
}

let preferences = readPreferences();

function savePreferences() {
    localStorage.setItem(preferenceKey, JSON.stringify(preferences));
}

function applyPreferences() {
    const root = document.documentElement;
    root.dataset.theme = preferences.theme;
    root.style.setProperty('--ui-scale', String(preferences.scale / 100));
    root.style.setProperty('--font-main', fontOptions[preferences.font]);
    root.style.setProperty('--clock-font', clockFontOptions[preferences.clockFont]);
    root.style.setProperty('--clock-size', `${preferences.clockSize}px`);
    root.style.setProperty('--accent', preferences.accent);

    const rgb = preferences.accent.match(/\w\w/g)?.map((part) => Number.parseInt(part, 16));
    if (rgb) {
        root.style.setProperty('--accent-rgb', rgb.join(', '));
    }

    const themeToggle = document.getElementById('themeToggle');
    const colorPicker = document.getElementById('accentPicker');
    const scaleSlider = document.getElementById('scaleSlider');
    const scaleValue = document.getElementById('scaleValue');
    const fontPicker = document.getElementById('fontPicker');
    const widgetToggle = document.getElementById('widgetToggle');
    const clockFontPicker = document.getElementById('clockFontPicker');
    const clockSizeSlider = document.getElementById('clockSizeSlider');
    const clockSizeValue = document.getElementById('clockSizeValue');
    const clockWidget = document.getElementById('dateTimeWidget');

    if (themeToggle) themeToggle.checked = preferences.theme === 'dark';
    if (colorPicker) colorPicker.value = preferences.accent;
    if (scaleSlider) scaleSlider.value = String(preferences.scale);
    if (scaleValue) scaleValue.textContent = `${preferences.scale}%`;
    if (fontPicker) fontPicker.value = preferences.font;
    if (widgetToggle) widgetToggle.checked = preferences.widgetVisible;
    if (clockFontPicker) clockFontPicker.value = preferences.clockFont;
    if (clockSizeSlider) clockSizeSlider.value = String(preferences.clockSize);
    if (clockSizeValue) clockSizeValue.textContent = `${preferences.clockSize}px`;
    if (clockWidget) clockWidget.hidden = !preferences.widgetVisible;
}

function updateClock() {
    const time = document.getElementById('clockDisplay');
    const date = document.getElementById('dateDisplay');

    if (!time || !date) return;

    const now = new Date();
    time.textContent = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).format(now);
    const formattedDate = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(now);
    date.textContent = `${formattedDate} · WIB`;
}

applyPreferences();
updateClock();
window.setInterval(updateClock, 1000);

const dashboardNavs = document.querySelectorAll('[data-dashboard-nav]');
const collectionNavs = document.querySelectorAll('[data-collection-nav]');
const userNavs = document.querySelectorAll('[data-users-nav]');
const mobileNav = document.querySelector('.mobile-bottom-nav');
const mobileNavItems = mobileNav?.querySelectorAll('[data-mobile-nav-key]') ?? [];
const settingsDrawer = document.getElementById('settingsDrawer');

function syncMobileNavIndicator() {
    if (!(mobileNav instanceof HTMLElement) || mobileNavItems.length === 0) return;

    const activeIndex = [...mobileNavItems].findIndex((item) => item.classList.contains('active'));
    mobileNav.style.setProperty('--mobile-nav-count', String(mobileNavItems.length));
    mobileNav.style.setProperty('--mobile-nav-index', String(Math.max(activeIndex, 0)));
}

function setMobileNavItemActive(item) {
    if (!item || !mobileNavItems.length) return;

    mobileNavItems.forEach((navItem) => navItem.classList.toggle('active', navItem === item));
    syncMobileNavIndicator();
}

function updateActiveNavigation() {
    const dashboardNav = dashboardNavs[0];
    const collectionNav = collectionNavs[0];
    const isDashboard = dashboardNav && window.location.pathname === new URL(dashboardNav.href).pathname;
    const collectionPath = collectionNav && new URL(collectionNav.href).pathname;
    const isCollection = collectionPath
        && (window.location.pathname === collectionPath || window.location.pathname.startsWith(`${collectionPath}/`));
    const userPath = userNavs[0] && new URL(userNavs[0].href).pathname.replace(/\/roles\/?$/, '');
    const isUsers = userPath
        && (window.location.pathname === userPath || window.location.pathname.startsWith(`${userPath}/`));
    const isSettingsOpen = settingsDrawer instanceof HTMLDialogElement && settingsDrawer.open;

    dashboardNavs.forEach((link) => link.classList.toggle('active', Boolean(isDashboard)));
    collectionNavs.forEach((link) => link.classList.toggle('active', Boolean(isCollection)));
    userNavs.forEach((link) => link.classList.toggle('active', Boolean(isUsers)));
    mobileNavItems.forEach((item) => {
        if (item.dataset.mobileNavKey === 'dashboard') item.classList.toggle('active', Boolean(isDashboard && !isSettingsOpen));
        if (item.dataset.mobileNavKey === 'collection') item.classList.toggle('active', Boolean(isCollection && !isSettingsOpen));
        if (item.dataset.mobileNavKey === 'account') item.classList.toggle('active', Boolean(window.location.pathname.startsWith('/profile') && !isSettingsOpen));
        if (item.dataset.mobileNavKey === 'users') item.classList.toggle('active', Boolean(isUsers && !isSettingsOpen));
        if (item.dataset.mobileNavKey === 'settings') item.classList.toggle('active', isSettingsOpen);
    });

    syncMobileNavIndicator();
}

window.addEventListener('hashchange', updateActiveNavigation);
window.addEventListener('pageshow', () => {
    document.body.classList.remove('mobile-nav-leaving');
    updateActiveNavigation();
});
updateActiveNavigation();
syncMobileNavIndicator();

let mobileNavigationTimeout = null;

mobileNavItems.forEach((item) => {
    if (!(item instanceof HTMLAnchorElement)) return;

    item.addEventListener('click', (event) => {
        if (!window.matchMedia('(max-width: 760px)').matches
            || event.button !== 0
            || event.metaKey
            || event.ctrlKey
            || event.shiftKey
            || event.altKey
            || item.target && item.target !== '_self'
            || item.hasAttribute('download')) return;

        const destination = new URL(item.href, window.location.href);
        if (destination.origin !== window.location.origin) return;

        if (mobileNavigationTimeout !== null) {
            window.clearTimeout(mobileNavigationTimeout);
            mobileNavigationTimeout = null;
        }

        if (destination.href === window.location.href) {
            document.body.classList.remove('mobile-nav-leaving');
            updateActiveNavigation();
            return;
        }

        if (destination.pathname === window.location.pathname) {
            document.body.classList.remove('mobile-nav-leaving');
            updateActiveNavigation();
            return;
        }

        event.preventDefault();
        setMobileNavItemActive(item);
        document.body.classList.add('mobile-nav-leaving');
        mobileNavigationTimeout = window.setTimeout(() => {
            window.location.assign(destination.href);
        }, 220);
    });
});

document.getElementById('themeToggle')?.addEventListener('change', (event) => {
    preferences.theme = event.currentTarget.checked ? 'dark' : 'light';
    applyPreferences();
    savePreferences();
});

document.getElementById('accentPicker')?.addEventListener('input', (event) => {
    preferences.accent = event.currentTarget.value;
    applyPreferences();
    savePreferences();
});

document.getElementById('scaleSlider')?.addEventListener('input', (event) => {
    preferences.scale = Number(event.currentTarget.value);
    applyPreferences();
    savePreferences();
});

document.getElementById('fontPicker')?.addEventListener('change', (event) => {
    preferences.font = event.currentTarget.value;
    applyPreferences();
    savePreferences();
});

document.getElementById('widgetToggle')?.addEventListener('change', (event) => {
    preferences.widgetVisible = event.currentTarget.checked;
    applyPreferences();
    savePreferences();
});

document.getElementById('clockFontPicker')?.addEventListener('change', (event) => {
    preferences.clockFont = event.currentTarget.value;
    applyPreferences();
    savePreferences();
});

document.getElementById('clockSizeSlider')?.addEventListener('input', (event) => {
    preferences.clockSize = Number(event.currentTarget.value);
    applyPreferences();
    savePreferences();
});

const passwordResetStatus = document.querySelector('[data-password-reset-status]');

if (passwordResetStatus instanceof HTMLElement) {
    const statusUrl = passwordResetStatus.dataset.statusUrl;
    const statusTitle = passwordResetStatus.querySelector('[data-status-title]');
    const statusMessage = passwordResetStatus.querySelector('[data-status-message]');
    const statusIcon = passwordResetStatus.querySelector('[data-status-icon]');
    const dismissStatus = passwordResetStatus.querySelector('[data-dismiss-reset-status]');
    let statusPollingTimer = null;
    let statusPollingActive = true;
    let currentStatus = passwordResetStatus.dataset.initialStatus || 'pending';

    dismissStatus?.addEventListener('click', () => {
        passwordResetStatus.hidden = true;
    });

    const statusPresentation = {
        pending: {
            title: 'Menunggu Administrator',
            icon: 'bi-hourglass-split',
            className: 'password-reset-status-pending',
        },
        checking: {
            title: 'Menunggu Administrator',
            icon: 'bi-hourglass-split',
            className: 'password-reset-status-pending',
        },
        approved: {
            title: 'Permintaan Disetujui',
            icon: 'bi-check-circle-fill',
            className: 'password-reset-status-approved',
        },
        rejected: {
            title: 'Permintaan Ditolak',
            icon: 'bi-x-circle-fill',
            className: 'password-reset-status-rejected',
        },
        unavailable: {
            title: 'Permintaan Tidak Tersedia',
            icon: 'bi-info-circle-fill',
            className: 'password-reset-status-error',
        },
    };

    async function refreshPasswordResetStatus() {
        if (!statusUrl || !statusPollingActive) return;

        try {
            const response = await fetch(statusUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) throw new Error(`Status reset password gagal dimuat (${response.status}).`);

            const result = await response.json();
            const presentation = statusPresentation[result.state];
            if (!presentation) return;

            const wasApproved = currentStatus === 'approved';
            if (result.state !== currentStatus) passwordResetStatus.hidden = false;
            currentStatus = result.state;
            passwordResetStatus.classList.remove(
                'password-reset-status-pending',
                'password-reset-status-approved',
                'password-reset-status-rejected',
                'password-reset-status-error',
            );
            passwordResetStatus.classList.add(presentation.className);
            if (statusTitle) statusTitle.textContent = presentation.title;
            if (statusMessage && typeof result.message === 'string') statusMessage.textContent = result.message;
            if (statusIcon) {
                statusIcon.replaceChildren();
                const icon = document.createElement('i');
                icon.className = `bi ${presentation.icon}`;
                icon.setAttribute('aria-hidden', 'true');
                statusIcon.append(icon);
            }

            if (result.state === 'approved' && !wasApproved) {
                if (statusMessage) {
                    statusMessage.textContent = 'Persetujuan diterima. Halaman akan diperbarui sebentar lagi untuk membuka formulir password baru.';
                }
                statusPollingActive = false;
                if (statusPollingTimer !== null) window.clearInterval(statusPollingTimer);
                window.setTimeout(() => window.location.reload(), 1800);
                return;
            }

            if (['approved', 'rejected', 'unavailable'].includes(result.state)) {
                statusPollingActive = false;
                if (statusPollingTimer !== null) window.clearInterval(statusPollingTimer);
            }
        } catch (error) {
            console.error('Pembaruan status reset password gagal.', error);
            passwordResetStatus.classList.add('password-reset-status-error');
            if (statusMessage) statusMessage.textContent = 'Status belum dapat diperbarui. Periksa koneksi Anda atau muat ulang halaman untuk mencoba kembali.';
        }
    }

    refreshPasswordResetStatus();
    statusPollingTimer = window.setInterval(refreshPasswordResetStatus, 5000);
}

document.querySelectorAll('[data-confirm-logout]').forEach((button) => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById('logoutConfirmModal');
        if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
});

document.querySelectorAll('[data-logout-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.submitting === 'true') return;

        event.preventDefault();
        form.dataset.submitting = 'true';
        document.body.classList.add('is-logging-out');

        const submitButton = form.querySelector('[data-logout-submit]');
        const spinner = form.querySelector('.logout-submit-spinner');
        const icon = form.querySelector('[data-logout-icon]');
        const label = form.querySelector('[data-logout-label]');

        if (submitButton instanceof HTMLButtonElement) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
        }
        if (spinner instanceof HTMLElement) spinner.hidden = false;
        if (icon instanceof HTMLElement) icon.hidden = true;
        if (label instanceof HTMLElement) label.textContent = 'Sedang keluar...';

        window.setTimeout(() => HTMLFormElement.prototype.submit.call(form), 420);
    });
});

const settingsButtons = document.querySelectorAll('[data-open-settings]');
let previousMobileNavItem = null;

function syncSettingsButtonState(isOpen) {
    settingsButtons.forEach((button) => {
        button.setAttribute('aria-expanded', String(isOpen));
        button.setAttribute('aria-label', isOpen ? 'Tutup pengaturan tampilan' : 'Buka pengaturan tampilan');
        button.classList.toggle('settings-button-active', isOpen);
    });

    if (isOpen) {
        previousMobileNavItem = [...mobileNavItems].find((item) => item.classList.contains('active')) ?? null;
        const mobileSettingsItem = mobileNav?.querySelector('[data-mobile-nav-key="settings"]');
        if (mobileSettingsItem instanceof HTMLElement) setMobileNavItemActive(mobileSettingsItem);
    } else if (previousMobileNavItem) {
        setMobileNavItemActive(previousMobileNavItem);
        previousMobileNavItem = null;
    }
}

function closeSettingsDrawer() {
    if (!(settingsDrawer instanceof HTMLDialogElement) || !settingsDrawer.open || settingsDrawer.classList.contains('dialog-closing')) return;

    settingsDrawer.classList.add('dialog-closing');
    syncSettingsButtonState(false);
    window.setTimeout(() => {
        settingsDrawer.close();
        settingsDrawer.classList.remove('dialog-closing');
    }, 240);
}

settingsButtons.forEach((button) => {
    button.addEventListener('click', () => {
        if (!(settingsDrawer instanceof HTMLDialogElement)) return;

        if (settingsDrawer.open) {
            closeSettingsDrawer();
        } else {
            settingsDrawer.classList.remove('dialog-closing');
            settingsDrawer.showModal();
            syncSettingsButtonState(true);
        }
    });
});

document.querySelectorAll('[data-close-settings]').forEach((button) => {
    button.addEventListener('click', closeSettingsDrawer);
});

document.addEventListener('click', (event) => {
    if (!(settingsDrawer instanceof HTMLDialogElement) || !settingsDrawer.open) return;
    if (event.target.closest?.('#logoutConfirmModal')) return;
    if (settingsDrawer.contains(event.target) || event.target.closest?.('[data-open-settings]')) return;
    if (document.querySelector('dialog[open]:not(#settingsDrawer)')) return;

    closeSettingsDrawer();
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || !(settingsDrawer instanceof HTMLDialogElement) || !settingsDrawer.open) return;
    if (document.querySelector('dialog[open]:not(#settingsDrawer)')) return;

    event.preventDefault();
    closeSettingsDrawer();
});

document.querySelectorAll('[data-close-confirmation]').forEach((button) => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById('logoutConfirmModal');
        if (dialog instanceof HTMLDialogElement) dialog.close();
    });
});

const requestDeleteDialog = document.getElementById('deletePasswordResetRequestDialog');
let requestFormPendingDeletion = null;

document.querySelectorAll('[data-request-delete-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.confirmed === 'true') {
            delete form.dataset.confirmed;
            return;
        }

        event.preventDefault();
        if (!(requestDeleteDialog instanceof HTMLDialogElement)) {
            console.error('Dialog konfirmasi penghapusan riwayat reset password tidak ditemukan.');
            return;
        }

        requestFormPendingDeletion = form;
        requestDeleteDialog.querySelector('[data-delete-request-name]').textContent = form.dataset.requestName || 'pengguna';
        requestDeleteDialog.querySelector('[data-delete-request-email]').textContent = form.dataset.requestEmail || 'email tidak tersedia';
        requestDeleteDialog.showModal();
    });
});

document.querySelector('[data-cancel-request-delete]')?.addEventListener('click', () => {
    if (requestDeleteDialog instanceof HTMLDialogElement) requestDeleteDialog.close();
});

document.querySelector('[data-confirm-request-delete]')?.addEventListener('click', () => {
    if (!(requestFormPendingDeletion instanceof HTMLFormElement)) {
        console.error('Form penghapusan riwayat permintaan tidak tersedia.');
        return;
    }

    requestFormPendingDeletion.dataset.confirmed = 'true';
    requestFormPendingDeletion.requestSubmit();
    if (requestDeleteDialog instanceof HTMLDialogElement) requestDeleteDialog.close();
});

requestDeleteDialog?.addEventListener('close', () => {
    requestFormPendingDeletion = null;
});

function closeProfileDialog(dialog) {
    if (!(dialog instanceof HTMLDialogElement) || !dialog.open || dialog.classList.contains('dialog-closing')) return;

    dialog.classList.add('dialog-closing');
    window.setTimeout(() => {
        dialog.close();
        dialog.classList.remove('dialog-closing');
    }, 190);
}

document.querySelector('[data-open-avatar]')?.addEventListener('click', () => {
    const dialog = document.getElementById('avatarPreviewDialog');
    if (dialog instanceof HTMLDialogElement) dialog.showModal();
});

const userAvatarDialog = document.getElementById('userAvatarPreviewDialog');

document.querySelectorAll('[data-user-avatar-preview]').forEach((button) => {
    button.addEventListener('click', () => {
        if (!(userAvatarDialog instanceof HTMLDialogElement)) return;

        const image = userAvatarDialog.querySelector('[data-preview-avatar-image]');
        const name = userAvatarDialog.querySelector('[data-preview-avatar-name]');
        const email = userAvatarDialog.querySelector('[data-preview-avatar-email]');

        if (!(image instanceof HTMLImageElement) || !(name instanceof HTMLElement) || !(email instanceof HTMLElement)) {
            console.error('Konten pratinjau foto profil pengguna tidak tersedia.');
            return;
        }

        image.src = button.dataset.avatarUrl || '';
        image.alt = `Foto profil ${button.dataset.avatarName || 'pengguna'}`;
        name.textContent = button.dataset.avatarName || 'Pengguna';
        email.textContent = button.dataset.avatarEmail || '';
        userAvatarDialog.showModal();
    });
});

document.querySelector('[data-open-delete-avatar]')?.addEventListener('click', () => {
    const dialog = document.getElementById('deleteAvatarDialog');
    if (dialog instanceof HTMLDialogElement) dialog.showModal();
});

document.querySelectorAll('[data-close-dialog]').forEach((button) => {
    button.addEventListener('click', () => closeProfileDialog(button.closest('dialog')));
});

document.querySelectorAll('dialog').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;

        if (dialog === settingsDrawer) {
            closeSettingsDrawer();
        } else if (dialog.matches('.profile-dialog')) {
            closeProfileDialog(dialog);
        } else {
            dialog.close();
        }
    });

    if (dialog === settingsDrawer) {
        dialog.addEventListener('cancel', (event) => {
            event.preventDefault();
            closeSettingsDrawer();
        });
    }

    if (dialog.matches('.profile-dialog')) {
        dialog.addEventListener('cancel', (event) => {
            event.preventDefault();
            closeProfileDialog(dialog);
        });
    }
});

document.querySelectorAll('[data-dismiss-message]').forEach((button) => {
    button.addEventListener('click', () => {
        const message = button.closest('[data-auto-dismiss]');
        if (!message) return;

        message.classList.add('flash-message-leaving');
        window.setTimeout(() => message.remove(), 350);
    });
});

document.querySelectorAll('[data-delete-book]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm('Hapus buku ini dari koleksi? Tindakan ini tidak dapat dibatalkan.')) {
            event.preventDefault();
        }
    });
});

function showAccountToast(title, message, active) {
    document.querySelectorAll('.account-toast[data-client-toast]').forEach((toast) => toast.remove());

    const toast = document.createElement('div');
    toast.className = `account-toast ${active ? 'account-toast-success' : 'account-toast-neutral'}`;
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.dataset.autoDismiss = '';
    toast.dataset.clientToast = '';

    const icon = document.createElement('span');
    icon.className = 'account-toast-icon';
    const iconElement = document.createElement('i');
    iconElement.className = `bi ${active ? 'bi-person-check-fill' : 'bi-person-dash-fill'}`;
    icon.append(iconElement);

    const copy = document.createElement('span');
    copy.className = 'account-toast-copy';
    const heading = document.createElement('strong');
    heading.textContent = title;
    const detail = document.createElement('span');
    detail.textContent = message;
    copy.append(heading, detail);

    const close = document.createElement('button');
    close.className = 'account-toast-close';
    close.type = 'button';
    close.setAttribute('aria-label', 'Tutup notifikasi');
    close.innerHTML = '<i class="bi bi-x-lg"></i>';
    close.addEventListener('click', () => toast.remove());

    toast.append(icon, copy, close);
    document.body.append(toast);
    window.setTimeout(() => {
        toast.classList.add('flash-message-leaving');
        window.setTimeout(() => toast.remove(), 350);
    }, 4500);
}

document.querySelectorAll('[data-account-toggle-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        if (!(form instanceof HTMLFormElement)) return;

        const toggle = form.querySelector('[data-account-switch]');
        if (!(toggle instanceof HTMLButtonElement)) return;

        event.preventDefault();
        if (toggle.disabled) return;

        toggle.disabled = true;
        toggle.setAttribute('aria-busy', 'true');
        toggle.classList.add('is-changing');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) throw new Error('Token keamanan tidak ditemukan. Muat ulang halaman lalu coba kembali.');

            const response = await fetch(form.action, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            let result;
            try {
                result = await response.json();
            } catch (error) {
                if (error instanceof SyntaxError) {
                    throw new Error('Server mengirim respons yang tidak dapat dibaca. Muat ulang halaman lalu coba kembali.');
                }

                throw error;
            }

            if (!response.ok) {
                throw new Error(result.message || 'Status akun gagal diperbarui.');
            }

            const isActive = Boolean(result.active);
            const name = toggle.dataset.accountName || 'pengguna';
            const state = form.closest('.account-status-cell')?.querySelector('[data-account-state]');
            const activity = form.closest('tr')?.querySelector('[data-account-activity]');

            toggle.classList.toggle('is-on', isActive);
            toggle.classList.toggle('is-off', !isActive);
            toggle.setAttribute('aria-checked', String(isActive));
            toggle.setAttribute('aria-label', `${isActive ? 'Nonaktifkan' : 'Aktifkan'} akun ${name}`);
            toggle.title = isActive ? 'Klik untuk menonaktifkan akun' : 'Klik untuk mengaktifkan akun';

            if (state) {
                state.classList.toggle('is-account-active', isActive);
                state.classList.toggle('is-account-inactive', !isActive);
                state.dataset.active = String(isActive);
                const label = state.querySelector('span:last-child');
                if (label) label.textContent = isActive ? 'Aktif' : 'Nonaktif';
            }

            if (activity) {
                const wasOnline = activity.dataset.online === 'true';
                activity.textContent = isActive ? (wasOnline ? 'Online' : 'Offline') : 'Akun nonaktif';
                activity.classList.toggle('text-online', isActive && wasOnline);
                activity.classList.toggle('text-offline', !isActive || !wasOnline);
                activity.dataset.online = String(isActive && wasOnline);
            }

            showAccountToast('Status akun diperbarui', result.message, isActive);
        } catch (error) {
            if (error instanceof TypeError) {
                showAccountToast('Perubahan tidak tersimpan', 'Tidak dapat terhubung ke server. Periksa koneksi Anda lalu coba kembali.', false);
            } else if (error instanceof Error) {
                showAccountToast('Status akun tidak berubah', error.message, false);
            } else {
                throw error;
            }
        } finally {
            toggle.disabled = false;
            toggle.removeAttribute('aria-busy');
            toggle.classList.remove('is-changing');
        }
    });
});

document.querySelectorAll('[data-auto-dismiss]').forEach((message) => {
    window.setTimeout(() => {
        message.classList.add('flash-message-leaving');
        message.addEventListener('transitionend', () => message.remove(), { once: true });
        window.setTimeout(() => message.remove(), 500);
    }, 4500);
});
