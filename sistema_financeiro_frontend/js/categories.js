function initCategories() {

    initCategorySearch();

    initCategoryDateFilter();

    initCategoryCheckboxFilter(
        ".category-type-filter",
        ".selected-category-type",
        "Tipo"
    );

    initCategoryCheckboxFilter(
        ".category-status-filter",
        ".selected-category-status",
        "Status"
    );

    filterCategories();

}

// Funcionalidade de pesquisa
function initCategorySearch() {

    const searchInput = document.querySelector(
        ".categories-search input"
    );

    if (!searchInput) return;

    searchInput.addEventListener("input", () => {
        filterCategories();
    });

}

// Funcionalidade de filtragem
function filterCategories() {

    const rows = document.querySelectorAll(
        ".categories-table tbody tr.table-row"
    );

    // Pesquisa
    const search = document.querySelector(
        ".categories-search input"
    )?.value.toLowerCase().trim() ?? "";

    // Data
    const selectedDate = document.querySelector(
        "#category-date-filter"
    )?.value ?? "";

    // Tipo
    const selectedTypes = getSelectedCategoryFilterValues(
        ".category-type-filter"
    );

    // Status
    const selectedStatuses = getSelectedCategoryFilterValues(
        ".category-status-filter"
    );

    let visibleRows = 0;

    rows.forEach(row => {

        const name = row.dataset.name.toLowerCase();
        const date = row.dataset.date;
        const type = row.dataset.type;
        const status = row.dataset.status;

        // Pesquisa por nome
        const matchesSearch = name.includes(search);

        // Data
        const matchesDate =
            !selectedDate || date === selectedDate;

        // Tipo
        const matchesType =
            selectedTypes === null ||
            selectedTypes.includes(type);

        // Status
        const matchesStatus =
            selectedStatuses === null ||
            selectedStatuses.includes(status);

        // Combinação dos filtros
        const isVisible =
            matchesSearch &&
            matchesDate &&
            matchesType &&
            matchesStatus;

        row.hidden = !isVisible;

        if (isVisible) {
            visibleRows++;
        }

    });

    updateCategoryEmptyState(visibleRows, rows.length);

}

// Altera o estado da div vazia
function updateCategoryEmptyState(visibleRows, totalRows) {

    const tableSection = document.querySelector(
        ".categories-table-section"
    );

    if (!tableSection) return;

    const tableContainer = tableSection.querySelector(
        ".table-responsive"
    );

    const emptyState = tableSection.querySelector(
        ".categories-empty-state"
    );

    const emptyMessage = emptyState.querySelector(
        ".categories-empty-message"
    );

    const isEmpty = visibleRows === 0;

    tableSection.classList.toggle("is-empty", isEmpty);

    tableContainer.classList.toggle("d-none", isEmpty);

    emptyState.classList.toggle("d-none", !isEmpty);

    if (isEmpty) {

        emptyMessage.textContent = totalRows === 0
            ? "Não há categorias cadastradas."
            : "Nenhuma categoria encontrada para os filtros selecionados.";

    }

}

// Filtro de datas
function initCategoryDateFilter() {

    const dateFilter = document.querySelector(
        ".categories-date-filter"
    );

    if (!dateFilter) return;

    const dateInput = dateFilter.querySelector(
        "#category-date-filter"
    );

    const selectedDate = dateFilter.querySelector(
        ".selected-category-date"
    );

    const clearButton = dateFilter.querySelector(
        ".clear-filter-btn"
    );

    function updateDate() {

        const date = dateInput.value;

        if (date) {
            const [year, month, day] = date.split("-");

            selectedDate.textContent = `${day}/${month}/${year}`;
        }
        else {
            selectedDate.textContent = "Data";
        }

        filterCategories();
    }

    dateInput.addEventListener("change", updateDate);

    clearButton.addEventListener("click", () => {

        dateInput.value = "";

        updateDate();

    });

}

// Filtros com checkbox (tipo e status)
function initCategoryCheckboxFilter(
    filterSelector,
    labelSelector,
    defaultLabel
) {

    const filter = document.querySelector(filterSelector);

    if (!filter) return;

    const allCheckbox = filter.querySelector(
        'input[data-value="all"]'
    );

    const options = [...filter.querySelectorAll(
        'input[type="checkbox"]:not([data-value="all"])'
    )];

    const selectedLabel = filter.querySelector(labelSelector);

    function updateLabel() {

        const selected = options.filter(option => option.checked);

        if (allCheckbox.checked || selected.length === 0) {
            selectedLabel.textContent = defaultLabel;
        }
        else if (selected.length === 1) {
            selectedLabel.textContent = selected[0].dataset.value;
        }
        else {
            selectedLabel.textContent = `${selected.length} selecionados`;
        }

    }

    // Checkbox "Todos"
    allCheckbox.addEventListener("change", () => {

        options.forEach(option => {
            option.checked = false;
        });

        allCheckbox.checked = true;

        updateLabel();
        filterCategories();

    });

    // Checkboxes individuais
    options.forEach(option => {

        option.addEventListener("change", () => {

            const selected = options.filter(item => item.checked);

            allCheckbox.checked = selected.length === 0;

            updateLabel();
            filterCategories();

        });

    });

}

// Recuperando os valores selecionados
function getSelectedCategoryFilterValues(filterSelector) {

    const filter = document.querySelector(filterSelector);

    if (!filter) return null;

    const allCheckbox = filter.querySelector(
        'input[data-value="all"]'
    );

    if (allCheckbox?.checked) {
        return null;
    }

    const selected = filter.querySelectorAll(
        'input[type="checkbox"]:checked:not([data-value="all"])'
    );

    return [...selected].map(option => option.dataset.value);

}