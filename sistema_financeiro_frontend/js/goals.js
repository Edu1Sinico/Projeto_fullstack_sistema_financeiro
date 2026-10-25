function initGoals() {

    // Modais
    initGoalModal();
    initContributionModal();
    initGoalForms();

    // Progresso e status
    updateGoalProgress();

    // Filtros
    initGoalSearch();
    initGoalStatusFilter();

    // Estado inicial
    filterGoals();

}

// Verifica se a data é superior que a data atual
function initGoalModal() {

    const modal = document.querySelector("#goalModal");

    if (!modal) return;

    const deadlineInput = modal.querySelector("#goalDeadline");

    modal.addEventListener("show.bs.modal", () => {

        const today = new Date();

        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, "0");
        const day = String(today.getDate()).padStart(2, "0");

        deadlineInput.min = `${year}-${month}-${day}`;

    });

}

const goalAccounts = [
    { id: 1, name: "Conta do banco" },
    { id: 2, name: "Conta de teste" }
];

// Iniciando o select de contas do modal de contribuição
function initContributionModal() {

    const modal = document.querySelector("#contributionModal");

    if (!modal) return;

    const accountSelect = modal.querySelector("#contributionAccount");

    const goalIdInput = modal.querySelector("#contributionGoalId");

    // Preencher as contas disponíveis
    goalAccounts.forEach(account => {

        const option = document.createElement("option");

        option.value = account.id;
        option.textContent = account.name;

        accountSelect.appendChild(option);

    });

    // Identificar qual meta abriu o modal
    modal.addEventListener("show.bs.modal", event => {

        const button = event.relatedTarget;

        if (!button) return;

        const goalId = button.dataset.goalId;

        goalIdInput.value = goalId ?? "";

    });

}

// Evitando o envio acidnetal de formulários 
function initGoalForms() {

    const forms = [
        document.querySelector("#goalForm"),
        document.querySelector("#contributionForm")
    ];

    forms.forEach(form => {

        if (!form) return;

        form.addEventListener("submit", event => {

            event.preventDefault();

            // Cadastro será implementado com Laravel.

        });

    });

}

// Filtro de pesquisa
function initGoalSearch() {

    const searchInput = document.querySelector(
        ".goals-search input"
    );

    if (!searchInput) return;

    searchInput.addEventListener("input", () => {
        filterGoals();
    });

}

// Filtro de status
function initGoalStatusFilter() {

    const filter = document.querySelector(
        ".goals-status-filter"
    );

    if (!filter) return;

    const allCheckbox = filter.querySelector(
        'input[data-value="all"]'
    );

    const options = [...filter.querySelectorAll(
        'input[type="checkbox"]:not([data-value="all"])'
    )];

    const selectedLabel = filter.querySelector(
        ".selected-goal-status"
    );

    function updateLabel() {

        const selected = options.filter(
            option => option.checked
        );

        if (allCheckbox.checked || selected.length === 0) {
            selectedLabel.textContent = "Status";
        }
        else if (selected.length === 1) {
            selectedLabel.textContent =
                selected[0].dataset.value;
        }
        else {
            selectedLabel.textContent =
                `${selected.length} selecionados`;
        }

    }

    // Opção "Todos os status"
    allCheckbox.addEventListener("change", () => {

        options.forEach(option => {
            option.checked = false;
        });

        allCheckbox.checked = true;

        updateLabel();
        filterGoals();

    });

    // Opções individuais
    options.forEach(option => {

        option.addEventListener("change", () => {

            const selected = options.filter(
                item => item.checked
            );

            allCheckbox.checked = selected.length === 0;

            updateLabel();
            filterGoals();

        });

    });

}

// Recuperar os status selecionados
function getSelectedGoalStatuses() {

    const filter = document.querySelector(
        ".goals-status-filter"
    );

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

    return [...selected].map(
        option => option.dataset.value
    );

}

// Função de filtragem
function filterGoals() {

    const cards = document.querySelectorAll(
        ".goals-grid .goal-card"
    );

    const search = document.querySelector(
        ".goals-search input"
    )?.value.toLocaleLowerCase("pt-BR").trim() ?? "";

    const selectedStatuses = getSelectedGoalStatuses();

    let visibleCards = 0;

    cards.forEach(card => {

        const name = (card.dataset.name ?? "")
            .toLocaleLowerCase("pt-BR");

        const status = card.dataset.status;

        // Pesquisa por nome
        const matchesSearch = name.includes(search);

        // Filtro de status
        const matchesStatus =
            selectedStatuses === null ||
            selectedStatuses.includes(status);

        // Combinação dos filtros
        const isVisible =
            matchesSearch &&
            matchesStatus;

        card.hidden = !isVisible;

        if (isVisible) {
            visibleCards++;
        }

    });

    updateGoalEmptyState(visibleCards, cards.length);

}

// Alteração do estado de exibição das metas vazias
function updateGoalEmptyState(visibleCards, totalCards) {

    const goalsGrid = document.querySelector(
        ".goals-grid"
    );

    const emptyState = document.querySelector(
        ".goals-empty-state"
    );

    if (!goalsGrid || !emptyState) return;

    const emptyMessage = emptyState.querySelector(
        ".goals-empty-message"
    );

    const isEmpty = visibleCards === 0;

    goalsGrid.classList.toggle("d-none", isEmpty);
    emptyState.classList.toggle("d-none", !isEmpty);

    if (isEmpty) {

        emptyMessage.textContent = totalCards === 0
            ? "Não há metas cadastradas."
            : "Nenhuma meta encontrada para os filtros selecionados.";

    }

}

// Cálculo automático da barra de progresso
function calculateGoalProgress(currentAmount, targetAmount) {

    if (targetAmount <= 0) return 0;

    const progress = (currentAmount / targetAmount) * 100;

    return Math.min(Math.max(progress, 0), 100);
}

function getGoalStatus(currentAmount, targetAmount) {

    if (targetAmount <= 0) {
        return "Não iniciado";
    }

    if (currentAmount >= targetAmount) {
        return "Concluído";
    }

    if (currentAmount > 0) {
        return "Em andamento";
    }

    return "Não iniciado";
}

// Atualiza a barra de progresso
function updateGoalProgress() {

    const cards = document.querySelectorAll(
        ".goals-grid .goal-card"
    );

    cards.forEach(card => {

        const currentAmount = Number(card.dataset.current);
        const targetAmount = Number(card.dataset.target);

        const progress = calculateGoalProgress(
            currentAmount,
            targetAmount
        );

        const status = getGoalStatus(
            currentAmount,
            targetAmount
        );

        const progressBar = card.querySelector(
            ".goal-progress-bar"
        );

        const progressFill = card.querySelector(
            ".goal-progress-fill"
        );

        const progressLabel = card.querySelector(
            ".goal-progress-percentage"
        );

        // Atualiza a largura da barra
        progressFill.style.width = `${progress}%`;

        // Atualiza o percentual exibido
        progressLabel.textContent =
            `${progress.toLocaleString("pt-BR", {
                maximumFractionDigits: 1
            })}%`;

        // Atualiza os atributos de acessibilidade
        progressBar.setAttribute(
            "aria-valuenow",
            progress.toFixed(1)
        );

        // Armazena o status calculado
        card.dataset.status = status;

    });

}