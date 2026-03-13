<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
        exit;
    }

    // Campos obrigatórios
    $campos = ['nome_completo', 'telefone', 'email', 'cidade', 'cargo_desejado'];
    $dados  = [];
    $faltando = [];

    foreach ($campos as $campo) {
        $valor = trim((string) ($_POST[$campo] ?? ''));
        if ($valor === '') {
            $faltando[] = $campo;
        }
        $dados[$campo] = strip_tags($valor);
    }

    if (!empty($faltando)) {
        echo json_encode([
            'success' => false,
            'message' => 'Campos obrigatórios não preenchidos: ' . implode(', ', $faltando),
        ]);
        exit;
    }

    // Valida e-mail
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'E-mail inválido.']);
        exit;
    }

    // Upload do currículo
    if (!isset($_FILES['curriculo']) || $_FILES['curriculo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'O upload do currículo é obrigatório.']);
        exit;
    }

    $file = $_FILES['curriculo'];

    // Valida extensão
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $extensoesPermitidas = ['pdf', 'doc', 'docx'];
    if (!in_array($ext, $extensoesPermitidas, true)) {
        echo json_encode([
            'success' => false,
            'message' => 'Formato de arquivo não permitido. Use PDF, DOC ou DOCX.',
        ]);
        exit;
    }

    // Valida tamanho (5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        echo json_encode([
            'success' => false,
            'message' => 'O arquivo do currículo deve ter no máximo 5MB.',
        ]);
        exit;
    }

    // Cria diretório de upload
    $uploadDir = __DIR__ . '/../storage/uploads/curriculos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Nome do arquivo: timestamp + nome limpo
    $nomeArquivo = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file['name']);
    $destino     = $uploadDir . $nomeArquivo;

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar o arquivo.']);
        exit;
    }

    $dados['curriculo_path'] = $nomeArquivo;

    // Salva no banco
    $db = require __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/Curriculo.php';

    $model = new Curriculo($db);
    $id    = $model->create($dados);

    echo json_encode([
        'success' => true,
        'message' => 'Currículo enviado com sucesso!',
        'id'      => $id,
    ]);

} catch (Throwable $e) {
    error_log('Erro ao salvar currículo: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno ao processar seu currículo. Tente novamente.',
    ]);
}
