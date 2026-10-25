let dashboardTransactionPeriod = 30;

function initDashboard() {

    // Filtros globais
    initDashboardAccountFilter();
    initDashboardPeriodFilter();

    // Indicadores financeiros
    updateDashboard();

    // Filtros internos do painel Transações recentes
    initDashboardCategoryFilter();
    initDashboardTransactionPeriodFilter();

    // Exibição inicial das transações
    filterDashboardTransactions();

    // Painel de metas
    initDashboardGoalFilter();

    // Atualizando o estado vazio das metas
    updateDashboardGoalsEmptyState();

    // Navegação para a página de Metas
    initDashboardGoalNavigation();
}

const dashboardAccounts = [
    {
        id: 1,
        name: "Conta do banco",
        type: "Conta bancária",
        balance: 950.00
    },
    {
        id: 2,
        name: "Carteira",
        type: "Dinheiro",
        balance: 300.00
    }
];

// Formatação monetária
function formatDashboardCurrency(value) {

    return value.toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL"
    });

}

// Recuperando as contas selecionadas
function getSelectedDashboardAccounts() {

    const allCheckbox = document.querySelector("#all-accounts");

    if (allCheckbox?.checked) {
        return dashboardAccounts.map(account => account.id);
    }

    const selected = document.querySelectorAll(
        "#dashboard-account-options input:checked"
    );

    return [...selected].map(
        checkbox => Number(checkbox.value)
    );

}

// Filtro de período
function isDashboardTransactionInPeriod(transaction, startDate, endDate) {

    if (startDate && transaction.date < startDate) {
        return false;
    }

    if (endDate && transaction.date > endDate) {
        return false;
    }

    return true;
}

function initDashboardPeriodFilter() {

    const startInput = document.querySelector("#start-date");
    const endInput = document.querySelector("#end-date");

    const clearButton = document.querySelector(
        ".period-menu .clear-filter-btn"
    );

    if (!startInput || !endInput || !clearButton) return;

    function handlePeriodChange() {

        // Impede intervalo invertido
        if (
            startInput.value &&
            endInput.value &&
            startInput.value > endInput.value
        ) {
            endInput.setCustomValidity(
                "A data final não pode ser anterior à data inicial."
            );

            endInput.reportValidity();
            return;
        }

        endInput.setCustomValidity("");

        updateDashboard();

    }

    startInput.addEventListener("change", handlePeriodChange);
    endInput.addEventListener("change", handlePeriodChange);

    clearButton.addEventListener("click", () => {

        startInput.value = "";
        endInput.value = "";
        endInput.setCustomValidity("");

        updateDashboard();

    });

}

// Filtro global do dashboard
function initDashboardAccountFilter() {

    const container = document.querySelector(
        "#dashboard-account-options"
    );

    const allCheckbox = document.querySelector(
        "#all-accounts"
    );

    const selectedLabel = document.querySelector(
        ".selected-dashboard-accounts"
    );

    if (!container || !allCheckbox) return;

    // Preenche a lista de contas
    dashboardAccounts.forEach(account => {

        const wrapper = document.createElement("div");
        wrapper.className = "form-check";

        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.className = "form-check-input";
        checkbox.id = `dashboard-account-${account.id}`;
        checkbox.value = account.id;

        const label = document.createElement("label");
        label.className = "form-check-label";
        label.htmlFor = checkbox.id;
        label.textContent = account.name;

        wrapper.append(checkbox, label);
        container.appendChild(wrapper);

    });

    const options = [
        ...container.querySelectorAll('input[type="checkbox"]')
    ];

    function updateLabel() {

        const selected = options.filter(
            option => option.checked
        );

        if (allCheckbox.checked || selected.length === 0) {
            selectedLabel.textContent = "Contas";
        }
        else if (selected.length === 1) {

            const account = dashboardAccounts.find(
                account => account.id === Number(selected[0].value)
            );

            selectedLabel.textContent = account?.name ?? "Contas";

        }
        else {
            selectedLabel.textContent =
                `${selected.length} contas`;
        }

    }

    // Selecionar todas
    allCheckbox.addEventListener("change", () => {

        options.forEach(option => {
            option.checked = false;
        });

        allCheckbox.checked = true;

        updateLabel();
        updateDashboard();

    });

    // Seleção individual
    options.forEach(option => {

        option.addEventListener("change", () => {

            const selected = options.filter(
                item => item.checked
            );

            allCheckbox.checked = selected.length === 0;

            updateLabel();
            updateDashboard();

        });

    });

}

// Atualizando o dashboard
function updateDashboard() {

    const selectedAccountIds = getSelectedDashboardAccounts();

    const startDate = document.querySelector("#start-date")?.value ?? "";
    const endDate = document.querySelector("#end-date")?.value ?? "";

    // Contas selecionadas
    const filteredAccounts = dashboardAccounts.filter(account =>
        selectedAccountIds.includes(account.id)
    );

    // Transações selecionadas
    const filteredTransactions = dashboardTransactions.filter(transaction => {

        const matchesAccount =
            selectedAccountIds.includes(transaction.accountId);

        const matchesPeriod = isDashboardTransactionInPeriod(
            transaction,
            startDate,
            endDate
        );

        return matchesAccount && matchesPeriod;

    });

    // Saldo total
    const totalBalance = filteredAccounts.reduce(
        (total, account) => total + account.balance,
        0
    );

    // Receitas
    const totalIncome = filteredTransactions
        .filter(transaction => transaction.type === "income")
        .reduce((total, transaction) => total + transaction.amount, 0);

    // Despesas
    const totalExpenses = filteredTransactions
        .filter(transaction => transaction.type === "expense")
        .reduce((total, transaction) => total + transaction.amount, 0);

    // Resultado do período
    const periodResult = totalIncome - totalExpenses;

    // Atualiza os cards
    document.querySelector("#dashboard-total-balance").textContent =
        formatDashboardCurrency(totalBalance);

    document.querySelector("#dashboard-total-income").textContent =
        `+ ${formatDashboardCurrency(totalIncome)}`;

    document.querySelector("#dashboard-total-expenses").textContent =
        `- ${formatDashboardCurrency(totalExpenses)}`;

    const resultElement = document.querySelector(
        "#dashboard-period-result"
    );

    const resultCard = resultElement.closest(".card-info");

    resultElement.textContent =
        `${periodResult < 0 ? "-" : periodResult > 0 ? "+" : ""} ${formatDashboardCurrency(Math.abs(periodResult))}`.trim();

    resultCard.classList.toggle("positive", periodResult > 0);
    resultCard.classList.toggle("negative", periodResult < 0);

    // Indicador colorido
    const resultIndicator = document.querySelector(
        "#dashboard-result-indicator"
    );

    resultIndicator.classList.toggle("dot-green", periodResult >= 0);
    resultIndicator.classList.toggle("dot-red", periodResult < 0);

    // Atualiza o painel de contas
    updateDashboardAccountsPanel(filteredAccounts, totalBalance);

}

// Atualização do painel de detalhes da conta
function updateDashboardAccountsPanel(accounts, totalBalance) {

    const accountsList = document.querySelector(
        ".dashboard .accounts-list"
    );

    const balanceElement = document.querySelector(
        "#dashboard-accounts-balance"
    );

    if (!accountsList || !balanceElement) return;

    balanceElement.textContent =
        formatDashboardCurrency(totalBalance);

    accountsList.replaceChildren();

    if (accounts.length === 0) {

        const emptyState = document.createElement("div");
        emptyState.className = "empty-state";

        const icon = document.createElement("i");
        icon.className = "bi bi-inbox";

        const message = document.createElement("p");
        message.textContent = "Não há contas disponíveis.";

        emptyState.append(icon, message);
        accountsList.appendChild(emptyState);

        return;
    }

    accounts.forEach(account => {

        const item = document.createElement("div");
        item.className = "account-item";

        const description = document.createElement("div");
        description.className = "account-description";

        const title = document.createElement("span");
        title.className = "account-title";
        title.textContent = account.name;

        const type = document.createElement("span");
        type.className = "account-type";
        type.textContent = account.type;

        description.append(title, type);

        const value = document.createElement("span");
        value.className = "account-value";
        value.textContent = formatDashboardCurrency(account.balance);

        if (account.balance < 0) {
            value.classList.add("negative");
        }

        item.append(description, value);
        accountsList.appendChild(item);

    });

}

// Dados de transações

const dashboardTransactions = [
    {
        id: 1,
        accountId: 1,
        categoryId: 1,
        description: "Salário",
        type: "income",
        amount: 1000.00,
        date: "2026-10-13"
    },
    {
        id: 2,
        accountId: 1,
        categoryId: 2,
        description: "Alimentação",
        type: "expense",
        amount: 50.00,
        date: "2026-10-13"
    },
    {
        id: 3,
        accountId: 2,
        categoryId: 3,
        description: "Freelance",
        type: "income",
        amount: 500.00,
        date: "2026-10-15"
    },
    {
        id: 4,
        accountId: 2,
        categoryId: 4,
        description: "Transporte",
        type: "expense",
        amount: 200.00,
        date: "2026-10-18"
    }
];

// Dados de categorias

const dashboardCategories = [
    { id: 1, name: "Salário", type: "income" },
    { id: 2, name: "Alimentação", type: "expense" },
    { id: 3, name: "Freelance", type: "income" },
    { id: 4, name: "Transporte", type: "expense" }
];

// Iniciando os checkboxes
function initDashboardCategoryFilter() {

    const container = document.querySelector(
        "#dashboard-category-options"
    );

    const allCheckbox = document.querySelector(
        "#all-transaction-categories"
    );

    if (!container || !allCheckbox) return;

    container.replaceChildren();

    dashboardCategories.forEach(category => {

        const wrapper = document.createElement("div");
        wrapper.className = "form-check";

        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.className = "form-check-input";
        checkbox.id = `dashboard-category-${category.id}`;
        checkbox.value = category.id;

        const label = document.createElement("label");
        label.className = "form-check-label";
        label.htmlFor = checkbox.id;
        label.textContent = category.name;

        wrapper.append(checkbox, label);
        container.appendChild(wrapper);

    });

    const options = [
        ...container.querySelectorAll('input[type="checkbox"]')
    ];

    allCheckbox.addEventListener("change", () => {

        options.forEach(option => {
            option.checked = false;
        });

        allCheckbox.checked = true;

        filterDashboardTransactions();

    });

    options.forEach(option => {

        option.addEventListener("change", () => {

            const selected = options.filter(
                item => item.checked
            );

            allCheckbox.checked = selected.length === 0;

            filterDashboardTransactions();

        });

    });

}

// Recuperando categorias selecionadas
function getSelectedDashboardCategories() {

    const allCheckbox = document.querySelector(
        "#all-transaction-categories"
    );

    if (allCheckbox?.checked) {
        return dashboardCategories.map(category => category.id);
    }

    const selected = document.querySelectorAll(
        "#dashboard-category-options input:checked"
    );

    return [...selected].map(
        checkbox => Number(checkbox.value)
    );

}

// Filtro do período das transações
function initDashboardTransactionPeriodFilter() {

    const periodFilter = document.querySelector(
        ".transaction-filter-btn:has(.selected-period)"
    );

    if (!periodFilter) return;

    const selectedPeriod = periodFilter.querySelector(
        ".selected-period"
    );

    const periodOptions = periodFilter
        .closest(".transaction-filter")
        .querySelectorAll("[data-days]");

    periodOptions.forEach(option => {

        option.addEventListener("click", () => {

            dashboardTransactionPeriod = Number(option.dataset.days);

            selectedPeriod.textContent = option.textContent.trim();

            periodOptions.forEach(item => {
                item.classList.remove("active");
            });

            option.classList.add("active");

            filterDashboardTransactions();

        });

    });

}

// Calculando os últimos dias
function isTransactionWithinLastDays(dateString, days) {

    const today = new Date();

    const startDate = new Date(today);
    startDate.setHours(0, 0, 0, 0);

    // Inclui o dia atual no intervalo
    startDate.setDate(startDate.getDate() - (days - 1));

    const transactionDate = new Date(`${dateString}T00:00:00`);

    return transactionDate >= startDate && transactionDate <= today;

}

// Filtragem de transações
function filterDashboardTransactions() {

    const selectedCategories = getSelectedDashboardCategories();

    const filteredTransactions = dashboardTransactions.filter(
        transaction => {

            const matchesCategory =
                selectedCategories.includes(transaction.categoryId);

            const matchesPeriod = isTransactionWithinLastDays(
                transaction.date,
                dashboardTransactionPeriod
            );

            return matchesCategory && matchesPeriod;

        }
    );

    // Ordena da mais recente para a mais antiga
    filteredTransactions.sort((a, b) =>
        b.date.localeCompare(a.date)
    );

    renderDashboardTransactions(filteredTransactions);

}

// Atualizando a lista de transações
function renderDashboardTransactions(transactions) {

    const container = document.querySelector(
        ".dashboard .transactions-list"
    );

    const emptyState = document.querySelector(
        ".transactions-empty-state"
    );

    if (!container || !emptyState) return;

    container.replaceChildren();

    emptyState.classList.toggle(
        "d-none",
        transactions.length > 0
    );

    if (transactions.length === 0) return;

    transactions.forEach(transaction => {

        const item = document.createElement("div");
        item.className = "transaction-item";

        const description = document.createElement("div");
        description.className = "transaction-description";

        const indicator = document.createElement("span");
        indicator.className = transaction.type === "income"
            ? "dot-green"
            : "dot-red";

        const name = document.createElement("span");
        name.textContent = transaction.description;

        description.append(indicator, name);

        const value = document.createElement("span");
        value.className = transaction.type === "income"
            ? "transaction-value positive"
            : "transaction-value negative";

        const sign = transaction.type === "income" ? "+" : "-";

        value.textContent =
            `${sign} ${formatDashboardCurrency(transaction.amount)}`;

        const date = document.createElement("span");
        date.className = "transaction-date";

        date.textContent = new Date(
            `${transaction.date}T00:00:00`
        ).toLocaleDateString("pt-BR");

        item.append(description, value, date);
        container.appendChild(item);

    });

}


// Ordenação das metas por prazo
function sortDashboardGoals(order = "asc") {

    const goalsList = document.querySelector(
        ".dashboard .goals-list"
    );

    if (!goalsList) return;

    const goals = [
        ...goalsList.querySelectorAll(".goal-item")
    ];

    goals.sort((a, b) => {

        const dateA = a.dataset.deadline;
        const dateB = b.dataset.deadline;

        if (order === "asc") {
            return dateA.localeCompare(dateB);
        }

        return dateB.localeCompare(dateA);

    });

    goals.forEach(goal => {
        goalsList.appendChild(goal);
    });

}

// Inicialização do filtro de ordenação das metas
function initDashboardGoalFilter() {

    const goalFilter = document.querySelector(
        ".dashboard .goal-filter"
    );

    if (!goalFilter) return;

    const selectedGoalOrder = goalFilter.querySelector(
        ".selected-goal-order"
    );

    const goalOrderOptions = goalFilter.querySelectorAll(
        ".dropdown-item[data-order]"
    );

    goalOrderOptions.forEach(option => {

        option.addEventListener("click", () => {

            const selectedOrder = option.dataset.order;

            // Atualiza o texto do filtro
            selectedGoalOrder.textContent =
                option.textContent.trim();

            // Atualiza a opção ativa
            goalOrderOptions.forEach(item => {
                item.classList.remove("active");
            });

            option.classList.add("active");

            // Ordena os cards
            sortDashboardGoals(selectedOrder);

        });

    });

    // Ordenação inicial
    sortDashboardGoals("asc");

}

// Navegação do Dashboard para a página de Metas
function initDashboardGoalNavigation() {

    const buttons = document.querySelectorAll(
        ".dashboard .goal-details-btn"
    );

    buttons.forEach(button => {

        button.addEventListener("click", () => {

            const goalsPageLink = document.querySelector(
                '.sidebar .page-btn[data-page="goals"]'
            );

            if (!goalsPageLink) return;

            goalsPageLink.click();

        });

    });

}

// Atualizando o estado vazio do painel de metas
function updateDashboardGoalsEmptyState() {

    const goalsList = document.querySelector(
        ".dashboard .goals-list"
    );

    const emptyState = document.querySelector(
        ".dashboard .goals-empty-state"
    );

    if (!goalsList || !emptyState) return;

    // Verifica quantas metas existem
    const goals = goalsList.querySelectorAll(".goal-item");

    const hasGoals = goals.length > 0;

    // Exibe a lista quando existem metas
    goalsList.classList.toggle("d-none", !hasGoals);

    // Exibe a mensagem quando não existem metas
    emptyState.classList.toggle("d-none", hasGoals);
}