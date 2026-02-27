<?php
declare(strict_types=1);

class VagasController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /* ════════════════════════════════════════
       INDEX — lista todas as vagas
    ════════════════════════════════════════ */
    public function index(): void
    {
        $stmt  = $this->db->query("SELECT * FROM vagas ORDER BY empresa ASC, cargo ASC");
        $vagas = $stmt->fetchAll();
        require __DIR__ . '/../views/vagas/index.php';
    }

    /* ════════════════════════════════════════
       STORE — cria nova vaga (POST AJAX)
    ════════════════════════════════════════ */
    public function store(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $empresa = trim((string)($_POST['empresa'] ?? ''));
        $cargo   = trim((string)($_POST['cargo']   ?? ''));

        if ($empresa === '' || $cargo === '') {
            echo json_encode(['success' => false, 'message' => 'Empresa e cargo são obrigatórios.']);
            exit;
        }

        // Evita duplicata ativa
        $check = $this->db->prepare("SELECT id FROM vagas WHERE empresa = ? AND cargo = ? AND ativo = 1 LIMIT 1");
        $check->execute([$empresa, $cargo]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Esta vaga já está cadastrada e ativa.']);
            exit;
        }

        $stmt = $this->db->prepare("INSERT INTO vagas (empresa, cargo, ativo, created_at) VALUES (?, ?, 1, NOW())");
        $stmt->execute([$empresa, $cargo]);

        echo json_encode(['success' => true, 'message' => 'Vaga cadastrada com sucesso!', 'id' => $this->db->lastInsertId()]);
        exit;
    }

    /* ════════════════════════════════════════
       TOGGLE STATUS — ativa / desativa (POST AJAX)
    ════════════════════════════════════════ */
    public function toggleStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id   = (int)($_POST['id']    ?? 0);
        $ativo = (int)($_POST['ativo'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $stmt = $this->db->prepare("UPDATE vagas SET ativo = ? WHERE id = ?");
        $stmt->execute([$ativo ? 1 : 0, $id]);

        echo json_encode(['success' => true]);
        exit;
    }

    /* ════════════════════════════════════════
       DELETE — exclui vaga (POST AJAX)
    ════════════════════════════════════════ */
    public function delete(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $stmt = $this->db->prepare("DELETE FROM vagas WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Vaga excluída.']);
        exit;
    }
}
