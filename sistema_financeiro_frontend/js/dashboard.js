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