<?php
/**
 * ===== ARQUIVO: delete-product.php =====
 * Descrição: Deletar produto do banco de dados
 * Responsável por: Remover produto por ID
 * 
 * USO: POST com JSON contendo {id: 123}
 */

// Requer autenticação de admin
require_once 'check-auth.php';

// ===== VERIFICA MÉTODO DA REQUISIÇÃO =====

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use POST.'
    ]);
    exit();
}

// ===== LEITURA DOS DADOS =====

// Pega dados JSON do corpo da requisição
$json = file_get_contents('php://input');

// Decodifica JSON
$data = json_decode($json, true);

// ===== VALIDAÇÃO =====

// Verifica se ID foi enviado
if (!isset($data['id']) || empty($data['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do produto é obrigatório'
    ]);
    exit();
}

// Converte ID para inteiro
$id = (int)$data['id'];

// Valida se ID é maior que zero
if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do produto inválido'
    ]);
    exit();
}

// ===== CONECTA COM BANCO DE DADOS =====

// Obtém conexão MySQL
$conn = getConnection();

// ===== VERIFICA SE PRODUTO EXISTE =====

// Prepara query para verificar existência
$stmt_check = $conn->prepare("SELECT id FROM produtos WHERE id = ?");

// Vincula parâmetro
$stmt_check->bind_param("i", $id);

// Executa query
$stmt_check->execute();

// Pega resultado
$result = $stmt_check->get_result();

// Verifica se produto existe
if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Produto não encontrado'
    ]);
    
    // Fecha conexões
    $stmt_check->close();
    $conn->close();
    exit();
}

// Fecha statement de verificação
$stmt_check->close();

// ===== DELETA PRODUTO =====

// Prepara query SQL de deleção
$stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");

// Vincula parâmetro
$stmt->bind_param("i", $id);

// Executa query
if ($stmt->execute()) {
    // Sucesso na deleção
    echo json_encode([
        'success' => true,
        'message' => 'Produto deletado com sucesso'
    ]);
} else {
    // Erro na deleção
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao deletar produto: ' . $stmt->error
    ]);
}

// ===== FECHA CONEXÕES =====

// Fecha statement
$stmt->close();

// Fecha conexão
$conn->close();

?>
