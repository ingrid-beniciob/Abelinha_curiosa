<?php
/**
 * ===== ARQUIVO: save-product.php =====
 * Descrição: Criar ou atualizar produtos
 * Responsável por: CRUD de produtos (Create e Update)
 * 
 * USO: POST com JSON contendo dados do produto
 * - Criar: Enviar sem 'id'
 * - Atualizar: Enviar com 'id' do produto
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

// ===== VALIDAÇÃO DOS DADOS =====

// Campos obrigatórios
$campos_obrigatorios = ['nome', 'preco', 'categoria'];

// Verifica se todos os campos obrigatórios foram enviados
foreach ($campos_obrigatorios as $campo) {
    if (!isset($data[$campo]) || empty($data[$campo])) {
        echo json_encode([
            'success' => false,
            'message' => "Campo '{$campo}' é obrigatório"
        ]);
        exit();
    }
}

// Extrai dados do array
$nome = trim($data['nome']);
$descricao = isset($data['descricao']) ? trim($data['descricao']) : '';
$preco = (float)$data['preco'];
$categoria = trim($data['categoria']);
$estoque = isset($data['estoque']) ? (int)$data['estoque'] : 0;
$imagem = isset($data['imagem']) ? trim($data['imagem']) : '';

// Valida preço (deve ser maior que zero)
if ($preco <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Preço deve ser maior que zero'
    ]);
    exit();
}

// Valida categoria (deve ser uma das 4 categorias válidas)
$categorias_validas = ['piticos', 'fofinhos', 'crescidinhos', 'grandinhos'];
if (!in_array($categoria, $categorias_validas)) {
    echo json_encode([
        'success' => false,
        'message' => 'Categoria inválida'
    ]);
    exit();
}

// ===== CONECTA COM BANCO DE DADOS =====

// Obtém conexão MySQL
$conn = getConnection();

// ===== VERIFICA SE É CRIAÇÃO OU ATUALIZAÇÃO =====

// Se 'id' foi enviado, é atualização
if (isset($data['id']) && !empty($data['id'])) {
    // ===== ATUALIZAÇÃO DE PRODUTO =====
    
    $id = (int)$data['id'];
    
    // Prepara query SQL de atualização
    $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, imagem = ?, categoria = ?, estoque = ? WHERE id = ?";
    
    // Prepara statement
    $stmt = $conn->prepare($sql);
    
    // Vincula parâmetros
    $stmt->bind_param("ssdssii", $nome, $descricao, $preco, $imagem, $categoria, $estoque, $id);
    
    // Executa query
    if ($stmt->execute()) {
        // Sucesso na atualização
        echo json_encode([
            'success' => true,
            'message' => 'Produto atualizado com sucesso',
            'produto_id' => $id
        ]);
    } else {
        // Erro na atualização
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao atualizar produto: ' . $stmt->error
        ]);
    }
    
} else {
    // ===== CRIAÇÃO DE NOVO PRODUTO =====
    
    // Prepara query SQL de inserção
    $sql = "INSERT INTO produtos (nome, descricao, preco, imagem, categoria, estoque) VALUES (?, ?, ?, ?, ?, ?)";
    
    // Prepara statement
    $stmt = $conn->prepare($sql);
    
    // Vincula parâmetros
    $stmt->bind_param("ssdssi", $nome, $descricao, $preco, $imagem, $categoria, $estoque);
    
    // Executa query
    if ($stmt->execute()) {
        // Pega ID do produto recém-criado
        $produto_id = $conn->insert_id;
        
        // Sucesso na criação
        echo json_encode([
            'success' => true,
            'message' => 'Produto criado com sucesso',
            'produto_id' => $produto_id
        ]);
    } else {
        // Erro na criação
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao criar produto: ' . $stmt->error
        ]);
    }
}

// ===== FECHA CONEXÕES =====

// Fecha statement
$stmt->close();

// Fecha conexão
$conn->close();

?>
