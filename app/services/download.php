<?php

use PhpOffice\PhpWord\TemplateProcessor;

function sendContractDocx(array $contract): void
{
    require_once APP_ROOT . '/vendor/autoload.php';

    $tipoContrato = strtoupper(trim($contract['tipo_contrato'] ?? ''));

    if ($tipoContrato === 'PJ') {
        $templateFile = APP_ROOT . '/storage/templates/modelo_contrato_pj.docx';
    } elseif ($tipoContrato === 'RCA') {
        $templateFile = APP_ROOT . '/storage/templates/modelo_contrato_rca.docx';
    } else {
        die("Erro: Tipo de contrato inválido ou não definido.");
    }

    if (!file_exists($templateFile)) {
        die("Erro: Modelo não encontrado em " . $templateFile);
    }

    try {
        $templateProcessor = new TemplateProcessor($templateFile);

        // --- 1. DADOS DA EMPRESA ---
        $templateProcessor->setValue('razaoSocial', $contract['razao_social'] ?? '');
        $templateProcessor->setValue('cnpj', $contract['cnpj'] ?? '');
        
        // Concatena Rua, Número e Bairro para o campo {enderecoEmpresa}
        $endEmpresa = sprintf(
            '%s, %s - %s',
            $contract['endereco'] ?? '',
            $contract['numero'] ?? '',
            $contract['bairro'] ?? ''
        );
        $templateProcessor->setValue('enderecoEmpresa', $endEmpresa);
        
        $templateProcessor->setValue('cidadeEmpresa', $contract['cidade'] ?? '');
        $templateProcessor->setValue('ufEmpresa', $contract['uf'] ?? '');

        // --- 2. DADOS DO SÓCIO ---
        $templateProcessor->setValue('nomeSocio', $contract['nome_socio'] ?? '');
        $templateProcessor->setValue('nacionalidadeSocio', $contract['nacionalidade'] ?? '');
        $templateProcessor->setValue('estadoCivilSocio', $contract['estado_civil'] ?? '');
        $templateProcessor->setValue('profissaoSocio', $contract['profissao'] ?? '');
        $templateProcessor->setValue('rgSocio', $contract['rg'] ?? '');
        $templateProcessor->setValue('cpfSocio', $contract['cpf'] ?? '');
        
        // Concatena Rua, Número e Bairro para o campo {enderecoSocio}
        $endSocio = sprintf(
            '%s, %s - %s',
            $contract['endereco_socio'] ?? '',
            $contract['numero_socio'] ?? '',
            $contract['bairro_socio'] ?? ''
        );
        $templateProcessor->setValue('enderecoSocio', $endSocio);
        
        $templateProcessor->setValue('cidadeSocio', $contract['cidade_socio'] ?? '');
        $templateProcessor->setValue('ufSocio', $contract['uf_socio'] ?? '');
        $templateProcessor->setValue('cepSocio', $contract['cep_socio'] ?? '');
        $templateProcessor->setValue('emailSocio', $contract['email_socio'] ?? '');

        // --- 3. EXTRAS (Data atual) ---
        // Substitui [data da assinatura] pela data de hoje por extenso, se necessário
        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
        $dataHoje = strftime('%d de %B de %Y', strtotime('today'));
        $templateProcessor->setValue('data da assinatura', $dataHoje);

        // --- DOWNLOAD ---
        $tempFileName = tempnam(sys_get_temp_dir(), 'Con_');
        $templateProcessor->saveAs($tempFileName);

        $nomeArquivo = 'Contrato_' . preg_replace('/[^a-zA-Z0-9]/', '_', $contract['razao_social']) . '.docx';

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($tempFileName));

        readfile($tempFileName);
        unlink($tempFileName);
        exit;

    } catch (\Exception $e) {
        die("Erro ao gerar contrato: " . $e->getMessage());
    }
}