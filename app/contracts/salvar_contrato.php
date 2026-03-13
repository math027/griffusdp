<?php
declare(strict_types=1);
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

try {
    $pdo = require __DIR__ . '/../config/database.php';

    $cnpjRaw   = $_POST['cnpj'] ?? '00000000000000';
    $cnpjPasta = preg_replace('/\D/', '', $cnpjRaw);

    // Uploads ficam em app/storage/uploads (fora do public_html)
    $uploadDir = __DIR__ . '/../storage/uploads/' . $cnpjPasta . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // ── Limites de tamanho (arquivo original, antes da compressão) ────────
    $limitBytes = [
        'docContratoSocial'  => 10 * 1024 * 1024,
        'docEndEmpresa'      => 5  * 1024 * 1024,
        'docCartaoCnpj'      => 3  * 1024 * 1024,
        'docCore'            => 5  * 1024 * 1024,
        'docCpfSocio'        => 3  * 1024 * 1024,
        'docIdentidadeSocio' => 5  * 1024 * 1024,
        'docEndSocioComp'    => 5  * 1024 * 1024,
    ];

    foreach ($limitBytes as $field => $maxSize) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            if ($_FILES[$field]['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => "O arquivo do campo $field excede o limite permitido."]);
                exit;
            }
        }
    }

    // ── Validação: todos os documentos são obrigatórios ───────────────────
    $docsObrigatorios = [
        'docContratoSocial'  => 'Contrato Social / Certificado MEI',
        'docEndEmpresa'      => 'Comprovante de Endereço (Empresa)',
        'docCartaoCnpj'      => 'Cartão CNPJ',
        'docCore'            => 'CORE',
        'docCpfSocio'        => 'CPF do Sócio',
        'docIdentidadeSocio' => 'Identidade do Sócio (RG)',
        'docEndSocioComp'    => 'Comprovante de Endereço (Sócio)',
    ];

    $faltando = [];
    foreach ($docsObrigatorios as $campo => $label) {
        if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
            $faltando[] = $label;
        }
    }

    if (!empty($faltando)) {
        echo json_encode([
            'success' => false,
            'message' => 'Documentos obrigatórios não enviados: ' . implode(', ', $faltando),
        ]);
        exit;
    }

    // ── Mapeamento campo → coluna do banco ────────────────────────────────
    $fileFields = [
        'docContratoSocial'  => 'doc_contrato_social',
        'docEndEmpresa'      => 'doc_end_empresa',
        'docCartaoCnpj'      => 'doc_cartao_cnpj',
        'docCore'            => 'doc_core',
        'docCpfSocio'        => 'doc_cpf_socio',
        'docIdentidadeSocio' => 'doc_identidade_socio',
        'docEndSocioComp'    => 'doc_end_socio_comp',
    ];

    $filePaths = [];

    foreach ($fileFields as $inputName => $dbColumn) {
        $filePaths[$dbColumn] = null;

        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            continue;
        }

        $tmpPath = $_FILES[$inputName]['tmp_name'];
        // O navegador já entrega imagens em WebP comprimido; salva o arquivo como recebido
        $ext     = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
        $destino = $uploadDir . $inputName . '.' . $ext;
        if (move_uploaded_file($tmpPath, $destino)) {
            $filePaths[$dbColumn] = 'uploads/' . $cnpjPasta . '/' . $inputName . '.' . $ext;
        }
    }

    // ── INSERT ────────────────────────────────────────────────────────────
    $sql = "INSERT INTO contratos (
        tipo_contrato,
        razao_social, cnpj, cep, endereco, numero, bairro, cidade, uf, celular, email_empresa,
        banco, agencia, conta, pix,
        nome_socio, cpf, rg, orgao_expedidor, nascimento, nacionalidade, estado_civil, profissao, email_socio,
        cep_socio, endereco_socio, numero_socio, bairro_socio, cidade_socio, uf_socio,
        doc_contrato_social, doc_end_empresa, doc_cartao_cnpj, doc_core,
        doc_cpf_socio, doc_identidade_socio, doc_end_socio_comp, status
    ) VALUES (
        :tipo_contrato,
        :razao_social, :cnpj, :cep, :endereco, :numero, :bairro, :cidade, :uf, :celular, :email_empresa,
        :banco, :agencia, :conta, :pix,
        :nome_socio, :cpf, :rg, :orgao_expedidor, :nascimento, :nacionalidade, :estado_civil, :profissao, :email_socio,
        :cep_socio, :endereco_socio, :numero_socio, :bairro_socio, :cidade_socio, :uf_socio,
        :doc_contrato_social, :doc_end_empresa, :doc_cartao_cnpj, :doc_core,
        :doc_cpf_socio, :doc_identidade_socio, :doc_end_socio_comp, :status
    )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':tipo_contrato'         => trim($_POST['tipoContrato'] ?? ''),
        ':razao_social'          => trim($_POST['razaoSocial'] ?? ''),
        ':cnpj'                  => trim($_POST['cnpj'] ?? ''),
        ':cep'                   => trim($_POST['cep'] ?? ''),
        ':endereco'              => trim($_POST['endereco'] ?? ''),
        ':numero'                => trim($_POST['numero'] ?? ''),
        ':bairro'                => trim($_POST['bairro'] ?? ''),
        ':cidade'                => trim($_POST['cidade'] ?? ''),
        ':uf'                    => trim($_POST['uf'] ?? ''),
        ':celular'               => trim($_POST['celular'] ?? ''),
        ':email_empresa'         => trim($_POST['emailEmpresa'] ?? ''),
        ':banco'                 => trim($_POST['banco'] ?? ''),
        ':agencia'               => trim($_POST['agencia'] ?? ''),
        ':conta'                 => trim($_POST['conta'] ?? ''),
        ':pix'                   => trim($_POST['pix'] ?? ''),
        ':nome_socio'            => trim($_POST['nomeSocio'] ?? ''),
        ':cpf'                   => trim($_POST['cpf'] ?? ''),
        ':rg'                    => trim($_POST['rg'] ?? ''),
        ':orgao_expedidor'       => trim($_POST['orgaoExpedidor'] ?? ''),
        ':nascimento'            => $_POST['nascimento'] ?? '',
        ':nacionalidade'         => trim($_POST['nacionalidade'] ?? ''),
        ':estado_civil'          => trim($_POST['estadoCivil'] ?? ''),
        ':profissao'             => trim($_POST['profissao'] ?? ''),
        ':email_socio'           => trim($_POST['emailSocio'] ?? ''),
        ':cep_socio'             => trim($_POST['cepSocio'] ?? ''),
        ':endereco_socio'        => trim($_POST['enderecoSocio'] ?? ''),
        ':numero_socio'          => trim($_POST['numeroSocio'] ?? ''),
        ':bairro_socio'          => trim($_POST['bairroSocio'] ?? ''),
        ':cidade_socio'          => trim($_POST['cidadeSocio'] ?? ''),
        ':uf_socio'              => trim($_POST['ufSocio'] ?? ''),
        ':doc_contrato_social'   => $filePaths['doc_contrato_social'],
        ':doc_end_empresa'       => $filePaths['doc_end_empresa'],
        ':doc_cartao_cnpj'       => $filePaths['doc_cartao_cnpj'],
        ':doc_core'              => $filePaths['doc_core'],
        ':doc_cpf_socio'         => $filePaths['doc_cpf_socio'],
        ':doc_identidade_socio'  => $filePaths['doc_identidade_socio'],
        ':doc_end_socio_comp'    => $filePaths['doc_end_socio_comp'],
        ':status'                => 'novo',
    ]);

    echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso!']);

} catch (Exception $e) {
    error_log('Erro ao salvar contrato: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao salvar. Tente novamente.']);
}