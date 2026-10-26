document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-auth-action]');
    if (!form) return;

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const error = document.querySelector('#authError');
        const button = form.querySelector('[type="submit"]');
        error.classList.add('d-none');
        button.disabled = true;

        try {
            const payload = Object.fromEntries(new FormData(form).entries());
            delete payload.remember;

            const response = await fetch(`/api/auth/${form.dataset.authAction}`, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (!response.ok) throw data;
            if (form.dataset.authAction === 'register') {
                try { sessionStorage.setItem('finaxis-feedback', 'Cadastro realizado com sucesso.'); } catch {}
            }
            window.location.href = '/dashboard';
        } catch (failure) {
            const validation = failure?.errors ? Object.values(failure.errors).flat()[0] : null;
            error.textContent = validation || failure?.message || 'Não foi possível concluir a autenticação.';
            error.classList.remove('d-none');
        } finally {
            button.disabled = false;
        }
    });
});
