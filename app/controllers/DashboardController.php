<?php
declare(strict_types=1);

class DashboardController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function index(): void
    {
        $data = $this->getStats();
        $csrfToken = $this->csrfToken();

        require VIEW_PATH . '/dashboard/index.php';
    }

    private function getStats(): array
    {
        // ---- Contratos ----
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM contratos");
        $totalContratos = (int)$stmt->fetch()['total'];

        $stmt = $this->db->query("SELECT status, COUNT(*) AS total FROM contratos GROUP BY status");
        $contratosPorStatus = [];
        while ($row = $stmt->fetch()) {
            $contratosPorStatus[strtolower($row['status'])] = (int)$row['total'];
        }

        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM contratos WHERE status = 'novo'");
        $contratosNovos = (int)$stmt->fetch()['total'];

        $stmt = $this->db->query(
            "SELECT id, razao_social, nome_socio, cnpj, status, data_cadastro
             FROM contratos ORDER BY id DESC LIMIT 5"
        );
        $contratosRecentes = $stmt->fetchAll();

        // ---- Fichas de Seleção ----
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM fichas_selecao");
        $totalFichas = (int)$stmt->fetch()['total'];

        $stmt = $this->db->query("SELECT status, COUNT(*) AS total FROM fichas_selecao GROUP BY status");
        $fichasPorStatus = [];
        while ($row = $stmt->fetch()) {
            $fichasPorStatus[strtolower($row['status'])] = (int)$row['total'];
        }

        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM fichas_selecao WHERE status = 'novo'");
        $fichasNovas = (int)$stmt->fetch()['total'];

        $stmt = $this->db->query(
            "SELECT id, nome_completo, empresa, cargo, celular, email, status, data_inscricao
             FROM fichas_selecao ORDER BY id DESC LIMIT 5"
        );
        $fichasRecentes = $stmt->fetchAll();

        // ---- Fichas por empresa ----
        $stmt = $this->db->query(
            "SELECT empresa, COUNT(*) AS total FROM fichas_selecao GROUP BY empresa ORDER BY total DESC"
        );
        $fichasPorEmpresa = [];
        while ($row = $stmt->fetch()) {
            $fichasPorEmpresa[$row['empresa']] = (int)$row['total'];
        }

        // ---- Funcionários ----
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM aniversariantes");
        $totalFuncionarios = (int)$stmt->fetch()['total'];

        $stmt = $this->db->query("SELECT tipo, COUNT(*) AS total FROM aniversariantes GROUP BY tipo");
        $funcPorTipo = [];
        while ($row = $stmt->fetch()) {
            $funcPorTipo[strtoupper($row['tipo'])] = (int)$row['total'];
        }

        // ---- Aniversariantes do dia ----
        $stmt = $this->db->query(
            "SELECT id, nome, setor, tipo, DAY(data_aniversario) as dia
             FROM aniversariantes
             WHERE MONTH(data_aniversario) = MONTH(CURDATE())
               AND DAY(data_aniversario) = DAY(CURDATE())
             ORDER BY nome ASC"
        );
        $aniversariantesHoje = $stmt->fetchAll();

        // ---- Aniversariantes do mês ----
        $stmt = $this->db->query(
            "SELECT id, nome, setor, tipo, DAY(data_aniversario) as dia
             FROM aniversariantes
             WHERE MONTH(data_aniversario) = MONTH(CURDATE())
             ORDER BY DAY(data_aniversario) ASC"
        );
        $aniversariantesMes = $stmt->fetchAll();

        return [
            'totalContratos'       => $totalContratos,
            'contratosNovos'       => $contratosNovos,
            'contratosPorStatus'   => $contratosPorStatus,
            'contratosRecentes'    => $contratosRecentes,
            'totalFichas'          => $totalFichas,
            'fichasNovas'          => $fichasNovas,
            'fichasPorStatus'      => $fichasPorStatus,
            'fichasRecentes'       => $fichasRecentes,
            'fichasPorEmpresa'     => $fichasPorEmpresa,
            'totalFuncionarios'    => $totalFuncionarios,
            'funcPorTipo'          => $funcPorTipo,
            'aniversariantesHoje'  => $aniversariantesHoje,
            'aniversariantesMes'   => $aniversariantesMes,
        ];
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
