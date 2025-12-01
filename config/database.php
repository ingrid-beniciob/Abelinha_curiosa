<?php
/**
 * ===== ARQUIVO: database.php =====
 * Descrição: Configuração e conexão com banco de dados MySQL
 * Responsável por: Estabelecer conexão com MySQL via mysqli
 */

// Inicia sessão PHP para uso em autenticação
session_start();

// ===== CONFIGURAÇÕES DO BANCO DE DADOS =====

// Host do servidor MySQL (localhost para XAMPP)
define('DB_HOST', 'localhost');

// Nome do banco de dados
define('DB_NAME', 'abelinha_curiosa');

// Usuário do MySQL (padrão do XAMPP)
define('DB_USER', 'root');

// Senha do MySQL (vazio no XAMPP padrão)
define('DB_PASS', '');

// Charset para conexão (UTF-8 para acentuação correta)
define('DB_CHARSET', 'utf8mb4');

// ===== FUNÇÃO DE CONEXÃO =====

/**
 * Cria e retorna conexão com banco de dados MySQL
 * 
 * @return mysqli Objeto de conexão MySQLi
 * @throws Exception Se falhar ao conectar
 */
function getConnection() {
    // Cria nova conexão mysqli
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verifica se houve erro na conexão
    if ($conn->connect_error) {
        // Registra erro no log do PHP
        error_log("Erro de conexão: " . $conn->connect_error);
        
        // Retorna erro em JSON para o frontend
        die(json_encode([
            'success' => false,
            'message' => 'Erro ao conectar com banco de dados'
        ]));
    }
    
    // Define charset da conexão para UTF-8
    $conn->set_charset(DB_CHARSET);
    
    // Retorna objeto de conexão
    return $conn;
}

// ===== CONFIGURAÇÕES DE CORS (para requisições AJAX) =====

// Permite requisições de qualquer origem (desenvolvimento)
header('Access-Control-Allow-Origin: *');

// Permite métodos HTTP necessários
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Permite cabeçalhos personalizados
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Define tipo de conteúdo como JSON (padrão para APIs)
header('Content-Type: application/json; charset=utf-8');

// Trata requisições OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Retorna status 200 OK para preflight
    http_response_code(200);
    exit();
}

// ===== CONFIGURAÇÕES DE ERRO =====

// Ativa exibição de erros (desenvolvimento)
// IMPORTANTE: Desativar em produção
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ===== CONFIGURAÇÕES DE UPLOAD =====

// Tamanho máximo de upload: 5MB
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '6M');

?>
