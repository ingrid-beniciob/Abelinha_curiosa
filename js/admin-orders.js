/* Descrição: Gerenciamento de pedidos no painel admin
 * Responsável por: Listar e atualizar status de pedidos
 */

// ===== FUNÇÃO: CARREGAR PEDIDOS ===== //

/**
 * Carrega lista de pedidos
 * @param {string} status - Filtro de status (opcional)
 */
async function carregarPedidos(status = '') {
    try {
        // Verifica autenticação
        const autenticado = await verificarAutenticacao();
        if (!autenticado) return;

        // Monta URL
        const url = status
            ? `../php/list-orders.php?status=${status}`
            : '../php/list-orders.php';

        // Faz requisição
        const response = await fetch(url);
        const data = await response.json();

        // Verifica sucesso
        if (!data.success) {
            alert('Erro ao carregar pedidos');
            return;
        }

        // Renderiza pedidos
        renderizarPedidos(data.pedidos);

    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar pedidos');
    }
}

// ===== FUNÇÃO: RENDERIZAR PEDIDOS =====

/**
 * Renderiza tabela de pedidos
 * @param {Array} pedidos - Array de pedidos
 */
function renderizarPedidos(pedidos) {
    const container = document.getElementById('orders-table');

    if (!container) return;

    if (pedidos.length === 0) {
        container.innerHTML = '<p class="no-data">Nenhum pedido encontrado.</p>';
        return;
    }

    let html = `
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>E-mail</th>
                    <th>Cidade/UF</th>
                    <th>Valor Total</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
    `;

    pedidos.forEach(pedido => {
        const data = new Date(pedido.data_pedido).toLocaleDateString('pt-BR');
        const hora = new Date(pedido.data_pedido).toLocaleTimeString('pt-BR');

        html += `
            <tr>
                <td>#${pedido.id}</td>
                <td>${pedido.nome_cliente}</td>
                <td>${pedido.email}</td>
                <td>${pedido.cidade}/${pedido.estado}</td>
                <td>R$ ${parseFloat(pedido.valor_total).toFixed(2).replace('.', ',')}</td>
                <td>
                    <select onchange="atualizarStatus(${pedido.id}, this.value)" 
                            class="status-select status-${pedido.status}">
                        <option value="pendente" ${pedido.status === 'pendente' ? 'selected' : ''}>Pendente</option>
                        <option value="pago" ${pedido.status === 'pago' ? 'selected' : ''}>Pago</option>
                        <option value="enviado" ${pedido.status === 'enviado' ? 'selected' : ''}>Enviado</option>
                        <option value="entregue" ${pedido.status === 'entregue' ? 'selected' : ''}>Entregue</option>
                        <option value="cancelado" ${pedido.status === 'cancelado' ? 'selected' : ''}>Cancelado</option>
                    </select>
                </td>
                <td>${data} ${hora}</td>
                <td>
                    <button onclick="verDetalhes(${pedido.id})" class="btn-icon" title="Ver Detalhes">👁️</button>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';

    container.innerHTML = html;
}

// ===== FUNÇÃO: ATUALIZAR STATUS =====

/**
 * Atualiza status do pedido
 * @param {number} id - ID do pedido
 * @param {string} novoStatus - Novo status
 */
async function atualizarStatus(id, novoStatus) {
    try {
        // Faz requisição
        const response = await fetch('../php/update-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                status: novoStatus
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('Status atualizado com sucesso!');
            // Recarrega lista
            carregarPedidos();
        } else {
            alert(data.message || 'Erro ao atualizar status');
            // Recarrega para reverter mudança visual
            carregarPedidos();
        }

    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao atualizar status');
        carregarPedidos();
    }
}

// ===== FUNÇÃO: VER DETALHES =====

/**
 * Mostra detalhes do pedido
 * @param {number} id - ID do pedido
 */
function verDetalhes(id) {
    alert(`Visualização de detalhes do pedido #${id} será implementada em breve!`);
    // TODO: Criar modal ou página de detalhes
}

// ===== FUNÇÃO: FILTRAR POR STATUS =====

/**
 * Filtra pedidos por status
 */
function filtrarPorStatus() {
    const select = document.getElementById('filter-status');
    const status = select.value;
    carregarPedidos(status);
}
