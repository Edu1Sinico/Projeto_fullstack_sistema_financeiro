document.addEventListener("DOMContentLoaded", () => {

    // Formulário de login
    const loginForm = document.querySelector("#loginForm");

    if (loginForm) {

        loginForm.addEventListener("submit", event => {

            event.preventDefault();

            // Autenticação será implementada com Laravel
            alert("A autenticação será disponibilizada futuramente.");

        });

    }

    // Formulário de cadastro
    const registerForm = document.querySelector("#registerForm");

    if (registerForm) {

        registerForm.addEventListener("submit", event => {

            event.preventDefault();

            const name = document.querySelector("#registerName").value.trim();

            const password = document.querySelector("#registerPassword").value;

            const confirmPassword = document.querySelector(
                "#registerConfirmPassword"
            ).value;

            const errorMessage = document.querySelector("#registerError");

            // Limpa mensagens anteriores
            errorMessage.textContent = "";
            errorMessage.classList.add("d-none");

            // Validação do nome
            if (name.length < 3) {
                showRegisterError("O nome deve possuir pelo menos 3 caracteres.");
                return;
            }

            // Validação da senha
            if (password.length < 8) {
                showRegisterError("A senha deve possuir pelo menos 8 caracteres.");
                return;
            }

            // Confirmação de senha
            if (password !== confirmPassword) {
                showRegisterError("As senhas informadas não coincidem.");
                return;
            }

            // Cadastro real será implementado com Laravel
            alert("Formulário validado! O cadastro será integrado futuramente.");

        });

    }

});

// Exibição dos erros de cadastro
function showRegisterError(message) {

    const errorMessage = document.querySelector("#registerError");

    if (!errorMessage) return;

    errorMessage.textContent = message;
    errorMessage.classList.remove("d-none");

}