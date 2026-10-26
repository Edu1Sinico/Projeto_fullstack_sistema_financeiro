
document.addEventListener("DOMContentLoaded", () => {

    const settingsModal = document.querySelector("#settingsModal");
    const settingsForm = document.querySelector("#settingsForm");

    if (!settingsModal || !settingsForm) return;

    const nameInput = document.querySelector("#settingsName");
    const editNameButton = document.querySelector("#editSettingsName");
    const passwordButton = document.querySelector("#openPasswordModal");

    let savedName = nameInput.value;

    // Permitir edição do nome
    editNameButton.addEventListener("click", () => {

        nameInput.readOnly = false;
        nameInput.focus();
        nameInput.select();

    });

    // Abrir modal de alteração de senha
    passwordButton.addEventListener("click", () => {

        const settingsInstance = bootstrap.Modal.getOrCreateInstance(settingsModal);

        settingsModal.addEventListener("hidden.bs.modal", () => {

            const passwordModal = document.querySelector("#changePasswordModal");

            if (passwordModal) {
                bootstrap.Modal.getOrCreateInstance(passwordModal).show();
            }

        }, { once: true });

        settingsInstance.hide();

    });

    // Salvar alterações demonstrativas
    settingsForm.addEventListener("submit", event => {

        event.preventDefault();

        const newName = nameInput.value.trim();

        if (newName.length < 3) {
            alert("O nome deve possuir pelo menos 3 caracteres.");
            nameInput.focus();
            return;
        }

        savedName = newName;
        nameInput.value = savedName;
        nameInput.readOnly = true;

        // Atualiza o nome exibido no menu lateral e no cabeçalho
        document.querySelectorAll(".profile-name, .profile-title")
            .forEach(element => {
                element.textContent = savedName;
            });

        alert("Configurações atualizadas na interface. A persistência será integrada futuramente.");

        bootstrap.Modal.getOrCreateInstance(settingsModal).hide();

    });

    // Descartar alterações não salvas
    settingsModal.addEventListener("hidden.bs.modal", () => {

        nameInput.value = savedName;
        nameInput.readOnly = true;

    });

});
