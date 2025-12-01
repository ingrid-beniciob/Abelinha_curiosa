<?php
/**
 * ===== ARQUIVO: calculate.php =====
 * Descrição: Cálculo de frete e busca de endereço por CEP
 * Responsável por: Integração com ViaCEP e simulação de valores de frete
 * 
 * USO: POST com JSON contendo {cep: "01310-100", carrinho: [...]}
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

// Verifica se CEP foi enviado
if (!isset($data['cep']) || empty($data['cep'])) {
    echo json_encode([
        'success' => false,
        'message' => 'CEP é obrigatório'
    ]);
    exit();
}

// Remove caracteres não numéricos do CEP
$cep = preg_replace('/[^0-9]/', '', $data['cep']);

// Valida formato do CEP (8 dígitos)
if (strlen($cep) !== 8) {
    echo json_encode([
        'success' => false,
        'message' => 'CEP inválido. Formato: 00000-000'
    ]);
    exit();
}

// ===== BUSCA ENDEREÇO NO VIACEP =====

/**
 * Consulta API do ViaCEP para buscar endereço
 * @param string $cep CEP sem formatação (apenas números)
 * @return array|false Dados do endereço ou false se erro
 */
function buscarEndereco($cep) {
    // Monta URL da API ViaCEP
    $url = "https://viacep.com.br/ws/{$cep}/json/";
    
    // Faz requisição HTTP GET
    $response = @file_get_contents($url);
    
    // Verifica se houve erro na requisição
    if ($response === false) {
        return false;
    }
    
    // Decodifica JSON da resposta
    $endereco = json_decode($response, true);
    
    // Verifica se CEP existe (ViaCEP retorna 'erro' se não encontrar)
    if (isset($endereco['erro']) && $endereco['erro'] === true) {
        return false;
    }
    
    // Retorna dados do endereço
    return $endereco;
}

// Busca endereço
$endereco = buscarEndereco($cep);

// Verifica se encontrou o endereço
if ($endereco === false) {
    echo json_encode([
        'success' => false,
        'message' => 'CEP não encontrado'
    ]);
    exit();
}

// ===== CÁLCULO DO FRETE =====

/**
 * Calcula valor do frete baseado no estado
 * @param string $estado Sigla do estado (SP, RJ, MG, etc.)
 * @param array $carrinho Itens do carrinho (opcional)
 * @return array Valor e prazo de entrega
 */
function calcularFrete($estado, $carrinho = []) {
    // Tabela de valores de frete por região
    $tabela_frete = [
        // Sudeste
        'SP' => ['valor' => 20.00, 'prazo' => '3-5 dias úteis'],
        'RJ' => ['valor' => 25.00, 'prazo' => '4-6 dias úteis'],
        'MG' => ['valor' => 30.00, 'prazo' => '5-7 dias úteis'],
        'ES' => ['valor' => 35.00, 'prazo' => '6-8 dias úteis'],
        
        // Sul
        'PR' => ['valor' => 35.00, 'prazo' => '5-7 dias úteis'],
        'SC' => ['valor' => 40.00, 'prazo' => '6-8 dias úteis'],
        'RS' => ['valor' => 45.00, 'prazo' => '7-9 dias úteis'],
        
        // Centro-Oeste
        'GO' => ['valor' => 40.00, 'prazo' => '6-8 dias úteis'],
        'MT' => ['valor' => 50.00, 'prazo' => '8-10 dias úteis'],
        'MS' => ['valor' => 45.00, 'prazo' => '7-9 dias úteis'],
        'DF' => ['valor' => 40.00, 'prazo' => '6-8 dias úteis'],
        
        // Nordeste
        'BA' => ['valor' => 45.00, 'prazo' => '7-9 dias úteis'],
        'SE' => ['valor' => 50.00, 'prazo' => '8-10 dias úteis'],
        'AL' => ['valor' => 50.00, 'prazo' => '8-10 dias úteis'],
        'PE' => ['valor' => 50.00, 'prazo' => '8-10 dias úteis'],
        'PB' => ['valor' => 55.00, 'prazo' => '9-11 dias úteis'],
        'RN' => ['valor' => 55.00, 'prazo' => '9-11 dias úteis'],
        'CE' => ['valor' => 60.00, 'prazo' => '10-12 dias úteis'],
        'PI' => ['valor' => 60.00, 'prazo' => '10-12 dias úteis'],
        'MA' => ['valor' => 65.00, 'prazo' => '11-13 dias úteis'],
        
        // Norte
        'TO' => ['valor' => 65.00, 'prazo' => '11-13 dias úteis'],
        'PA' => ['valor' => 70.00, 'prazo' => '12-14 dias úteis'],
        'AP' => ['valor' => 75.00, 'prazo' => '13-15 dias úteis'],
        'RR' => ['valor' => 80.00, 'prazo' => '14-16 dias úteis'],
        'AM' => ['valor' => 80.00, 'prazo' => '14-16 dias úteis'],
        'AC' => ['valor' => 85.00, 'prazo' => '15-17 dias úteis'],
        'RO' => ['valor' => 75.00, 'prazo' => '13-15 dias úteis']
    ];
    
    // Pega frete do estado (ou padrão se não encontrar)
    $frete = $tabela_frete[$estado] ?? ['valor' => 50.00, 'prazo' => '7-10 dias úteis'];
    
    // Calcula quantidade total de itens no carrinho (peso simulado)
    $qtd_itens = 0;
    if (!empty($carrinho)) {
        foreach ($carrinho as $item) {
            $qtd_itens += isset($item['quantidade']) ? (int)$item['quantidade'] : 1;
        }
    }
    
    // Se tiver mais de 3 itens, adiciona R$ 5 por item extra
    if ($qtd_itens > 3) {
        $frete['valor'] += ($qtd_itens - 3) * 5.00;
    }
    
    // Retorna frete calculado
    return $frete;
}

// Calcula frete baseado no estado do endereço
$carrinho = isset($data['carrinho']) ? $data['carrinho'] : [];
$frete = calcularFrete($endereco['uf'], $carrinho);

// ===== RETORNA RESPOSTA =====

// Retorna sucesso com endereço e frete
echo json_encode([
    'success' => true,
    'endereco' => [
        'cep' => $endereco['cep'],
        'logradouro' => $endereco['logradouro'],
        'complemento' => $endereco['complemento'] ?? '',
        'bairro' => $endereco['bairro'],
        'cidade' => $endereco['localidade'],
        'estado' => $endereco['uf']
    ],
    'frete' => [
        'valor' => number_format($frete['valor'], 2, '.', ''),
        'prazo' => $frete['prazo']
    ]
], JSON_UNESCAPED_UNICODE);

?>
