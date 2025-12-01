<?php
/**
 * ===== ARQUIVO: list.php =====
 * Descrição: Listagem de produtos do e-commerce
 * Responsável por: Buscar e retornar produtos do banco de dados
 * 
 * EXEMPLOS DE USO:
 * - Todos os produtos: GET /php/list.php
 * - Por categoria: GET /php/list.php?categoria=piticos
 */

// Inclui arquivo de configuração e conexão
require_once '../config/database.php';

// ===== CONECTA COM BANCO DE DADOS =====

// Obtém conexão MySQL
$conn = getConnection();

// ===== PREPARA QUERY SQL =====

// Inicia query base
$sql = "SELECT id, nome, descricao, preco, imagem, categoria, estoque, data_cadastro FROM produtos";

// Verifica se foi enviado parâmetro de categoria
if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    // Pega valor da categoria
    $categoria = $_GET['categoria'];
    
    // Adiciona filtro WHERE na query
    $sql .= " WHERE categoria = ?";
    
    // Prepara statement com parâmetro
    $stmt = $conn->prepare($sql);
    
    // Vincula parâmetro (categoria)
    $stmt->bind_param("s", $categoria);
} else {
    // Sem filtro - pega todos os produtos
    $stmt = $conn->prepare($sql);
}

// Adiciona ordenação por data de cadastro (mais recentes primeiro)
$sql .= " ORDER BY data_cadastro DESC";

// Prepara statement novamente com ORDER BY
if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $sql = "SELECT id, nome, descricao, preco, imagem, categoria, estoque, data_cadastro FROM produtos WHERE categoria = ? ORDER BY data_cadastro DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categoria);
} else {
    $sql = "SELECT id, nome, descricao, preco, imagem, categoria, estoque, data_cadastro FROM produtos ORDER BY data_cadastro DESC";
    $stmt = $conn->prepare($sql);
}

// ===== EXECUTA QUERY =====

// Executa statement
$stmt->execute();

// Pega resultado
$result = $stmt->get_result();

// ===== MONTA ARRAY DE PRODUTOS =====

// Inicializa array vazio
$produtos = [];

// Percorre cada linha do resultado
while ($row = $result->fetch_assoc()) {
    // Adiciona produto ao array
    $produtos[] = [
        'id' => (int)$row['id'],
        'nome' => $row['nome'],
        'descricao' => $row['descricao'],
        'preco' => (float)$row['preco'],
        'imagem' => $row['imagem'],
        'categoria' => $row['categoria'],
        'estoque' => (int)$row['estoque'],
        'data_cadastro' => $row['data_cadastro']
    ];
}

// ===== RETORNA RESPOSTA JSON =====

// Retorna sucesso com array de produtos
echo json_encode([
    'success' => true,
    'total' => count($produtos),
    'produtos' => $produtos
], JSON_UNESCAPED_UNICODE); // JSON_UNESCAPED_UNICODE para acentos corretos

// ===== FECHA CONEXÕES =====

// Fecha statement
$stmt->close();

// Fecha conexão
$conn->close();

?>
