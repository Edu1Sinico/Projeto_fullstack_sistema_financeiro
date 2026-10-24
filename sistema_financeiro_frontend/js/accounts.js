// Função para controle do filtro de data
function initAccounts() {

    // Elementos do filtro de data
    const dateFilter = document.querySelector(
        ".accounts .date-filter-btn"
    );

    const selectedDate = dateFilter?.querySelector(
        ".selected-account-date"
    );

    const dateInput = document.querySelector(
        "#account-date-filter"
    );

    const clearButton = document.querySelector(
        ".accounts .date-filter-menu .clear-filter-btn"
    );

    // Verifica se os elementos existem
    if (!dateFilter || !selectedDate || !dateInput || !clearButton) {
        return;
    }

    // Evento de seleção da data
    dateInput.addEventListener("change", () => {

        const dateValue = dateInput.value;

        if (!dateValue) {
            selectedDate.textContent = "Data";
            return;
        }

        // Converte YYYY-MM-DD para DD/MM/YYYY
        const [year, month, day] = dateValue.split("-");

        const formattedDate = `${day}/${month}/${year}`;

        // Atualiza o texto do botão
        selectedDate.textContent = formattedDate;

    });

    // Evento para limpar o filtro
    clearButton.addEventListener("click", () => {

        // Limpa o campo de data
        dateInput.value = "";

        // Restaura o texto original
        selectedDate.textContent = "Data";

        // Limpando os filtros
        filterAccounts();

    });

    initCheckboxFilter(".account-type-filter", "Tipo");
    initCheckboxFilter(".account-status-filter", "Status");

    const accountsPage = document.querySelector(".accounts");

    if (!accountsPage) return;

    // Pesquisa
    const searchInput = accountsPage.querySelector(
        ".accounts-search input"
    );

    searchInput.addEventListener("input", filterAccounts);

    // Data
    dateInput.addEventListener("change", filterAccounts);

    // Inicializa o filtro de Tipo
    initCheckboxFilter(
        ".accounts .type-filter-btn",
        "Tipo"
    );

    initCheckboxFilter(".account-type-filter", "Tipo");

    // Inicializa o filtro de Status
    initCheckboxFilter(
        ".accounts .status-filter-btn",
        "Status"
    );

    // Tipos e status
    const checkboxes = accountsPage.querySelectorAll(
        ".account-type-filter input, .account-status-filter input"
    );

    initCheckboxFilter(".account-status-filter", "Status");

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", filterAccounts);
    });

    // Primeira atualização da tabela
    filterAccounts();

}

// Função para controle dos checkboxes dos filtros
function initCheckboxFilter(filterSelector, defaultLabel) {

    const filter = document.querySelector(filterSelector);

    if (!filter) return;

    const selectedLabel = filter.querySelector(".filter-btn span");

    const allCheckbox = filter.querySelector(
        'input[type="checkbox"]:first-of-type'
    );

    const checkboxes = Array.from(
        filter.querySelectorAll('input[type="checkbox"]')
    );

    const individualCheckboxes = checkboxes.filter(
        checkbox => checkbox !== allCheckbox
    );

    if (!selectedLabel || !allCheckbox) return;

    // Atualiza o texto exibido no botão
    function updateFilterLabel() {

        const selected = individualCheckboxes.filter(
            checkbox => checkbox.checked
        );

        if (allCheckbox.checked || selected.length === 0) {
            selectedLabel.textContent = defaultLabel;
        } else if (selected.length === 1) {
            const label = filter.querySelector(
                `label[for="${selected[0].id}"]`
            );

            selectedLabel.textContent = label.textContent.trim();
        } else {
            selectedLabel.textContent = `${selected.length} selecionados`;
        }
    }

    // Selecionar todas as opções
    allCheckbox.addEventListener("change", () => {

        allCheckbox.checked = true;

        individualCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        updateFilterLabel();
    });

    // Selecionar opções individuais
    individualCheckboxes.forEach(checkbox => {

        checkbox.addEventListener("change", () => {

            allCheckbox.checked = false;

            const hasSelected = individualCheckboxes.some(
                item => item.checked
            );

            // Se nenhuma opção estiver selecionada
            if (!hasSelected) {
                allCheckbox.checked = true;
            }

            updateFilterLabel();
        });

    });

}

// Responsável pela filtragem dos elementos
function filterAccounts() {

    const accountsPage = document.querySelector(".accounts");

    if (!accountsPage) return;

    // Elementos dos filtros
    const searchInput = accountsPage.querySelector(
        ".accounts-search input"
    );

    const dateInput = accountsPage.querySelector(
        "#account-date-filter"
    );

    const typeCheckboxes = accountsPage.querySelectorAll(
        ".account-type-filter .form-check-input:checked"
    );

    const statusCheckboxes = accountsPage.querySelectorAll(
        ".account-status-filter .form-check-input:checked"
    );

    // Valores dos filtros
    const searchValue = searchInput.value.trim().toLowerCase();
    const dateValue = dateInput.value;

    // Tipos selecionados
    const selectedTypes = Array.from(typeCheckboxes)
        .filter(checkbox => checkbox.id !== "all-types")
        .map(checkbox => checkbox.nextElementSibling.textContent.trim());

    // Status selecionados
    const selectedStatuses = Array.from(statusCheckboxes)
        .filter(checkbox => checkbox.id !== "all-status")
        .map(checkbox => checkbox.nextElementSibling.textContent.trim());

    // Verifica se a opção "Todos" está marcada
    const allTypes = accountsPage.querySelector("#all-types").checked;
    const allStatuses = accountsPage.querySelector("#all-status").checked;

    // Registros da tabela
    const rows = accountsPage.querySelectorAll(
        ".accounts-table tbody .table-row"
    );

    let visibleCount = 0;

    rows.forEach(row => {

        const name = row.dataset.name.toLowerCase();
        const date = row.dataset.date;
        const type = row.dataset.type;
        const status = row.dataset.status;

        // Verificação individual
        const matchesSearch = name.includes(searchValue);
        const matchesDate = !dateValue || date === dateValue;
        const matchesType = allTypes || selectedTypes.includes(type);
        const matchesStatus = allStatuses || selectedStatuses.includes(status);

        // Todos os critérios devem ser verdadeiros
        const isVisible =
            matchesSearch &&
            matchesDate &&
            matchesType &&
            matchesStatus;

        row.classList.toggle("d-none", !isVisible);

        if (isVisible) {
            visibleCount++;
        }

    });

    updateAccountsEmptyState(visibleCount, rows.length);
}

// Responsável pela exibição da mensagem de vazio ou cadastro da tabela

function updateAccountsEmptyState(visibleCount, totalCount) {

    const table = document.querySelector(
        ".accounts-table-section .table-responsive"
    );

    const emptyState = document.querySelector(
        ".accounts-empty-state"
    );

    const message = document.querySelector(
        ".accounts-empty-message"
    );

    if (!table || !emptyState || !message) return;

    const isEmpty = visibleCount === 0;

    table.classList.toggle("d-none", isEmpty);
    emptyState.classList.toggle("d-none", !isEmpty);

    if (totalCount === 0) {
        message.textContent = "Não há contas cadastradas";
    } else {
        message.textContent = "Nenhuma conta encontrada";
    }
}
