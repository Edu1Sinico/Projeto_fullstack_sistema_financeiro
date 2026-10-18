// Filtro de período das transações

const periodFilter = document.querySelector(
    ".transaction-filter-btn:has(.selected-period)"
);

const selectedPeriod = periodFilter.querySelector(
    ".selected-period"
);

const periodOptions = periodFilter
    .closest(".transaction-filter")
    .querySelectorAll(".dropdown-item");

periodOptions.forEach(option => {

    option.addEventListener("click", () => {

        selectedPeriod.textContent = option.textContent.trim();

        periodOptions.forEach(item => {
            item.classList.remove("active");
        });

        option.classList.add("active");

    });

});

// Filtro de prazos das metas
const goalFilter = document.querySelector(
    ".goal-filter-btn:has(.selected-goal-order)"
);

const selectedGoalOrder = goalFilter.querySelector(
    ".selected-goal-order"
);

const goalOrderOptions = goalFilter
    .closest(".goal-filter")
    .querySelectorAll(".dropdown-item");

goalOrderOptions.forEach(option => {

    option.addEventListener("click", () => {

        selectedGoalOrder.textContent = option.textContent.trim();

        goalOrderOptions.forEach(item => {
            item.classList.remove("active");
        });

        option.classList.add("active");

    });

});