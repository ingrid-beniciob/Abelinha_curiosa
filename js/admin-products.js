/* Descrição: Gerenciamento de produtos no painel admin
 * Responsável por: CRUD de produtos (listar, editar, deletar)
 */

// ===== FUNÇÃO: CARREGAR PRODUTOS ===== //

/**
 * Carrega lista de produtos
 * @param {string} categoria - Filtro de categoria (opcional)
 */
async function carregarProdutos(categoria = '') {
    try {
        // Verifica autenticação
        const autenticado = await verificarAutenticacao();
        if (!autenticado) return;

        // Monta URL
        const url = categoria
            ? `../php/list.php?categoria=${categoria}`
            : '../php/list.php';

        // Faz requisição
        const response = await fetch(url);
        const data = await response.json();

        // Verifica sucesso
        if (!data.success) {
            alert('Erro ao carregar produtos');
            return;
        }

        // Renderiza produtos
        renderizarProdutos(data.produtos);

    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar produtos');
    }
}

// ===== FUNÇÃO: RENDERIZAR PRODUTOS =====

/**
 * Renderiza tabela de produtos
 * @param {Array} produtos - Array de produtos
 */
function renderizarProdutos(produtos) {
    const container = document.getElementById('products-table');

    if (!container) return;

    if (produtos.length === 0) {
        container.innerHTML = '<p class="no-data">Nenhum produto cadastrado.</p>';
        return;
    }

    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagem</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
    `;

    produtos.forEach(produto => {
        html += `
            <tr>
                <td>${produto.id}</td>
                <td>
                    <img src="../${produto.imagem}" alt="${produto.nome}" class="product-thumb">
                </td>
                <td>${produto.nome}</td>
                <td>${produto.categoria}</td>
                <td>R$ ${parseFloat(produto.preco).toFixed(2).replace('.', ',')}</td>
                <td>${produto.estoque}</td>
                <td>
                    <button onclick="editarProduto(${produto.id})" class="btn-icon" title="Editar">✏️</button>
                    <button onclick="deletarProduto(${produto.id}, '${produto.nome}')" class="btn-icon" title="Deletar">🗑️</button>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';

    container.innerHTML = html;
}

// ===== FUNÇÃO: EDITAR PRODUTO =====

/**
 * Redireciona para formulário de edição
 * @param {number} id - ID do produto
 */
function editarProduto(id) {
    window.location.href = `admin-product-form.html?id=${id}`;
}

// ===== FUNÇÃO: DELETAR PRODUTO =====

/**
 * Deleta produto
 * @param {number} id - ID do produto
 * @param {string} nome - Nome do produto
 */
async function deletarProduto(id, nome) {
    // Confirma deleção
    if (!confirm(`Deseja realmente deletar o produto "${nome}"?`)) {
        return;
    }

    try {
        // Faz requisição
        const response = await fetch('../php/delete-product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const data = await response.json();

        if (data.success) {
            alert('Produto deletado com sucesso!');
            // Recarrega lista
            carregarProdutos();
        } else {
            alert(data.message || 'Erro ao deletar produto');
        }

    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao deletar produto');
    }
}

// ===== FUNÇÃO: FILTRAR POR CATEGORIA =====

/**
 * Filtra produtos por categoria
 */
function filtrarPorCategoria() {
    const select = document.getElementById('filter-category');
    const categoria = select.value;
    carregarProdutos(categoria);
}
