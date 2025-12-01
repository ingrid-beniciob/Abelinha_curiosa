<?php
/* Descrição: Upload de imagens de produtos
 * Responsável por: Receber, validar e salvar imagens enviadas pelo admin
 */

// Requer autenticação de admin
require_once 'check-auth.php';

// ===== VERIFICA MÉTODO DA REQUISIÇÃO =====//

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use POST.'
    ]);
    exit();
}

// ===== VALIDAÇÃO DO ARQUIVO =====

// Verifica se arquivo foi enviado
if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode([
        'success' => false,
        'message' => 'Nenhuma imagem foi enviada'
    ]);
    exit();
}

// Pega informações do arquivo
$arquivo = $_FILES['imagem'];

// Verifica se houve erro no upload
if ($arquivo['error'] !== UPLOAD_ERR_OK) {
    // Mensagens de erro por código
    $erros_upload = [
        UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do servidor)',
        UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (limite do formulário)',
        UPLOAD_ERR_PARTIAL => 'Upload parcial - tente novamente',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada',
        UPLOAD_ERR_CANT_WRITE => 'Erro ao salvar arquivo',
        UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
    ];
    
    $mensagem_erro = $erros_upload[$arquivo['error']] ?? 'Erro desconhecido no upload';
    
    echo json_encode([
        'success' => false,
        'message' => $mensagem_erro
    ]);
    exit();
}

// ===== VALIDAÇÃO DO TIPO DE ARQUIVO =====

// Extensões permitidas
$extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Pega extensão do arquivo
$nome_arquivo = $arquivo['name'];
$extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));

// Verifica se extensão é permitida
if (!in_array($extensao, $extensoes_permitidas)) {
    echo json_encode([
        'success' => false,
        'message' => 'Formato de imagem não permitido. Use: JPG, PNG, GIF ou WebP'
    ]);
    exit();
}

// ===== VALIDAÇÃO DO TAMANHO =====

// Tamanho máximo: 5MB (em bytes)
$tamanho_maximo = 5 * 1024 * 1024; // 5MB

// Verifica tamanho
if ($arquivo['size'] > $tamanho_maximo) {
    echo json_encode([
        'success' => false,
        'message' => 'Imagem muito grande. Máximo: 5MB'
    ]);
    exit();
}

// ===== VALIDAÇÃO DO MIME TYPE =====

// Tipos MIME permitidos
$tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// Pega MIME type do arquivo
$tipo_arquivo = mime_content_type($arquivo['tmp_name']);

// Verifica se tipo é permitido
if (!in_array($tipo_arquivo, $tipos_permitidos)) {
    echo json_encode([
        'success' => false,
        'message' => 'Arquivo não é uma imagem válida'
    ]);
    exit();
}

// ===== DEFINE PASTA DE DESTINO =====

// Pega categoria do produto (se enviada)
$categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : 'produtos';

// Valida categoria
$categorias_validas = ['piticos', 'fofinhos', 'crescidinhos', 'grandinhos', 'produtos'];
if (!in_array($categoria, $categorias_validas)) {
    $categoria = 'produtos'; // Fallback para pasta padrão
}

// Define pasta de upload
$pasta_upload = "../assets/images/{$categoria}/";

// Cria pasta se não existir
if (!file_exists($pasta_upload)) {
    mkdir($pasta_upload, 0755, true); // 0755 = permissões rwxr-xr-x
}

// ===== GERA NOME ÚNICO PARA O ARQUIVO =====

// Gera nome único usando timestamp + random + extensão original
$nome_unico = time() . '_' . uniqid() . '.' . $extensao;

// Caminho completo do arquivo
$caminho_completo = $pasta_upload . $nome_unico;

// ===== MOVE ARQUIVO PARA PASTA FINAL =====

// Tenta mover arquivo da pasta temporária para pasta final
if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
    // ===== UPLOAD BEM-SUCEDIDO =====
    
    // Caminho relativo para salvar no banco
    $caminho_banco = "assets/images/{$categoria}/" . $nome_unico;
    
    // Retorna sucesso com caminho do arquivo
    echo json_encode([
        'success' => true,
        'message' => 'Imagem enviada com sucesso',
        'imagem' => $caminho_banco,
        'nome_arquivo' => $nome_unico,
        'tamanho' => $arquivo['size'],
        'tipo' => $tipo_arquivo
    ]);
    
} else {
    // ===== ERRO AO MOVER ARQUIVO =====
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar imagem no servidor'
    ]);
}

?>
