<?php
declare(strict_types=1);

class ContractController
{
    private Contract $model;

    public function __construct(PDO $db)
    {
        $this->model = new Contract($db);
    }

    /**
     * Lista contratos.
     */
    public function index(): void
    {
        $contracts = $this->model->getAll();
        $csrfToken = $this->getCsrfToken();

        require VIEW_PATH . '/contracts/index.php';
    }

    /**
     * Formulário de edição.
     */
    public function edit(int $id): void
    {
        $contract = $this->model->find($id);
        if (!$contract) {
            http_response_code(404);
            exit('Contrato não encontrado.');
        }

        $csrfToken = $this->getCsrfToken();
        require VIEW_PATH . '/contracts/edit.php';
    }

    /**
     * Processa edição (texto + upload de documentos).
     */
    public function update(): void
    {
        $this->requirePost();
        $this->requireValidCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $payload = $this->sanitizePayload($_POST);

        // ── Processar uploads de documentos ──────────────────────────────────
        $fileFields = [
            'upload_doc_contrato_social'  => 'doc_contrato_social',
            'upload_doc_end_empresa'      => 'doc_end_empresa',
            'upload_doc_cartao_cnpj'      => 'doc_cartao_cnpj',
            'upload_doc_core'             => 'doc_core',
            'upload_doc_cpf_socio'        => 'doc_cpf_socio',
            'upload_doc_identidade_socio' => 'doc_identidade_socio',
            'upload_doc_end_socio_comp'   => 'doc_end_socio_comp',
        ];

        $cnpjRaw   = $payload['cnpj'] ?? '00000000000000';
        $cnpjPasta = preg_replace('/\D/', '', $cnpjRaw);
        $uploadDir = APP_ROOT . '/storage/uploads/' . $cnpjPasta . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $limitBytes = [
            'upload_doc_contrato_social'  => 10 * 1024 * 1024,
            'upload_doc_end_empresa'      =>  5 * 1024 * 1024,
            'upload_doc_cartao_cnpj'      =>  3 * 1024 * 1024,
            'upload_doc_core'             =>  5 * 1024 * 1024,
            'upload_doc_cpf_socio'        =>  3 * 1024 * 1024,
            'upload_doc_identidade_socio' =>  5 * 1024 * 1024,
            'upload_doc_end_socio_comp'   =>  5 * 1024 * 1024,
        ];

        foreach ($fileFields as $inputName => $dbColumn) {
            if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
                // Sem novo arquivo: manter o valor existente do hidden input
                continue;
            }

            // Valida tamanho
            $maxSize = $limitBytes[$inputName] ?? (5 * 1024 * 1024);
            if ($_FILES[$inputName]['size'] > $maxSize) {
                $this->sendJson(false, "O arquivo do campo {$dbColumn} excede o limite permitido.");
                return;
            }

            $tmpPath  = $_FILES[$inputName]['tmp_name'];
            $ext      = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
            // Usa o nome do campo DB como nome do arquivo (ex: doc_contrato_social.webp)
            $destino  = $uploadDir . $dbColumn . '.' . $ext;

            if (move_uploaded_file($tmpPath, $destino)) {
                $payload[$dbColumn] = 'uploads/' . $cnpjPasta . '/' . $dbColumn . '.' . $ext;
            }
        }

        $this->model->update($id, $payload);

        // Responde JSON para requisições AJAX, redirect para submits normais
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                  && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            $this->sendJson(true, 'Contrato atualizado com sucesso!');
        } else {
            $this->redirectToIndex();
        }
    }

    /**
     * Envia resposta JSON e encerra.
     */
    private function sendJson(bool $success, string $message): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    /**
     * Atualiza status.
     */
    public function changeStatus(): void
    {
        $this->requirePost();
        $this->requireValidCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');

        $this->model->updateStatus($id, $status);
        $this->redirectToIndex();
    }

    /**
     * Exclui contrato.
     */
    public function delete(): void
    {
        $this->requirePost();
        $this->requireValidCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $this->model->delete($id);

        $this->redirectToIndex();
    }

    /**
     * Gera e baixa o contrato em .docx usando PHPWord.
     */
    public function download(int $id): void
    {
        $contract = $this->model->find($id);
        if (!$contract) {
            http_response_code(404);
            exit('Contrato não encontrado.');
        }

        require APP_ROOT . '/services/download.php';
        sendContractDocx($contract);
    }

    private function sanitizePayload(array $input): array
    {
        return [
            'tipo_contrato' => trim((string)($input['tipo_contrato'] ?? '')),
            'razao_social' => trim((string)($input['razao_social'] ?? '')),
            'cnpj' => trim((string)($input['cnpj'] ?? '')),
            'cep' => trim((string)($input['cep'] ?? '')),
            'endereco' => trim((string)($input['endereco'] ?? '')),
            'numero' => trim((string)($input['numero'] ?? '')),
            'bairro' => trim((string)($input['bairro'] ?? '')),
            'cidade' => trim((string)($input['cidade'] ?? '')),
            'uf' => trim((string)($input['uf'] ?? '')),
            'telefone' => trim((string)($input['telefone'] ?? '')),
            'celular' => trim((string)($input['celular'] ?? '')),
            'email_empresa' => trim((string)($input['email_empresa'] ?? '')),
            'banco' => trim((string)($input['banco'] ?? '')),
            'agencia' => trim((string)($input['agencia'] ?? '')),
            'conta' => trim((string)($input['conta'] ?? '')),
            'pix' => trim((string)($input['pix'] ?? '')),
            'nome_socio' => trim((string)($input['nome_socio'] ?? '')),
            'cpf' => trim((string)($input['cpf'] ?? '')),
            'rg' => trim((string)($input['rg'] ?? '')),
            'orgao_expedidor' => trim((string)($input['orgao_expedidor'] ?? '')),
            'nascimento' => (string)($input['nascimento'] ?? ''),
            'nacionalidade' => trim((string)($input['nacionalidade'] ?? '')),
            'estado_civil' => trim((string)($input['estado_civil'] ?? '')),
            'profissao' => trim((string)($input['profissao'] ?? '')),
            'email_socio' => trim((string)($input['email_socio'] ?? '')),
            'cep_socio' => trim((string)($input['cep_socio'] ?? '')),
            'endereco_socio' => trim((string)($input['endereco_socio'] ?? '')),
            'numero_socio' => trim((string)($input['numero_socio'] ?? '')),
            'bairro_socio' => trim((string)($input['bairro_socio'] ?? '')),
            'cidade_socio' => trim((string)($input['cidade_socio'] ?? '')),
            'uf_socio' => trim((string)($input['uf_socio'] ?? '')),
            'doc_contrato_social' => trim((string)($input['doc_contrato_social'] ?? '')),
            'doc_end_empresa' => trim((string)($input['doc_end_empresa'] ?? '')),
            'doc_cartao_cnpj' => trim((string)($input['doc_cartao_cnpj'] ?? '')),
            'doc_core' => trim((string)($input['doc_core'] ?? '')),
            'doc_cpf_socio' => trim((string)($input['doc_cpf_socio'] ?? '')),
            'doc_identidade_socio' => trim((string)($input['doc_identidade_socio'] ?? '')),
            'doc_end_socio_comp' => trim((string)($input['doc_end_socio_comp'] ?? '')),
            'status' => (string)($input['status'] ?? ''),
            'data_cadastro' => (string)($input['data_cadastro'] ?? ''),
        ];
    }

    private function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Método não permitido.');
        }
    }

    private function redirectToIndex(): void
    {
        header('Location: index.php?section=contratos');
        exit;
    }

    private function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function requireValidCsrf(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        $sessionToken = (string)($_SESSION['csrf_token'] ?? '');

        if (!$token || !hash_equals($sessionToken, $token)) {
            http_response_code(403);
            exit('Requisição inválida.');
        }
    }
}
