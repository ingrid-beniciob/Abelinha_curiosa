/* Descrição: Gerenciamento do carrinho de compras
 * Responsável por: Adicionar, remover e atualizar itens no localStorage
 * Incluir em todas as páginas que interagem com carrinho
 */

// ===== FUNÇÃO: OBTER CARRINHO ===== //

/**
 * Busca carrinho do localStorage
 * @returns {Array} Array de itens do carrinho
 */
function obterCarrinho() {
    // Busca string JSON do localStorage
    const carrinhoJSON = localStorage.getItem('cart');

    // Se não existe, retorna array vazio
    if (!carrinhoJSON) {
        return [];
    }

    // Converte JSON para array e retorna
    return JSON.parse(carrinhoJSON);
}

// ===== FUNÇÃO: SALVAR CARRINHO =====

/**
 * Salva carrinho no localStorage
 * @param {Array} carrinho - Array de itens do carrinho
 */
function salvarCarrinho(carrinho) {
    // Converte array para JSON
    const carrinhoJSON = JSON.stringify(carrinho);

    // Salva no localStorage
    localStorage.setItem('cart', carrinhoJSON);

    // Atualiza contador visual do carrinho
    if (typeof atualizarContadorCarrinho === 'function') {
        atualizarContadorCarrinho();
    }
}

// ===== FUNÇÃO: ADICIONAR AO CARRINHO =====

/**
 * Adiciona produto ao carrinho
 * @param {number} produtoId - ID do produto
 * @param {number} quantidade - Quantidade a adicionar (padrão: 1)
 */
async function adicionarAoCarrinho(produtoId, quantidade = 1) {
    try {
        // Busca dados do produto via API
        const response = await fetch('../php/list.php');
        const data = await response.json();

        // Verifica se API retornou sucesso
        if (!data.success) {
            mostrarNotificacao('Erro ao buscar produto', 'error');
            return;
        }

        // Busca produto específico no array
        const produto = data.produtos.find(p => p.id === parseInt(produtoId));

        // Verifica se produto foi encontrado
        if (!produto) {
            mostrarNotificacao('Produto não encontrado', 'error');
            return;
        }

        // Verifica se há estoque disponível
        if (produto.estoque < quantidade) {
            mostrarNotificacao(`Apenas ${produto.estoque} unidades disponíveis`, 'error');
            return;
        }

        // Obtém carrinho atual
        const carrinho = obterCarrinho();

        // Verifica se produto já está no carrinho
        const itemExistente = carrinho.find(item => item.id === produtoId);

        if (itemExistente) {
            // Produto já existe - verifica se não ultrapassa estoque
            const novaQuantidade = itemExistente.quantidade + quantidade;

            if (novaQuantidade > produto.estoque) {
                mostrarNotificacao(`Quantidade máxima: ${produto.estoque}`, 'error');
                return;
            }

            // Atualiza quantidade
            itemExistente.quantidade = novaQuantidade;
        } else {
            // Produto novo - adiciona ao carrinho
            carrinho.push({
                id: produto.id,
                nome: produto.nome,
                preco: produto.preco,
                imagem: produto.imagem,
                categoria: produto.categoria,
                quantidade: quantidade,
                estoque: produto.estoque
            });
        }

        // Salva carrinho atualizado
        salvarCarrinho(carrinho);

        // Mostra notificação de sucesso
        mostrarNotificacao('Produto adicionado ao carrinho!', 'success');

    } catch (error) {
        // Erro na requisição
        console.error('Erro ao adicionar ao carrinho:', error);
        mostrarNotificacao('Erro ao adicionar produto', 'error');
    }
}

// ===== FUNÇÃO: REMOVER DO CARRINHO =====

/**
 * Remove produto do carrinho
 * @param {number} produtoId - ID do produto a remover
 */
function removerDoCarrinho(produtoId) {
    // Obtém carrinho atual
    let carrinho = obterCarrinho();

    // Filtra removendo o produto
    carrinho = carrinho.filter(item => item.id !== parseInt(produtoId));

    // Salva carrinho atualizado
    salvarCarrinho(carrinho);

    // Mostra notificação
    mostrarNotificacao('Produto removido do carrinho', 'info');
}

// ===== FUNÇÃO: ATUALIZAR QUANTIDADE =====

/**
 * Atualiza quantidade de um produto no carrinho
 * @param {number} produtoId - ID do produto
 * @param {number} novaQuantidade - Nova quantidade
 */
function atualizarQuantidade(produtoId, novaQuantidade) {
    // Converte para número inteiro
    novaQuantidade = parseInt(novaQuantidade);

    // Valida quantidade mínima
    if (novaQuantidade < 1) {
        mostrarNotificacao('Quantidade mínima: 1', 'error');
        return;
    }

    // Obtém carrinho atual
    const carrinho = obterCarrinho();

    // Busca item no carrinho
    const item = carrinho.find(i => i.id === parseInt(produtoId));

    // Se item não existe, retorna
    if (!item) {
        return;
    }

    // Verifica se não ultrapassa estoque
    if (novaQuantidade > item.estoque) {
        mostrarNotificacao(`Quantidade máxima: ${item.estoque}`, 'error');
        return;
    }

    // Atualiza quantidade
    item.quantidade = novaQuantidade;

    // Salva carrinho
    salvarCarrinho(carrinho);
}

// ===== FUNÇÃO: LIMPAR CARRINHO =====

/**
 * Remove todos os itens do carrinho
 */
function limparCarrinho() {
    // Remove item do localStorage
    localStorage.removeItem('cart');

    // Atualiza contador
    if (typeof atualizarContadorCarrinho === 'function') {
        atualizarContadorCarrinho();
    }
}

// ===== FUNÇÃO: CALCULAR SUBTOTAL =====

/**
 * Calcula valor total do carrinho
 * @returns {number} Valor total
 */
function calcularSubtotal() {
    // Obtém carrinho
    const carrinho = obterCarrinho();

    // Reduz array somando preço * quantidade
    const subtotal = carrinho.reduce((total, item) => {
        return total + (item.preco * item.quantidade);
    }, 0);

    // Retorna valor
    return subtotal;
}

// ===== FUNÇÃO: FORMATAR PREÇO =====

/**
 * Formata valor numérico para formato de moeda brasileira
 * @param {number} valor - Valor a formatar
 * @returns {string} Valor formatado (ex: "123,45")
 */
function formatarPreco(valor) {
    // Converte para número e formata com 2 casas decimais
    return parseFloat(valor).toFixed(2).replace('.', ',');
}

// ===== FUNÇÃO: MOSTRAR NOTIFICAÇÃO =====

/**
 * Exibe notificação para o usuário
 * @param {string} mensagem - Texto da notificação
 * @param {string} tipo - Tipo: 'success', 'error', 'info'
 */
function mostrarNotificacao(mensagem, tipo = 'info') {
    // Cria elemento de notificação
    const notificacao = document.createElement('div');

    // Define classes CSS
    notificacao.className = `notification notification-${tipo}`;

    // Define texto
    notificacao.textContent = mensagem;

    // Define estilos inline (caso CSS não esteja carregado)
    notificacao.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${tipo === 'success' ? '#4CAF50' : tipo === 'error' ? '#f44336' : '#2196F3'};
        color: white;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;

    // Adiciona ao body
    document.body.appendChild(notificacao);

    // Remove após 3 segundos
    setTimeout(() => {
        // Adiciona animação de saída
        notificacao.style.animation = 'slideOut 0.3s ease';

        // Remove do DOM após animação
        setTimeout(() => {
            document.body.removeChild(notificacao);
        }, 300);
    }, 3000);
}

// ===== ADICIONA ESTILOS DE ANIMAÇÃO =====

// Cria elemento style para animações
const styleAnimacoes = document.createElement('style');
styleAnimacoes.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;

// Adiciona estilos ao head
document.head.appendChild(styleAnimacoes);
