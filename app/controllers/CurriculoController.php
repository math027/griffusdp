<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Curriculo.php';

class CurriculoController
{
    private PDO $db;
    private Curriculo $model;

    public function __construct(PDO $db)
    {
        $this->db    = $db;
        $this->model = new Curriculo($db);
    }

    /* ═══════════════════════════════════════════
       INDEX — lista todos os currículos
    ═══════════════════════════════════════════ */
    public function index(): void
    {
        $curriculos = $this->model->getAll();
        $csrfToken  = $this->csrfToken();
        require __DIR__ . '/../views/curriculos/index.php';
    }

    /* ═══════════════════════════════════════════
       VIEW — visualizar CV (serve o arquivo)
    ═══════════════════════════════════════════ */
    public function viewCv(int $id): void
    {
        $cv = $this->model->find($id);
        if (!$cv || empty($cv['curriculo_path'])) {
            http_response_code(404);
            exit('Currículo não encontrado.');
        }

        $filePath = __DIR__ . '/../storage/uploads/curriculos/' . $cv['curriculo_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('Arquivo do currículo não encontrado no servidor.');
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $mime = $mimeMap[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /* ═══════════════════════════════════════════
       GET — retorna dados de um currículo em JSON
    ═══════════════════════════════════════════ */
    public function get(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $cv = $this->model->find($id);
        if (!$cv) {
            echo json_encode(['success' => false, 'message' => 'Currículo não encontrado.']);
            return;
        }

        echo json_encode(['success' => true, 'data' => $cv]);
    }

    /* ═══════════════════════════════════════════
       UPDATE — atualiza dados do currículo via AJAX
    ═══════════════════════════════════════════ */
    public function update(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $validos = ['novo', 'em_analise', 'ficha_enviada', 'aprovado', 'rejeitado', 'banco_talentos'];
        $status  = trim((string) ($_POST['status'] ?? ''));

        if (!in_array($status, $validos, true)) {
            echo json_encode(['success' => false, 'message' => 'Status inválido.']);
            return;
        }

        $data = [
            'nome_completo'  => trim((string) ($_POST['nome_completo']  ?? '')),
            'telefone'       => trim((string) ($_POST['telefone']        ?? '')),
            'email'          => trim((string) ($_POST['email']           ?? '')),
            'cidade'         => trim((string) ($_POST['cidade']          ?? '')),
            'cargo_desejado' => trim((string) ($_POST['cargo_desejado']  ?? '')),
            'status'         => $status,
        ];

        foreach (['nome_completo', 'telefone', 'email', 'cidade', 'cargo_desejado'] as $campo) {
            if ($data[$campo] === '') {
                echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
                return;
            }
        }

        $this->model->update($id, $data);
        echo json_encode(['success' => true, 'message' => 'Currículo atualizado com sucesso.']);
    }

    /* ═══════════════════════════════════════════
       CHANGE STATUS — via AJAX
    ═══════════════════════════════════════════ */
    public function changeStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();

        $id     = (int) ($_POST['id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? ''));

        $validos = ['novo', 'em_analise', 'ficha_enviada', 'aprovado', 'rejeitado', 'banco_talentos'];

        if (!$id || !in_array($status, $validos, true)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
            return;
        }

        $this->model->updateStatus($id, $status);
        echo json_encode(['success' => true]);
    }

    /* ═══════════════════════════════════════════
       DELETE — exclui currículo via AJAX
    ═══════════════════════════════════════════ */
    public function delete(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        // Remove o arquivo do disco
        $cv = $this->model->find($id);
        if ($cv && !empty($cv['curriculo_path'])) {
            $filePath = __DIR__ . '/../storage/uploads/curriculos/' . $cv['curriculo_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $this->model->delete($id);
        echo json_encode(['success' => true, 'message' => 'Currículo excluído.']);
    }

    /* ═══════════════════════════════════════════
       GENERATE TOKEN — gera link para ficha de seleção
    ═══════════════════════════════════════════ */
    public function generateToken(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $cv = $this->model->find($id);
        if (!$cv) {
            echo json_encode(['success' => false, 'message' => 'Currículo não encontrado.']);
            return;
        }

        // Gera token único
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $stmt = $this->db->prepare(
            "INSERT INTO selecao_tokens (curriculo_id, token, usado, expires_at, created_at)
             VALUES (?, ?, 0, ?, NOW())"
        );
        $stmt->execute([$id, $token, $expiresAt]);

        // Atualiza status do currículo
        $this->model->updateStatus($id, 'ficha_enviada');

        // Monta URL base do link
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl  = $protocol . '://' . $host;

        // Detecta caminho relativo para selecao
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $selecaoUrl = rtrim($baseUrl, '/') . rtrim($scriptDir, '/admin') . '/selecao/ficha.php?token=' . $token;

        // Mensagem WhatsApp
        $msgWhatsapp = "Olá {$cv['nome_completo']}! 👋\n\n"
            . "Recebemos seu currículo para a vaga de *{$cv['cargo_desejado']}*.\n\n"
            . "Para dar continuidade ao processo seletivo, pedimos que preencha a ficha de seleção no link abaixo:\n\n"
            . "{$selecaoUrl}\n\n"
            . "⚠️ Este link é de uso único e expira em 7 dias.\n\n"
            . "Qualquer dúvida, estamos à disposição!\n"
            . "Equipe RH — Griffus SA";

        // Formata telefone para WhatsApp (apenas dígitos, com 55)
        $telefone = preg_replace('/\D/', '', $cv['telefone']);
        if (strlen($telefone) <= 11) {
            $telefone = '55' . $telefone;
        }

        echo json_encode([
            'success'      => true,
            'token'        => $token,
            'link'         => $selecaoUrl,
            'expires_at'   => $expiresAt,
            'whatsapp_url' => 'https://wa.me/' . $telefone . '?text=' . rawurlencode($msgWhatsapp),
            'mensagem'     => $msgWhatsapp,
            'candidato'    => $cv,
        ]);
    }

    /* ═══════════════════════════════════════════
       TALENT BANK — currículos no banco de talentos
    ═══════════════════════════════════════════ */
    public function talentBank(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $cargo = trim((string) ($_GET['cargo'] ?? ''));
        if ($cargo === '') {
            $talentos = $this->model->getByStatus('banco_talentos');
        } else {
            $talentos = $this->model->getByCargo($cargo);
        }

        echo json_encode(['success' => true, 'data' => $talentos]);
    }

    /* ═══════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════ */
    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function verifyCsrf(): void
    {
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }
    }
}