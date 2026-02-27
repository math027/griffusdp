<?php
declare(strict_types=1);

class SelecaoController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /* ════════════════════════════════════════
       INDEX — lista todas as fichas
    ════════════════════════════════════════ */
    public function index(): void
    {
        $stmt = $this->db->query(
            "SELECT * FROM fichas_selecao ORDER BY id DESC"
        );
        $fichas    = $stmt->fetchAll();
        $csrfToken = $this->csrfToken();

        require __DIR__ . '/../views/selecao/index.php';
    }

    /* ════════════════════════════════════════
       VIEW — exibe ficha em página própria
    ════════════════════════════════════════ */
    public function view(int $id): void
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM fichas_selecao WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $ficha = $stmt->fetch();

        if (!$ficha) {
            http_response_code(404);
            exit('Ficha de seleção não encontrada.');
        }

        $csrfToken = $this->csrfToken();
        require __DIR__ . '/../views/selecao/view.php';
    }

    /* ════════════════════════════════════════
       CHANGE STATUS — altera status via AJAX
    ════════════════════════════════════════ */
    public function changeStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();

        $id     = (int)($_POST['id']     ?? 0);
        $status = trim((string)($_POST['status'] ?? ''));

        $validos = [
            'novo', 'em_analise', 'entrevista_agendada',
            'entrevistado', 'aprovado', 'contratado',
            'reprovado', 'arquivado', 'sem_contato', 'faltou'
        ];

        if (!$id || !in_array($status, $validos, true)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
            return;
        }

        $stmt = $this->db->prepare(
            "UPDATE fichas_selecao SET status = ? WHERE id = ?"
        );
        $stmt->execute([$status, $id]);

        echo json_encode(['success' => true]);
    }

    /* ════════════════════════════════════════
       SAVE INTERVIEW — salva dados da entrevista via AJAX
    ════════════════════════════════════════ */
    public function saveInterview(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        // Data/hora — aceita formato datetime-local (YYYY-MM-DDTHH:MM)
        $dataRaw = trim((string)($_POST['data_entrevista'] ?? ''));
        $dataEntrevista = '';
        if ($dataRaw !== '') {
            // Normaliza tanto "2025-06-10T14:30" quanto "2025-06-10 14:30"
            $dataEntrevista = str_replace('T', ' ', $dataRaw);
            if (!strtotime($dataEntrevista)) {
                echo json_encode(['success' => false, 'message' => 'Data de entrevista inválida.']);
                return;
            }
        }

        $localEntrevista     = trim(strip_tags((string)($_POST['local_entrevista']     ?? '')));
        $obsEntrevista       = trim(strip_tags((string)($_POST['obs_entrevista']       ?? '')));
        $resultadoEntrevista = trim((string)($_POST['resultado_entrevista'] ?? ''));

        $resultadosValidos = ['', 'pendente', 'aprovado_entrevista', 'reprovado_entrevista', 'faltou'];
        if (!in_array($resultadoEntrevista, $resultadosValidos, true)) {
            echo json_encode(['success' => false, 'message' => 'Resultado inválido.']);
            return;
        }

        // Se agendou entrevista e ainda estava em status inicial, avança automaticamente
        $stmt = $this->db->prepare(
            "SELECT status FROM fichas_selecao WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Ficha não encontrada.']);
            return;
        }

        $novoStatus = $row['status'];
        if ($dataEntrevista !== '' && in_array($row['status'], ['novo', 'em_analise'], true)) {
            $novoStatus = 'entrevista_agendada';
        }

        $stmt = $this->db->prepare(
            "UPDATE fichas_selecao
             SET data_entrevista      = :data,
                 local_entrevista     = :local,
                 obs_entrevista       = :obs,
                 resultado_entrevista = :resultado,
                 status               = :status
             WHERE id = :id"
        );
        $stmt->execute([
            ':data'      => $dataEntrevista !== '' ? $dataEntrevista : null,
            ':local'     => $localEntrevista     !== '' ? $localEntrevista     : null,
            ':obs'       => $obsEntrevista       !== '' ? $obsEntrevista       : null,
            ':resultado' => $resultadoEntrevista !== '' ? $resultadoEntrevista : null,
            ':status'    => $novoStatus,
            ':id'        => $id,
        ]);

        echo json_encode(['success' => true]);
    }

    /* ════════════════════════════════════════
       DELETE — exclui ficha
    ════════════════════════════════════════ */
    public function delete(): void
    {
        $this->verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM fichas_selecao WHERE id = ?");
            $stmt->execute([$id]);
        }

        header('Location: index.php?section=selecao');
        exit;
    }

    /* ════════════════════════════════════════
       HELPERS
    ════════════════════════════════════════ */
    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function verifyCsrf(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }
    }
}
