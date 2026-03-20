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
                empresa     VARCHAR(150) DEFAULT NULL,
                nome        VARCHAR(150) NOT NULL,
                cpf         VARCHAR(20) DEFAULT NULL,
                funcao      VARCHAR(150) DEFAULT NULL,
                setor       VARCHAR(100) NOT NULL,
                tipo        ENUM('CLT','PJ') NOT NULL DEFAULT 'CLT',
                data_aniversario DATE NOT NULL,
                data_admissao DATE DEFAULT NULL,
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
            $this->db->exec("ALTER TABLE aniversariantes ADD COLUMN telefone VARCHAR(20) DEFAULT NULL AFTER data_admissao");
        }
        if (!in_array('foto_path', $cols)) {
            $this->db->exec("ALTER TABLE aniversariantes ADD COLUMN foto_path VARCHAR(255) DEFAULT NULL AFTER telefone");
        }
        if (!in_array('msg_enviada_ano', $cols)) {
            $this->db->exec("ALTER TABLE aniversariantes ADD COLUMN msg_enviada_ano INT DEFAULT 0 AFTER foto_path");
        }
        if (!in_array('empresa', $cols)) {
            $this->db->exec("ALTER TABLE aniversariantes ADD COLUMN empresa VARCHAR(150) DEFAULT NULL AFTER id");
        }
        if (!in_array('cpf', $cols)) {
            $this->db->exec("ALTER TABLE aniversariantes ADD COLUMN cpf VARCHAR(20) DEFAULT NULL AFTER nome");
        }
        if (!in_array('funcao', $cols)) {
            $this->db->exec("ALTER TABLE aniversariantes ADD COLUMN funcao VARCHAR(150) DEFAULT NULL AFTER cpf");
        }
        if (!in_array('data_admissao', $cols)) {
            $this->db->exec("ALTER TABLE aniversariantes ADD COLUMN data_admissao DATE DEFAULT NULL AFTER data_aniversario");
        }
    }

    /** Página principal — lista todos os funcionários */
    public function index(): void
    {
        $stmt = $this->db->query(
            "SELECT id, empresa, nome, cpf, funcao, setor, tipo,
                    data_aniversario, data_admissao, telefone, foto_path,
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
        $id       = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $empresa  = trim($_POST['empresa'] ?? '');
        $nome     = trim($_POST['nome'] ?? '');
        $cpf      = preg_replace('/\D/', '', trim($_POST['cpf'] ?? ''));
        $funcao   = trim($_POST['funcao'] ?? '');
        $setor    = trim($_POST['setor'] ?? '');
        $tipo     = in_array($_POST['tipo'] ?? '', ['CLT', 'PJ']) ? $_POST['tipo'] : 'CLT';
        $data     = trim($_POST['data_aniversario'] ?? '');
        $dataAdm  = trim($_POST['data_admissao'] ?? '');
        $telefone = preg_replace('/\D/', '', trim($_POST['telefone'] ?? ''));

        if (!$nome || !$setor || !$data) {
            return ['error' => 'Preencha todos os campos obrigatórios'];
        }

        $d = \DateTime::createFromFormat('Y-m-d', $data);
        if (!$d) return ['error' => 'Data de nascimento inválida'];

        $dataAdmFmt = null;
        if ($dataAdm) {
            $dAdm = \DateTime::createFromFormat('Y-m-d', $dataAdm);
            if ($dAdm) $dataAdmFmt = $dataAdm;
        }

        // Upload de foto
        $fotoPath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fotoPath = $this->handleFotoUpload($_FILES['foto'], $id);
            if ($fotoPath === false) {
                return ['error' => 'Erro ao fazer upload da foto. Formatos: JPG, PNG, WEBP. Max 5MB.'];
            }
        }

        if ($id) {
            $sql = "UPDATE aniversariantes SET empresa=:empresa, nome=:nome, cpf=:cpf, funcao=:funcao, setor=:setor, tipo=:tipo, data_aniversario=:data, data_admissao=:data_adm, telefone=:telefone";
            $params = [
                ':empresa' => $empresa ?: null, ':nome' => $nome, ':cpf' => $cpf ?: null,
                ':funcao' => $funcao ?: null, ':setor' => $setor, ':tipo' => $tipo,
                ':data' => $data, ':data_adm' => $dataAdmFmt,
                ':telefone' => $telefone ?: null, ':id' => $id
            ];

            if ($fotoPath !== null) {
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
                "INSERT INTO aniversariantes (empresa, nome, cpf, funcao, setor, tipo, data_aniversario, data_admissao, telefone, foto_path)
                 VALUES (:empresa, :nome, :cpf, :funcao, :setor, :tipo, :data, :data_adm, :telefone, :foto)"
            );
            $stmt->execute([
                ':empresa' => $empresa ?: null, ':nome' => $nome, ':cpf' => $cpf ?: null,
                ':funcao' => $funcao ?: null, ':setor' => $setor, ':tipo' => $tipo,
                ':data' => $data, ':data_adm' => $dataAdmFmt,
                ':telefone' => $telefone ?: null, ':foto' => $fotoPath
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

    /**
     * Endpoint para servir templates de aniversário
     */
    public function template(string $name): void
    {
        $allowed = ['normal', 'dayoff'];
        if (!in_array($name, $allowed)) {
            http_response_code(404);
            exit;
        }

        $path = dirname(__DIR__) . '/storage/aniversario/' . $name . '.png';
        if (!file_exists($path)) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: max-age=86400, public');
        readfile($path);
        exit;
    }

    /**
     * Importar funcionários via arquivo Excel (.xlsx) ou CSV
     * 
     * === VERSÃO CORRIGIDA ===
     * Corrige o erro "Unexpected token '<'; '<br /> <b>' is not valid JSON"
     * Implementa controles rigorosos de output buffering e tratamento de erros
     */
    public function importExcel(): void
    {
        // === INÍCIO DA CORREÇÃO ===
        // 1. Desabilitar display de erros para evitar HTML no output
        $oldDisplayErrors = ini_get('display_errors');
        ini_set('display_errors', '0');
        
        // 2. Limpar TODOS os buffers de output que possam existir
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // 3. Iniciar novo buffer limpo
        ob_start();
        
        // 4. Registrar função de shutdown para capturar erros fatais
        register_shutdown_function(function() use ($oldDisplayErrors) {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                // Limpar qualquer output anterior
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'error' => 'Erro fatal: ' . $error['message'],
                    'file' => basename($error['file']),
                    'line' => $error['line']
                ], JSON_UNESCAPED_UNICODE);
            }
            
            // Restaurar configuração original
            ini_set('display_errors', $oldDisplayErrors);
        });
        
        try {
            // 5. Validar upload do arquivo
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                throw new \RuntimeException('Nenhum arquivo enviado ou erro no upload.');
            }

            $file     = $_FILES['arquivo'];
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $tmpPath  = $file['tmp_name'];

            // 6. Validar extensão
            if (!in_array($ext, ['csv', 'xlsx'])) {
                throw new \RuntimeException('Formato inválido. Use .xlsx ou .csv');
            }

            // 7. Parse do arquivo
            if ($ext === 'csv') {
                $rows = $this->parseCsv($tmpPath);
            } else {
                $rows = $this->parseXlsx($tmpPath);
            }

            // Validar que há dados
            if (empty($rows)) {
                throw new \RuntimeException('Nenhum dado encontrado no arquivo');
            }

            // 8. Importar dados
            $imported = 0;
            $skipped  = [];

            foreach ($rows as $i => $row) {
                $linha = $i + 2; // linha real no arquivo (header = 1)

                try {
                    // Mapeamento flexível: aceita nomes do Excel ou nomes internos
                    $empresa = trim($row['empresa'] ?? $row[0] ?? '');
                    $nome    = trim($row['funcionario'] ?? $row['nome'] ?? $row[1] ?? '');
                    $cpf     = preg_replace('/\D/', '', trim($row['cpf'] ?? $row[2] ?? ''));
                    $tel     = preg_replace('/\D/', '', trim($row['whatsapp'] ?? $row['telefone'] ?? $row[3] ?? ''));
                    $funcao  = trim($row['funcao'] ?? $row[4] ?? '');
                    $setor   = trim($row['departamento'] ?? $row['setor'] ?? $row[5] ?? '');
                    $dataNasc = trim($row['nascimento'] ?? $row['data_aniversario'] ?? $row['data'] ?? $row[6] ?? '');
                    $dataAdm  = trim($row['admissao'] ?? $row['data_admissao'] ?? $row[7] ?? '');

                    // Validar nome obrigatório
                    if (empty($nome)) {
                        $skipped[] = "Linha {$linha}: nome em branco";
                        continue;
                    }

                    // Processar data de nascimento
                    $dataNascFmt = $this->parseDate($dataNasc);
                    if ($dataNascFmt === null && !empty($dataNasc)) {
                        $skipped[] = "Linha {$linha}: data nascimento inválida \"{$dataNasc}\" (use DD/MM/AAAA ou AAAA-MM-DD)";
                        continue;
                    }
                    
                    // Se não há data, usar placeholder
                    if ($dataNascFmt === null) {
                        $dataNascFmt = '1900-01-01';
                    }

                    // Processar data de admissão
                    $dataAdmFmt = $this->parseDate($dataAdm);

                    // Inserir no banco
                    $stmt = $this->db->prepare(
                        "INSERT INTO aniversariantes (empresa, nome, cpf, funcao, setor, tipo, data_aniversario, data_admissao, telefone)
                         VALUES (:empresa, :nome, :cpf, :funcao, :setor, :tipo, :data, :data_adm, :tel)"
                    );
                    
                    $stmt->execute([
                        ':empresa' => !empty($empresa) ? strtoupper($empresa) : null,
                        ':nome'    => strtoupper($nome),
                        ':cpf'     => !empty($cpf) ? $cpf : null,
                        ':funcao'  => !empty($funcao) ? strtoupper($funcao) : null,
                        ':setor'   => !empty($setor) ? strtoupper($setor) : 'N/D',
                        ':tipo'    => 'CLT',
                        ':data'    => $dataNascFmt,
                        ':data_adm'=> $dataAdmFmt,
                        ':tel'     => !empty($tel) ? $tel : null,
                    ]);
                    
                    $imported++;
                    
                } catch (\PDOException $e) {
                    // Se houver erro de duplicação ou outro erro de BD
                    $skipped[] = "Linha {$linha}: erro ao inserir - " . $e->getMessage();
                    continue;
                }
            }

            // 9. Preparar resposta de sucesso
            $response = [
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'total_rows' => count($rows),
                'message' => sprintf(
                    'Importação concluída: %d registros importados%s',
                    $imported,
                    count($skipped) > 0 ? ', ' . count($skipped) . ' ignorados' : ''
                )
            ];

            // 10. Limpar buffer e enviar resposta JSON
            ob_end_clean();
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(200);
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
        } catch (\Throwable $e) {
            // Limpar qualquer output anterior
            ob_end_clean();
            
            // Enviar resposta de erro
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'error' => $e->getMessage(),
                'file'  => basename($e->getFile()),
                'line'  => $e->getLine()
            ], JSON_UNESCAPED_UNICODE);
        }
        
        // Restaurar configuração original
        ini_set('display_errors', $oldDisplayErrors);
        
        exit;
    }

    /**
     * Helper para parse de datas em múltiplos formatos
     * 
     * @param string|null $date Data a ser parseada
     * @return string|null Data no formato YYYY-MM-DD ou null se inválida
     */
    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Tentar DD/MM/YYYY
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        
        // Tentar YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        
        // Tentar DD-MM-YYYY
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        return null;
    }

    /** Lê CSV (com ou sem BOM, separador vírgula ou ponto-e-vírgula) */
    private function parseCsv(string $path): array
    {
        $handle = @fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('Não foi possível abrir o arquivo CSV.');
        }

        try {
            // Remove BOM UTF-8 se existir
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            // Detecta separador lendo primeira linha
            $firstLine = fgets($handle);
            rewind($handle);
            
            // Detectar BOM novamente após rewind
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            
            $sep = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';

            $rows    = [];
            $headers = null;

            while (($cols = fgetcsv($handle, 0, $sep)) !== false) {
                // Pular linhas vazias
                if (empty(array_filter($cols))) {
                    continue;
                }

                if ($headers === null) {
                    // Primeira linha = cabeçalho
                    $headers = array_map(function($h) {
                        // Remover conteúdo entre parênteses
                        $h = preg_replace('/\([^)]*\)/', '', $h);
                        // Normalizar
                        $h = strtolower(trim($h));
                        // Transliterar
                        $h = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $h) ?: $h;
                        // Remover caracteres especiais
                        $h = preg_replace('/[^a-z0-9]+/', '_', $h);
                        return trim($h, '_');
                    }, $cols);
                    continue;
                }

                // Montar linha associativa
                $row = [];
                foreach ($headers as $idx => $key) {
                    $row[$key] = isset($cols[$idx]) ? trim($cols[$idx]) : '';
                }
                
                $rows[] = $row;
            }

            fclose($handle);
            return $rows;
            
        } catch (\Throwable $e) {
            fclose($handle);
            throw $e;
        }
    }

    /** Parser leve de XLSX usando ZipArchive + SimpleXML (sem PhpSpreadsheet) */
    private function parseXlsx(string $path): array
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('Extensão ZipArchive não disponível');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Não foi possível abrir o arquivo XLSX.');
        }

        try {
            // Strings compartilhadas
            $sharedStrings = [];
            $ssXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($ssXml !== false) {
                $ss = new \SimpleXMLElement($ssXml);
                $ss->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                foreach ($ss->xpath('//x:si') as $si) {
                    // Concatena todos os nós <t>
                    $text = '';
                    foreach ($si->xpath('.//x:t') as $t) {
                        $text .= (string)$t;
                    }
                    $sharedStrings[] = $text;
                }
            }

            // Sheet1
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml === false) {
                $zip->close();
                throw new \RuntimeException('Planilha não encontrada no arquivo XLSX.');
            }

            $sheet = new \SimpleXMLElement($sheetXml);
            $sheet->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            $matrix   = [];
            $maxCol   = 0;

            foreach ($sheet->xpath('//x:row') as $row) {
                $rowIdx = (int)$row['r'] - 1;
                foreach ($row->xpath('x:c') as $cell) {
                    $ref  = (string)$cell['r'];          // ex: A1, B3
                    $col  = $this->colLetterToIndex($ref);
                    $type = (string)($cell['t'] ?? '');
                    $v    = (string)($cell->v ?? '');

                    if ($type === 's') {
                        // String compartilhada
                        $v = $sharedStrings[(int)$v] ?? '';
                    } elseif ($type === 'str') {
                        // Resultado de fórmula — já em $v
                    } else {
                        // Número puro, datas Excel, etc.
                        // Se parece data serial (número inteiro entre 1 e 80000)
                        if (is_numeric($v) && (int)$v == $v && (int)$v > 0 && (int)$v < 80000 && $this->looksLikeDateColumn($col)) {
                            $v = $this->excelDateToString((int)$v);
                        }
                    }

                    $matrix[$rowIdx][$col] = trim($v);
                    if ($col > $maxCol) $maxCol = $col;
                }
            }

            $zip->close();

            if (empty($matrix)) {
                return [];
            }

            // Primeira linha = cabeçalho
            $headerRow = $matrix[0] ?? [];
            $headers   = [];
            for ($c = 0; $c <= $maxCol; $c++) {
                $h = trim($headerRow[$c] ?? '');
                // Transliterar acentos → ASCII e depois normalizar
                $h = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $h) ?: $h;
                $h = strtolower($h);
                $h = preg_replace('/[^a-z0-9]+/', '_', $h);
                $h = trim($h, '_');
                $headers[$c] = $h;
            }

            // Montar linhas
            $rows = [];
            for ($r = 1; isset($matrix[$r]); $r++) {
                $row = [];
                $isEmpty = true;
                
                for ($c = 0; $c <= $maxCol; $c++) {
                    $value = $matrix[$r][$c] ?? '';
                    $row[$headers[$c] ?? $c] = $value;
                    
                    if (!empty($value)) {
                        $isEmpty = false;
                    }
                }
                
                // Pular linhas vazias
                if (!$isEmpty) {
                    $rows[] = $row;
                }
            }

            return $rows;
            
        } catch (\Throwable $e) {
            $zip->close();
            throw $e;
        }
    }

    private function colLetterToIndex(string $cellRef): int
    {
        preg_match('/^([A-Z]+)/', strtoupper($cellRef), $m);
        $letters = $m[1] ?? 'A';
        $idx = 0;
        foreach (str_split($letters) as $ch) {
            $idx = $idx * 26 + (ord($ch) - ord('A') + 1);
        }
        return $idx - 1;
    }

    private function looksLikeDateColumn(int $col): bool
    {
        // Colunas de data: G=nascimento (idx 6), H=admissao (idx 7)
        // Também mantém D (idx 3) para compatibilidade com formato antigo
        return in_array($col, [3, 6, 7]);
    }

    private function excelDateToString(int $serial): string
    {
        // Excel base date: 1900-01-01 = serial 1 (with bug: serial 60 = 1900-02-29 fictício)
        $base = \DateTime::createFromFormat('Y-m-d', '1899-12-31');
        $base->modify("+{$serial} days");
        return $base->format('d/m/Y');
    }

}