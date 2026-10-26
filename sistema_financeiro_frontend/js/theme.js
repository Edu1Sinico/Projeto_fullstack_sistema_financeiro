// Mantém os controles do menu e das configurações sincronizados.
function syncThemeControls(enabled) {
    document.querySelectorAll("#profileDarkMode, #settingsDarkMode")
        .forEach(control => {
            control.checked = enabled;
        });

    const status = document.querySelector("#darkModeStatus");
    if (status) {
        status.textContent = enabled ? "Ativado" : "Desativado";
    }
}

// Aplica o tema imediatamente e salva a preferência neste navegador.
function setDarkMode(enabled) {
    document.body.classList.toggle("dark-mode", enabled);
    syncThemeControls(enabled);

    try {
        localStorage.setItem("system-theme", enabled ? "dark" : "light");
    } catch {
        // A alternância continua funcionando se o armazenamento estiver indisponível.
    }
}

function initializeTheme() {
    let enabled = document.body.classList.contains("dark-mode");
    try {
        const savedTheme = localStorage.getItem("system-theme");
        if (savedTheme !== null) enabled = savedTheme === "dark";
    } catch {
        // Mantém o tema atual quando o navegador bloqueia o armazenamento.
    }

    document.body.classList.toggle("dark-mode", enabled);
    syncThemeControls(enabled);

    document.querySelectorAll("#profileDarkMode, #settingsDarkMode")
        .forEach(control => {
            control.addEventListener("change", () => setDarkMode(control.checked));
        });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeTheme, { once: true });
} else {
    initializeTheme();
}

// Reflete alterações feitas em outra aba da aplicação.
window.addEventListener("storage", event => {
    if (event.key === "system-theme" || event.key === null) {
        const enabled = event.key !== null && event.newValue === "dark";
        document.body.classList.toggle("dark-mode", enabled);
        syncThemeControls(enabled);
    }
});