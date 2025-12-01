/* Carregamento e exibição de produtos
 * Responsável por: Buscar produtos da API e renderizar na página
 * Incluir nas páginas de categorias (piticos, fofinhos, etc)
 */

// ===== FUNÇÃO: CARREGAR PRODUTOS =====//

/**
 * Carrega produtos de uma categoria específica
 * @param {string} categoria - Nome da categoria (piticos, fofinhos, etc)
 */
async function carregarProdutos(categoria) {
    try {
        // Exibe loading
        mostrarLoading(true);

        // Monta URL da API
        const url = categoria
            ? `../php/list.php?categoria=${categoria}`
            : '../php/list.php';

        // Faz requisição para API
        const response = await fetch(url);

        // Converte resposta para JSON
        const data = await response.json();

        // Verifica se API retornou sucesso
        if (!data.success) {
            throw new Error(data.message || 'Erro ao carregar produtos');
        }

        // Renderiza produtos na página
        renderizarProdutos(data.produtos);

        // Esconde loading
        mostrarLoading(false);

    } catch (error) {
        // Erro ao carregar produtos
        console.error('Erro:', error);

        // Esconde loading
        mostrarLoading(false);

        // Exibe mensagem de erro
        const container = document.getElementById('products-grid');
        if (container) {
            container.innerHTML = `
                <div class="error-message">
                    <p>Erro ao carregar produtos. Tente novamente.</p>
                    <button onclick="location.reload()">Recarregar</button>
                </div>
            `;
        }
    }
}

// ===== FUNÇÃO: RENDERIZAR PRODUTOS =====

/**
 * Renderiza lista de produtos no grid
 * @param {Array} produtos - Array de objetos de produtos
 */
function renderizarProdutos(produtos) {
    // Pega container do grid
    const container = document.getElementById('products-grid');

    // Se container não existe, retorna
    if (!container) {
        console.error('Container products-grid não encontrado');
        return;
    }

    // Limpa container
    container.innerHTML = '';

    // Verifica se há produtos
    if (!produtos || produtos.length === 0) {
        container.innerHTML = `
            <div class="no-products">
                <p>Nenhum produto encontrado nesta categoria.</p>
            </div>
        `;
        return;
    }

    // Percorre cada produto
    produtos.forEach(produto => {
        // Cria card do produto
        const card = criarCardProduto(produto);

        // Adiciona ao container
        container.appendChild(card);
    });
}

// ===== FUNÇÃO: CRIAR CARD DE PRODUTO =====

/**
 * Cria elemento HTML do card de produto
 * @param {Object} produto - Objeto com dados do produto
 * @returns {HTMLElement} Elemento do card
 */
function criarCardProduto(produto) {
    // Cria div do card
    const card = document.createElement('div');
    card.className = 'product-card';
    card.dataset.productId = produto.id;

    // Define HTML interno do card
    card.innerHTML = `
        <!-- Imagem do produto -->
        <div class="product-image">
            <img src="../${produto.imagem}" alt="${produto.nome}" loading="lazy">
        </div>
        
        <!-- Informações principais -->
        <div class="product-info">
            <h3 class="product-name">${produto.nome}</h3>
            <p class="product-price">R$ ${formatarPreco(produto.preco)}</p>
            
            <!-- Botão adicionar ao carrinho -->
            <button class="btn-add-cart" onclick="adicionarAoCarrinho(${produto.id})">
                Adicionar ao Carrinho
            </button>
        </div>
        
        <!-- Hover: Informações completas -->
        <div class="product-hover">
            <h3>${produto.nome}</h3>
            <p class="description">${produto.descricao || 'Produto de qualidade para o desenvolvimento infantil'}</p>
            <p class="price">R$ ${formatarPreco(produto.preco)}</p>
            <p class="stock">Estoque: ${produto.estoque} unidades</p>
            
            <!-- Botão no hover -->
            <button class="btn-add-hover" onclick="adicionarAoCarrinho(${produto.id})">
                Adicionar ao Carrinho
            </button>
        </div>
    `;

    // Retorna elemento criado
    return card;
}

// ===== FUNÇÃO: FORMATAR PREÇO =====

/**
 * Formata valor para moeda brasileira
 * @param {number} valor - Valor numérico
 * @returns {string} Valor formatado (ex: "89,90")
 */
function formatarPreco(valor) {
    // Converte para número
    const numero = parseFloat(valor);

    // Formata com 2 casas decimais e vírgula
    return numero.toFixed(2).replace('.', ',');
}

// ===== FUNÇÃO: MOSTRAR/ESCONDER LOADING =====

/**
 * Exibe ou esconde indicador de carregamento
 * @param {boolean} mostrar - true para mostrar, false para esconder
 */
function mostrarLoading(mostrar) {
    // Pega container do grid
    const container = document.getElementById('products-grid');

    // Se container não existe, retorna
    if (!container) {
        return;
    }

    if (mostrar) {
        // Exibe loading
        container.innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
                <p>Carregando produtos...</p>
            </div>
        `;
    }
    // Se false, não faz nada (será substituído pelos produtos)
}

// ===== FUNÇÃO: BUSCAR PRODUTOS =====

/**
 * Busca produtos por termo
 * @param {string} termo - Termo de busca
 */
async function buscarProdutos(termo) {
    try {
        // Exibe loading
        mostrarLoading(true);

        // Faz requisição para API de busca
        const response = await fetch(`../php/search.php?q=${encodeURIComponent(termo)}`);

        // Converte resposta para JSON
        const data = await response.json();

        // Verifica se API retornou sucesso
        if (!data.success) {
            throw new Error(data.message || 'Erro ao buscar produtos');
        }

        // Renderiza resultados
        renderizarProdutos(data.produtos);

        // Esconde loading
        mostrarLoading(false);

        // Exibe quantidade de resultados
        if (data.produtos.length === 0) {
            mostrarNotificacao(`Nenhum resultado para "${termo}"`, 'info');
        } else {
            mostrarNotificacao(`${data.produtos.length} produto(s) encontrado(s)`, 'success');
        }

    } catch (error) {
        // Erro ao buscar
        console.error('Erro:', error);

        // Esconde loading
        mostrarLoading(false);

        // Exibe mensagem de erro
        mostrarNotificacao('Erro ao realizar busca', 'error');
    }
}

// ===== CSS DO LOADING (INLINE) =====

// Adiciona estilos do loading
const styleLoading = document.createElement('style');
styleLoading.textContent = `
    /* Loading container */
    .loading {
        grid-column: 1 / -1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
    }
    
    /* Spinner animado */
    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #FFC107;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 20px;
    }
    
    /* Animação de rotação */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Mensagem de erro */
    .error-message {
        grid-column: 1 / -1;
        text-align: center;
        padding: 40px 20px;
    }
    
    .error-message button {
        margin-top: 15px;
        padding: 10px 20px;
        background: #FFC107;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }
    
    .error-message button:hover {
        background: #FFB300;
    }
    
    /* Nenhum produto */
    .no-products {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        font-size: 18px;
        color: #666;
    }
`;

// Adiciona ao head
document.head.appendChild(styleLoading);
