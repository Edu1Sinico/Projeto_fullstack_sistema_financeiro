const resourceModalTexts = {
    accounts: {create: 'Adicionar uma conta', edit: 'Editar conta'},
    categories: {create: 'Adicionar uma categoria', edit: 'Editar categoria'},
    transactions: {create: 'Adicionar transação', edit: 'Editar transação'},
    goals: {create: 'Adicionar meta', edit: 'Editar meta'},
};

function setResourceModalMode(form, mode) {
    const texts = resourceModalTexts[form.dataset.resource] ?? resourceModalTexts.goals;
    const isEditing = mode === 'edit';
    form.dataset.mode = mode;
    form.closest('.modal')?.querySelector('.js-modal-title')?.replaceChildren(
        document.createTextNode(isEditing ? texts.edit : texts.create)
    );
    const label = form.querySelector('.js-submit-label');
    if (label) label.textContent = isEditing ? 'Salvar alterações' : '+ Adicionar';
}

function resetResourceForm(form) {
    form.reset();
    form.querySelector('[name="id"]').value = '';
    form.querySelector('.form-error')?.classList.add('d-none');
    setResourceModalMode(form, 'create');
    const createOnly = form.querySelector('.js-create-only');
    if (createOnly) {
        createOnly.classList.remove('d-none');
        createOnly.querySelectorAll('input').forEach(input => input.disabled = false);
    }
}

function fillResourceForm(form, record) {
    resetResourceForm(form);
    Object.entries(record).forEach(([key, value]) => {
        const input = form.elements.namedItem(key);
        if (!input || value === null) return;
        const normalizedValue = typeof value === 'object' && value.date ? value.date : value;
        input.value = input.type === 'date' && typeof normalizedValue === 'string'
            ? normalizedValue.slice(0, 10)
            : normalizedValue;
    });
    const createOnly = form.querySelector('.js-create-only');
    if (createOnly) {
        createOnly.classList.add('d-none');
        createOnly.querySelectorAll('input').forEach(input => input.disabled = true);
    }
    setResourceModalMode(form, 'edit');
}

function setLocalTransactionDate(form) {
    if (form.dataset.resource !== 'transactions') return;
    const today = new Date();
    form.elements.namedItem('transaction_date').value = [
        today.getFullYear(),
        String(today.getMonth() + 1).padStart(2, '0'),
        String(today.getDate()).padStart(2, '0'),
    ].join('-');
}

function initResourceForms() {
    document.querySelectorAll('form[data-resource]').forEach(form => {
        form.addEventListener('submit', async event => {
            event.preventDefault();
            const payload = Object.fromEntries(new FormData(form).entries());
            const id = payload.id;
            delete payload.id;

            try {
                await apiRequest(`/api/${form.dataset.resource}${id ? `/${id}` : ''}`, {
                    method: id ? 'PUT' : 'POST',
                    body: JSON.stringify(payload),
                });
                const names = {accounts: 'Conta', categories: 'Categoria', transactions: 'Transação', goals: 'Meta'};
                try { sessionStorage.setItem('finaxis-feedback', `${names[form.dataset.resource]} ${id ? 'atualizada' : 'cadastrada'} com sucesso.`); } catch {}
                window.location.reload();
            } catch (failure) {
                const target = form.querySelector('.form-error');
                target.textContent = formErrorMessage(failure);
                target.classList.remove('d-none');
            }
        });
    });

    document.querySelectorAll('form[data-resource]').forEach(form => {
        const modal = form.closest('.modal');
        modal?.addEventListener('show.bs.modal', event => {
            const trigger = event.relatedTarget;
            const isEditing = trigger?.dataset.formMode === 'edit';

            if (isEditing) {
                const record = JSON.parse(trigger.dataset.record);
                fillResourceForm(form, record);
                if (form.dataset.resource === 'transactions') filterCategoryOptions(form, record.category_id);
                return;
            }

            resetResourceForm(form);
            if (form.dataset.resource === 'transactions') {
                // Usa a data local para evitar mudança de dia por conversão para UTC.
                setLocalTransactionDate(form);
                filterCategoryOptions(form);
            }
        });
    });

    document.querySelectorAll('.js-delete-record').forEach(button => button.addEventListener('click', async () => {
        if (!window.confirm(`Deseja excluir “${button.dataset.name}”?`)) return;
        button.disabled = true;
        try {
            await apiRequest(button.dataset.url, {method: 'DELETE'});
            try { sessionStorage.setItem('finaxis-feedback', 'Registro excluído com sucesso.'); } catch {}
            window.location.reload();
        } catch (failure) {
            showFeedback(formErrorMessage(failure), 'danger');
            button.disabled = false;
        }
    }));
}

function filterCategoryOptions(form, selected = null) {
    if (!form || form.dataset.resource !== 'transactions') return;
    const type = form.elements.namedItem('type').value;
    const category = form.elements.namedItem('category_id');
    let selectedVisible = false;
    [...category.options].forEach(option => {
        if (!option.dataset.type) return;
        option.hidden = option.dataset.type !== type;
        option.disabled = option.hidden;
        if (!option.hidden && String(option.value) === String(selected)) selectedVisible = true;
    });
    category.value = selectedVisible ? selected : '';
}

function initTableFilters() {
    const page = document.querySelector('[data-filter-page]');
    if (!page || page.dataset.filterPage === 'goals') return;
    const rows = [...page.querySelectorAll('tbody .table-row')];
    const empty = page.querySelector('.js-filter-empty');
    const table = page.querySelector('.table-responsive');

    const apply = () => {
        const search = page.querySelector('.js-table-search')?.value.trim().toLocaleLowerCase('pt-BR') || '';
        const date = page.querySelector('.js-date-filter')?.value || '';
        const start = page.querySelector('.js-start-date')?.value || '';
        const end = page.querySelector('.js-end-date')?.value || '';
        const type = page.querySelector('.js-type-filter')?.value || '';
        const status = page.querySelector('.js-status-filter')?.value || '';
        const category = page.querySelector('.js-category-filter')?.value || '';
        const account = page.querySelector('.js-account-filter')?.value || '';
        let visible = 0;

        rows.forEach(row => {
            const show = (!search || row.dataset.search.includes(search)) && (!date || row.dataset.date === date) &&
                (!start || row.dataset.date >= start) && (!end || row.dataset.date <= end) &&
                (!type || row.dataset.type === type) && (!status || row.dataset.status === status) &&
                (!category || row.dataset.category === category) && (!account || row.dataset.account === account);
            row.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        table?.classList.toggle('d-none', visible === 0);
        empty?.classList.toggle('d-none', visible !== 0);
        page.querySelector('.table-section')?.classList.toggle('is-empty', visible === 0);
    };

    page.querySelectorAll('input, select').forEach(input => input.addEventListener(input.type === 'search' ? 'input' : 'change', apply));
    page.querySelectorAll('.js-clear-filter').forEach(button => button.addEventListener('click', () => {
        button.closest('.dropdown-menu')?.querySelectorAll('input').forEach(input => input.value = '');
        apply();
    }));
    apply();
}

function initGoals() {
    const page = document.querySelector('[data-filter-page="goals"]');
    if (!page) return;
    const cards = [...page.querySelectorAll('.goal-card')];
    const grid = page.querySelector('.goals-grid');
    const empty = page.querySelector('.js-filter-empty');
    const apply = () => {
        const search = page.querySelector('.js-goal-search').value.trim().toLocaleLowerCase('pt-BR');
        const status = page.querySelector('.js-goal-status').value;
        let visible = 0;
        cards.forEach(card => {
            const show = (!search || card.dataset.search.includes(search)) && (!status || card.dataset.status === status);
            card.hidden = !show;
            if (show) visible++;
        });
        grid.classList.toggle('d-none', visible === 0);
        empty.classList.toggle('d-none', visible !== 0);
    };
    page.querySelector('.js-goal-search').addEventListener('input', apply);
    page.querySelector('.js-goal-status').addEventListener('change', apply);

    if (window.Sortable && grid) {
        Sortable.create(grid, {animation: 200, handle: '.goal-drag-handle', onEnd: async () => {
            const goal_order = [...grid.querySelectorAll('.goal-card')].map(card => Number(card.dataset.goalId));
            try { await apiRequest('/api/preferences', {method: 'PUT', body: JSON.stringify({goal_order})}); }
            catch (failure) { showFeedback(formErrorMessage(failure), 'danger'); }
        }});
    }

    const contributionModal = document.querySelector('#contributionModal');
    contributionModal?.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        contributionModal.querySelector('[name="goal_id"]').value = button.dataset.goalId;
        contributionModal.querySelector('.js-contribution-goal').textContent = button.dataset.goalTitle;
    });
    document.querySelector('#contributionForm')?.addEventListener('submit', async event => {
        event.preventDefault();
        const form = event.currentTarget;
        const payload = Object.fromEntries(new FormData(form).entries());
        const goal = payload.goal_id;
        delete payload.goal_id;
        try {
            await apiRequest(`/api/goals/${goal}/contributions`, {method: 'POST', body: JSON.stringify(payload)});
            try { sessionStorage.setItem('finaxis-feedback', 'Contribuição adicionada com sucesso.'); } catch {}
            window.location.reload();
        } catch (failure) {
            const target = form.querySelector('.form-error');
            target.textContent = formErrorMessage(failure);
            target.classList.remove('d-none');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initResourceForms();
    initTableFilters();
    initGoals();
    const transactionForm = document.querySelector('#transactionForm');
    transactionForm?.elements.namedItem('type')?.addEventListener('change', () => filterCategoryOptions(transactionForm));
});
