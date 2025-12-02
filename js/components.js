/* Descrição: Templates de Header e Footer compartilhados
 * Responsável por: Inserir header e footer em todas as páginas HTML
 *  Incluir em todas as páginas após <div id="header"></div> e <div id="footer"></div>
 */

// ===== TEMPLATE DO HEADER =====//

// Define HTML do cabeçalho
const headerHTML = `
<header>
    <!-- Container principal do navbar -->
    <div class="container navbar">
        <!-- Logo e nome da loja -->
        <a href="../index.html" class="logo">
            <img src="../assets/images/site/logo.jpg" alt="Logo Abelinha Curiosa" class="logo-img">
            Abelinha Curiosa
        </a>
        
        <!-- Barra de busca -->
        <div class="search-bar">
            <input type="text" id="search-input" placeholder="Buscar brinquedos..." autocomplete="off">
            <button class="search-btn" onclick="realizarBusca()">
                <img src="../assets/images/site/lupa.png" alt="Buscar" class="search-icon-img">
            </button>
        </div>
        
        <!-- Menu de navegação -->
        <nav>
            <ul class="nav-links">
                <li><a href="../index.html">Início</a></li>
                <li><a href="piticos.html">Piticos</a></li>
                <li><a href="fofinhos.html">Fofinhos</a></li>
                <li><a href="crescidinhos.html">Crescidinhos</a></li>
                <li><a href="grandinhos.html">Grandinhos</a></li>
            </ul>
        </nav>
        
        <!-- Ícone do carrinho -->
        <a href="cart.html" class="cart-icon">
            <img src="../assets/images/site/carrinho.png" alt="Carrinho" class="cart-icon-img">
        </a>
    </div>
</header>
`;

// ===== TEMPLATE DO FOOTER =====

// Define HTML do rodapé
const footerHTML = `
<footer>
    <!-- Container do conteúdo do footer -->
    <div class="container footer-content">
        <!-- Seção: Sobre Nós -->
        <div class="footer-section">
            <h3>Sobre Nós</h3>
            <p>Nossa missão é auxiliar no desenvolvimento infantil através de brinquedos educativos e divertidos.</p>
        </div>
        
        <!-- Seção: Contato -->
        <div class="footer-section">
            <h3>Contato</h3>
            <p>Email: contato@abelinhacuriosa.com.br</p>
            <p>Tel: (11) 91234-5678</p>
        </div>
        
        <!-- Seção: Links Úteis -->
        <div class="footer-section">
            <h3>Links Úteis</h3>
            <ul>
                <li><a href="#">Política de Privacidade</a></li>
                <li><a href="#">Trocas e Devoluções</a></li>
                <li><a href="admin-login.html" style="color: var(--color-accent); font-size: 0.9rem;">
                    Acesso Administrativo</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Copyright -->
    <div class="footer-copyright">
        &copy; 2025 Abelinha Curiosa. Todos os direitos reservados.
    </div>
</footer>
`;

// ===== FUNÇÃO DE INICIALIZAÇÃO =====

/**
 * Insere header e footer no DOM
 * Chamada automaticamente quando página carrega
 */
function inicializarComponentes() {
    // Pega elemento do header
    const headerContainer = document.getElementById('header');

    // Se elemento existe, insere HTML do header
    if (headerContainer) {
        headerContainer.innerHTML = headerHTML;
    }

    // Pega elemento do footer
    const footerContainer = document.getElementById('footer');

    // Se elemento existe, insere HTML do footer
    if (footerContainer) {
        footerContainer.innerHTML = footerHTML;
    }


    // Configura evento de busca com Enter
    configurarBusca();
}

// ===== FUNÇÃO DE ATUALIZAÇÃO DO CONTADOR DO CARRINHO =====



/**
 * Realiza busca de produtos
 * Redireciona para página de resultados com termo de busca
 */
function realizarBusca() {
    console.log('🔍 realizarBusca() chamada!');
    // Pega valor do input de busca
    const input = document.getElementById('search-input');
    console.log('Input:', input);

    // Se input existe, pega termo de busca
    if (input) {
        const termo = input.value.trim();
        console.log('Termo de busca:', termo);

        // Se termo não está vazio
        if (termo.length >= 2) {
            console.log('✅ Termo válido, redirecionando...');

            // Detecta se está na raiz ou em /pages/
            const isRootPage = window.location.pathname.endsWith('index.html') ||
                window.location.pathname.endsWith('/') ||
                !window.location.pathname.includes('/pages/');

            const url = isRootPage
                ? `pages/busca.html?busca=${encodeURIComponent(termo)}`
                : `busca.html?busca=${encodeURIComponent(termo)}`;

            console.log('Redirecionando para:', url);
            window.location.href = url;
        } else {
            alert('Digite pelo menos 2 caracteres para buscar');
        }
    }
}

/**
 * Configura evento de busca com tecla Enter
 */
function configurarBusca() {
    // Pega input de busca
    const input = document.getElementById('search-input');

    // Se input existe, adiciona evento
    if (input) {
        // Evento quando pressiona tecla
        input.addEventListener('keypress', function (e) {
            // Se tecla é Enter (código 13)
            if (e.key === 'Enter' || e.keyCode === 13) {
                // Realiza busca
                realizarBusca();
            }
        });
    }
}

// ===== EXECUÇÃO AUTOMÁTICA =====

// Executa quando DOM estiver completamente carregado
document.addEventListener('DOMContentLoaded', inicializarComponentes);

// Também executa se DOM já estiver carregado (fallback)
if (document.readyState === 'loading') {
    // DOM ainda carregando, aguarda evento
    document.addEventListener('DOMContentLoaded', inicializarComponentes);
} else {
    // DOM já carregado, executa imediatamente
    inicializarComponentes();
}
