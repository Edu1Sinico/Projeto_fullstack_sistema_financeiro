window.appUrl = function (path = '') {
    const base = String(window.FINAXIS_BASE_URL || '').replace(/\/$/, '');
    const normalizedPath = String(path).startsWith('/') ? path : `/${path}`;
    return `${base}${normalizedPath}`;
};

window.apiRequest = async function (url, options = {}) {
    const response = await fetch(appUrl(url), {
        credentials: 'same-origin',
        ...options,
        headers: {
            'Accept': 'application/json',
            ...(options.body ? {'Content-Type': 'application/json'} : {}),
            ...(options.headers || {}),
        },
    });

    if (response.status === 401) {
        window.location.href = appUrl('/login');
        throw new Error('Sessão expirada.');
    }

    const data = response.status === 204 ? null : await response.json().catch(() => null);
    if (!response.ok) throw data || {message: 'Não foi possível concluir a operação.'};
    return data;
};

window.formErrorMessage = function (failure) {
    if (failure?.errors) return Object.values(failure.errors).flat().join(' ');
    return failure?.message || 'Não foi possível concluir a operação.';
};

window.showFeedback = function (message, type = 'success') {
    const feedback = document.querySelector('#appFeedback');
    if (!feedback) return;
    feedback.textContent = message;
    feedback.className = `alert app-feedback alert-${type}`;
    window.setTimeout(() => feedback.classList.add('d-none'), 5000);
};

function setFormError(form, message) {
    const target = form.querySelector('.form-error');
    if (!target) return;
    target.textContent = message;
    target.classList.remove('d-none');
}

async function persistTheme(enabled) {
    const theme = enabled ? 'dark' : 'light';
    document.body.classList.toggle('dark-mode', enabled);
    document.body.dataset.theme = theme;
    document.querySelectorAll('#profileDarkMode, #settingsDarkMode').forEach(input => input.checked = enabled);
    const label = document.querySelector('#darkModeStatus');
    if (label) label.textContent = enabled ? 'Ativado' : 'Desativado';

    try {
        await apiRequest('/api/preferences', {method: 'PUT', body: JSON.stringify({theme})});
    } catch (failure) {
        showFeedback(formErrorMessage(failure), 'danger');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        const pendingFeedback = sessionStorage.getItem('finaxis-feedback');
        if (pendingFeedback) {
            sessionStorage.removeItem('finaxis-feedback');
            showFeedback(pendingFeedback);
        }
    } catch {
        // O restante da aplicação funciona mesmo se o navegador bloquear o storage.
    }

    document.querySelectorAll('.js-logout').forEach(button => button.addEventListener('click', async () => {
        button.disabled = true;
        try { await apiRequest('/api/auth/logout', {method: 'POST'}); } finally { window.location.href = appUrl('/login'); }
    }));

    document.querySelectorAll('#profileDarkMode, #settingsDarkMode').forEach(input => {
        input.addEventListener('change', () => persistTheme(input.checked));
    });

    document.querySelectorAll('.password-toggle').forEach(button => button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.target);
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        button.querySelector('i')?.classList.toggle('bi-eye');
        button.querySelector('i')?.classList.toggle('bi-eye-slash');
    }));

    const settingsForm = document.querySelector('#settingsForm');
    const nameInput = document.querySelector('#settingsName');
    document.querySelector('#editSettingsName')?.addEventListener('click', () => {
        nameInput.readOnly = false;
        nameInput.focus();
    });

    document.querySelector('#openPasswordModal')?.addEventListener('click', () => {
        bootstrap.Modal.getOrCreateInstance('#settingsModal').hide();
        window.setTimeout(() => bootstrap.Modal.getOrCreateInstance('#changePasswordModal').show(), 250);
    });

    settingsForm?.addEventListener('submit', async event => {
        event.preventDefault();
        try {
            const user = await apiRequest('/api/profile', {method: 'PUT', body: JSON.stringify({name: nameInput.value.trim()})});
            document.querySelectorAll('.profile-name, .profile-title').forEach(element => element.textContent = user.name);
            nameInput.readOnly = true;
            bootstrap.Modal.getOrCreateInstance('#settingsModal').hide();
            showFeedback('Perfil atualizado com sucesso.');
        } catch (failure) { setFormError(settingsForm, formErrorMessage(failure)); }
    });

    const passwordForm = document.querySelector('#changePasswordForm');
    passwordForm?.addEventListener('submit', async event => {
        event.preventDefault();
        const payload = Object.fromEntries(new FormData(passwordForm).entries());
        try {
            await apiRequest('/api/profile/password', {method: 'PUT', body: JSON.stringify(payload)});
            passwordForm.reset();
            bootstrap.Modal.getOrCreateInstance('#changePasswordModal').hide();
            showFeedback('Senha alterada com sucesso.');
        } catch (failure) { setFormError(passwordForm, formErrorMessage(failure)); }
    });

    const deleteForm = document.querySelector('#deleteProfileForm');
    deleteForm?.addEventListener('submit', async event => {
        event.preventDefault();
        if (!window.confirm('Excluir definitivamente sua conta e todos os dados?')) return;
        try {
            await apiRequest('/api/profile', {method: 'DELETE', body: JSON.stringify(Object.fromEntries(new FormData(deleteForm).entries()))});
            window.location.href = appUrl('/login');
        } catch (failure) { setFormError(deleteForm, formErrorMessage(failure)); }
    });
});
