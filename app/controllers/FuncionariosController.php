<?php
declare(strict_types=1);

class FuncionariosController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS aniversariantes (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                nome        VARCHAR(150) NOT NULL,
                setor       VARCHAR(100) NOT NULL,
                tipo        ENUM('CLT','PJ') NOT NULL DEFAULT 'CLT',
                data_aniversario DATE NOT NULL,
                criado_em   DATETIME DEFAULT CURRENT_TIMESTAMP,
                atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    }

    /** Página principal — lista todos os funcionários */
    public function index(): void
    {
        $stmt = $this->db->query(
            "SELECT id, nome, setor, tipo, data_aniversario,
                    DAY(data_aniversario) as dia,
                    MONTH(data_aniversario) as mes
             FROM aniversariantes
             ORDER BY nome ASC"
        );
        $funcionarios = $stmt->fetchAll();

        // Setores únicos para dropdown de filtro
        $stmtSetores = $this->db->query(
            "SELECT DISTINCT setor FROM aniversariantes ORDER BY setor ASC"
        );
        $setores = $stmtSetores->fetchAll(PDO::FETCH_COLUMN);

        $csrfToken = $_SESSION['csrf_token'] ?? '';

        require_once dirname(__DIR__) . '/views/funcionarios/index.php';
    }

    /** API JSON — save / delete */
    public function api(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $action = (string)($_GET['api_action'] ?? '');

        try {
            switch ($action) {
                case 'save':
                    echo json_encode($this->save());
                    break;
                case 'delete':
                    echo json_encode($this->deleteRecord((int)($_POST['id'] ?? 0)));
                    break;
                default:
                    echo json_encode(['error' => 'Ação inválida']);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    private function save(): array
    {
        $id    = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $nome  = trim($_POST['nome'] ?? '');
        $setor = trim($_POST['setor'] ?? '');
        $tipo  = in_array($_POST['tipo'] ?? '', ['CLT', 'PJ']) ? $_POST['tipo'] : 'CLT';
        $data  = trim($_POST['data_aniversario'] ?? '');

        if (!$nome || !$setor || !$data) {
            return ['error' => 'Preencha todos os campos'];
        }

        $d = \DateTime::createFromFormat('Y-m-d', $data);
        if (!$d) return ['error' => 'Data inválida'];

        if ($id) {
            $stmt = $this->db->prepare(
                "UPDATE aniversariantes SET nome=:nome, setor=:setor, tipo=:tipo, data_aniversario=:data WHERE id=:id"
            );
            $stmt->execute([':nome' => $nome, ':setor' => $setor, ':tipo' => $tipo, ':data' => $data, ':id' => $id]);
            return ['success' => true, 'action' => 'updated', 'id' => $id];
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO aniversariantes (nome, setor, tipo, data_aniversario) VALUES (:nome, :setor, :tipo, :data)"
            );
            $stmt->execute([':nome' => $nome, ':setor' => $setor, ':tipo' => $tipo, ':data' => $data]);
            return ['success' => true, 'action' => 'inserted', 'id' => (int)$this->db->lastInsertId()];
        }
    }

    private function deleteRecord(int $id): array
    {
        if (!$id) return ['error' => 'ID inválido'];
        $stmt = $this->db->prepare("DELETE FROM aniversariantes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return ['success' => true];
    }
}
