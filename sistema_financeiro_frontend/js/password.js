document.addEventListener("DOMContentLoaded", () => {

    const passwordModal = document.querySelector("#changePasswordModal");
    const passwordForm = document.querySelector("#changePasswordForm");

    if (!passwordModal || !passwordForm) return;

    // Mostrar ou ocultar senha
    const toggleButtons = passwordModal.querySelectorAll(".password-toggle");

    toggleButtons.forEach(button => {

        button.addEventListener("click", () => {

            const inputId = button.dataset.target;
            const input = document.getElementById(inputId);

            if (!input) return;

            const isPassword = input.type === "password";

            input.type = isPassword ? "text" : "password";

            const icon = button.querySelector("i");

            icon.classList.toggle("bi-eye", isPassword);
            icon.classList.toggle("bi-eye-slash", !isPassword);

            button.setAttribute("aria-pressed", String(isPassword));
            button.setAttribute(
                "aria-label",
                `${isPassword ? "Ocultar" : "Mostrar"} ${inputId === "currentPassword" ? "senha atual" : "senha"}`
            );

        });

    });

    // Validação do formulário
    passwordForm.addEventListener("submit", event => {

        event.preventDefault();

        const currentPassword = document.querySelector("#currentPassword").value;
        const newPassword = document.querySelector("#newPassword").value;
        const confirmPassword = document.querySelector("#confirmNewPassword").value;

        const errorMessage = document.querySelector("#changePasswordError");

        errorMessage.textContent = "";
        errorMessage.classList.add("d-none");

        if (newPassword.length < 8) {
            showPasswordError("A nova senha deve possuir pelo menos 8 caracteres.");
            return;
        }

        if (currentPassword === newPassword) {
            showPasswordError("A nova senha deve ser diferente da senha atual.");
            return;
        }

        if (newPassword !== confirmPassword) {
            showPasswordError("As novas senhas informadas não coincidem.");
            return;
        }

        // A alteração real será implementada com Laravel.
        alert("Formulário validado! A alteração de senha será integrada futuramente.");

    });

    // Limpar o formulário ao fechar o modal
    passwordModal.addEventListener("hidden.bs.modal", () => {

        passwordForm.reset();

        const errorMessage = document.querySelector("#changePasswordError");

        errorMessage.textContent = "";
        errorMessage.classList.add("d-none");

        toggleButtons.forEach(button => {

            const input = document.getElementById(button.dataset.target);

            if (input) input.type = "password";

            const icon = button.querySelector("i");

            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");

            button.setAttribute("aria-pressed", "false");

        });

    });

});

function showPasswordError(message) {

    const errorMessage = document.querySelector("#changePasswordError");

    if (!errorMessage) return;

    errorMessage.textContent = message;
    errorMessage.classList.remove("d-none");

}