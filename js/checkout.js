/* Descrição: Lógica da página de checkout
 * Responsável por: Processar finalização da compra, buscar CEP e calcular frete
 */

// ===== FUNÇÃO: BUSCAR ENDEREÇO POR CEP =====//

/**
 * Busca endereço via CEP e calcula frete
 * @param {string} cep - CEP digitado pelo usuário
 */
async function buscarCEP(cep) {
    try {
        // Remove caracteres não numéricos
        cep = cep.replace(/\D/g, '');

        // Valida formato do CEP (8 dígitos)
        if (cep.length !== 8) {
            mostrarNotificacao('CEP inválido', 'error');
            return;
        }

        // Exibe loading
        mostrarLoadingCEP(true);

        // Obtém carrinho para enviar no cálculo
        const carrinho = obterCarrinho();

        // Faz requisição para API
        const response = await fetch('../php/calculate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cep: cep,
                carrinho: carrinho
            })
        });

        // Converte resposta para JSON
        const data = await response.json();

        // Esconde loading
        mostrarLoadingCEP(false);

        // Verifica se API retornou sucesso
        if (!data.success) {
            mostrarNotificacao(data.message || 'CEP não encontrado', 'error');
            return;
        }

        // Preenche campos do endereço
        preencherEndereco(data.endereco);

        // Exibe informações de frete
        exibirFrete(data.frete);

        // Calcula total
        calcularTotal(data.frete.valor);

        mostrarNotificacao('CEP encontrado!', 'success');

    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
        mostrarLoadingCEP(false);
        mostrarNotificacao('Erro ao buscar CEP', 'error');
    }
}

// ===== FUNÇÃO: PREENCHER CAMPOS DE ENDEREÇO =====

/**
 * Preenche campos do formulário com dados do endereço
 * @param {Object} endereco - Dados do endereço retornados pela API
 */
function preencherEndereco(endereco) {
    // Preenche campos (usando optional chaining para evitar erros)
    document.getElementById('endereco')?.setAttribute('value', endereco.logradouro || '');
    document.getElementById('bairro')?.setAttribute('value', endereco.bairro || '');
    document.getElementById('cidade')?.setAttribute('value', endereco.cidade || '');
    document.getElementById('estado')?.setAttribute('value', endereco.estado || '');

    // Atualiza valores visíveis
    document.getElementById('endereco').value = endereco.logradouro || '';
    document.getElementById('bairro').value = endereco.bairro || '';
    document.getElementById('cidade').value = endereco.cidade || '';
    document.getElementById('estado').value = endereco.estado || '';

    // Foca no campo número
    document.getElementById('numero')?.focus();
}

// ===== FUNÇÃO: EXIBIR INFORMAÇÕES DE FRETE =====

/**
 * Exibe valor e prazo do frete
 * @param {Object} frete - Dados do frete (valor e prazo)
 */
function exibirFrete(frete) {
    // Pega container do frete
    const freteInfo = document.getElementById('frete-info');

    // Se não existe, retorna
    if (!freteInfo) return;

    // Define HTML
    freteInfo.innerHTML = `
        <div class="frete-valor">
            <span>Frete:</span>
            <span class="valor">R$ ${formatarPreco(parseFloat(frete.valor))}</span>
        </div>
        <div class="frete-prazo">
            <span>Prazo de entrega:</span>
            <span>${frete.prazo}</span>
        </div>
    `;

    // Mostra container
    freteInfo.style.display = 'block';

    // Armazena valor do frete para usar no pedido
    freteInfo.dataset.valorFrete = frete.valor;
}

// ===== FUNÇÃO: CALCULAR TOTAL =====

/**
 * Calcula valor total (subtotal + frete)
 * @param {number} valorFrete - Valor do frete
 */
function calcularTotal(valorFrete) {
    // Calcula subtotal do carrinho
    const subtotal = calcularSubtotal();

    // Converte frete para número
    const frete = parseFloat(valorFrete);

    // Calcula total
    const total = subtotal + frete;

    // Atualiza elementos na página
    document.getElementById('subtotal-valor').textContent = `R$ ${formatarPreco(subtotal)}`;
    document.getElementById('frete-valor').textContent = `R$ ${formatarPreco(frete)}`;
    document.getElementById('total-valor').textContent = `R$ ${formatarPreco(total)}`;
}

// ===== FUNÇÃO: MOSTRAR/ESCONDER LOADING DO CEP =====

/**
 * Exibe ou esconde loading do CEP
 * @param {boolean} mostrar - true para mostrar, false para esconder
 */
function mostrarLoadingCEP(mostrar) {
    const btn = document.querySelector('.btn-buscar-cep');

    if (btn) {
        if (mostrar) {
            btn.textContent = 'Buscando...';
            btn.disabled = true;
        } else {
            btn.textContent = 'Buscar CEP';
            btn.disabled = false;
        }
    }
}

// ===== FUNÇÃO: FINALIZAR PEDIDO =====

/**
 * Processa finalização do pedido
 * @param {Event} event - Evento do formulário
 */
async function finalizarPedido(event) {
    // Previne envio padrão do formulário
    event.preventDefault();

    // Obtém dados do formulário
    const form = event.target;
    const formData = new FormData(form);

    // Valida campos obrigatórios
    const camposObrigatorios = ['nome_cliente', 'email', 'cep', 'endereco', 'numero', 'cidade', 'estado'];

    for (const campo of camposObrigatorios) {
        if (!formData.get(campo)) {
            mostrarNotificacao(`Campo "${campo}" é obrigatório`, 'error');
            return;
        }
    }

    // Verifica se frete foi calculado
    const freteInfo = document.getElementById('frete-info');
    if (!freteInfo || !freteInfo.dataset.valorFrete) {
        mostrarNotificacao('Por favor, calcule o frete primeiro', 'error');
        return;
    }

    // Monta objeto do pedido
    const pedido = {
        nome_cliente: formData.get('nome_cliente'),
        email: formData.get('email'),
        cep: formData.get('cep'),
        endereco: formData.get('endereco'),
        numero: formData.get('numero'),
        complemento: formData.get('complemento') || '',
        cidade: formData.get('cidade'),
        estado: formData.get('estado'),
        valor_frete: parseFloat(freteInfo.dataset.valorFrete),
        carrinho: obterCarrinho()
    };

    // Valida se carrinho não está vazio
    if (pedido.carrinho.length === 0) {
        mostrarNotificacao('Carrinho está vazio', 'error');
        return;
    }

    try {
        // Exibe loading
        const btnSubmit = form.querySelector('button[type="submit"]');
        const textoOriginal = btnSubmit.textContent;
        btnSubmit.textContent = 'Processando...';
        btnSubmit.disabled = true;

        // Envia pedido para API
        const response = await fetch('../php/create-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(pedido)
        });

        // Converte resposta
        const data = await response.json();

        // Restaura botão
        btnSubmit.textContent = textoOriginal;
        btnSubmit.disabled = false;

        // Verifica se houve sucesso
        if (!data.success) {
            mostrarNotificacao(data.message || 'Erro ao processar pedido', 'error');
            return;
        }

        // Pedido criado com sucesso!

        // Limpa carrinho
        limparCarrinho();

        // Redireciona para página de confirmação
        window.location.href = `confirmation.html?pedido=${data.pedido_id}`;

    } catch (error) {
        console.error('Erro ao finalizar pedido:', error);
        mostrarNotificacao('Erro ao processar pedido', 'error');

        // Restaura botão
        const btnSubmit = form.querySelector('button[type="submit"]');
        btnSubmit.textContent = 'Finalizar Pedido';
        btnSubmit.disabled = false;
    }
}

// ===== CONFIGURAÇÃO DE EVENTOS =====

/**
 * Configura eventos da página quando carregar
 */
document.addEventListener('DOMContentLoaded', function () {
    // Configura máscara de CEP
    const inputCEP = document.getElementById('cep');
    if (inputCEP) {
        inputCEP.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.substr(0, 5) + '-' + value.substr(5, 3);
            }
            e.target.value = value;
        });
    }

    // Renderiza resumo do pedido
    renderizarResumoCheckout();
});

/**
 * Renderiza resumo do pedido no checkout
 */
function renderizarResumoCheckout() {
    const container = document.getElementById('resumo-pedido');
    if (!container) return;

    const carrinho = obterCarrinho();
    const subtotal = calcularSubtotal();

    let html = '<h3>Resumo do Pedido</h3>';

    carrinho.forEach(item => {
        html += `
            <div class="resumo-item">
                <span>${item.nome} (${item.quantidade}x)</span>
                <span>R$ ${formatarPreco(item.preco * item.quantidade)}</span>
            </div>
        `;
    });

    html += `
        <div class="resumo-subtotal">
            <span>Subtotal:</span>
            <span id="subtotal-valor">R$ ${formatarPreco(subtotal)}</span>
        </div>
        <div class="resumo-frete">
            <span>Frete:</span>
            <span id="frete-valor">-</span>
        </div>
        <div class="resumo-total">
            <strong>Total:</strong>
            <strong id="total-valor">R$ ${formatarPreco(subtotal)}</strong>
        </div>
    `;

    container.innerHTML = html;
}
