<?php
/**
 * ===== ARQUIVO: search.php =====
 * Descrição: Buscar produtos por termo de pesquisa
 * Responsável por: Pesquisa em nome e descrição dos produtos
 * 
 * USO: GET /php/search.php?q=mobile
 */

// Inclui arquivo de configuração
require_once '../config/database.php';

// ===== VALIDAÇÃO =====

// Verifica se parâmetro 'q' (query) foi enviado
if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Termo de busca é obrigatório'
    ]);
    exit();
}

// Pega termo de busca e remove espaços extras
$termo = trim($_GET['q']);

// Valida tamanho mínimo do termo (mínimo 2 caracteres)
if (strlen($termo) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Termo de busca deve ter no mínimo 2 caracteres'
    ]);
    exit();
}

// ===== CONECTA COM BANCO DE DADOS =====

// Obtém conexão MySQL
$conn = getConnection();

// ===== PREPARA BUSCA =====

// Adiciona wildcards (%) para busca LIKE
$termo_busca = "%{$termo}%";

// Prepara query SQL
// Busca em 'nome' OU 'descricao' usando LIKE
$sql = "SELECT id, nome, descricao, preco, imagem, categoria, estoque, data_cadastro 
        FROM produtos 
        WHERE nome LIKE ? OR descricao LIKE ?
        ORDER BY nome ASC";

// Prepara statement
$stmt = $conn->prepare($sql);

// Vincula parâmetros (mesmo termo para nome e descrição)
$stmt->bind_param("ss", $termo_busca, $termo_busca);

// ===== EXECUTA BUSCA =====

// Executa query
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

// ===== RETORNA RESPOSTA =====

// Retorna sucesso com resultados
echo json_encode([
    'success' => true,
    'termo' => $termo,
    'total' => count($produtos),
    'produtos' => $produtos
], JSON_UNESCAPED_UNICODE);

// ===== FECHA CONEXÕES =====

// Fecha statement
$stmt->close();

// Fecha conexão
$conn->close();

?>
