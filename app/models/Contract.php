<?php
declare(strict_types=1);

class Contract
{
    private PDO $db;

    private const FIELDS = [
        'id',
        'tipo_contrato',
        'razao_social',
        'cnpj',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'uf',
        'telefone',
        'celular',
        'email_empresa',
        'banco',
        'agencia',
        'conta',
        'pix',
        'nome_socio',
        'cpf',
        'rg',
        'orgao_expedidor',
        'nascimento',
        'nacionalidade',
        'estado_civil',
        'profissao',
        'email_socio',
        'cep_socio',
        'endereco_socio',
        'numero_socio',
        'bairro_socio',
        'cidade_socio',
        'uf_socio',
        'doc_contrato_social',
        'doc_end_empresa',
        'doc_cartao_cnpj',
        'doc_core',
        'doc_cpf_socio',
        'doc_identidade_socio',
        'doc_end_socio_comp',
        'status',
        'data_cadastro',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Retorna todos os contratos cadastrados.
     */
    public function getAll(): array
    {
        $fields = implode(', ', self::FIELDS);
        $stmt = $this->db->query("SELECT {$fields} FROM contratos ORDER BY data_cadastro DESC, id DESC");
        $rows = $stmt->fetchAll();

        return array_map([$this, 'normalize'], $rows);
    }

    /**
     * Busca um contrato por ID.
     */
    public function find(int $id): ?array
    {
        $fields = implode(', ', self::FIELDS);
        $stmt = $this->db->prepare("SELECT {$fields} FROM contratos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->normalize($row) : null;
    }

    /**
     * Atualiza os dados básicos do contrato.
     */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE contratos
                SET tipo_contrato = :tipo_contrato,
                    razao_social = :razao_social,
                    cnpj = :cnpj,
                    cep = :cep,
                    endereco = :endereco,
                    numero = :numero,
                    bairro = :bairro,
                    cidade = :cidade,
                    uf = :uf,
                    telefone = :telefone,
                    celular = :celular,
                    email_empresa = :email_empresa,
                    banco = :banco,
                    agencia = :agencia,
                    conta = :conta,
                    pix = :pix,
                    nome_socio = :nome_socio,
                    cpf = :cpf,
                    rg = :rg,
                    orgao_expedidor = :orgao_expedidor,
                    nascimento = :nascimento,
                    nacionalidade = :nacionalidade,
                    estado_civil = :estado_civil,
                    profissao = :profissao,
                    email_socio = :email_socio,
                    cep_socio = :cep_socio,
                    endereco_socio = :endereco_socio,
                    numero_socio = :numero_socio,
                    bairro_socio = :bairro_socio,
                    cidade_socio = :cidade_socio,
                    uf_socio = :uf_socio,
                    doc_contrato_social = :doc_contrato_social,
                    doc_end_empresa = :doc_end_empresa,
                    doc_cartao_cnpj = :doc_cartao_cnpj,
                    doc_core = :doc_core,
                    doc_cpf_socio = :doc_cpf_socio,
                    doc_identidade_socio = :doc_identidade_socio,
                    doc_end_socio_comp = :doc_end_socio_comp,
                    status = :status,
                    data_cadastro = :data_cadastro
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':tipo_contrato' => $data['tipo_contrato'] ?? '',
            ':razao_social' => $data['razao_social'],
            ':cnpj' => $data['cnpj'],
            ':cep' => $data['cep'],
            ':endereco' => $data['endereco'],
            ':numero' => $data['numero'],
            ':bairro' => $data['bairro'],
            ':cidade' => $data['cidade'],
            ':uf' => $data['uf'],
            ':telefone' => $data['telefone'],
            ':celular' => $data['celular'],
            ':email_empresa' => $data['email_empresa'],
            ':banco' => $data['banco'],
            ':agencia' => $data['agencia'],
            ':conta' => $data['conta'],
            ':pix' => $data['pix'],
            ':nome_socio' => $data['nome_socio'],
            ':cpf' => $data['cpf'],
            ':rg' => $data['rg'],
            ':orgao_expedidor' => $data['orgao_expedidor'],
            ':nascimento' => $data['nascimento'],
            ':nacionalidade' => $data['nacionalidade'],
            ':estado_civil' => $data['estado_civil'],
            ':profissao' => $data['profissao'],
            ':email_socio' => $data['email_socio'],
            ':cep_socio' => $data['cep_socio'],
            ':endereco_socio' => $data['endereco_socio'],
            ':numero_socio' => $data['numero_socio'],
            ':bairro_socio' => $data['bairro_socio'],
            ':cidade_socio' => $data['cidade_socio'],
            ':uf_socio' => $data['uf_socio'],
            ':doc_contrato_social' => $data['doc_contrato_social'],
            ':doc_end_empresa' => $data['doc_end_empresa'],
            ':doc_cartao_cnpj' => $data['doc_cartao_cnpj'],
            ':doc_core' => $data['doc_core'],
            ':doc_cpf_socio' => $data['doc_cpf_socio'],
            ':doc_identidade_socio' => $data['doc_identidade_socio'],
            ':doc_end_socio_comp' => $data['doc_end_socio_comp'],
            ':status' => $data['status'],
            ':data_cadastro' => $data['data_cadastro'],
            ':id' => $id,
        ]);
    }

    /**
     * Atualiza somente o status.
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE contratos SET status = :status WHERE id = :id');

        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    /**
     * Remove um contrato.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contratos WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Normaliza campos para o controller/view.
     */
    private function normalize(array $row): array
    {
        $documentos = [
            'Contrato Social' => $row['doc_contrato_social'] ?? '',
            'Comprovante End. Empresa' => $row['doc_end_empresa'] ?? '',
            'Cartão CNPJ' => $row['doc_cartao_cnpj'] ?? '',
            'CORE' => $row['doc_core'] ?? '',
            'CPF Sócio' => $row['doc_cpf_socio'] ?? '',
            'Identidade Sócio' => $row['doc_identidade_socio'] ?? '',
            'Comprovante End. Sócio' => $row['doc_end_socio_comp'] ?? '',
        ];

        $row['documentos'] = array_filter($documentos, static fn ($v) => (string)$v !== '');

        return $row;
    }
}
