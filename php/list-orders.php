<?php
/**
 * ===== ARQUIVO: list-orders.php =====
 * Descrição: Listar pedidos (área administrativa)
 * Responsável por: Buscar pedidos com filtros opcionais
 * 
 * USO: GET /php/list-orders.php ou GET /php/list-orders.php?status=pendente
 */

// Requer autenticação de admin
require_once 'check-auth.php';

// ===== CONECTA COM BANCO DE DADOS =====

// Obtém conexão MySQL
$conn = getConnection();

// ===== PREPARA QUERY SQL =====

// Query base para listar pedidos
$sql = "SELECT id, nome_cliente, email, cep, endereco, numero, complemento, cidade, estado, 
               valor_subtotal, valor_frete, valor_total, status, data_pedido 
        FROM pedidos";

// Verifica se foi enviado filtro de status
if (isset($_GET['status']) && !empty($_GET['status'])) {
    // Pega valor do status
    $status = $_GET['status'];
    
    // Valida status (deve ser um dos valores válidos)
    $status_validos = ['pendente', 'pago', 'enviado', 'entregue', 'cancelado'];
    
    if (in_array($status, $status_validos)) {
        // Adiciona filtro WHERE na query
        $sql .= " WHERE status = ?";
        $usar_filtro = true;
    } else {
        $usar_filtro = false;
    }
} else {
    $usar_filtro = false;
}

// Adiciona ordenação (mais recentes primeiro)
$sql .= " ORDER BY data_pedido DESC";

// ===== PREPARA STATEMENT =====

if ($usar_filtro) {
    // Com filtro de status
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
} else {
    // Sem filtro
    $stmt = $conn->prepare($sql);
}

// ===== EXECUTA QUERY =====

// Executa statement
$stmt->execute();

// Pega resultado
$result = $stmt->get_result();

// ===== MONTA ARRAY DE PEDIDOS =====

// Inicializa array vazio
$pedidos = [];

// Percorre cada linha do resultado
while ($row = $result->fetch_assoc()) {
    // Adiciona pedido ao array
    $pedidos[] = [
        'id' => (int)$row['id'],
        'nome_cliente' => $row['nome_cliente'],
        'email' => $row['email'],
        'cep' => $row['cep'],
        'endereco' => $row['endereco'],
        'numero' => $row['numero'],
        'complemento' => $row['complemento'],
        'cidade' => $row['cidade'],
        'estado' => $row['estado'],
        'valor_subtotal' => (float)$row['valor_subtotal'],
        'valor_frete' => (float)$row['valor_frete'],
        'valor_total' => (float)$row['valor_total'],
        'status' => $row['status'],
        'data_pedido' => $row['data_pedido']
    ];
}

// ===== RETORNA RESPOSTA =====

// Retorna sucesso com array de pedidos
echo json_encode([
    'success' => true,
    'total' => count($pedidos),
    'pedidos' => $pedidos
], JSON_UNESCAPED_UNICODE);

// ===== FECHA CONEXÕES =====

// Fecha statement
$stmt->close();

// Fecha conexão
$conn->close();

?>
