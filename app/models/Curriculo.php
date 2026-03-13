<?php
declare(strict_types=1);

class Curriculo
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /* ── Listar todos ─────────────────────────────── */
    public function getAll(): array
    {
        return $this->db
            ->query("SELECT * FROM curriculos ORDER BY id DESC")
            ->fetchAll();
    }

    /* ── Buscar por ID ────────────────────────────── */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM curriculos WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /* ── Criar ────────────────────────────────────── */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO curriculos (nome_completo, telefone, email, cidade, cargo_desejado, curriculo_path, status, created_at)
             VALUES (:nome, :telefone, :email, :cidade, :cargo, :path, 'novo', NOW())"
        );
        $stmt->execute([
            ':nome'     => $data['nome_completo'],
            ':telefone' => $data['telefone'],
            ':email'    => $data['email'],
            ':cidade'   => $data['cidade'],
            ':cargo'    => $data['cargo_desejado'],
            ':path'     => $data['curriculo_path'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /* ── Atualizar dados do currículo ─────────────── */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE curriculos
             SET nome_completo  = :nome,
                 telefone       = :telefone,
                 email          = :email,
                 cidade         = :cidade,
                 cargo_desejado = :cargo,
                 status         = :status
             WHERE id = :id"
        );
        return $stmt->execute([
            ':nome'     => $data['nome_completo'],
            ':telefone' => $data['telefone'],
            ':email'    => $data['email'],
            ':cidade'   => $data['cidade'],
            ':cargo'    => $data['cargo_desejado'],
            ':status'   => $data['status'],
            ':id'       => $id,
        ]);
    }

    /* ── Atualizar status ─────────────────────────── */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE curriculos SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /* ── Excluir ──────────────────────────────────── */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM curriculos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /* ── Buscar por status ────────────────────────── */
    public function getByStatus(string $status): array
    {
        $stmt = $this->db->prepare("SELECT * FROM curriculos WHERE status = ? ORDER BY id DESC");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    /* ── Buscar por cargo (para banco de talentos) ── */
    public function getByCargo(string $cargo): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM curriculos
             WHERE status = 'banco_talentos'
               AND LOWER(cargo_desejado) LIKE LOWER(CONCAT('%', ?, '%'))
             ORDER BY created_at DESC"
        );
        $stmt->execute([$cargo]);
        return $stmt->fetchAll();
    }
}
