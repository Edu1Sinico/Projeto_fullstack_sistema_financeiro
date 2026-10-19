
/**
 * Carrega uma página HTML dentro do conteúdo principal.
 * @param {string} page - Nome da página a ser carregada.
 */

async function loadPage(page) {
    const pageContent = document.getElementById("page-content");

    try {
        // Solicita o arquivo HTML da página
        const response = await fetch(`pages/${page}.html`);

        // Verifica se a requisição foi bem-sucedida
        if (!response.ok) {
            throw new Error(`Erro ao carregar a página: ${page}`);
        }

        // Converte a resposta para texto HTML
        const html = await response.text();

        // Insere o conteúdo dentro do main
        pageContent.innerHTML = html;

        // Inicializa funcionalidades específicas da página
        if (page === "dashboard") {
            initDashboard();
        }

        return true;

    } catch (error) {
        console.error("Erro no carregamento:", error);

        pageContent.innerHTML = `
            <div class="alert alert-danger">
                Não foi possível carregar a página solicitada.
            </div>
        `;
    }
}

// Inicialização da aplicação
document.addEventListener("DOMContentLoaded", () => {

    // Seleciona todos os links da sidebar
    const pageLinks = document.querySelectorAll(
        ".sidebar-navigation .page-btn"
    );

    // Adiciona um evento de clique para cada link
    pageLinks.forEach(link => {

        link.addEventListener("click", async (event) => {

            // Impede a navegação padrão do href="#"
            event.preventDefault();

            // Recupera o nome da página
            const page = link.dataset.page;

            // Carrega a página selecionada
            const loaded = await loadPage(page);

            // Atualiza o botão ativo se o carregamento funcionar
            if (loaded) {
                pageLinks.forEach(item => {
                    item.classList.remove("active");
                });

                link.classList.add("active");
            }

        });

    });

    // Carrega o Dashboard inicialmente
    loadPage("dashboard");
});