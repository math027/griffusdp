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
                telefone    VARCHAR(20) DEFAULT NULL,
                foto_path   VARCHAR(255) DEFAULT NULL,
                msg_enviada_ano INT DEFAULT 0,
                criado_em   DATETIME DEFAULT CURRENT_TIMESTAMP,
                atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        // Adicionar colunas se não existirem (para tabela já criada)
        $cols = $this->db->query("SHOW COLUMNS FROM aniversariantes")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('telefone', $cols)) {
            $this->db->exec("ALTER TABLE aniversariantes ADD COLUMN telefone VARCHAR(20) DEFAULT NULL AFTER data_aniversario");
        }
        if (!in_array('foto_path', $cols)) {
            $this->db->exec("ALTER TABLE aniversariantes ADD COLUMN foto_path VARCHAR(255) DEFAULT NULL AFTER telefone");
        }
        if (!in_array('msg_enviada_ano', $cols)) {
            $this->db->exec("ALTER TABLE aniversariantes ADD COLUMN msg_enviada_ano INT DEFAULT 0 AFTER foto_path");
        }
    }

    /** Página principal — lista todos os funcionários */
    public function index(): void
    {
        $stmt = $this->db->query(
            "SELECT id, nome, setor, tipo, data_aniversario, telefone, foto_path,
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
                case 'delete_foto':
                    echo json_encode($this->deleteFoto((int)($_POST['id'] ?? 0)));
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
        $telefone = preg_replace('/\D/', '', trim($_POST['telefone'] ?? ''));

        if (!$nome || !$setor || !$data) {
            return ['error' => 'Preencha todos os campos obrigatórios'];
        }

        $d = \DateTime::createFromFormat('Y-m-d', $data);
        if (!$d) return ['error' => 'Data inválida'];

        // Upload de foto
        $fotoPath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fotoPath = $this->handleFotoUpload($_FILES['foto'], $id);
            if ($fotoPath === false) {
                return ['error' => 'Erro ao fazer upload da foto. Formatos: JPG, PNG, WEBP. Max 5MB.'];
            }
        }

        if ($id) {
            $sql = "UPDATE aniversariantes SET nome=:nome, setor=:setor, tipo=:tipo, data_aniversario=:data, telefone=:telefone";
            $params = [':nome' => $nome, ':setor' => $setor, ':tipo' => $tipo, ':data' => $data, ':telefone' => $telefone ?: null, ':id' => $id];

            if ($fotoPath !== null) {
                // Deletar foto antiga
                $this->removeOldFoto($id);
                $sql .= ", foto_path=:foto";
                $params[':foto'] = $fotoPath;
            }
            $sql .= " WHERE id=:id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return ['success' => true, 'action' => 'updated', 'id' => $id];
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO aniversariantes (nome, setor, tipo, data_aniversario, telefone, foto_path)
                 VALUES (:nome, :setor, :tipo, :data, :telefone, :foto)"
            );
            $stmt->execute([
                ':nome' => $nome, ':setor' => $setor, ':tipo' => $tipo,
                ':data' => $data, ':telefone' => $telefone ?: null,
                ':foto' => $fotoPath
            ]);
            return ['success' => true, 'action' => 'inserted', 'id' => (int)$this->db->lastInsertId()];
        }
    }

    private function handleFotoUpload(array $file, ?int $id): string|false
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed) || $file['size'] > $maxSize) {
            return false;
        }

        $uploadDir = dirname(__DIR__) . '/storage/funcionarios/foto';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = match ($file['type']) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg'
        };

        $filename = 'func_' . time() . '_' . uniqid() . '.' . $ext;
        $destino = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            return false;
        }

        return 'funcionarios/foto/' . $filename;
    }

    private function removeOldFoto(int $id): void
    {
        $stmt = $this->db->prepare("SELECT foto_path FROM aniversariantes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $old = $stmt->fetchColumn();
        if ($old) {
            $fullPath = dirname(__DIR__) . '/storage/uploads/' . $old;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    private function deleteFoto(int $id): array
    {
        if (!$id) return ['error' => 'ID inválido'];
        $this->removeOldFoto($id);
        $stmt = $this->db->prepare("UPDATE aniversariantes SET foto_path = NULL WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return ['success' => true];
    }

    private function deleteRecord(int $id): array
    {
        if (!$id) return ['error' => 'ID inválido'];
        $this->removeOldFoto($id);
        $stmt = $this->db->prepare("DELETE FROM aniversariantes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return ['success' => true];
    }

    /**
     * Endpoint para servir a foto do funcionário.
     */
    public function foto(int $id): void
    {
        $stmt = $this->db->prepare("SELECT foto_path FROM aniversariantes WHERE id = :id AND (foto_path IS NOT NULL AND foto_path != '')");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            exit;
        }

        // Tenta achar com o caminho novo (que já inclui a pasta base, ou não)
        // Antes era salva em "funcionarios/foto/..." ou "funcionarios/..."
        // Então prefixamos com STORAGE_PATH.
        $path = dirname(__DIR__) . '/storage/uploads/' . $row['foto_path']; // caminho antigo
        if (!file_exists($path)) {
            $path = dirname(__DIR__) . '/storage/' . $row['foto_path']; // caminho novo
        }

        if (!file_exists($path)) {
            http_response_code(404);
            exit;
        }

        $info = getimagesize($path);
        if ($info) {
            header('Content-Type: ' . $info['mime']);
            header('Content-Length: ' . filesize($path));
            header('Cache-Control: max-age=86400, public');
            readfile($path);
        } else {
            http_response_code(404);
        }
        exit;
    }
}
