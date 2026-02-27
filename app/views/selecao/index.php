<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge(string $status): string {
        // Garante que status vazio mostre "Novo"
        if (empty($status)) {
            $status = 'novo';
        }
        $map = [
            'novo'                 => ['label' => 'Novo',                'color' => '#e91e63'],
            'em_analise'           => ['label' => 'Em Análise',          'color' => '#ff9800'],
            'entrevista_agendada'  => ['label' => 'Entrevista Agendada', 'color' => '#9c27b0'],
            'entrevistado'         => ['label' => 'Entrevistado',        'color' => '#2196f3'],
            'aprovado'             => ['label' => 'Aprovado',            'color' => '#4caf50'],
            'contratado'           => ['label' => 'Contratado',          'color' => '#00796b'],
            'reprovado'            => ['label' => 'Reprovado',           'color' => '#f44336'],
            'arquivado'            => ['label' => 'Arquivado',           'color' => '#9e9e9e'],
        ];
        $s = $map[$status] ?? ['label' => ucfirst($status), 'color' => '#757575'];
        return sprintf(
            '<span style="background:%s;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.78rem;font-weight:600;">%s</span>',
            $s['color'],
            $s['label']
        );
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fichas de Seleção — Admin</title>
    <link rel="shortcut icon" href="assets/images/icone.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── Tabela ── */
        .selecao-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .selecao-table thead tr { background: #f8f9fa; }
        .selecao-table th {
            padding: 12px 14px; text-align: left;
            font-weight: 600; color: #555; border-bottom: 2px solid #eee;
        }
        .selecao-table td {
            padding: 11px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle;
        }
        .selecao-table tr:hover td { background: #fff8fb; }
        .primary   { font-weight: 600; color: #212121; }
        .secondary { font-size: 0.82rem; color: #888; margin-top: 2px; }
        .empresa-badge {
            display: inline-block; padding: 2px 9px; border-radius: 10px;
            font-size: 0.78rem; font-weight: 600; color: #fff;
        }
        .empresa-griffus { background: #e91e63; }
        .empresa-belmax  { background: #5b9bd5; }

        /* ── Modal ── */
        .modal-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 1000;
            justify-content: center; align-items: center;
        }
        .modal-backdrop.open { display: flex; }
        .modal {
            background: #fff; border-radius: 12px; padding: 30px;
            max-width: 700px; width: 95%; max-height: 88vh;
            overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.25);
            position: relative;
        }
        .modal h3 {
            color: #e91e63; font-size: 1.1rem; margin-bottom: 18px;
            border-bottom: 2px solid #f8bbd0; padding-bottom: 8px;
        }
        .modal p { margin: 7px 0; font-size: .9rem; line-height: 1.5; }
        .modal p strong { color: #555; min-width: 170px; display: inline-block; }
        .modal-close {
            position: absolute; top: 14px; right: 18px;
            background: none; border: none; font-size: 1.5rem;
            cursor: pointer; color: #888; line-height: 1;
        }
        .modal-close:hover { color: #e91e63; }
        .section-title-modal {
            font-size: .78rem; font-weight: 700; color: #e91e63;
            text-transform: uppercase; letter-spacing: 1px;
            margin: 18px 0 6px; padding-bottom: 4px;
            border-bottom: 1px solid #f8bbd0;
        }

        /* ── Pipeline de RH ── */
        .pipeline { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
        .pipeline-btn {
            padding: 6px 13px; border: 2px solid transparent;
            border-radius: 20px; cursor: pointer;
            font-size: .82rem; font-weight: 600; transition: all .15s;
        }
        .pipeline-btn:hover { filter: brightness(.9); }
        .pipeline-btn.ativo { border-color: #333 !important; box-shadow: 0 0 0 1px #333; }
        .pb-em_analise          { background:#fff3e0; color:#e65100; }
        .pb-entrevista_agendada { background:#f3e5f5; color:#6a1b9a; }
        .pb-entrevistado        { background:#e3f2fd; color:#1565c0; }
        .pb-aprovado            { background:#e8f5e9; color:#2e7d32; }
        .pb-contratado          { background:#e0f2f1; color:#00695c; }
        .pb-reprovado           { background:#ffebee; color:#c62828; }
        .pb-arquivado           { background:#f5f5f5; color:#616161; }

        /* ── Seção de entrevista ── */
        .interview-box {
            background: #f8f9fa; border: 1px solid #e0e0e0;
            border-radius: 8px; padding: 16px; margin-top: 8px;
        }
        .interview-box label {
            font-size: .82rem; color: #555; font-weight: 600;
            display: block; margin-bottom: 4px; margin-top: 10px;
        }
        .interview-box label:first-child { margin-top: 0; }
        .interview-box input[type="datetime-local"],
        .interview-box input[type="text"],
        .interview-box textarea,
        .interview-box select {
            width: 100%; padding: 8px 10px; border: 1px solid #ddd;
            border-radius: 6px; font-size: .88rem; box-sizing: border-box;
            font-family: inherit; background: #fff;
        }
        .interview-box input:focus,
        .interview-box textarea:focus,
        .interview-box select:focus { border-color: #e91e63; outline: none; }
        .interview-box textarea { resize: vertical; min-height: 75px; }
        .btn-save-interview {
            margin-top: 12px; background: #e91e63; color: #fff;
            border: none; padding: 9px 20px; border-radius: 7px;
            cursor: pointer; font-size: .88rem; font-weight: 600;
        }
        .btn-save-interview:hover { opacity: .9; }
        .save-feedback {
            display: inline-block; margin-left: 10px; font-size: .82rem;
            color: #2e7d32; font-weight: 600; opacity: 0; transition: opacity .3s;
        }
        .save-feedback.show { opacity: 1; }

        /* ── Filtros / cabeçalho ── */
        .filters { display: flex; gap: 14px; margin-bottom: 20px; flex-wrap: wrap; }
        .filters input, .filters select {
            padding: 9px 13px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .9rem; color: #333; outline: none;
        }
        .filters input:focus, .filters select:focus { border-color: #e91e63; }
        .filters input { flex: 1; min-width: 200px; }
        .page-header {
            display: flex; justify-content: space-between;
            align-items: flex-start; margin-bottom: 20px;
        }
        /* .page-header h1 { font-size: 1.4rem; color: #212121; } */
        .page-header p  { color: #888; font-size: .9rem; margin-top: 4px; }
        .icon-btn {
            background: none; border: none; cursor: pointer;
            padding: 6px 9px; border-radius: 6px; color: #666;
            transition: all .2s; font-size: .95rem;
            text-decoration: none; display: inline-flex; align-items: center;
        }
        .icon-btn:hover { background: #f0f0f0; color: #e91e63; }
        .icon-btn.danger:hover { background: #ffebee; color: #e53935; }
        .actions { display: flex; gap: 4px; }
        .empty-state {
            text-align: center; padding: 50px 20px; color: #888;
            background: #fff; border-radius: 12px; border: 1px dashed #ddd;
        }
        .pre-box {
            white-space: pre-wrap; background: #fafafa;
            padding: 10px; border-radius: 6px; border: 1px solid #eee;
            font-size: .85rem; margin: 4px 0 0;
        }

        /* ── Experiência cards no modal ── */
        .exp-list-modal { display: flex; flex-direction: column; gap: 8px; margin: 8px 0 6px; }
        .exp-card-modal { border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
        .exp-card-modal-header {
            display: flex; align-items: center; gap: 9px;
            background: #fdf2f6; padding: 8px 12px;
            border-bottom: 1px solid #f8bbd0;
        }
        .exp-num-modal {
            background: #e91e63; color: #fff;
            width: 20px; height: 20px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .7rem; font-weight: 700; flex-shrink: 0;
        }
        .exp-card-modal-header strong { font-size: .88rem; color: #212121; }
        .exp-cargo-modal {
            margin-left: auto; font-size: .77rem;
            color: #e91e63; font-weight: 600;
            background: #fff; padding: 2px 9px;
            border-radius: 10px; border: 1px solid #f8bbd0;
        }
        .exp-card-modal-body {
            padding: 8px 12px; display: flex; flex-wrap: wrap; gap: 6px 20px;
        }
        .exp-card-modal-body span { font-size: .82rem; color: #555; }

    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar__logo">GRIFFUS<span>DP</span></div>
    <nav class="sidebar__menu">
        <a href="index.php?section=dashboard" class="sidebar__item">Dashboard</a>
        <a href="index.php?section=contratos"  class="sidebar__item">Contratos</a>
        <a href="index.php?section=selecao"    class="sidebar__item is-active">Fichas de Seleção</a>
        <a href="index.php?section=vagas" class="sidebar__item">Vagas Disponíveis</a>
        <a href="index.php?section=funcionarios" class="sidebar__item">Funcionários</a>
        <a href="index.php?section=aniversariantes" class="sidebar__item">Aniversariantes</a>
    </nav>
    <div class="sidebar__footer">
        <a href="../logout.php" class="sidebar__logout">Sair</a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <div>
            <h1>Fichas de Seleção</h1>
            <p>Gerencie as candidaturas e o pipeline de RH</p>
        </div>
        <a class="btn-primary" href="../selecao/" target="_blank">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir Formulário
        </a>
    </div>

    <div class="filters">
        <input type="text" id="filtroTexto" placeholder="Buscar por nome, cargo, CPF..." oninput="filtrar()">
        <select id="filtroEmpresa" onchange="filtrar()">
            <option value="">Todas as Empresas</option>
            <option value="Griffus">Griffus</option>
            <option value="Belmax">Belmax</option>
        </select>
        <select id="filtroStatus" onchange="filtrar()">
            <option value="">Todos os Status</option>
            <option value="novo">Novo</option>
            <option value="em_analise">Em Análise</option>
            <option value="entrevista_agendada">Entrevista Agendada</option>
            <option value="entrevistado">Entrevistado</option>
            <option value="aprovado">Aprovado</option>
            <option value="contratado">Contratado</option>
            <option value="reprovado">Reprovado</option>
            <option value="arquivado">Arquivado</option>
        </select>
    </div>

    <?php if (empty($fichas)) : ?>
        <div class="empty-state">
            <i class="fa-regular fa-folder-open" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
            <p>Nenhuma ficha de seleção encontrada.</p>
        </div>
    <?php else : ?>
        <div class="card table-card">
            <table class="selecao-table" id="tabelaFichas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Candidato</th>
                        <th>Empresa / Cargo</th>
                        <th>Contato</th>
                        <th>Data</th>
                        <th>Entrevista</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($fichas as $f) : ?>
                    <tr data-nome="<?= e(strtolower($f['nome_completo'] ?? '')) ?>"
                        data-cargo="<?= e(strtolower($f['cargo'] ?? '')) ?>"
                        data-cpf="<?= e($f['cpf'] ?? '') ?>"
                        data-empresa="<?= e($f['empresa'] ?? '') ?>"
                        data-status="<?= e($f['status'] ?? '') ?>">
                        <td>#<?= (int)$f['id'] ?></td>
                        <td>
                            <div class="primary"><?= e($f['nome_completo'] ?? '') ?></div>
                            <div class="secondary"><?= e($f['cpf'] ?? '') ?></div>
                        </td>
                        <td>
                            <span class="empresa-badge empresa-<?= strtolower(e($f['empresa'] ?? '')) ?>">
                                <?= e($f['empresa'] ?? '') ?>
                            </span>
                            <div class="secondary" style="margin-top:4px;"><?= e($f['cargo'] ?? '') ?></div>
                        </td>
                        <td>
                            <div><?= e($f['celular'] ?? '') ?></div>
                            <div class="secondary"><?= e($f['email'] ?? '') ?></div>
                        </td>
                        <td><?= e($f['data_inscricao'] ?? '') ?></td>
                        <td>
                            <?php if (!empty($f['data_entrevista'])) : ?>
                                <div style="font-size:.82rem;color:#6a1b9a;font-weight:600;">
                                    <i class="fa-regular fa-calendar-check"></i>
                                    <?= e(date('d/m/Y H:i', strtotime($f['data_entrevista']))) ?>
                                </div>
                                <?php if (!empty($f['local_entrevista'])) : ?>
                                    <div class="secondary"><?= e($f['local_entrevista']) ?></div>
                                <?php endif; ?>
                            <?php else : ?>
                                <span style="color:#bbb;font-size:.82rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= statusBadge($f['status'] ?? 'novo') ?></td>
                        <td>
                            <div class="actions">
                                <a class="icon-btn"
                                   href="index.php?section=selecao&action=view_selecao&id=<?= (int)$f['id'] ?>"
                                   target="_blank"
                                   title="Visualizar detalhes">
                                    <i class="fa-regular fa-eye"></i>
                                </a>
                                <form method="post"
                                      action="index.php?section=selecao&action=delete_selecao"
                                      onsubmit="return confirm('Excluir esta ficha permanentemente?');"
                                      style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken ?? '') ?>">
                                    <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                                    <button class="icon-btn danger" type="submit" title="Excluir">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<!-- Modal -->
<div class="modal-backdrop" id="modalBackdrop">
    <div class="modal">
        <button class="modal-close" id="modalClose" aria-label="Fechar">&times;</button>
        <div id="modalBody"></div>
    </div>
</div>

<!--
    DADOS DAS FICHAS
    Emitidos como objeto JS — zero duplo escape, zero problema de encoding.
    json_encode com JSON_HEX_TAG e JSON_HEX_AMP protege contra XSS inline.
-->
<script>
const FICHAS = <?= json_encode(
    array_column($fichas ?? [], null, 'id'),
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
) ?>;
const CSRF = <?= json_encode($csrfToken ?? '', JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>

<script>
"use strict";

/* ── Utilitário: escape HTML para exibição ── */
function esc(v) {
    if (v == null || v === '') return '—';
    return String(v)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}


/* ── Constrói HTML das experiências (JSON) ── */
function buildExperiencias(raw) {
    var arr = null;
    try { arr = JSON.parse(raw); } catch(e) {}
    if (!Array.isArray(arr) || arr.length === 0) {
        return '<div class="pre-box"><em style="color:#bbb;">Não informado</em></div>';
    }
    var html = '<div class="exp-list-modal">';
    arr.forEach(function(exp, i) {
        var admissao = exp.data_admissao
            ? exp.data_admissao.split('-').reverse().join('/') : null;
        var demissao = exp.data_demissao
            ? exp.data_demissao.split('-').reverse().join('/') : null;
        html += '<div class="exp-card-modal">';
        html += '<div class="exp-card-modal-header">';
        html += '<span class="exp-num-modal">' + (i+1) + '</span>';
        html += '<strong>' + esc(exp.empresa) + '</strong>';
        html += '<span class="exp-cargo-modal">' + esc(exp.cargo) + '</span>';
        html += '</div>';
        html += '<div class="exp-card-modal-body">';
        if (admissao || demissao) {
            html += '<span>&#128197; ' + (admissao || '?') + ' &rarr; ' + (demissao || '<em>atual</em>') + '</span>';
        }
        if (exp.ultimo_salario) {
            html += '<span>&#128176; ' + esc(exp.ultimo_salario) + '</span>';
        }
        if (exp.motivo_saida) {
            html += '<span>&#128172; ' + esc(exp.motivo_saida) + '</span>';
        }
        html += '</div></div>';
    });
    html += '</div>';
    return html;
}

/* ═══════════════ MODAL ═══════════════ */
(function () {
    var backdrop  = document.getElementById('modalBackdrop');
    var modalBody = document.getElementById('modalBody');
    var closeBtn  = document.getElementById('modalClose');

    function fecharModal() { backdrop.classList.remove('open'); }

    closeBtn.addEventListener('click', fecharModal);
    backdrop.addEventListener('click', function (ev) {
        if (ev.target === backdrop) fecharModal();
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') fecharModal();
    });

    /* ── Constrói HTML do modal a partir do objeto JS ── */
    function buildModal(d) {
        var statusAtual = d.status || 'novo';

        var pipeline = [
            ['em_analise',          'Em Análise'],
            ['entrevista_agendada', 'Entrevista Agendada'],
            ['entrevistado',        'Entrevistado'],
            ['aprovado',            'Aprovado'],
            ['contratado',          'Contratado'],
            ['reprovado',           'Reprovado'],
            ['arquivado',           'Arquivado'],
        ].map(function (item) {
            var ativo = statusAtual === item[0] ? ' ativo' : '';
            return '<button type="button" class="pipeline-btn pb-' + item[0] + ativo + '" '
                + 'onclick="alterarStatus(' + d.id + ',\'' + item[0] + '\')">'
                + item[1] + '</button>';
        }).join('');

        var dtEntrevista = d.data_entrevista
            ? d.data_entrevista.replace(' ', 'T').substring(0, 16)
            : '';

        var resultOpts = [
            ['', '— Selecione —'],
            ['pendente',              'Pendente'],
            ['aprovado_entrevista',   'Aprovado na entrevista'],
            ['reprovado_entrevista',  'Reprovado na entrevista'],
        ].map(function (o) {
            var sel = d.resultado_entrevista === o[0] ? ' selected' : '';
            return '<option value="' + o[0] + '"' + sel + '>' + o[1] + '</option>';
        }).join('');

        return ''
            + '<h3><i class="fa-regular fa-id-card" style="margin-right:8px;"></i>'
            + 'Ficha de Seleção #' + esc(d.id) + '</h3>'

            /* ── Vaga ── */
            + '<div class="section-title-modal">Vaga</div>'
            + '<p><strong>Empresa:</strong> '           + esc(d.empresa)         + '</p>'
            + '<p><strong>Cargo Pretendido:</strong> '  + esc(d.cargo)           + '</p>'
            + '<p><strong>Data de Inscrição:</strong> ' + esc(d.data_inscricao)  + '</p>'

            /* ── Dados Pessoais ── */
            + '<div class="section-title-modal">Dados Pessoais</div>'
            + '<p><strong>Nome:</strong> '              + esc(d.nome_completo)   + '</p>'
            + '<p><strong>CPF:</strong> '               + esc(d.cpf)             + '</p>'
            + '<p><strong>RG:</strong> '                + esc(d.rg)
                + (d.orgao_expedidor ? ' &mdash; ' + esc(d.orgao_expedidor) : '') + '</p>'
            + '<p><strong>Nascimento:</strong> '        + esc(d.data_nascimento) + '</p>'
            + '<p><strong>Estado Civil:</strong> '      + esc(d.estado_civil)    + '</p>'
            + '<p><strong>Naturalidade:</strong> '      + esc(d.naturalidade)    + '</p>'
            + '<p><strong>Nacionalidade:</strong> '     + esc(d.nacionalidade)   + '</p>'
            + '<p><strong>Filhos:</strong> '            + esc(d.possui_filhos)
                + (d.possui_filhos === 'sim' ? ' &mdash; ' + esc(d.qtd_filhos) + ' filho(s)' : '') + '</p>'
            + '<p><strong>CNH:</strong> '               + esc(d.possui_cnh)
                + (d.possui_cnh === 'sim' ? ' &mdash; Categoria ' + esc(d.categoria_cnh) : '') + '</p>'

            /* ── Endereço ── */
            + '<div class="section-title-modal">Endereço</div>'
            + '<p><strong>Logradouro:</strong> '        + esc(d.endereco) + ', ' + esc(d.numero)
                + (d.complemento ? ' &mdash; ' + esc(d.complemento) : '') + '</p>'
            + '<p><strong>Bairro:</strong> '            + esc(d.bairro)  + '</p>'
            + '<p><strong>Cidade / UF:</strong> '       + esc(d.cidade) + ' &mdash; ' + esc(d.uf) + '</p>'
            + '<p><strong>CEP:</strong> '               + esc(d.cep)    + '</p>'

            /* ── Contato ── */
            + '<div class="section-title-modal">Contato</div>'
            + '<p><strong>Celular:</strong> '           + esc(d.celular) + '</p>'
            + '<p><strong>E-mail:</strong> '            + esc(d.email)   + '</p>'

            /* ── Formação ── */
            + '<div class="section-title-modal">Formação &amp; Experiência</div>'
            + '<p><strong>Escolaridade:</strong> '      + esc(d.escolaridade) + '</p>'
            + '<p><strong>Curso:</strong> '             + esc(d.curso)        + '</p>'
            + '<p><strong>Experiência Profissional:</strong></p>'
            + buildExperiencias(d.experiencia)
            + '<p><strong>Habilidades:</strong></p>'
            + '<div class="pre-box">'   + esc(d.habilidades)  + '</div>'

            /* ── Entrevista ── */
            + '<div class="section-title-modal">Entrevista &amp; Processo Seletivo</div>'
            + '<div class="interview-box">'
            +   '<label>Data e hora da entrevista</label>'
            +   '<input type="datetime-local" id="ipt_data" value="' + esc(dtEntrevista) + '">'
            +   '<label>Local / Link da entrevista</label>'
            +   '<input type="text" id="ipt_local" placeholder="Ex: Sala 02 · meet.google.com/abc..." value="' + esc(d.local_entrevista || '') + '">'
            +   '<label>Observações</label>'
            +   '<textarea id="ipt_obs">' + esc(d.obs_entrevista || '') + '</textarea>'
            +   '<label>Resultado da entrevista</label>'
            +   '<select id="ipt_resultado">' + resultOpts + '</select>'
            +   '<div style="margin-top:12px;">'
            +     '<button type="button" class="btn-save-interview" onclick="salvarEntrevista(' + d.id + ')">'
            +       '<i class="fa-regular fa-floppy-disk"></i> Salvar entrevista'
            +     '</button>'
            +     '<span class="save-feedback" id="saveFeedback">✓ Salvo!</span>'
            +   '</div>'
            + '</div>'

            /* ── Pipeline ── */
            + '<div class="section-title-modal">Pipeline de RH</div>'
            + '<div class="pipeline">' + pipeline + '</div>';
    }

    /* ── Abre modal ── */
    document.querySelectorAll('.js-view-ficha').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = parseInt(btn.getAttribute('data-id'), 10);
            var d  = FICHAS[id];

            if (!d) {
                alert('Dados não encontrados para a ficha #' + id + '. Recarregue a página.');
                return;
            }

            modalBody.innerHTML = buildModal(d);
            backdrop.classList.add('open');
        });
    });
})();

/* ═══════════════ SALVAR ENTREVISTA ═══════════════ */
function salvarEntrevista(id) {
    var btn  = document.querySelector('.btn-save-interview');
    var feed = document.getElementById('saveFeedback');

    btn.disabled = true;
    btn.textContent = 'Salvando...';

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
            /* Atualiza o objeto em memória */
            FICHAS[id].data_entrevista      = document.getElementById('ipt_data').value;
            FICHAS[id].local_entrevista     = document.getElementById('ipt_local').value;
            FICHAS[id].obs_entrevista       = document.getElementById('ipt_obs').value;
            FICHAS[id].resultado_entrevista = document.getElementById('ipt_resultado').value;

            feed.classList.add('show');
            setTimeout(function () {
                feed.classList.remove('show');
                location.reload();
            }, 1200);
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

/* ═══════════════ ALTERAR STATUS ═══════════════ */
function alterarStatus(id, status) {
    var labels = {
        em_analise: 'Em Análise', entrevista_agendada: 'Entrevista Agendada',
        entrevistado: 'Entrevistado', aprovado: 'Aprovado',
        contratado: 'Contratado', reprovado: 'Reprovado', arquivado: 'Arquivado'
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
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(function () { alert('Erro de conexão.'); });
}

/* ═══════════════ FILTRO CLIENT-SIDE ═══════════════ */
function filtrar() {
    var texto   = document.getElementById('filtroTexto').value.toLowerCase().trim();
    var empresa = document.getElementById('filtroEmpresa').value;
    var status  = document.getElementById('filtroStatus').value;

    document.querySelectorAll('#tabelaFichas tbody tr').forEach(function (tr) {
        var ok =
            (!texto   || ['data-nome','data-cargo','data-cpf'].some(function(a){ return (tr.getAttribute(a)||'').indexOf(texto) > -1; })) &&
            (!empresa || tr.getAttribute('data-empresa') === empresa) &&
            (!status  || tr.getAttribute('data-status')  === status);

        tr.style.display = ok ? '' : 'none';
    });
}
</script>
</body>
</html>