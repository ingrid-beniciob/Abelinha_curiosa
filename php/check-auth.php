<?php
/**
 * ===== ARQUIVO: check-auth.php =====
 * Descrição: Verificação de autenticação para páginas administrativas
 * Responsável por: Proteger rotas que exigem login de admin
 * 
 * USO: Incluir no início de páginas/APIs que exigem autenticação
 * Exemplo: require_once '../php/check-auth.php';
 */

// Inclui arquivo de configuração (inicia sessão)
require_once '../config/database.php';

// ===== VERIFICAÇÃO DE AUTENTICAÇÃO =====

// Verifica se sessão de admin existe
if (!isset($_SESSION['admin'])) {
    // Não está autenticado - retorna erro 401 (Unauthorized)
    http_response_code(401);
    
    // Retorna mensagem de erro em JSON
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado. Faça login para continuar.',
        'authenticated' => false
    ]);
    
    // Interrompe execução do script
    exit();
}

// ===== USUÁRIO AUTENTICADO =====

// Define variável global com dados do admin logado
$admin = $_SESSION['admin'];

// Define constante indicando que usuário está autenticado
define('IS_AUTHENTICATED', true);

?>
