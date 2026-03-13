<?php
declare(strict_types=1);

/**
 * Ficha de Seleção via Token Único.
 * 
 * - Valida o token (existe, não usado, não expirado)
 * - Carrega dados do currículo para pré-preencher
 * - Após envio, marca o token como usado
 */

$db = require __DIR__ . '/../../app/config/database.php';

// ── Validar Token ────────────────────────────────────
$token = trim((string) ($_GET['token'] ?? ''));

if ($token === '') {
    http_response_code(400);
    exit('Link inválido. Nenhum token fornecido.');
}

$stmt = $db->prepare(
    "SELECT t.*, c.nome_completo, c.telefone, c.email, c.cidade, c.cargo_desejado
     FROM selecao_tokens t
     JOIN curriculos c ON c.id = t.curriculo_id
     WHERE t.token = ? LIMIT 1"
);
$stmt->execute([$token]);
$tokenData = $stmt->fetch();

// Buscar empresa baseada no cargo desejado
$empresaSugerida = '';
if ($tokenData && !empty($tokenData['cargo_desejado'])) {
    $stmtVaga = $db->prepare(
        "SELECT empresa FROM vagas WHERE cargo = ? AND ativo = 1 LIMIT 1"
    );
    $stmtVaga->execute([$tokenData['cargo_desejado']]);
    $vaga = $stmtVaga->fetch();
    if ($vaga) {
        $empresaSugerida = $vaga['empresa'];
    }
}

if (!$tokenData) {
    http_response_code(404);
    exit('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Link Inválido</title><style>body{font-family:Poppins,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;background:#fafafa;}.box{text-align:center;padding:40px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.1);max-width:400px;}.box h2{color:#e91e63;margin-bottom:10px;}.box p{color:#666;}</style></head><body><div class="box"><h2>❌ Link Inválido</h2><p>Este link de ficha de seleção não foi encontrado.</p></div></body></html>');
}

if ($tokenData['usado']) {
    exit('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Link Já Utilizado</title><style>body{font-family:Poppins,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;background:#fafafa;}.box{text-align:center;padding:40px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.1);max-width:400px;}.box h2{color:#ff9800;margin-bottom:10px;}.box p{color:#666;}</style></head><body><div class="box"><h2>⚠️ Link Já Utilizado</h2><p>Este link já foi utilizado para enviar sua ficha de seleção. Obrigado!</p></div></body></html>');
}

if (strtotime($tokenData['expires_at']) < time()) {
    exit('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Link Expirado</title><style>body{font-family:Poppins,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;background:#fafafa;}.box{text-align:center;padding:40px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.1);max-width:400px;}.box h2{color:#f44336;margin-bottom:10px;}.box p{color:#666;}</style></head><body><div class="box"><h2>⏰ Link Expirado</h2><p>Este link expirou. Entre em contato com o RH para solicitar um novo.</p></div></body></html>');
}

// ── Processar envio do formulário ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ficha'])) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        // Coleta campos
        $fields = [
            'empresa', 'cargo', 'nome_completo', 'cpf', 'rg', 'orgao_expedidor',
            'data_nascimento', 'estado_civil', 'naturalidade', 'nacionalidade',
            'possui_filhos', 'qtd_filhos', 'possui_cnh', 'categoria_cnh',
            'cep', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'uf',
            'celular', 'email', 'escolaridade', 'curso',
            'experiencia', 'habilidades', 'motivacao', 'lazer',
            'qualidades_pessoais', 'qualidades_profissionais', 'oportunidades_melhoria',
            'esporte', 'animal_domestico', 'expectativas', 'fatores_ambiente', 'motivo_escolha',
            'disponibilidade_horario', 'disponibilidade_info', 'pretensao_salarial', 'indicado_por'
        ];

        $data = [];
        foreach ($fields as $f) {
            $data[$f] = trim(strip_tags((string) ($_POST[$f] ?? '')));
        }

        // Insere na fichas_selecao
        $stmt = $db->prepare(
            "INSERT INTO fichas_selecao (
                empresa, cargo, nome_completo, cpf, rg, orgao_expedidor,
                data_nascimento, estado_civil, naturalidade, nacionalidade,
                possui_filhos, qtd_filhos, possui_cnh, categoria_cnh,
                cep, endereco, numero, complemento, bairro, cidade, uf,
                celular, email, escolaridade, curso,
                experiencia, habilidades, motivacao, lazer,
                qualidades_pessoais, qualidades_profissionais, oportunidades_melhoria,
                esporte, animal_domestico, expectativas, fatores_ambiente, motivo_escolha,
                disponibilidade_horario, disponibilidade_info, pretensao_salarial, indicado_por,
                data_inscricao, status
            ) VALUES (
                :empresa, :cargo, :nome_completo, :cpf, :rg, :orgao_expedidor,
                :data_nascimento, :estado_civil, :naturalidade, :nacionalidade,
                :possui_filhos, :qtd_filhos, :possui_cnh, :categoria_cnh,
                :cep, :endereco, :numero, :complemento, :bairro, :cidade, :uf,
                :celular, :email, :escolaridade, :curso,
                :experiencia, :habilidades, :motivacao, :lazer,
                :qualidades_pessoais, :qualidades_profissionais, :oportunidades_melhoria,
                :esporte, :animal_domestico, :expectativas, :fatores_ambiente, :motivo_escolha,
                :disponibilidade_horario, :disponibilidade_info, :pretensao_salarial, :indicado_por,
                CURDATE(), 'novo'
            )"
        );

        $stmt->execute([
            ':empresa'                  => $data['empresa'],
            ':cargo'                    => $data['cargo'],
            ':nome_completo'            => $data['nome_completo'],
            ':cpf'                      => $data['cpf'],
            ':rg'                       => $data['rg'],
            ':orgao_expedidor'          => $data['orgao_expedidor'],
            ':data_nascimento'          => $data['data_nascimento'],
            ':estado_civil'             => $data['estado_civil'],
            ':naturalidade'             => $data['naturalidade'],
            ':nacionalidade'            => $data['nacionalidade'] ?: 'Brasileiro(a)',
            ':possui_filhos'            => $data['possui_filhos'] ?: 'nao',
            ':qtd_filhos'               => (int) $data['qtd_filhos'],
            ':possui_cnh'               => $data['possui_cnh'] ?: 'nao',
            ':categoria_cnh'            => $data['categoria_cnh'],
            ':cep'                      => $data['cep'],
            ':endereco'                 => $data['endereco'],
            ':numero'                   => $data['numero'],
            ':complemento'              => $data['complemento'],
            ':bairro'                   => $data['bairro'],
            ':cidade'                   => $data['cidade'],
            ':uf'                       => $data['uf'],
            ':celular'                  => $data['celular'],
            ':email'                    => $data['email'],
            ':escolaridade'             => $data['escolaridade'],
            ':curso'                    => $data['curso'],
            ':experiencia'              => $data['experiencia'] ?: '[]',
            ':habilidades'              => $data['habilidades'],
            ':motivacao'                => $data['motivacao'],
            ':lazer'                    => $data['lazer'],
            ':qualidades_pessoais'      => $data['qualidades_pessoais'],
            ':qualidades_profissionais' => $data['qualidades_profissionais'],
            ':oportunidades_melhoria'   => $data['oportunidades_melhoria'],
            ':esporte'                  => $data['esporte'],
            ':animal_domestico'         => $data['animal_domestico'],
            ':expectativas'             => $data['expectativas'],
            ':fatores_ambiente'         => $data['fatores_ambiente'],
            ':motivo_escolha'           => $data['motivo_escolha'],
            ':disponibilidade_horario'  => $data['disponibilidade_horario'] ?: 'sim',
            ':disponibilidade_info'     => $data['disponibilidade_info'],
            ':pretensao_salarial'       => $data['pretensao_salarial'],
            ':indicado_por'             => $data['indicado_por'],
        ]);

        // Marca token como usado
        $stmtToken = $db->prepare("UPDATE selecao_tokens SET usado = 1 WHERE token = ?");
        $stmtToken->execute([$token]);

        // Atualiza status do currículo
        $stmtCv = $db->prepare("UPDATE curriculos SET status = 'aprovado' WHERE id = ?");
        $stmtCv->execute([$tokenData['curriculo_id']]);

        echo json_encode(['success' => true, 'message' => 'Ficha enviada com sucesso!']);
        exit;

    } catch (Throwable $e) {
        error_log('Erro ao salvar ficha via token: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro interno. Tente novamente.']);
        exit;
    }
}

// ── Dados pré-preenchidos do currículo ────────────────
$pre = [
    'nome_completo'  => $tokenData['nome_completo'] ?? '',
    'celular'        => $tokenData['telefone'] ?? '',
    'email'          => $tokenData['email'] ?? '',
    'cidade'         => $tokenData['cidade'] ?? '',
    'cargo_desejado' => $tokenData['cargo_desejado'] ?? '',
    'empresa'        => $empresaSugerida,
];

function esc(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Seleção — <?= esc($pre['nome_completo']) ?></title>
    <link rel="stylesheet" href="assets/css/selecao.css">
    <link rel="stylesheet" href="../assets/css/loading.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="../contratos/assets/images/icone.png" type="image/x-icon">
    <style>
        .pre-filled {
            background-color: #f3e5f5 !important;
            border-color: #ce93d8 !important;
        }
        .pre-filled-note {
            font-size: 0.8rem; color: #7b1fa2; margin-top: 2px; font-weight: 500;
        }
    </style>
</head>
<body>

    <!-- Loading overlay -->
    <div id="griffus-loading-overlay" style="display:none;">
        <span class="gf-sparkle"></span><span class="gf-sparkle"></span><span class="gf-sparkle"></span>
        <span class="gf-sparkle"></span><span class="gf-sparkle"></span><span class="gf-sparkle"></span>
        <img class="gf-loading-logo" src="../contratos/assets/images/logo.webp" alt="Griffus">
        <div class="gf-bubble-track"><div class="gf-bubble"></div><div class="gf-bubble"></div><div class="gf-bubble"></div><div class="gf-bubble"></div><div class="gf-bubble"></div></div>
        <div class="gf-progress-wrap"><div class="gf-progress-bar"></div></div>
        <span class="gf-loading-label">Enviando sua ficha...</span>
        <span class="gf-loading-sublabel">Por favor, aguarde um momento</span>
    </div>

    <div class="main-container">
        <div class="header-strip">
            <img src="../contratos/assets/images/logo.webp" alt="Logo" class="logo">
            <h2>Ficha de Seleção</h2>
        </div>
        <div class="content-body">

            <div style="background:#f3e5f5;padding:14px 18px;border-radius:8px;margin-bottom:20px;border-left:4px solid #9c27b0;">
                <p style="font-size:.92rem;color:#4a148c;margin:0;">
                    <i class="fa-solid fa-info-circle"></i>
                    Olá <strong><?= esc($pre['nome_completo']) ?></strong>! Alguns dados já foram preenchidos com as informações do seu currículo.
                    Complete o restante para finalizar sua candidatura à vaga de <strong><?= esc($pre['cargo_desejado']) ?></strong>.
                </p>
            </div>

            <form id="formSelecao">
                <input type="hidden" name="submit_ficha" value="1">
                <input type="hidden" name="token" value="<?= esc($token) ?>">

                <!-- ====== IDENTIFICAÇÃO ====== -->
                <fieldset>
                    <legend>Identificação</legend>
                    <div class="form-group">
                        <label for="empresa">Empresa <span class="obrigatorio">*</span></label>
                        <select id="empresa" name="empresa" required>
                            <option value="">Selecione...</option>
                            <option value="BELMAX S/A"<?= $pre['empresa'] === 'BELMAX S/A' ? ' selected' : '' ?>>BELMAX S/A</option>
                            <option value="GRIFFUS SA"<?= $pre['empresa'] === 'GRIFFUS SA' ? ' selected' : '' ?>>GRIFFUS SA</option>
                            <option value="BIOPACK LTDA"<?= $pre['empresa'] === 'BIOPACK LTDA' ? ' selected' : '' ?>>BIOPACK LTDA</option>
                            <option value="GRIFFUSONLINE LTDA"<?= $pre['empresa'] === 'GRIFFUSONLINE LTDA' ? ' selected' : '' ?>>GRIFFUSONLINE LTDA</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cargo">Cargo Pretendido</label>
                        <input type="text" id="cargo" name="cargo" value="<?= esc($pre['cargo_desejado']) ?>" class="pre-filled" readonly>
                        <span class="pre-filled-note">Preenchido automaticamente</span>
                    </div>
                </fieldset>

                <!-- ====== DADOS PESSOAIS ====== -->
                <fieldset>
                    <legend>Dados Pessoais</legend>
                    <div class="form-group full-width">
                        <label for="nomeCompleto">Nome Completo</label>
                        <input type="text" id="nomeCompleto" name="nome_completo" value="<?= esc($pre['nome_completo']) ?>" class="pre-filled" readonly>
                        <span class="pre-filled-note">Preenchido automaticamente</span>
                    </div>
                    <div class="form-group">
                        <label for="cpf">CPF <span class="obrigatorio">*</span></label>
                        <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" required>
                    </div>
                    <div class="form-group">
                        <label for="rg">RG <span class="obrigatorio">*</span></label>
                        <input type="text" id="rg" name="rg" required>
                    </div>
                    <div class="form-group">
                        <label for="orgaoExpedidor">Órgão Expedidor</label>
                        <input type="text" id="orgaoExpedidor" name="orgao_expedidor" style="text-transform:uppercase;">
                    </div>
                    <div class="form-group">
                        <label for="dataNascimento">Data de Nascimento <span class="obrigatorio">*</span></label>
                        <input type="date" id="dataNascimento" name="data_nascimento" required>
                    </div>
                    <div class="form-group">
                        <label for="estadoCivil">Estado Civil <span class="obrigatorio">*</span></label>
                        <select id="estadoCivil" name="estado_civil" required>
                            <option value="">Selecione...</option>
                            <option>Solteiro(a)</option><option>Casado(a)</option>
                            <option>Divorciado(a)</option><option>Viúvo(a)</option>
                            <option>União Estável</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="naturalidade">Naturalidade</label>
                        <input type="text" id="naturalidade" name="naturalidade" placeholder="Cidade/UF">
                    </div>
                    <div class="form-group">
                        <label for="nacionalidade">Nacionalidade</label>
                        <input type="text" id="nacionalidade" name="nacionalidade" value="Brasileiro(a)">
                    </div>
                    <div class="form-group">
                        <label>Possui Filhos?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="possui_filhos" value="nao" checked> Não</label>
                            <label class="radio-label"><input type="radio" name="possui_filhos" value="sim"> Sim</label>
                        </div>
                    </div>
                    <div class="form-group" id="grupo-qtd-filhos" style="display:none;">
                        <label for="qtdFilhos">Quantos?</label>
                        <input type="number" id="qtdFilhos" name="qtd_filhos" min="1" max="20">
                    </div>
                    <div class="form-group">
                        <label>Possui CNH?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="possui_cnh" value="nao" checked> Não</label>
                            <label class="radio-label"><input type="radio" name="possui_cnh" value="sim"> Sim</label>
                        </div>
                    </div>
                    <div class="form-group" id="grupo-cnh" style="display:none;">
                        <label for="categoriaCnh">Categoria</label>
                        <select id="categoriaCnh" name="categoria_cnh">
                            <option value="">Selecione...</option>
                            <option>A</option><option>B</option><option>AB</option>
                            <option>C</option><option>D</option><option>E</option>
                        </select>
                    </div>
                </fieldset>

                <!-- ====== ENDEREÇO ====== -->
                <fieldset>
                    <legend>Endereço</legend>
                    <div class="form-group">
                        <label for="cep">CEP <span class="obrigatorio">*</span></label>
                        <input type="text" id="cep" name="cep" placeholder="00000-000" maxlength="9" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="endereco">Endereço <span class="obrigatorio">*</span></label>
                        <input type="text" id="endereco" name="endereco" required>
                    </div>
                    <div class="form-group">
                        <label for="numero">Número <span class="obrigatorio">*</span></label>
                        <input type="text" id="numero" name="numero" required>
                    </div>
                    <div class="form-group">
                        <label for="complemento">Complemento</label>
                        <input type="text" id="complemento" name="complemento">
                    </div>
                    <div class="form-group">
                        <label for="bairro">Bairro <span class="obrigatorio">*</span></label>
                        <input type="text" id="bairro" name="bairro" required>
                    </div>
                    <div class="form-group">
                        <label for="cidade">Cidade</label>
                        <input type="text" id="cidade" name="cidade" value="<?= esc($pre['cidade']) ?>" class="pre-filled" readonly>
                        <span class="pre-filled-note">Preenchido automaticamente</span>
                    </div>
                    <div class="form-group small">
                        <label for="uf">UF <span class="obrigatorio">*</span></label>
                        <input type="text" id="uf" name="uf" maxlength="2" style="text-transform:uppercase;" required>
                    </div>
                </fieldset>

                <!-- ====== CONTATO ====== -->
                <fieldset>
                    <legend>Contato</legend>
                    <div class="form-group">
                        <label for="celular">Celular</label>
                        <input type="tel" id="celular" name="celular" value="<?= esc($pre['celular']) ?>" class="pre-filled" readonly>
                        <span class="pre-filled-note">Preenchido automaticamente</span>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?= esc($pre['email']) ?>" class="pre-filled" readonly>
                        <span class="pre-filled-note">Preenchido automaticamente</span>
                    </div>
                </fieldset>

                <!-- ====== FORMAÇÃO ====== -->
                <fieldset>
                    <legend>Formação</legend>
                    <div class="form-group">
                        <label for="escolaridade">Escolaridade <span class="obrigatorio">*</span></label>
                        <select id="escolaridade" name="escolaridade" required>
                            <option value="">Selecione...</option>
                            <option>Fundamental Incompleto</option><option>Fundamental Completo</option>
                            <option>Médio Incompleto</option><option>Médio Completo</option>
                            <option>Superior Incompleto</option><option>Superior Completo</option>
                            <option>Pós-Graduação</option><option>Mestrado</option><option>Doutorado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="curso">Curso / Área</label>
                        <input type="text" id="curso" name="curso">
                    </div>
                    <div class="form-group full-width">
                        <label for="habilidades">Habilidades</label>
                        <textarea id="habilidades" name="habilidades" rows="3"></textarea>
                    </div>
                </fieldset>

                <!-- ====== EXPERIÊNCIA PROFISSIONAL ANTERIOR ====== -->
                <fieldset id="fieldset-experiencias">
                    <legend>Experiência Profissional Anterior</legend>

                    <div class="full-width experiencias-container">
                        <p class="exp-instrucao">Adicione até <strong>3 experiências anteriores</strong> (da mais recente para a mais antiga).</p>

                        <div id="lista-experiencias">
                            <!-- Blocos inseridos via JS -->
                        </div>

                        <div id="area-adicionar-exp">
                            <button type="button" id="btn-add-exp" class="btn-adicionar-exp">
                                <i class="fa-solid fa-plus"></i> Adicionar Experiência
                            </button>
                            <span id="exp-counter-msg" class="exp-counter-msg"></span>
                        </div>
                    </div>

                    <input type="hidden" id="experiencia" name="experiencia" value="[]">
                </fieldset>

                <!-- ====== QUESTIONÁRIO ====== -->
                <fieldset>
                    <legend>Questionário</legend>

                    <div class="form-group full-width questao-destaque">
                        <p class="questao-titulo-destaque">As questões abaixo vão nos possibilitar conhecê-lo um pouco melhor</p>
                    </div>

                    <div class="form-group full-width">
                        <label for="motivacao">O que mais te motiva e o que mais te revolta?</label>
                        <textarea id="motivacao" name="motivacao" rows="3" placeholder="Descreva..."></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="lazer">O que você gosta de fazer nos momentos de lazer?</label>
                        <textarea id="lazer" name="lazer" rows="3" placeholder="Descreva..."></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="qualidades_pessoais">Quais são as suas qualidades pessoais?</label>
                        <textarea id="qualidades_pessoais" name="qualidades_pessoais" rows="3" placeholder="Descreva..."></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="qualidades_profissionais">Quais são as suas qualidades profissionais?</label>
                        <textarea id="qualidades_profissionais" name="qualidades_profissionais" rows="3" placeholder="Descreva..."></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="oportunidades_melhoria">Quais suas oportunidades de Melhoria?</label>
                        <textarea id="oportunidades_melhoria" name="oportunidades_melhoria" rows="3" placeholder="Descreva..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="esporte">Pratica algum esporte? Qual?</label>
                        <input type="text" id="esporte" name="esporte" placeholder="Ex: Futebol, Academia, Corrida...">
                    </div>

                    <div class="form-group">
                        <label for="animal_domestico">Possui animal doméstico? Qual?</label>
                        <input type="text" id="animal_domestico" name="animal_domestico" placeholder="Ex: Cachorro, Gato...">
                    </div>

                    <div class="form-group full-width">
                        <label for="expectativas">Fale um pouco das expectativas para seu futuro profissional e pessoal:</label>
                        <textarea id="expectativas" name="expectativas" rows="3" placeholder="Descreva suas expectativas..."></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="fatores_ambiente">Para você, quais os fatores MAIS e MENOS importantes em se tratando do ambiente de trabalho?</label>
                        <textarea id="fatores_ambiente" name="fatores_ambiente" rows="3" placeholder="Descreva..."></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="motivo_escolha">Apresente motivos para escolhermos você ao invés de outras pessoas:</label>
                        <textarea id="motivo_escolha" name="motivo_escolha" rows="3" placeholder="Descreva..."></textarea>
                    </div>
                </fieldset>

                <!-- ====== DISPONIBILIDADE & PRETENSÃO ====== -->
                <fieldset>
                    <legend>Disponibilidade &amp; Pretensão</legend>

                    <div class="form-group full-width">
                        <label>Você possui disponibilidade para trabalhar em qualquer horário? <span class="obrigatorio">*</span></label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="disponibilidade_horario" value="sim" required> Sim</label>
                            <label class="radio-label"><input type="radio" name="disponibilidade_horario" value="nao"> Não</label>
                        </div>
                    </div>

                    <div class="form-group full-width" id="grupo-disponibilidade-info" style="display:none;">
                        <label for="disponibilidade_info">Caso negativo, favor informar:</label>
                        <input type="text" id="disponibilidade_info" name="disponibilidade_info"
                            placeholder="Informe sua disponibilidade de horário...">
                    </div>

                    <div class="form-group">
                        <label for="pretensao_salarial">Pretensão Salarial <span class="obrigatorio">*</span></label>
                        <input type="text" id="pretensao_salarial" name="pretensao_salarial"
                            placeholder="Ex: R$ 2.500,00" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="indicado_por">Se foi indicado por alguém que já trabalha nesta empresa, cite o nome:</label>
                        <input type="text" id="indicado_por" name="indicado_por"
                            placeholder="Nome do colaborador que te indicou (se houver)">
                    </div>
                </fieldset>

                <div class="button-container">
                    <button type="submit" id="btn-submit">Enviar Ficha</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/toast.js"></script>
    <script>
    "use strict";

    // Toggle filhos / CNH / disponibilidade
    document.querySelectorAll('input[name="possui_filhos"]').forEach(r => {
        r.addEventListener('change', () => {
            document.getElementById('grupo-qtd-filhos').style.display = r.value === 'sim' && r.checked ? '' : 'none';
        });
    });
    document.querySelectorAll('input[name="possui_cnh"]').forEach(r => {
        r.addEventListener('change', () => {
            document.getElementById('grupo-cnh').style.display = r.value === 'sim' && r.checked ? '' : 'none';
        });
    });
    document.querySelectorAll('input[name="disponibilidade_horario"]').forEach(r => {
        r.addEventListener('change', () => {
            document.getElementById('grupo-disponibilidade-info').style.display = r.value === 'nao' && r.checked ? '' : 'none';
        });
    });

    // CPF mask
    var cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 9) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6,9)+'-'+v.slice(9);
            else if (v.length > 6) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6);
            else if (v.length > 3) v = v.slice(0,3)+'.'+v.slice(3);
            this.value = v;
        });
    }

    // CEP auto-fill
    var cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '').slice(0, 8);
            if (v.length > 5) v = v.slice(0,5)+'-'+v.slice(5);
            this.value = v;
        });
        cepInput.addEventListener('blur', function() {
            var cep = this.value.replace(/\D/g, '');
            if (cep.length !== 8) return;
            fetch('https://viacep.com.br/ws/'+cep+'/json/')
                .then(r => r.json())
                .then(data => {
                    if (data.erro) return;
                    document.getElementById('endereco').value = data.logradouro || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('uf').value = data.uf || '';
                    document.getElementById('numero').focus();
                })
                .catch(() => {});
        });
    }

    // ══════════════════════════════════════════════════════
    // EXPERIÊNCIAS PROFISSIONAIS DINÂMICAS
    // ══════════════════════════════════════════════════════
    (function() {
        let contadorExp = 0;
        const maxExperiencias = 3;
        const listaExp = document.getElementById('lista-experiencias');
        const btnAdd = document.getElementById('btn-add-exp');
        const counterMsg = document.getElementById('exp-counter-msg');
        const hiddenExp = document.getElementById('experiencia');
        let experiencias = [];

        function atualizarContador() {
            const total = experiencias.length;
            if (total >= maxExperiencias) {
                btnAdd.disabled = true;
                counterMsg.textContent = `Máximo atingido (${total}/${maxExperiencias})`;
                counterMsg.style.color = '#ff6f00';
            } else {
                btnAdd.disabled = false;
                if (total === 0) {
                    counterMsg.textContent = '';
                } else {
                    counterMsg.textContent = `${total}/${maxExperiencias} experiência(s)`;
                    counterMsg.style.color = '#666';
                }
            }
        }

        function esc(v) {
            if (!v) return '';
            return String(v)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function renderizarExperiencias() {
            listaExp.innerHTML = '';
            experiencias.forEach((exp, idx) => {
                const bloco = document.createElement('div');
                bloco.className = 'exp-bloco';
                bloco.dataset.idx = idx;
                bloco.innerHTML = `
                    <div class="exp-bloco-header">
                        <div class="exp-bloco-titulo">
                            <i class="fa-solid fa-briefcase"></i>
                            Experiência ${idx + 1}
                        </div>
                        <button type="button" class="btn-remover-exp" onclick="removerExperiencia(${idx})">
                            <i class="fa-solid fa-xmark"></i> Remover
                        </button>
                    </div>
                    <div class="exp-grid">
                        <div class="form-group">
                            <label>Empresa <span class="obrigatorio">*</span></label>
                            <input type="text" placeholder="Nome da empresa" value="${esc(exp.empresa)}"
                                   onchange="atualizarCampoExp(${idx}, 'empresa', this.value)" required>
                        </div>
                        <div class="form-group">
                            <label>Cargo <span class="obrigatorio">*</span></label>
                            <input type="text" placeholder="Cargo exercido" value="${esc(exp.cargo)}"
                                   onchange="atualizarCampoExp(${idx}, 'cargo', this.value)" required>
                        </div>
                        <div class="form-group">
                            <label>Período</label>
                            <input type="text" placeholder="Ex: Jan/2020 - Dez/2022" value="${esc(exp.periodo)}"
                                   onchange="atualizarCampoExp(${idx}, 'periodo', this.value)">
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label>Atividades Principais</label>
                        <textarea rows="2" placeholder="Descreva suas principais atividades..."
                                  onchange="atualizarCampoExp(${idx}, 'atividades', this.value)">${esc(exp.atividades)}</textarea>
                    </div>
                `;
                listaExp.appendChild(bloco);
            });
            atualizarContador();
        }

        window.atualizarCampoExp = function(idx, campo, valor) {
            if (experiencias[idx] !== undefined) {
                experiencias[idx][campo] = valor;
                hiddenExp.value = JSON.stringify(experiencias);
            }
        };

        window.removerExperiencia = function(idx) {
            experiencias.splice(idx, 1);
            renderizarExperiencias();
        };

        btnAdd.addEventListener('click', function() {
            if (experiencias.length >= maxExperiencias) return;
            experiencias.push({
                empresa: '',
                cargo: '',
                periodo: '',
                atividades: ''
            });
            renderizarExperiencias();
            
            // Foca no primeiro campo do novo bloco
            setTimeout(() => {
                const blocos = listaExp.querySelectorAll('.exp-bloco');
                const ultimo = blocos[blocos.length - 1];
                if (ultimo) {
                    const inp = ultimo.querySelector('input');
                    if (inp) inp.focus();
                }
            }, 50);
        });

        // Inicializa
        atualizarContador();
    })();

    // Form submit
    document.getElementById('formSelecao').addEventListener('submit', async function(e) {
        e.preventDefault();

        var fd = new FormData(this);
        var overlay = document.getElementById('griffus-loading-overlay');
        var btn = document.getElementById('btn-submit');

        if (overlay) overlay.style.display = 'flex';
        btn.disabled = true;

        try {
            var res = await fetch(window.location.href, { method: 'POST', body: fd });
            var data = await res.json();

            if (data.success) {
                window.location.href = '../obrigado.html';
            } else {
                if (typeof Toast !== 'undefined') Toast.error(data.message);
                else alert(data.message);
            }
        } catch (err) {
            if (typeof Toast !== 'undefined') Toast.error('Erro de conexão.');
            else alert('Erro de conexão.');
        } finally {
            if (overlay) overlay.style.display = 'none';
            btn.disabled = false;
        }
    });
    </script>

    <!-- ====================== MODAL LGPD ====================== -->
    <div id="modal-lgpd" class="modal-lgpd-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="modal-lgpd-titulo">
        <div class="modal-lgpd-box">
            <div class="modal-lgpd-header">
                <i class="fa-solid fa-shield-halved modal-lgpd-icon"></i>
                <h3 id="modal-lgpd-titulo">Termo de Consentimento e Privacidade</h3>
            </div>
            <div class="modal-lgpd-body">
                <p>
                    Ao prosseguir, você declara estar ciente de que os dados fornecidos neste formulário serão coletados e tratados,
                    exclusivamente para fins de <strong>contrato de prestação de serviços, recrutamento, seleção e análise de perfil</strong>
                    para vagas disponíveis.
                </p>
                <p>
                    Seus dados serão armazenados de forma segura e <strong>não serão compartilhados com terceiros</strong> sem sua
                    autorização prévia. Você poderá solicitar a exclusão dos seus dados a qualquer momento enviando um e-mail para
                    <a href="mailto:dp@belmax.com.br">dp@belmax.com.br</a>.
                </p>
            </div>
            <div class="modal-lgpd-check-row">
                <label class="modal-lgpd-check-label" for="check-lgpd">
                    <input type="checkbox" id="check-lgpd">
                    Li e aceito os termos de tratamento de dados conforme a LGPD.
                </label>
            </div>
            <div class="modal-lgpd-footer">
                <button type="button" id="btn-lgpd-cancelar" class="btn-lgpd-cancelar">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </button>
                <button type="button" id="btn-lgpd-confirmar" class="btn-lgpd-confirmar" disabled>
                    <i class="fa-solid fa-paper-plane"></i> Confirmar e Enviar
                </button>
            </div>
        </div>
    </div>

    <script>
    /*
     * LGPD — intercepta o submit na FASE DE CAPTURA (antes do handler principal)
     * e usa stopImmediatePropagation para bloquear completamente o evento
     * até o usuário aceitar os termos.
     */
    (function () {
        'use strict';

        var form        = document.getElementById('formSelecao');
        var modal       = document.getElementById('modal-lgpd');
        var checkLgpd   = document.getElementById('check-lgpd');
        var btnConfirmar= document.getElementById('btn-lgpd-confirmar');
        var btnCancelar = document.getElementById('btn-lgpd-cancelar');
        var termoAceito = false;  // flag LOCAL, sem risco de conflito global

        /* ── Abre o modal ────────────────────────────────── */
        function abrirModal() {
            checkLgpd.checked    = false;
            btnConfirmar.disabled = true;
            modal.style.display  = 'flex';
            document.body.style.overflow = 'hidden';
        }

        /* ── Fecha o modal ───────────────────────────────── */
        function fecharModal() {
            modal.style.display  = 'none';
            document.body.style.overflow = '';
        }

        /*
         * Listener de CAPTURA no form — roda ANTES de qualquer listener
         * registrado em fase de bolha (inclusive o principal).
         * Se o termo ainda não foi aceito: bloqueia tudo e abre o modal.
         * Se já foi aceito: limpa a flag e deixa o evento seguir normalmente.
         */
        form.addEventListener('submit', function (e) {
            if (!termoAceito) {
                e.preventDefault();
                e.stopImmediatePropagation(); // impede o handler principal de ver o evento
                abrirModal();
            } else {
                termoAceito = false; // reset para próxima submissão
                /* deixa o evento prosseguir — handler principal irá tratá-lo */
            }
        }, true /* capture = true */);

        /* ── Checkbox habilita botão ─────────────────────── */
        checkLgpd.addEventListener('change', function () {
            btnConfirmar.disabled = !this.checked;
        });

        /* ── Confirmar: seta flag e re-dispara submit ────── */
        btnConfirmar.addEventListener('click', function () {
            if (!checkLgpd.checked) return;
            fecharModal();
            termoAceito = true;
            /*
             * Cria e dispara um novo evento submit.
             * O listener de captura acima verá termoAceito=true
             * e deixará o evento chegar ao handler principal.
             */
            form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        });

        /* ── Cancelar ────────────────────────────────────── */
        btnCancelar.addEventListener('click', fecharModal);

        /* ── Clique fora fecha ───────────────────────────── */
        modal.addEventListener('click', function (e) {
            if (e.target === modal) fecharModal();
        });

        /* ── ESC fecha ───────────────────────────────────── */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') fecharModal();
        });

    })();
    </script>
    <!-- ========================================================= -->
</body>
</html>
