function initTransactions() {
    initTransactionPeriodFilter();

    initTransactionTypeFilter();

    updateTransactionCategories();

    initTransactionAccountFilter();
}

// Filtro de período das transações
function initTransactionPeriodFilter() {

    const periodFilter = document.querySelector(
        ".transactions-period-filter"
    );

    if (!periodFilter) return;

    const startDate = periodFilter.querySelector("#start-date");
    const endDate = periodFilter.querySelector("#end-date");

    const selectedPeriod = periodFilter.querySelector(
        ".selected-transaction-period"
    );

    const clearButton = periodFilter.querySelector(
        ".clear-filter-btn"
    );

    // Atualizar período selecionado
    function updatePeriod() {

        const start = startDate.value;
        const end = endDate.value;

        // Validar intervalo
        if (start && end && start > end) {
            endDate.setCustomValidity(
                "A data final não pode ser anterior à data inicial."
            );

            endDate.reportValidity();
            return;
        }

        endDate.setCustomValidity("");

        // Formatar data para padrão brasileiro
        function formatDate(date) {
            if (!date) return "";

            const [year, month, day] = date.split("-");
            return `${day}/${month}/${year}`;
        }

        if (start && end) {
            selectedPeriod.textContent =
                `${formatDate(start)} - ${formatDate(end)}`;
        }
        else if (start) {
            selectedPeriod.textContent =
                `A partir de ${formatDate(start)}`;
        }
        else if (end) {
            selectedPeriod.textContent =
                `Até ${formatDate(end)}`;
        }
        else {
            selectedPeriod.textContent = "Período";
        }

    }

    // Eventos dos campos de data
    startDate.addEventListener("change", updatePeriod);
    endDate.addEventListener("change", updatePeriod);

    // Limpar filtro
    clearButton.addEventListener("click", () => {

        startDate.value = "";
        endDate.value = "";

        endDate.setCustomValidity("");

        updatePeriod();

    });

}

const transactionCategories = [
    { id: 1, name: "Salário", type: "Receita" },
    { id: 2, name: "Investimentos", type: "Receita" },
    { id: 3, name: "Alimentação", type: "Despesa" },
    { id: 4, name: "Transporte", type: "Despesa" },
    { id: 5, name: "Pagamento", type: "Despesa" }
];

// Filtro de tipos
function initTransactionTypeFilter() {

    const filter = document.querySelector(
        ".transactions-type-filter"
    );

    if (!filter) return;

    const allCheckbox = filter.querySelector(
        '[data-value="all"]'
    );

    const options = [...filter.querySelectorAll(
        'input[type="checkbox"]:not([data-value="all"])'
    )];

    const selectedLabel = filter.querySelector(
        ".selected-transaction-type"
    );

    function updateLabel() {

        const selected = options.filter(option => option.checked);

        if (allCheckbox.checked) {
            selectedLabel.textContent = "Tipo";
        }
        else if (selected.length === 1) {
            selectedLabel.textContent = selected[0].dataset.value;
        }
        else {
            selectedLabel.textContent = `${selected.length} tipos`;
        }

    }

    allCheckbox.addEventListener("change", () => {

        if (allCheckbox.checked) {
            options.forEach(option => option.checked = false);
        }
        else {
            allCheckbox.checked = true;
        }

        updateLabel();
        updateTransactionCategories();

    });

    options.forEach(option => {

        option.addEventListener("change", () => {

            const selected = options.filter(item => item.checked);

            allCheckbox.checked = selected.length === 0;

            updateLabel();
            updateTransactionCategories();

        });

    });

}

// Filtro de categorias
function updateTransactionCategories() {

    const typeFilter = document.querySelector(
        ".transactions-type-filter"
    );

    const categoryFilter = document.querySelector(
        ".transactions-category-filter"
    );

    if (!typeFilter || !categoryFilter) return;

    const allTypes = typeFilter.querySelector(
        '[data-value="all"]'
    ).checked;

    const selectedTypes = [
        ...typeFilter.querySelectorAll(
            'input[type="checkbox"]:checked:not([data-value="all"])'
        )
    ].map(option => option.dataset.value);

    const availableCategories = transactionCategories.filter(category => {
        return allTypes || selectedTypes.includes(category.type);
    });

    const container = categoryFilter.querySelector(
        ".transaction-category-options"
    );

    const label = categoryFilter.querySelector(
        ".selected-transaction-category"
    );

    container.replaceChildren();
    label.textContent = "Categoria";

    function createOption(value, name, checked = false) {

        const wrapper = document.createElement("div");
        wrapper.className = "form-check";

        const input = document.createElement("input");
        input.type = "checkbox";
        input.className = "form-check-input";
        input.dataset.value = value;
        input.id = `transaction-category-${value}`;
        input.checked = checked;

        const optionLabel = document.createElement("label");
        optionLabel.className = "form-check-label";
        optionLabel.htmlFor = input.id;
        optionLabel.textContent = name;

        wrapper.append(input, optionLabel);
        container.appendChild(wrapper);

        return input;
    }

    const allCheckbox = createOption("all", "Todas as categorias", true);

    const options = availableCategories.map(category => {
        return createOption(
            String(category.id),
            category.name
        );
    });

    allCheckbox.addEventListener("change", () => {

        options.forEach(option => option.checked = false);
        allCheckbox.checked = true;

        label.textContent = "Categoria";

    });

    options.forEach(option => {

        option.addEventListener("change", () => {

            const selected = options.filter(item => item.checked);

            allCheckbox.checked = selected.length === 0;

            if (selected.length === 0) {
                label.textContent = "Categoria";
            }
            else if (selected.length === 1) {
                label.textContent = selected[0].nextElementSibling.textContent;
            }
            else {
                label.textContent = `${selected.length} categorias`;
            }

        });

    });

}

const transactionAccounts = [
    { id: 1, name: "Conta do banco" },
    { id: 2, name: "Conta de teste" }
];

// Filtro de contas
function initTransactionAccountFilter() {

    const filter = document.querySelector(
        ".transactions-account-filter"
    );

    if (!filter) return;

    const container = filter.querySelector(
        ".transaction-account-options"
    );

    const selectedLabel = filter.querySelector(
        ".selected-transaction-account"
    );

    container.replaceChildren();

    // Cria uma opção de seleção
    function createOption(value, name, checked = false) {

        const wrapper = document.createElement("div");
        wrapper.className = "form-check";

        const input = document.createElement("input");
        input.type = "checkbox";
        input.className = "form-check-input";
        input.dataset.value = value;
        input.id = `transaction-account-${value}`;
        input.checked = checked;

        const label = document.createElement("label");
        label.className = "form-check-label";
        label.htmlFor = input.id;
        label.textContent = name;

        wrapper.append(input, label);
        container.appendChild(wrapper);

        return input;
    }

    // Opção padrão
    const allCheckbox = createOption(
        "all",
        "Todas as contas",
        true
    );

    // Opções individuais
    const options = transactionAccounts.map(account => {
        return createOption(
            String(account.id),
            account.name
        );
    });

    // Atualiza o texto do botão
    function updateLabel() {

        const selected = options.filter(option => option.checked);

        if (allCheckbox.checked || selected.length === 0) {
            selectedLabel.textContent = "Conta";
        }
        else if (selected.length === 1) {
            selectedLabel.textContent =
                selected[0].nextElementSibling.textContent;
        }
        else {
            selectedLabel.textContent = `${selected.length} contas`;
        }

    }

    // Seleção de todas as contas
    allCheckbox.addEventListener("change", () => {

        options.forEach(option => {
            option.checked = false;
        });

        allCheckbox.checked = true;

        updateLabel();

    });

    // Seleção individual
    options.forEach(option => {

        option.addEventListener("change", () => {

            const selected = options.filter(item => item.checked);

            allCheckbox.checked = selected.length === 0;

            updateLabel();

        });

    });

}