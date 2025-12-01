<?php
/**
 * ===== ARQUIVO: create-order.php =====
 * Descrição: Criar novo pedido no e-commerce
 * Responsável por: Processar checkout e salvar pedido + itens
 * 
 * USO: POST com JSON contendo dados do cliente e carrinho
 */

// Inclui arquivo de configuração
require_once '../config/database.php';

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

// ===== VALIDAÇÃO DOS DADOS DO CLIENTE =====

// Campos obrigatórios do cliente
$campos_cliente = ['nome_cliente', 'email', 'cep', 'endereco', 'numero', 'cidade', 'estado'];

// Verifica se todos os campos foram enviados
foreach ($campos_cliente as $campo) {
    if (!isset($data[$campo]) || empty(trim($data[$campo]))) {
        echo json_encode([
            'success' => false,
            'message' => "Campo '{$campo}' é obrigatório"
        ]);
        exit();
    }
}

// Verifica se carrinho foi enviado e não está vazio
if (!isset($data['carrinho']) || !is_array($data['carrinho']) || empty($data['carrinho'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Carrinho está vazio'
    ]);
    exit();
}

// ===== EXTRAI DADOS =====

// Dados do cliente
$nome_cliente = trim($data['nome_cliente']);
$email = trim($data['email']);
$cep = preg_replace('/[^0-9]/', '', $data['cep']); // Remove caracteres não numéricos
$endereco = trim($data['endereco']);
$numero = trim($data['numero']);
$complemento = isset($data['complemento']) ? trim($data['complemento']) : '';
$cidade = trim($data['cidade']);
$estado = strtoupper(trim($data['estado'])); // Converte para maiúsculas

// Valores
$valor_frete = isset($data['valor_frete']) ? (float)$data['valor_frete'] : 0.00;
$carrinho = $data['carrinho'];

// ===== VALIDA EMAIL =====

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email inválido'
    ]);
    exit();
}

// ===== CONECTA COM BANCO DE DADOS =====

// Obtém conexão MySQL
$conn = getConnection();

// ===== INICIA TRANSAÇÃO =====
// Transação garante que pedido e itens sejam salvos juntos ou nenhum seja salvo

$conn->begin_transaction();

try {
    // ===== CALCULA SUBTOTAL =====
    
    $valor_subtotal = 0.00;
    
    // Percorre carrinho para calcular subtotal
    foreach ($carrinho as $item) {
        // Valida estrutura do item
        if (!isset($item['id']) || !isset($item['quantidade']) || !isset($item['preco'])) {
            throw new Exception('Item do carrinho inválido');
        }
        
        // Soma ao subtotal
        $valor_subtotal += (float)$item['preco'] * (int)$item['quantidade'];
    }
    
    // Calcula valor total (subtotal + frete)
    $valor_total = $valor_subtotal + $valor_frete;
    
    // ===== INSERE PEDIDO =====
    
    // Prepara query SQL para inserir pedido
    $sql_pedido = "INSERT INTO pedidos (nome_cliente, email, cep, endereco, numero, complemento, cidade, estado, valor_subtotal, valor_frete, valor_total, status) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente')";
    
    // Prepara statement
    $stmt_pedido = $conn->prepare($sql_pedido);
    
    // Vincula parâmetros
    $stmt_pedido->bind_param("ssssssssddd", 
        $nome_cliente, $email, $cep, $endereco, $numero, $complemento, $cidade, $estado,
        $valor_subtotal, $valor_frete, $valor_total
    );
    
    // Executa inserção do pedido
    if (!$stmt_pedido->execute()) {
        throw new Exception('Erro ao criar pedido');
    }
    
    // Pega ID do pedido recém-criado
    $pedido_id = $conn->insert_id;
    
    // Fecha statement do pedido
    $stmt_pedido->close();
    
    // ===== INSERE ITENS DO PEDIDO =====
    
    // Prepara query SQL para inserir itens
    $sql_item = "INSERT INTO itens_pedidos (pedido_id, produto_id, quantidade, preco_unitario, subtotal) 
                 VALUES (?, ?, ?, ?, ?)";
    
    // Prepara statement
    $stmt_item = $conn->prepare($sql_item);
    
    // Percorre cada item do carrinho
    foreach ($carrinho as $item) {
        // Extrai dados do item
        $produto_id = (int)$item['id'];
        $quantidade = (int)$item['quantidade'];
        $preco_unitario = (float)$item['preco'];
        $subtotal_item = $preco_unitario * $quantidade;
        
        // Vincula parâmetros
        $stmt_item->bind_param("iiidd", $pedido_id, $produto_id, $quantidade, $preco_unitario, $subtotal_item);
        
        // Executa inserção do item
        if (!$stmt_item->execute()) {
            throw new Exception('Erro ao adicionar item ao pedido');
        }
        
        // ===== ATUALIZA ESTOQUE DO PRODUTO =====
        
        // Prepara query para atualizar estoque
        $sql_estoque = "UPDATE produtos SET estoque = estoque - ? WHERE id = ?";
        $stmt_estoque = $conn->prepare($sql_estoque);
        $stmt_estoque->bind_param("ii", $quantidade, $produto_id);
        
        // Executa atualização de estoque
        if (!$stmt_estoque->execute()) {
            throw new Exception('Erro ao atualizar estoque');
        }
        
        $stmt_estoque->close();
    }
    
    // Fecha statement de itens
    $stmt_item->close();
    
    // ===== CONFIRMA TRANSAÇÃO =====
    
    // Commit - confirma todas as operações
    $conn->commit();
    
    // ===== RETORNA SUCESSO =====
    
    echo json_encode([
        'success' => true,
        'message' => 'Pedido criado com sucesso',
        'pedido_id' => $pedido_id,
        'valor_total' => number_format($valor_total, 2, '.', '')
    ]);
    
} catch (Exception $e) {
    // ===== ERRO - DESFAZ TRANSAÇÃO =====
    
    // Rollback - desfaz todas as operações
    $conn->rollback();
    
    // Retorna erro
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar pedido: ' . $e->getMessage()
    ]);
}

// ===== FECHA CONEXÃO =====

// Fecha conexão
$conn->close();

?>
