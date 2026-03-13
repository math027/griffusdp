<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('statusBadgeCv')) {
    function statusBadgeCv(string $status): string {
        if (empty($status)) $status = 'novo';
        $map = [
            'novo'           => ['label' => 'Novo',            'color' => '#e91e63'],
            'em_analise'     => ['label' => 'Em Análise',      'color' => '#ff9800'],
            'ficha_enviada'  => ['label' => 'Ficha Enviada',   'color' => '#9c27b0'],
            'aprovado'       => ['label' => 'Aprovado',        'color' => '#4caf50'],
            'rejeitado'      => ['label' => 'Rejeitado',       'color' => '#f44336'],
            'banco_talentos' => ['label' => 'Banco de Talentos','color' => '#5c6bc0'],
        ];
        $s = $map[$status] ?? ['label' => ucfirst($status), 'color' => '#757575'];
        return sprintf(
            '<span style="background:%s;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.78rem;font-weight:600;">%s</span>',
            $s['color'], $s['label']
        );
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currículos — Admin</title>
    <link rel="shortcut icon" href="assets/images/icone.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── Tabela ── */
        .cv-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .cv-table thead tr { background: #f8f9fa; }
        .cv-table th {
            padding: 12px 14px; text-align: left;
            font-weight: 600; color: #555; border-bottom: 2px solid #eee;
        }
        .cv-table td {
            padding: 11px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle;
        }
        .cv-table tr:hover td { background: #fff8fb; }
        .primary   { font-weight: 600; color: #212121; }
        .secondary { font-size: 0.82rem; color: #888; margin-top: 2px; }

        /* ── Filtros ── */
        .filters { display: flex; gap: 14px; margin-bottom: 20px; flex-wrap: wrap; }
        .filters input, .filters select {
            padding: 9px 13px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .9rem; color: #333; outline: none; font-family: inherit;
        }
        .filters input:focus, .filters select:focus { border-color: #e91e63; }
        .filters input { flex: 1; min-width: 200px; }

        /* ── Ações ── */
        .icon-btn {
            background: none; border: none; cursor: pointer;
            padding: 6px 9px; border-radius: 6px; color: #666;
            transition: all .2s; font-size: .95rem;
            text-decoration: none; display: inline-flex; align-items: center;
        }
        .icon-btn:hover { background: #f0f0f0; color: #e91e63; }
        .icon-btn.danger:hover { background: #ffebee; color: #e53935; }
        .icon-btn.whatsapp:hover { background: #e8f5e9; color: #25d366; }
        .actions { display: flex; gap: 4px; }
        .empty-state {
            text-align: center; padding: 50px 20px; color: #888;
            background: #fff; border-radius: 12px; border: 1px dashed #ddd;
        }

        /* ── Tabs ── */
        .tabs { display: flex; gap: 8px; margin-bottom: 20px; }
        .tab-btn {
            padding: 8px 18px; border: 1px solid #e0e0e0; border-radius: 20px;
            background: #fff; color: #666; cursor: pointer; font-size: .88rem;
            font-weight: 600; transition: all .2s; font-family: inherit;
        }
        .tab-btn:hover { border-color: #e91e63; color: #e91e63; }
        .tab-btn.active { background: #e91e63; color: #fff; border-color: #e91e63; }

        /* ── Modal ── */
        .modal-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 1000;
            justify-content: center; align-items: center;
        }
        .modal-backdrop.open { display: flex; }
        .modal {
            display: block !important;
            background: #fff; border-radius: 12px; padding: 30px;
            max-width: 600px; width: 95%; max-height: 88vh;
            overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.25);
            position: relative !important;
            inset: unset !important;
            margin: auto;
        }
        .modal h3 {
            color: #e91e63; font-size: 1.1rem; margin-bottom: 18px;
            border-bottom: 2px solid #f8bbd0; padding-bottom: 8px;
        }
        .modal-close {
            position: absolute; top: 14px; right: 18px;
            background: none; border: none; font-size: 1.5rem;
            cursor: pointer; color: #888; line-height: 1;
        }
        .modal-close:hover { color: #e91e63; }

        /* ── CV Preview Modal ── */
        .cv-preview-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.7); z-index: 2000;
            justify-content: center; align-items: center; padding: 20px;
        }
        .cv-preview-backdrop.open { display: flex; }
        .cv-preview-box {
            background: #fff; border-radius: 12px; width: 100%; max-width: 900px;
            height: 90vh; display: flex; flex-direction: column;
            box-shadow: 0 20px 60px rgba(0,0,0,.3); overflow: hidden;
        }
        .cv-preview-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 20px; background: #f8f9fa; border-bottom: 1px solid #eee;
        }
        .cv-preview-header h4 {
            margin: 0; font-size: 1rem; color: #333;
            display: flex; align-items: center; gap: 8px;
        }
        .cv-preview-header h4 i { color: #e91e63; }
        .cv-preview-actions { display: flex; gap: 8px; }
        .cv-preview-actions button, .cv-preview-actions a {
            padding: 8px 16px; border: none; border-radius: 8px;
            font-size: .85rem; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; gap: 6px; font-family: inherit;
            text-decoration: none;
        }
        .btn-cv-download { background: #e91e63; color: #fff; }
        .btn-cv-download:hover { background: #c2185b; }
        .btn-cv-close { background: #f5f5f5; color: #666; border: 1px solid #ddd !important; }
        .btn-cv-close:hover { background: #eee; color: #333; }
        .cv-preview-frame { flex: 1; border: none; width: 100%; }
        .cv-no-preview {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            justify-content: center; gap: 16px; color: #888; text-align: center; padding: 40px;
        }
        .cv-no-preview i { font-size: 3rem; color: #ccc; }

        /* ── Link section ── */
        .link-box {
            margin: 14px 0; padding: 14px; background: #f8f9fa;
            border: 1px solid #e0e0e0; border-radius: 8px;
        }
        .link-box input {
            width: 100%; padding: 10px; border: 1px solid #ddd;
            border-radius: 6px; font-size: .88rem; margin-bottom: 10px;
            box-sizing: border-box;
        }
        .link-box textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd;
            border-radius: 6px; font-size: .85rem; min-height: 120px;
            box-sizing: border-box; resize: vertical; font-family: inherit;
        }
        .link-actions { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .btn-copy, .btn-whatsapp {
            padding: 9px 18px; border: none; border-radius: 8px;
            font-size: .88rem; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; gap: 6px; font-family: inherit;
        }
        .btn-copy { background: #e3f2fd; color: #1565c0; }
        .btn-copy:hover { background: #bbdefb; }
        .btn-whatsapp { background: #25d366; color: #fff; }
        .btn-whatsapp:hover { opacity: .9; }

        /* ── Pipeline ── */
        .pipeline { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
        .pipeline-btn {
            padding: 6px 13px; border: 2px solid transparent;
            border-radius: 20px; cursor: pointer;
            font-size: .82rem; font-weight: 600; transition: all .15s;
            font-family: inherit;
        }
        .pipeline-btn:hover { filter: brightness(.9); }
        .pipeline-btn.ativo { border-color: #333 !important; box-shadow: 0 0 0 1px #333; }
        .pb-em_analise      { background:#fff3e0; color:#e65100; }
        .pb-ficha_enviada   { background:#f3e5f5; color:#6a1b9a; }
        .pb-aprovado        { background:#e8f5e9; color:#2e7d32; }
        .pb-rejeitado       { background:#ffebee; color:#c62828; }
        .pb-banco_talentos  { background:#e8eaf6; color:#283593; }

        /* ── Toast ── */
        .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; }
        .toast-msg {
            padding: 12px 20px; border-radius: 10px; font-size: 13px;
            color: #fff; margin-top: 8px;
            animation: toastIn .3s ease, toastOut .3s ease 2.7s forwards;
        }
        .toast-msg.success { background: #2e7d32; }
        .toast-msg.error   { background: #c62828; }
        @keyframes toastIn  { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes toastOut { from { opacity: 1; } to { opacity: 0; } }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar__logo">GRIFFUS<span>DP</span></div>
    <nav class="sidebar__menu">
        <a href="index.php?section=dashboard"       class="sidebar__item">Dashboard</a>
        <a href="index.php?section=contratos"        class="sidebar__item">Contratos</a>
        <a href="index.php?section=curriculos"       class="sidebar__item is-active">Currículos</a>
        <a href="index.php?section=selecao"          class="sidebar__item">Fichas de Seleção</a>
        <a href="index.php?section=vagas"            class="sidebar__item">Vagas Disponíveis</a>
        <a href="index.php?section=funcionarios"     class="sidebar__item">Funcionários</a>
        <a href="index.php?section=aniversariantes"  class="sidebar__item">Aniversariantes</a>
    </nav>
    <div class="sidebar__footer">
        <a href="../logout.php" class="sidebar__logout">Sair</a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <div>
            <h1>Currículos</h1>
            <p>Gerencie os currículos recebidos e o banco de talentos</p>
        </div>
        <a class="btn-primary" href="../curriculos/" target="_blank">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir Formulário
        </a>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="setTab('todos')">Todos</button>
        <button class="tab-btn" onclick="setTab('novo')">Novos</button>
        <button class="tab-btn" onclick="setTab('em_analise')">Em Análise</button>
        <button class="tab-btn" onclick="setTab('ficha_enviada')">Ficha Enviada</button>
        <button class="tab-btn" onclick="setTab('banco_talentos')">
            <i class="fa-solid fa-gem" style="margin-right:4px;"></i> Banco de Talentos
        </button>
    </div>

    <div class="filters">
        <input type="text" id="filtroTexto" placeholder="Buscar por nome, cargo, cidade..." oninput="filtrar()">
        <select id="filtroStatus" onchange="filtrar()">
            <option value="">Todos os Status</option>
            <option value="novo">Novo</option>
            <option value="em_analise">Em Análise</option>
            <option value="ficha_enviada">Ficha Enviada</option>
            <option value="aprovado">Aprovado</option>
            <option value="rejeitado">Rejeitado</option>
            <option value="banco_talentos">Banco de Talentos</option>
        </select>
    </div>

    <?php if (empty($curriculos)) : ?>
        <div class="empty-state">
            <i class="fa-regular fa-folder-open" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
            <p>Nenhum currículo recebido ainda.</p>
        </div>
    <?php else : ?>
        <div class="card table-card">
            <table class="cv-table" id="tabelaCv">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Candidato</th>
                        <th>Cargo Desejado</th>
                        <th>Cidade</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($curriculos as $cv) : ?>
                    <tr data-nome="<?= e(strtolower($cv['nome_completo'] ?? '')) ?>"
                        data-cargo="<?= e(strtolower($cv['cargo_desejado'] ?? '')) ?>"
                        data-cidade="<?= e(strtolower($cv['cidade'] ?? '')) ?>"
                        data-status="<?= e($cv['status'] ?? 'novo') ?>">
                        <td>#<?= (int)$cv['id'] ?></td>
                        <td>
                            <div class="primary"><?= e($cv['nome_completo'] ?? '') ?></div>
                            <div class="secondary"><?= e($cv['email'] ?? '') ?></div>
                            <div class="secondary"><?= e($cv['telefone'] ?? '') ?></div>
                        </td>
                        <td style="font-weight:600;"><?= e($cv['cargo_desejado'] ?? '') ?></td>
                        <td><?= e($cv['cidade'] ?? '') ?></td>
                        <td><?= e(date('d/m/Y', strtotime($cv['created_at']))) ?></td>
                        <td><?= statusBadgeCv($cv['status'] ?? 'novo') ?></td>
                        <td>
                            <div class="actions">
                                <!-- Visualizar CV -->
                                <button class="icon-btn"
                                        onclick="visualizarCv(<?= (int)$cv['id'] ?>, '<?= e($cv['nome_completo'] ?? '') ?>')"
                                        title="Visualizar currículo">
                                    <i class="fa-regular fa-file-lines"></i>
                                </button>
                                <!-- Enviar Ficha de Seleção -->
                                <button class="icon-btn whatsapp"
                                        onclick="enviarFicha(<?= (int)$cv['id'] ?>)"
                                        title="Enviar ficha de seleção">
                                    <i class="fa-regular fa-paper-plane"></i>
                                </button>
                                <!-- Excluir -->
                                <button class="icon-btn danger"
                                        onclick="excluirCv(<?= (int)$cv['id'] ?>)"
                                        title="Excluir currículo">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
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

<!-- CV Preview Modal -->
<div class="cv-preview-backdrop" id="cvPreviewBackdrop">
    <div class="cv-preview-box">
        <div class="cv-preview-header">
            <h4><i class="fa-regular fa-file-lines"></i> <span id="cvPreviewTitle">Currículo</span></h4>
            <div class="cv-preview-actions">
                <a class="btn-cv-download" id="cvDownloadBtn" href="#" target="_blank">
                    <i class="fa-solid fa-download"></i> Baixar
                </a>
                <button class="btn-cv-close" onclick="fecharCvPreview()">
                    <i class="fa-solid fa-xmark"></i> Fechar
                </button>
            </div>
        </div>
        <div id="cvPreviewContent" style="flex:1;overflow:hidden;"></div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
const CSRF = <?= json_encode($csrfToken ?? '', JSON_HEX_TAG | JSON_HEX_AMP) ?>;
const baseUrl = 'index.php?section=curriculos';

/* ── Toast ── */
function toast(msg, type) {
    const c = document.getElementById('toastContainer');
    const d = document.createElement('div');
    d.className = 'toast-msg ' + (type || 'success');
    d.textContent = msg;
    c.appendChild(d);
    setTimeout(() => d.remove(), 3200);
}

/* ── Modal ── */
function openModal(html) {
    document.getElementById('modalBody').innerHTML = html;
    document.getElementById('modalBackdrop').classList.add('open');
}
function closeModal() {
    document.getElementById('modalBackdrop').classList.remove('open');
}
document.getElementById('modalClose').addEventListener('click', closeModal);
document.getElementById('modalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

/* ═══════════════ VISUALIZAR CV (preview modal) ═══════════════ */
function visualizarCv(id, nome) {
    const url = baseUrl + '&action=view_cv&id=' + id;
    document.getElementById('cvPreviewTitle').textContent = 'Currículo — ' + nome;
    document.getElementById('cvDownloadBtn').href = url;
    document.getElementById('cvDownloadBtn').setAttribute('download', '');

    // Tenta mostrar em iframe (funciona bem para PDF)
    const container = document.getElementById('cvPreviewContent');
    container.innerHTML = '<iframe src="' + url + '" class="cv-preview-frame" style="width:100%;height:100%;border:none;"></iframe>';

    // Fallback: se iframe falhar (DOC/DOCX), mostra mensagem
    const iframe = container.querySelector('iframe');
    iframe.onerror = function() {
        container.innerHTML = '<div class="cv-no-preview">' +
            '<i class="fa-regular fa-file-lines"></i>' +
            '<p>Visualização não disponível para este formato.<br>Clique em <strong>Baixar</strong> para abrir o arquivo.</p>' +
            '</div>';
    };

    document.getElementById('cvPreviewBackdrop').classList.add('open');
}
function fecharCvPreview() {
    document.getElementById('cvPreviewBackdrop').classList.remove('open');
    document.getElementById('cvPreviewContent').innerHTML = '';
}
document.getElementById('cvPreviewBackdrop').addEventListener('click', function(e) {
    if (e.target === this) fecharCvPreview();
});

/* ═══════════════ ENVIAR FICHA DE SELEÇÃO ═══════════════ */
var _whatsappNum = '';

async function enviarFicha(id) {
    const fd = new FormData();
    fd.append('id', id);
    fd.append('csrf_token', CSRF);

    try {
        const res  = await fetch(baseUrl + '&action=generate_token', { method: 'POST', body: fd });
        const data = await res.json();

        if (!data.success) {
            toast(data.message || 'Erro ao gerar link.', 'error');
            return;
        }

        const c = data.candidato;

        // Extrai o número do WhatsApp
        const matchNum = data.whatsapp_url.match(/wa\.me\/(\d+)/);
        _whatsappNum = matchNum ? matchNum[1] : '';

        // Constrói HTML do modal via DOM para evitar problemas de escaping
        const modalBody = document.getElementById('modalBody');
        modalBody.innerHTML = '';

        // Header
        const h3 = document.createElement('h3');
        h3.innerHTML = '<i class="fa-regular fa-paper-plane" style="margin-right:8px;"></i>Enviar Ficha de Seleção';
        modalBody.appendChild(h3);

        // Info
        const infos = [
            ['Candidato', c.nome_completo],
            ['Cargo', c.cargo_desejado],
            ['Telefone', c.telefone]
        ];
        infos.forEach(function(item) {
            const p = document.createElement('p');
            p.innerHTML = '<strong>' + item[0] + ':</strong> ' + esc(item[1]);
            modalBody.appendChild(p);
        });

        // Link box
        const linkBox = document.createElement('div');
        linkBox.className = 'link-box';

        // Label do link
        const lblLink = document.createElement('label');
        lblLink.style.cssText = 'font-size:.82rem;font-weight:600;color:#555;display:block;margin-bottom:6px;';
        lblLink.textContent = 'Link da Ficha (uso único · expira em 7 dias)';
        linkBox.appendChild(lblLink);

        // Input do link
        const inputLink = document.createElement('input');
        inputLink.type = 'text';
        inputLink.id = 'linkFicha';
        inputLink.value = data.link;
        inputLink.readOnly = true;
        inputLink.onclick = function() { this.select(); };
        linkBox.appendChild(inputLink);

        // Label da mensagem
        const lblMsg = document.createElement('label');
        lblMsg.style.cssText = 'font-size:.82rem;font-weight:600;color:#555;display:block;margin-bottom:6px;margin-top:12px;';
        lblMsg.textContent = 'Mensagem WhatsApp (editável)';
        linkBox.appendChild(lblMsg);

        // Textarea da mensagem
        const txtMsg = document.createElement('textarea');
        txtMsg.id = 'msgWhatsapp';
        txtMsg.value = data.mensagem;
        linkBox.appendChild(txtMsg);

        // Botões
        const linkActions = document.createElement('div');
        linkActions.className = 'link-actions';

        const btnCopy = document.createElement('button');
        btnCopy.className = 'btn-copy';
        btnCopy.innerHTML = '<i class="fa-regular fa-copy"></i> Copiar Link';
        btnCopy.onclick = copiarLink;
        linkActions.appendChild(btnCopy);

        const btnWa = document.createElement('button');
        btnWa.className = 'btn-whatsapp';
        btnWa.innerHTML = '<i class="fa-brands fa-whatsapp"></i> Enviar WhatsApp';
        btnWa.onclick = function() { enviarWhatsapp(); };
        linkActions.appendChild(btnWa);

        linkBox.appendChild(linkActions);
        modalBody.appendChild(linkBox);

        document.getElementById('modalBackdrop').classList.add('open');
        toast('Link gerado com sucesso!');

        // Atualiza a linha na tabela
        const btnRow = document.querySelector('button[onclick="enviarFicha(' + id + ')"]');
        if (btnRow) {
            const row = btnRow.closest('tr');
            if (row) row.setAttribute('data-status', 'ficha_enviada');
        }
    } catch (e) {
        toast('Erro de conexão.', 'error');
    }
}

function copiarLink() {
    const input = document.getElementById('linkFicha');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => toast('Link copiado!'));
}

function enviarWhatsapp() {
    const msg = document.getElementById('msgWhatsapp').value;
    const url = 'https://wa.me/' + _whatsappNum + '?text=' + encodeURIComponent(msg);
    window.open(url, '_blank');
}

/* ═══════════════ ALTERAR STATUS ═══════════════ */
async function alterarStatus(id, status) {
    const labels = {
        em_analise: 'Em Análise', ficha_enviada: 'Ficha Enviada',
        aprovado: 'Aprovado', rejeitado: 'Rejeitado', banco_talentos: 'Banco de Talentos'
    };
    if (!confirm('Alterar status para "' + (labels[status] || status) + '"?')) return;

    const fd = new FormData();
    fd.append('id', id);
    fd.append('status', status);
    fd.append('csrf_token', CSRF);

    try {
        const res  = await fetch(baseUrl + '&action=status', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            toast('Status atualizado!');
            setTimeout(() => location.reload(), 800);
        } else {
            toast(data.message || 'Erro ao atualizar.', 'error');
        }
    } catch (e) {
        toast('Erro de conexão.', 'error');
    }
}

/* ═══════════════ EXCLUIR ═══════════════ */
async function excluirCv(id) {
    if (!confirm('Excluir este currículo permanentemente?')) return;

    const fd = new FormData();
    fd.append('id', id);
    fd.append('csrf_token', CSRF);

    try {
        const res  = await fetch(baseUrl + '&action=delete', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            toast('Currículo excluído.');
            setTimeout(() => location.reload(), 800);
        } else {
            toast(data.message || 'Erro ao excluir.', 'error');
        }
    } catch (e) {
        toast('Erro de conexão.', 'error');
    }
}

/* ═══════════════ FILTRO ═══════════════ */
function filtrar() {
    const texto  = document.getElementById('filtroTexto').value.toLowerCase().trim();
    const status = document.getElementById('filtroStatus').value;

    document.querySelectorAll('#tabelaCv tbody tr').forEach(function(tr) {
        const ok =
            (!texto  || ['data-nome','data-cargo','data-cidade'].some(a => (tr.getAttribute(a)||'').indexOf(texto) > -1)) &&
            (!status || tr.getAttribute('data-status') === status);
        tr.style.display = ok ? '' : 'none';
    });
}

/* ═══════════════ TABS ═══════════════ */
function setTab(val) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');

    if (val === 'todos') {
        document.getElementById('filtroStatus').value = '';
    } else {
        document.getElementById('filtroStatus').value = val;
    }
    filtrar();
}

/* ── Utilitário ── */
function esc(v) {
    if (v == null || v === '') return '—';
    return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
</body>
</html>