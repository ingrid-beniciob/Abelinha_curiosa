<?php
/*Descrição: Atualizar status de um pedido
 * Responsável por: Alterar status do pedido (pendente → pago → enviado → entregue)
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

// Verifica se ID e status foram enviados
if (!isset($data['id']) || empty($data['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do pedido é obrigatório'
    ]);
    exit();
}

if (!isset($data['status']) || empty($data['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Status é obrigatório'
    ]);
    exit();
}

// Converte ID para inteiro
$id = (int)$data['id'];

// Pega novo status
$novo_status = trim($data['status']);

// Valida status (deve ser um dos valores permitidos)
$status_validos = ['pendente', 'pago', 'enviado', 'entregue', 'cancelado'];

if (!in_array($novo_status, $status_validos)) {
    echo json_encode([
        'success' => false,
        'message' => 'Status inválido. Use: pendente, pago, enviado, entregue ou cancelado'
    ]);
    exit();
}

// ===== CONECTA COM BANCO DE DADOS =====

// Obtém conexão MySQL
$conn = getConnection();

// ===== VERIFICA SE PEDIDO EXISTE =====

// Prepara query para verificar existência
$stmt_check = $conn->prepare("SELECT id, status FROM pedidos WHERE id = ?");

// Vincula parâmetro
$stmt_check->bind_param("i", $id);

// Executa query
$stmt_check->execute();

// Pega resultado
$result = $stmt_check->get_result();

// Verifica se pedido existe
if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Pedido não encontrado'
    ]);
    
    // Fecha conexões
    $stmt_check->close();
    $conn->close();
    exit();
}

// Pega status atual
$pedido = $result->fetch_assoc();
$status_atual = $pedido['status'];

// Fecha statement de verificação
$stmt_check->close();

// ===== ATUALIZA STATUS =====

// Prepara query SQL de atualização
$stmt = $conn->prepare("UPDATE pedidos SET status = ? WHERE id = ?");

// Vincula parâmetros
$stmt->bind_param("si", $novo_status, $id);

// Executa query
if ($stmt->execute()) {
    // Sucesso na atualização
    echo json_encode([
        'success' => true,
        'message' => "Status atualizado de '{$status_atual}' para '{$novo_status}'",
        'pedido_id' => $id,
        'novo_status' => $novo_status
    ]);
} else {
    // Erro na atualização
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar status: ' . $stmt->error
    ]);
}

// ===== FECHA CONEXÕES =====

// Fecha statement
$stmt->close();

// Fecha conexão
$conn->close();

?>
