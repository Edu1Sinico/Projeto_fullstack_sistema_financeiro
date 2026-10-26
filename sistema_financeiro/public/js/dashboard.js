document.addEventListener('DOMContentLoaded', () => {
    const source = document.querySelector('#dashboardData');
    if (!source) return;
    const data = JSON.parse(source.textContent);
    const currency = value => Number(value).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});

    const allAccounts = document.querySelector('#all-accounts');
    const accountOptions = [...document.querySelectorAll('.dashboard-account-option')];
    const start = document.querySelector('#dashboardStartDate');
    const end = document.querySelector('#dashboardEndDate');
    const allCategories = document.querySelector('#dashboardAllCategories');
    const categoryOptions = [...document.querySelectorAll('.dashboard-category-option')];
    const recentPeriodOptions = [...document.querySelectorAll('.dashboard-recent-period')];
    let recentDays = 30;

    const localDateString = date => [
        date.getFullYear(),
        String(date.getMonth() + 1).padStart(2, '0'),
        String(date.getDate()).padStart(2, '0'),
    ].join('-');

    const selectedAccountIds = () => allAccounts.checked || !accountOptions.some(option => option.checked)
        ? data.accounts.map(account => Number(account.id))
        : accountOptions.filter(option => option.checked).map(option => Number(option.value));

    const selectedCategoryIds = () => allCategories.checked || !categoryOptions.some(option => option.checked)
        ? null
        : categoryOptions.filter(option => option.checked).map(option => Number(option.value));

    const isWithinRecentPeriod = date => {
        const today = new Date();
        const firstDay = new Date(today);
        firstDay.setDate(firstDay.getDate() - (recentDays - 1));

        // Compara apenas YYYY-MM-DD para não deslocar o lançamento por fuso horário.
        return date >= localDateString(firstDay) && date <= localDateString(today);
    };

    function render() {
        const ids = selectedAccountIds();
        const accounts = data.accounts.filter(account => ids.includes(Number(account.id)));
        const transactions = data.transactions.filter(transaction => ids.includes(Number(transaction.account_id)) &&
            (!start.value || transaction.date >= start.value) && (!end.value || transaction.date <= end.value));
        const balance = accounts.reduce((sum, account) => sum + Number(account.balance), 0);
        const income = transactions.filter(item => item.type === 'income').reduce((sum, item) => sum + Number(item.amount), 0);
        const expense = transactions.filter(item => item.type === 'expense').reduce((sum, item) => sum + Number(item.amount), 0);
        const result = income - expense;

        document.querySelector('#dashboard-total-balance').textContent = currency(balance);
        document.querySelector('#dashboard-total-income').textContent = `+ ${currency(income)}`;
        document.querySelector('#dashboard-total-expenses').textContent = `- ${currency(expense)}`;
        document.querySelector('#dashboard-period-result').textContent = `${result > 0 ? '+' : result < 0 ? '-' : ''} ${currency(Math.abs(result))}`.trim();
        document.querySelector('#dashboard-accounts-balance').textContent = currency(balance);
        document.querySelector('#dashboard-result-wrapper').classList.toggle('positive', result > 0);
        document.querySelector('#dashboard-result-wrapper').classList.toggle('negative', result < 0);
        document.querySelector('#dashboard-result-indicator').className = result < 0 ? 'dot-red' : 'dot-green';

        const accountsList = document.querySelector('#dashboardAccounts');
        accountsList.innerHTML = accounts.length ? accounts.map(account => `<div class="account-item"><div class="account-description"><span class="account-title"></span><span class="account-type">${{checking: 'Conta bancária', savings: 'Poupança', wallet: 'Carteira'}[account.type] || account.type}</span></div><span class="account-value">${currency(account.balance)}</span></div>`).join('') : '<div class="empty-state panel-empty-state"><i class="bi bi-inbox"></i><p>Não há contas disponíveis.</p></div>';
        accountsList.querySelectorAll('.account-title').forEach((element, index) => element.textContent = accounts[index].name);

        const categoryIds = selectedCategoryIds();
        const recent = [...transactions]
            .filter(transaction => (categoryIds === null || categoryIds.includes(Number(transaction.category_id))) && isWithinRecentPeriod(transaction.date))
            .sort((a, b) => b.date.localeCompare(a.date));
        const list = document.querySelector('#dashboardTransactions');
        const empty = document.querySelector('#dashboardTransactionsEmpty');
        list.innerHTML = '';
        recent.forEach(transaction => {
            const item = document.createElement('div');
            item.className = 'transaction-item';
            const description = document.createElement('div');
            description.className = 'transaction-description';
            const dot = document.createElement('span');
            dot.className = transaction.type === 'income' ? 'dot-green' : 'dot-red';
            const name = document.createElement('span');
            name.textContent = transaction.description;
            description.append(dot, name);
            const value = document.createElement('span');
            value.className = `transaction-value ${transaction.type === 'income' ? 'positive' : 'negative'}`;
            value.textContent = `${transaction.type === 'income' ? '+' : '-'} ${currency(transaction.amount)}`;
            const date = document.createElement('span');
            date.className = 'transaction-date';
            date.textContent = new Date(`${transaction.date}T00:00:00`).toLocaleDateString('pt-BR');
            item.append(description, value, date);
            list.appendChild(item);
        });
        empty.classList.toggle('d-none', recent.length > 0);
    }

    allAccounts.addEventListener('change', () => {
        allAccounts.checked = true;
        accountOptions.forEach(option => option.checked = false);
        render();
    });
    accountOptions.forEach(option => option.addEventListener('change', () => {
        allAccounts.checked = !accountOptions.some(item => item.checked);
        render();
    }));
    allCategories.addEventListener('change', () => {
        allCategories.checked = true;
        categoryOptions.forEach(option => option.checked = false);
        document.querySelector('#dashboardCategoryLabel').textContent = 'Categorias';
        render();
    });
    categoryOptions.forEach(option => option.addEventListener('change', () => {
        const selected = categoryOptions.filter(item => item.checked);
        allCategories.checked = selected.length === 0;
        document.querySelector('#dashboardCategoryLabel').textContent = selected.length === 1
            ? selected[0].nextElementSibling.textContent
            : selected.length > 1 ? `${selected.length} categorias` : 'Categorias';
        render();
    }));
    recentPeriodOptions.forEach(option => option.addEventListener('click', () => {
        recentDays = Number(option.dataset.days);
        recentPeriodOptions.forEach(item => item.classList.toggle('active', item === option));
        document.querySelector('#dashboardRecentPeriodLabel').textContent = option.textContent.trim();
        render();
    }));
    document.querySelectorAll('.dashboard-goal-order').forEach(option => option.addEventListener('click', () => {
        const goals = document.querySelector('#dashboardGoals');
        const items = [...goals.querySelectorAll('.goal-item')];
        const direction = option.dataset.order === 'desc' ? -1 : 1;
        items.sort((a, b) => a.dataset.deadline.localeCompare(b.dataset.deadline) * direction).forEach(item => goals.appendChild(item));
        document.querySelectorAll('.dashboard-goal-order').forEach(item => item.classList.toggle('active', item === option));
        document.querySelector('#dashboardGoalOrderLabel').textContent = option.textContent.trim();
    }));
    start.addEventListener('change', render);
    end.addEventListener('change', () => {
        end.setCustomValidity(start.value && end.value < start.value ? 'A data final não pode ser anterior à inicial.' : '');
        if (!end.checkValidity()) end.reportValidity();
        render();
    });
    document.querySelector('#clearDashboardPeriod').addEventListener('click', () => { start.value = ''; end.value = ''; render(); });
    render();
});
