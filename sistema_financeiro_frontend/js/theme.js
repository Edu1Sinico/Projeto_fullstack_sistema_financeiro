// Recupera o tema salvo
const savedTheme = localStorage.getItem("system-theme");

// Aplica o tema escuro, caso esteja salvo
if (savedTheme === "dark") {
    document.body.classList.add("dark-mode");
}

// Alterna o tema da aplicação
function setDarkMode(enabled) {

    document.body.classList.toggle("dark-mode", enabled);

    // Salva a preferência do usuário
    localStorage.setItem(
        "system-theme",
        enabled ? "dark" : "light"
    );

}