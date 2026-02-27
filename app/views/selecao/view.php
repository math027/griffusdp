<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge(string $status): string {
        $map = [
            'novo'                 => ['label' => 'Novo',                'color' => '#e91e63'],
            'em_analise'           => ['label' => 'Em Análise',          'color' => '#ff9800'],
            'entrevista_agendada'  => ['label' => 'Entrevista Agendada', 'color' => '#9c27b0'],
            'entrevistado'         => ['label' => 'Entrevistado',        'color' => '#2196f3'],
            'aprovado'             => ['label' => 'Aprovado',            'color' => '#4caf50'],
            'contratado'           => ['label' => 'Contratado',          'color' => '#00796b'],
            'reprovado'            => ['label' => 'Reprovado',           'color' => '#f44336'],
            'sem_contato'          => ['label' => 'Contato sem sucesso', 'color' => '#ff1100'],
            'faltou'               => ['label' => 'Faltou na Entrevista','color' => '#8d0900'],
            'arquivado'            => ['label' => 'Arquivado',           'color' => '#9e9e9e'],
        ];
        $s = $map[$status] ?? ['label' => ucfirst($status), 'color' => '#757575'];
        return sprintf(
            '<span style="background:%s;color:#fff;padding:4px 14px;border-radius:12px;font-size:0.82rem;font-weight:600;">%s</span>',
            $s['color'],
            $s['label']
        );
    }
}

if (!isset($ficha)) {
    die('Ficha não carregada.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Seleção #<?= (int)$ficha['id'] ?></title>
    <link rel="shortcut icon" href="assets/images/icone.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/edit-form.css">
    <style>
        /* ── Pipeline ── */
        .pipeline { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px; }
        .pipeline-btn {
            padding: 8px 16px; border: 2px solid transparent;
            border-radius: 20px; cursor: pointer;
            font-size: .83rem; font-weight: 600; transition: all .15s;
            font-family: inherit;
        }
        .pipeline-btn:hover { filter: brightness(.88); }
        .pipeline-btn.ativo { border-color: #333 !important; box-shadow: 0 0 0 1px #333; }
        .pb-em_analise          { background:#fff3e0; color:#e65100; }
        .pb-entrevista_agendada { background:#f3e5f5; color:#6a1b9a; }
        .pb-entrevistado        { background:#e3f2fd; color:#1565c0; }
        .pb-aprovado            { background:#e8f5e9; color:#2e7d32; }
        .pb-contratado          { background:#e0f2f1; color:#00695c; }
        .pb-reprovado           { background:#ffebee; color:#c62828; }
        .pb-sem_contato         { background:#ffe5e5; color:#ff1100; }
        .pb-faltou              { background:#e6cfcf; color:#8d0900; }
        .pb-arquivado           { background:#f5f5f5; color:#616161; }

        /* ── Entrevista box ── */
        .interview-box {
            background: #f8f9fa; border: 1px solid #e0e0e0;
            border-radius: 8px; padding: 20px; margin-top: 8px;
        }
        .interview-box label {
            font-size: .83rem; color: #555; font-weight: 600;
            display: block; margin-bottom: 5px; margin-top: 14px;
        }
        .interview-box label:first-child { margin-top: 0; }
        .interview-box input[type="datetime-local"],
        .interview-box input[type="text"],
        .interview-box textarea,
        .interview-box select {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd;
            border-radius: 6px; font-size: .9rem; box-sizing: border-box;
            font-family: inherit; background: #fff;
        }
        .interview-box input:focus,
        .interview-box textarea:focus,
        .interview-box select:focus { border-color: #e91e63; outline: none; }
        .interview-box textarea { resize: vertical; min-height: 80px; }

        /* ── Readonly info ── */
        .info-row { margin: 8px 0; font-size: .9rem; line-height: 1.55; }
        .info-row strong { color: #555; min-width: 180px; display: inline-block; }
        .pre-box {
            white-space: pre-wrap; background: #fafafa;
            padding: 10px 14px; border-radius: 6px; border: 1px solid #eee;
            font-size: .85rem; margin: 6px 0 0; line-height: 1.5;
        }

        /* ── Status badge row ── */
        .status-row {
            display: flex; align-items: center; gap: 12px; margin-bottom: 6px;
        }

        /* ── Feedback salvo ── */
        .save-feedback {
            display: inline-block; margin-left: 12px; font-size: .84rem;
            color: #2e7d32; font-weight: 600; opacity: 0; transition: opacity .3s;
        }
        .save-feedback.show { opacity: 1; }

        /* ── Grid 2 colunas para alguns campos ── */
        .form-grid-2 {
            display: grid; grid-template-columns: 1fr 1fr; gap: 0 30px;
        }
        @media (max-width: 600px) {
            .form-grid-2 { grid-template-columns: 1fr; }
        }

        /* ── Experiência cards ── */
        .exp-list-view { display: flex; flex-direction: column; gap: 10px; margin-top: 8px; }
        .exp-card-view {
            border: 1px solid #e8e8e8; border-radius: 8px;
            overflow: hidden; background: #fff;
        }
        .exp-card-header {
            display: flex; align-items: center; gap: 10px;
            background: #fdf2f6; padding: 10px 14px;
            border-bottom: 1px solid #f8bbd0;
        }
        .exp-num {
            background: #e91e63; color: #fff;
            width: 22px; height: 22px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 700; flex-shrink: 0;
        }
        .exp-card-header strong { font-size: .9rem; color: #212121; }
        .exp-cargo {
            margin-left: auto; font-size: .8rem;
            color: #e91e63; font-weight: 600;
            background: #fff; padding: 2px 10px;
            border-radius: 10px; border: 1px solid #f8bbd0;
        }
        .exp-card-body {
            padding: 10px 14px; display: flex;
            flex-wrap: wrap; gap: 10px 24px;
        }
        .exp-card-body span { font-size: .83rem; color: #555; display: flex; align-items: center; gap: 6px; }
        .exp-card-body i { color: #e91e63; font-size: .78rem; }

        /* ── Questionário ── */
        .questao-titulo-admin {
            background: #fffde7; border: 2px solid #f9a825;
            border-radius: 6px; padding: 10px 16px;
            font-size: .88rem; font-weight: 700; color: #5d4037;
            text-transform: uppercase; letter-spacing: .3px;
            text-align: center; margin-bottom: 18px;
        }
        .questao-item-view {
            border-left: 3px solid var(--griffus-pink, #e91e63);
            padding: 10px 14px;
            background: #fafafa;
            border-radius: 0 6px 6px 0;
            margin-bottom: 12px;
        }
        .questao-pergunta {
            font-size: .82rem; font-weight: 600;
            color: #757575; margin-bottom: 6px;
        }
        .questao-resposta {
            font-size: .9rem; color: #212121; line-height: 1.55;
        }
    </style>
</head>
<body class="edit-form-page">
<div class="main-container">

    <div class="header-strip">
        <h2>
            <i class="fa-regular fa-id-card" style="margin-right:10px;"></i>
            Ficha de Seleção #<?= (int)$ficha['id'] ?>
        </h2>
        <div class="header-actions">
            <div style="display:flex;align-items:center;gap:12px;">
                <?= statusBadge($ficha['status'] ?? 'novo') ?>
                <button type="button" class="btn btn-secondary" onclick="window.close();">
                    <i class="fa-solid fa-xmark"></i> Fechar
                </button>
            </div>
        </div>
    </div>

    <div class="content-body">

        <fieldset class="form-section">
            <legend class="section-title">Vaga</legend>
            <div class="section-fields form-grid-2">
                <div class="info-row"><strong>Empresa:</strong> <?= e($ficha['empresa'] ?? '') ?></div>
                <div class="info-row"><strong>Cargo Pretendido:</strong> <?= e($ficha['cargo'] ?? '') ?></div>
                <div class="info-row"><strong>Data de Inscrição:</strong> <?= e($ficha['data_inscricao'] ?? '') ?></div>
            </div>
        </fieldset>

        <fieldset class="form-section">
            <legend class="section-title">Dados Pessoais</legend>
            <div class="section-fields form-grid-2">
                <div class="info-row"><strong>Nome Completo:</strong> <?= e($ficha['nome_completo'] ?? '') ?></div>
                <div class="info-row"><strong>CPF:</strong> <?= e($ficha['cpf'] ?? '') ?></div>
                <div class="info-row">
                    <strong>RG:</strong> <?= e($ficha['rg'] ?? '') ?>
                    <?php if (!empty($ficha['orgao_expedidor'])): ?>
                        &mdash; <?= e($ficha['orgao_expedidor']) ?>
                    <?php endif; ?>
                </div>
                <div class="info-row"><strong>Data de Nascimento:</strong> <?= e($ficha['data_nascimento'] ?? '') ?></div>
                <div class="info-row"><strong>Estado Civil:</strong> <?= e($ficha['estado_civil'] ?? '') ?></div>
                <div class="info-row"><strong>Naturalidade:</strong> <?= e($ficha['naturalidade'] ?? '') ?></div>
                <div class="info-row"><strong>Nacionalidade:</strong> <?= e($ficha['nacionalidade'] ?? '') ?></div>
                <div class="info-row">
                    <strong>Filhos:</strong> <?= e($ficha['possui_filhos'] ?? '') ?>
                    <?php if (($ficha['possui_filhos'] ?? '') === 'sim'): ?>
                        &mdash; <?= e($ficha['qtd_filhos'] ?? '') ?> filho(s)
                    <?php endif; ?>
                </div>
                <div class="info-row">
                    <strong>CNH:</strong> <?= e($ficha['possui_cnh'] ?? '') ?>
                    <?php if (($ficha['possui_cnh'] ?? '') === 'sim'): ?>
                        &mdash; Categoria <?= e($ficha['categoria_cnh'] ?? '') ?>
                    <?php endif; ?>
                </div>
            </div>
        </fieldset>

        <fieldset class="form-section">
            <legend class="section-title">Endereço</legend>
            <div class="section-fields form-grid-2">
                <div class="info-row">
                    <strong>Logradouro:</strong>
                    <?= e($ficha['endereco'] ?? '') ?>, <?= e($ficha['numero'] ?? '') ?>
                    <?php if (!empty($ficha['complemento'])): ?>
                        &mdash; <?= e($ficha['complemento']) ?>
                    <?php endif; ?>
                </div>
                <div class="info-row"><strong>Bairro:</strong> <?= e($ficha['bairro'] ?? '') ?></div>
                <div class="info-row"><strong>Cidade / UF:</strong> <?= e($ficha['cidade'] ?? '') ?> &mdash; <?= e($ficha['uf'] ?? '') ?></div>
                <div class="info-row"><strong>CEP:</strong> <?= e($ficha['cep'] ?? '') ?></div>
            </div>
        </fieldset>

        <fieldset class="form-section">
            <legend class="section-title">Contato</legend>
            <div class="section-fields form-grid-2">
                <div class="info-row"><strong>Celular:</strong> <?= e($ficha['celular'] ?? '') ?></div>
                <div class="info-row"><strong>E-mail:</strong> <?= e($ficha['email'] ?? '') ?></div>
            </div>
        </fieldset>

        <fieldset class="form-section">
            <legend class="section-title">Formação &amp; Experiência</legend>
            <div class="section-fields">
                <div class="form-grid-2">
                    <div class="info-row"><strong>Escolaridade:</strong> <?= e($ficha['escolaridade'] ?? '') ?></div>
                    <div class="info-row"><strong>Curso:</strong> <?= e($ficha['curso'] ?? '') ?></div>
                </div>
                <div class="info-row" style="margin-top:14px;"><strong>Experiência Profissional:</strong></div>
                <?php
                $expJson = $ficha['experiencia'] ?? '';
                $expArr  = !empty($expJson) ? json_decode($expJson, true) : null;
                if (!empty($expArr) && is_array($expArr)):
                ?>
                <div class="exp-list-view">
                    <?php foreach ($expArr as $i => $exp): ?>
                    <div class="exp-card-view">
                        <div class="exp-card-header">
                            <span class="exp-num"><?= $i + 1 ?></span>
                            <strong><?= e($exp['empresa'] ?? '') ?></strong>
                            <span class="exp-cargo"><?= e($exp['cargo'] ?? '') ?></span>
                        </div>
                        <div class="exp-card-body">
                            <?php if (!empty($exp['data_admissao']) || !empty($exp['data_demissao'])): ?>
                            <span><i class="fa-regular fa-calendar"></i>
                                <?= e(!empty($exp['data_admissao']) ? date('d/m/Y', strtotime($exp['data_admissao'])) : '?') ?>
                                &nbsp;&rarr;&nbsp;
                                <?= !empty($exp['data_demissao']) ? e(date('d/m/Y', strtotime($exp['data_demissao']))) : '<em>atual</em>' ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($exp['ultimo_salario'])): ?>
                            <span><i class="fa-solid fa-dollar-sign"></i> <?= e($exp['ultimo_salario']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($exp['motivo_saida'])): ?>
                            <span><i class="fa-regular fa-comment-dots"></i> <?= e($exp['motivo_saida']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="pre-box"><em style="color:#bbb;">Não informado</em></div>
                <?php endif; ?>
                <div class="info-row" style="margin-top:14px;"><strong>Habilidades:</strong></div>
                <div class="pre-box"><?= e($ficha['habilidades'] ?? '') ?: '<em style="color:#bbb;">Não informado</em>' ?></div>
            </div>
        </fieldset>

        <fieldset class="form-section">
            <legend class="section-title">Questionário</legend>
            <div class="section-fields">

                <?php
                $perguntas = [
                    ['motivacao',                'O que mais te motiva e o que mais te revolta?'],
                    ['lazer',                    'O que você gosta de fazer nos momentos de lazer?'],
                    ['qualidades_pessoais',      'Quais são as suas qualidades pessoais?'],
                    ['qualidades_profissionais', 'Quais são as suas qualidades profissionais?'],
                    ['oportunidades_melhoria',   'Quais suas oportunidades de Melhoria?'],
                    ['esporte',                  'Pratica algum esporte? Qual?'],
                    ['animal_domestico',         'Possui animal doméstico? Qual?'],
                    ['expectativas',             'Expectativas para seu futuro profissional e pessoal:'],
                    ['fatores_ambiente',         'Fatores MAIS e MENOS importantes no ambiente de trabalho:'],
                    ['motivo_escolha',           'Motivos para escolhermos você ao invés de outras pessoas:'],
                ];
                foreach ($perguntas as [$campo_q, $pergunta]):
                    $resp = $ficha[$campo_q] ?? '';
                ?>
                <div class="questao-item-view">
                    <div class="questao-pergunta"><?= e($pergunta) ?></div>
                    <div class="questao-resposta"><?= !empty($resp) ? nl2br(e($resp)) : '<em style="color:#bbb;">Não respondido</em>' ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <fieldset class="form-section">
            <legend class="section-title">Disponibilidade &amp; Pretensão</legend>
            <div class="section-fields form-grid-2">
                <div class="info-row">
                    <strong>Disponibilidade qualquer horário:</strong>
                    <?php
                    $disp = $ficha['disponibilidade_horario'] ?? '';
                    echo $disp === 'sim' ? '<span style="color:#2e7d32;font-weight:600;">✔ Sim</span>'
                        : ($disp === 'nao' ? '<span style="color:#c62828;font-weight:600;">✖ Não</span>' : '—');
                    ?>
                </div>
                <?php if (($ficha['disponibilidade_horario'] ?? '') === 'nao' && !empty($ficha['disponibilidade_info'])): ?>
                <div class="info-row" style="grid-column:span 2;">
                    <strong>Horário disponível:</strong> <?= e($ficha['disponibilidade_info']) ?>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <strong>Pretensão Salarial:</strong>
                    <?= !empty($ficha['pretensao_salarial']) ? e($ficha['pretensao_salarial']) : '—' ?>
                </div>
                <div class="info-row">
                    <strong>Indicado por:</strong>
                    <?= !empty($ficha['indicado_por']) ? e($ficha['indicado_por']) : '<em style="color:#bbb;">Sem indicação</em>' ?>
                </div>
            </div>
        </fieldset>

        <fieldset class="form-section">
            <legend class="section-title">Pipeline de RH</legend>
            <div class="section-fields">
                <p style="font-size:.88rem;color:#888;margin:0 0 10px;">Clique para alterar o status da candidatura:</p>
                <div class="pipeline" id="pipelineContainer">
                    <?php
                    $statusAtual = $ficha['status'] ?? 'novo';
                    $etapas = [
                        ['em_analise',          'Em Análise'],
                        ['entrevista_agendada', 'Entrevista Agendada'],
                        ['entrevistado',        'Entrevistado'],
                        ['aprovado',            'Aprovado'],
                        ['contratado',          'Contratado'],
                        ['reprovado',           'Reprovado'],
                        ['sem_contato',         'Contato sem sucesso'],
                        ['faltou',              'Faltou na Entrevista'],
                        ['arquivado',           'Arquivado'],
                    ];
                    foreach ($etapas as [$key, $label]):
                        $ativo = ($statusAtual === $key) ? ' ativo' : '';
                    ?>
                    <button type="button"
                            class="pipeline-btn pb-<?= $key ?><?= $ativo ?>"
                            onclick="alterarStatus(<?= (int)$ficha['id'] ?>, '<?= $key ?>')">
                        <?= $label ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </fieldset>

        <fieldset class="form-section">
            <legend class="section-title">Entrevista &amp; Processo Seletivo</legend>
            <div class="section-fields">
                <div class="interview-box">
                    <label>Data e hora da entrevista</label>
                    <input type="datetime-local" id="ipt_data"
                           value="<?= e(!empty($ficha['data_entrevista']) ? str_replace(' ', 'T', substr($ficha['data_entrevista'], 0, 16)) : '') ?>">

                    <label>Local / Link da entrevista</label>
                    <input type="text" id="ipt_local"
                           placeholder="Ex: Sala 02 · meet.google.com/abc..."
                           value="<?= e($ficha['local_entrevista'] ?? '') ?>">

                    <label>Observações</label>
                    <textarea id="ipt_obs"><?= e($ficha['obs_entrevista'] ?? '') ?></textarea>

                    <label>Resultado da entrevista</label>
                    <select id="ipt_resultado">
                        <?php
                        $resultOpts = [
                            ''                       => '— Selecione —',
                            'pendente'               => 'Pendente',
                            'aprovado_entrevista'    => 'Aprovado na entrevista',
                            'reprovado_entrevista'   => 'Reprovado na entrevista',
                            'faltou'                 => 'Faltou na entrevista'   
                        ];
                        foreach ($resultOpts as $val => $lbl):
                            $sel = (($ficha['resultado_entrevista'] ?? '') === $val) ? ' selected' : '';
                        ?>
                            <option value="<?= e($val) ?>"<?= $sel ?>><?= e($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div style="margin-top:16px;">
                        <button type="button" class="btn" id="btnSalvarEntrevista"
                                onclick="salvarEntrevista(<?= (int)$ficha['id'] ?>)">
                            <i class="fa-regular fa-floppy-disk"></i> Salvar entrevista
                        </button>
                        <span class="save-feedback" id="saveFeedback">✓ Salvo com sucesso!</span>
                    </div>
                </div>
            </div>
        </fieldset>

        <div class="button-container">
            <button type="button" class="btn btn-secondary" onclick="window.close();">
                <i class="fa-solid fa-arrow-left"></i> Fechar
            </button>
        </div>

    </div></div><script>
const FICHA_ID = <?= (int)$ficha['id'] ?>;
const CSRF     = <?= json_encode($csrfToken ?? '', JSON_HEX_TAG | JSON_HEX_AMP) ?>;

/* ─── Alterar Status ─── */
function alterarStatus(id, status) {
    var labels = {
        em_analise: 'Em Análise', entrevista_agendada: 'Entrevista Agendada',
        entrevistado: 'Entrevistado', aprovado: 'Aprovado',
        contratado: 'Contratado', reprovado: 'Reprovado', arquivado: 'Arquivado',
        sem_contato: 'Contato sem sucesso', faltou: 'Faltou na Entrevista'
    };
    if (!confirm('Alterar status para "' + (labels[status] || status) + '"?')) return;

    fetch('index.php?section=selecao&action=status_selecao', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    'id=' + id + '&status=' + encodeURIComponent(status) + '&csrf_token=' + encodeURIComponent(CSRF)
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            /* Atualiza visual dos botões sem recarregar */
            document.querySelectorAll('.pipeline-btn').forEach(function (btn) {
                btn.classList.remove('ativo');
            });
            var ativo = document.querySelector('.pb-' + status);
            if (ativo) ativo.classList.add('ativo');

            /* Atualiza badge no header */
            var badgeMap = {
                novo: ['Novo','#e91e63'], em_analise: ['Em Análise','#ff9800'],
                entrevista_agendada: ['Entrevista Agendada','#9c27b0'],
                entrevistado: ['Entrevistado','#2196f3'], aprovado: ['Aprovado','#4caf50'],
                contratado: ['Contratado','#00796b'], reprovado: ['Reprovado','#f44336'],
                arquivado: ['Arquivado','#9e9e9e'],
                sem_contato: ['Contato sem sucesso', '#ff1100'], faltou: ['Faltou na Entrevista', '#8d0900']
            };
            var b = badgeMap[status];
            if (b) {
                var badge = document.querySelector('.header-strip span[style]');
                if (badge) {
                    badge.textContent = b[0];
                    badge.style.background = b[1];
                }
            }
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(function () { alert('Erro de conexão.'); });
}

/* ─── Salvar Entrevista ─── */
function salvarEntrevista(id) {
    var btn  = document.getElementById('btnSalvarEntrevista');
    var feed = document.getElementById('saveFeedback');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando...';

    var params = new URLSearchParams({
        id:                   id,
        data_entrevista:      document.getElementById('ipt_data').value,
        local_entrevista:     document.getElementById('ipt_local').value,
        obs_entrevista:       document.getElementById('ipt_obs').value,
        resultado_entrevista: document.getElementById('ipt_resultado').value,
        csrf_token:           CSRF
    });

    fetch('index.php?section=selecao&action=entrevista_selecao', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    params.toString()
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            feed.classList.add('show');
            setTimeout(function () { feed.classList.remove('show'); }, 2500);

            /* Se agendou entrevista, atualiza pipeline visual */
            var dataVal = document.getElementById('ipt_data').value;
            if (dataVal) {
                var statusAtual = (document.querySelector('.pipeline-btn.ativo') || {}).className || '';
                if (statusAtual.includes('pb-novo') || statusAtual.includes('pb-em_analise') || !statusAtual) {
                    alterarStatusSilencioso('entrevista_agendada');
                }
            }
        } else {
            alert('Erro ao salvar: ' + data.message);
        }
    })
    .catch(function () { alert('Erro de conexão ao salvar entrevista.'); })
    .finally(function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-regular fa-floppy-disk"></i> Salvar entrevista';
    });
}

/* Atualiza visual do pipeline sem confirm */
function alterarStatusSilencioso(status) {
    document.querySelectorAll('.pipeline-btn').forEach(function (b) { b.classList.remove('ativo'); });
    var ativo = document.querySelector('.pb-' + status);
    if (ativo) ativo.classList.add('ativo');
}
</script>
</body>
</html>