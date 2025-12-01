/* Descrição: Autenticação administrativa
 * Responsável por: Login e verificação de autenticação do admin
 */

// ===== FUNÇÃO: FAZER LOGIN ===== //

/**
 * Processa login do administrador
 * @param {Event} event - Evento do formulário
 */
async function fazerLogin(event) {
    // Previne envio padrão
    event.preventDefault();

    // Pega dados do formulário
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;

    // Valida campos
    if (!email || !senha) {
        mostrarErro('Preencha todos os campos');
        return;
    }

    try {
        // Desabilita botão
        const btn = event.target.querySelector('button[type="submit"]');
        btn.textContent = 'Entrando...';
        btn.disabled = true;

        // Faz requisição para API
        const response = await fetch('../php/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                senha: senha
            })
        });

        // Converte resposta
        const data = await response.json();

        // Restaura botão
        btn.textContent = 'Entrar';
        btn.disabled = false;

        // Verifica resultado
        if (!data.success) {
            mostrarErro(data.message || 'Erro ao fazer login');
            return;
        }

        // Login bem-sucedido!
        // Redireciona para dashboard
        window.location.href = 'admin-dashboard.html';

    } catch (error) {
        console.error('Erro:', error);
        mostrarErro('Erro ao conectar com servidor');

        // Restaura botão
        const btn = event.target.querySelector('button[type="submit"]');
        btn.textContent = 'Entrar';
        btn.disabled = false;
    }
}

// ===== FUNÇÃO: VERIFICAR AUTENTICAÇÃO =====

/**
 * Verifica se usuário está autenticado
 * Redireciona para login se não estiver
 */
async function verificarAutenticacao() {
    try {
        // Faz requisição para verificar sessão
        const response = await fetch('../php/auth.php');

        // Converte resposta
        const data = await response.json();

        // Se não está autenticado, redireciona para login
        if (!data.authenticated) {
            window.location.href = 'admin-login.html';
            return false;
        }

        // Está autenticado
        return true;

    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        // Em caso de erro, redireciona para login
        window.location.href = 'admin-login.html';
        return false;
    }
}

// ===== FUNÇÃO: FAZER LOGOUT =====

/**
 * Encerra sessão do administrador
 */
async function fazerLogout() {
    try {
        // Faz requisição de logout
        await fetch('../php/auth.php?action=logout');

        // Redireciona para login
        window.location.href = 'admin-login.html';

    } catch (error) {
        console.error('Erro ao fazer logout:', error);
        // Mesmo com erro, redireciona
        window.location.href = 'admin-login.html';
    }
}

// ===== FUNÇÃO: MOSTRAR ERRO =====

/**
 * Exibe mensagem de erro
 * @param {string} mensagem - Mensagem de erro
 */
function mostrarErro(mensagem) {
    const errorDiv = document.getElementById('error-message');
    if (errorDiv) {
        errorDiv.textContent = mensagem;
        errorDiv.style.display = 'block';

        // Esconde após 5 segundos
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 5000);
    } else {
        alert(mensagem);
    }
}
