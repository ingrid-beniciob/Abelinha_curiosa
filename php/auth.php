<?php
/**
 * ===== ARQUIVO: auth.php =====
 * Descrição: Autenticação de administradores (login e logout)
 * Responsável por: Validar credenciais e gerenciar sessões PHP
 */

// Inclui arquivo de configuração e conexão com banco
require_once '../config/database.php';

// ===== VERIFICA MÉTODO DA REQUISIÇÃO =====

// Verifica se é requisição POST (login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Chama função de login
    handleLogin();
} 
// Verifica se é requisição GET com action=logout (logout)
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Chama função de logout
    handleLogout();
}
// Verifica se é requisição GET para verificar status da sessão
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Verifica se usuário está logado
    checkAuthStatus();
} 
// Método não permitido
else {
    // Retorna erro 405
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]);
}

// ===== FUNÇÃO DE LOGIN =====

/**
 * Processa login do administrador
 * Valida email e senha, cria sessão se credenciais corretas
 */
function handleLogin() {
    // Pega dados brutos do POST (JSON)
    $json = file_get_contents('php://input');
    
    // Decodifica JSON para array PHP
    $data = json_decode($json, true);
    
    // Verifica se email e senha foram enviados
    if (!isset($data['email']) || !isset($data['senha'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Email e senha são obrigatórios'
        ]);
        return;
    }
    
    // Pega email e senha do array
    $email = trim($data['email']);
    $senha = $data['senha'];
    
    // Valida formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email inválido'
        ]);
        return;
    }
    
    // Conecta com banco de dados
    $conn = getConnection();
    
    // Prepara query SQL para buscar usuário por email
    $stmt = $conn->prepare("SELECT id_usuario, nome, email, senha FROM usuarios WHERE email = ?");
    
    // Vincula parâmetro (email)
    $stmt->bind_param("s", $email);
    
    // Executa query
    $stmt->execute();
    
    // Pega resultado
    $result = $stmt->get_result();
    
    // Verifica se encontrou usuário
    if ($result->num_rows === 0) {
        // Usuário não encontrado
        echo json_encode([
            'success' => false,
            'message' => 'Email ou senha incorretos'
        ]);
        
        // Fecha statement e conexão
        $stmt->close();
        $conn->close();
        return;
    }
    
    // Pega dados do usuário
    $usuario = $result->fetch_assoc();
    
    // Verifica se senha está correta (compara com hash)
    if (!password_verify($senha, $usuario['senha'])) {
        // Senha incorreta
        echo json_encode([
            'success' => false,
            'message' => 'Email ou senha incorretos'
        ]);
        
        // Fecha statement e conexão
        $stmt->close();
        $conn->close();
        return;
    }
    
    // ===== LOGIN BEM-SUCEDIDO =====
    
    // Cria sessão com dados do usuário (sem a senha)
    $_SESSION['admin'] = [
        'id' => $usuario['id_usuario'],
        'nome' => $usuario['nome'],
        'email' => $usuario['email']
    ];
    
    // Retorna sucesso com dados do usuário
    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso',
        'user' => [
            'id' => $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email']
        ]
    ]);
    
    // Fecha statement e conexão
    $stmt->close();
    $conn->close();
}

// ===== FUNÇÃO DE LOGOUT =====

/**
 * Encerra sessão do administrador
 * Destroi dados da sessão PHP
 */
function handleLogout() {
    // Remove apenas dados do admin da sessão
    unset($_SESSION['admin']);
    
    // Destroi sessão completamente
    session_destroy();
    
    // Retorna sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Logout realizado com sucesso'
    ]);
}

// ===== FUNÇÃO DE VERIFICAÇÃO DE STATUS =====

/**
 * Verifica se usuário está autenticado
 * Retorna dados da sessão se logado
 */
function checkAuthStatus() {
    // Verifica se existe sessão de admin
    if (isset($_SESSION['admin'])) {
        // Retorna que está autenticado com dados do usuário
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user' => $_SESSION['admin']
        ]);
    } else {
        // Retorna que não está autenticado
        echo json_encode([
            'success' => true,
            'authenticated' => false
        ]);
    }
}

?>
