<?php
$empresas = ['BELMAX S/A', 'GRIFFUS SA', 'BIOPACK LTDA', 'GRIFFUSONLINE LTDA'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vagas Disponíveis — Admin</title>
    <link rel="shortcut icon" href="assets/images/icone.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pagination.css">
    <style>
        /* ── Tabela ── */
        .vagas-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .vagas-table thead tr { background: #f8f9fa; }
        .vagas-table th {
            padding: 12px 14px; text-align: left;
            font-weight: 600; color: #555; border-bottom: 2px solid #eee;
        }
        .vagas-table td {
            padding: 11px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle;
        }
        .vagas-table tr:hover td { background: #fff8fb; }

        .empresa-tag {
            display: inline-block; background: #f3e5f5; color: #7b1fa2;
            font-size: .78rem; font-weight: 700; padding: 2px 10px; border-radius: 20px;
        }

        /* ── Tabs ── */
        .tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .tab-btn {
            padding: 8px 18px; border: 1px solid #e0e0e0; border-radius: 20px;
            background: #fff; color: #666; cursor: pointer; font-size: .88rem;
            font-weight: 600; transition: all .2s; font-family: inherit;
        }
        .tab-btn:hover { border-color: #e91e63; color: #e91e63; }
        .tab-btn.active { background: #e91e63; color: #fff; border-color: #e91e63; }

        /* ── Filtros ── */
        .filters input, .filters select {
            padding: 9px 13px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .9rem; color: #333; outline: none; font-family: inherit;
        }
        .filters input:focus, .filters select:focus { border-color: #e91e63; }
        .filters input { flex: 1; min-width: 200px; }

        /* ── Toggle ── */
        .toggle-wrap { display: flex; align-items: center; gap: .5rem; }
        .toggle { position: relative; display: inline-block; width: 38px; height: 22px; }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .toggle .slider {
            position: absolute; inset: 0; background: #ccc; border-radius: 22px;
            cursor: pointer; transition: background .2s;
        }
        .toggle .slider:before {
            content: ''; position: absolute; width: 16px; height: 16px;
            left: 3px; bottom: 3px; background: #fff; border-radius: 50%;
            transition: transform .2s;
        }
        .toggle input:checked + .slider { background: #4caf50; }
        .toggle input:checked + .slider:before { transform: translateX(16px); }
        .toggle-label { font-size: .8rem; color: #666; min-width: 60px; }

        /* ── Ações ── */
        .icon-btn { background: none; border: none; cursor: pointer; padding: 6px 9px; border-radius: 6px; color: #666; transition: all .2s; font-size: .95rem; }
        .icon-btn:hover { background: #f0f0f0; color: #e91e63; }
        .icon-btn.danger:hover { background: #ffebee; color: #e53935; }
        .actions { display: flex; gap: 4px; }

        .empty-state {
            text-align: center; padding: 50px 20px; color: #888;
            background: #fff; border-radius: 12px; border: 1px dashed #ddd;
        }
        .empty-state i { font-size: 2rem; display: block; margin-bottom: .5rem; }

        /* ── Modal ── */
        .modal-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 1000;
            justify-content: center; align-items: center;
        }
        .modal-backdrop.open { display: flex; }
        .modal-backdrop.open .modal {
            display: block; position: relative; inset: auto;
        }
        .modal {
            background: #fff; border-radius: 12px; padding: 30px;
            max-width: 500px; width: 95%;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
            position: relative;
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
        .modal-field {
            display: flex; flex-direction: column; gap: 4px; margin-bottom: 14px;
        }
        .modal-field label {
            font-size: .82rem; font-weight: 600; color: #555;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .modal-field input, .modal-field select {
            padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .9rem; outline: none; font-family: inherit; width: 100%;
            box-sizing: border-box;
        }
        .modal-field input:focus, .modal-field select:focus {
            border-color: #e91e63;
            box-shadow: 0 0 0 3px rgba(233,30,99,.12);
        }
        .modal-actions { display: flex; gap: 10px; margin-top: 8px; }
        .modal-actions button { flex: 1; }
        .btn-modal-save {
            background: linear-gradient(90deg, #e91e63, #ff4fa3); color: #fff;
            border: none; padding: 11px 20px; border-radius: 8px;
            font-size: .9rem; font-weight: 600; cursor: pointer; transition: opacity .2s;
        }
        .btn-modal-save:hover { opacity: .9; }
        .btn-modal-cancel {
            background: #f5f5f5; color: #555; border: 1px solid #e0e0e0;
            padding: 11px 20px; border-radius: 8px;
            font-size: .9rem; font-weight: 600; cursor: pointer;
        }
        .btn-modal-cancel:hover { background: #eee; }

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
        <a href="index.php?section=contratos"       class="sidebar__item">Contratos</a>
        <a href="index.php?section=curriculos"      class="sidebar__item">Currículos</a>
        <a href="index.php?section=selecao"         class="sidebar__item">Fichas de Seleção</a>
        <a href="index.php?section=vagas"           class="sidebar__item is-active">Vagas Disponíveis</a>
        <a href="index.php?section=funcionarios"     class="sidebar__item">Funcionários</a>
        <a href="index.php?section=aniversariantes" class="sidebar__item">Aniversariantes</a>
    </nav>
    <div class="sidebar__footer">
        <a href="../logout.php" class="sidebar__logout">Sair</a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <div>
            <h1>Vagas Disponíveis</h1>
            <p>Gerencie as vagas abertas da empresa</p>
        </div>
        <button class="btn-primary" onclick="openModal()">
            <i class="fa-solid fa-plus"></i> Nova Vaga
        </button>
    </div>

    <!-- Tabs Empresas -->
    <div class="tabs">
        <button class="tab-btn active" onclick="setTab('todas')">Todas</button>
        <?php foreach ($empresas as $emp): ?>
            <button class="tab-btn" onclick="setTab('<?= strtolower(htmlspecialchars($emp)) ?>')"><?= htmlspecialchars($emp) ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Filtros -->
    <div class="filters">
        <input type="text" id="filtroCargo" placeholder="Buscar por cargo..." oninput="filtrar()">
        <select id="filtroEmpresa" onchange="filtrar()">
            <option value="">Todas as Empresas</option>
            <?php foreach ($empresas as $emp): ?>
                <option value="<?= strtolower(htmlspecialchars($emp)) ?>"><?= htmlspecialchars($emp) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="filtroStatus" onchange="filtrar()">
            <option value="">Todas</option>
            <option value="1">Ativas</option>
            <option value="0">Inativas</option>
        </select>
    </div>

    <?php if (empty($vagas)) : ?>
        <div class="empty-state">
            <i class="fa-solid fa-briefcase"></i>
            <p>Nenhuma vaga cadastrada ainda.</p>
        </div>
    <?php else : ?>
        <div class="card table-card">
            <table class="vagas-table" id="tabelaVagas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Empresa</th>
                        <th>Cargo / Vaga</th>
                        <th>Cadastrada em</th>
                        <th>Disponível</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($vagas as $v): ?>
                <tr id="row-<?= (int)$v['id'] ?>"
                    data-empresa="<?= strtolower(htmlspecialchars($v['empresa'])) ?>"
                    data-cargo="<?= strtolower(htmlspecialchars($v['cargo'])) ?>"
                    data-ativo="<?= $v['ativo'] ? '1' : '0' ?>">
                    <td style="color:#aaa;font-size:.8rem"><?= (int)$v['id'] ?></td>
                    <td><span class="empresa-tag"><?= htmlspecialchars($v['empresa']) ?></span></td>
                    <td style="font-weight:600"><?= htmlspecialchars($v['cargo']) ?></td>
                    <td style="color:#888;font-size:.82rem">
                        <?= date('d/m/Y', strtotime($v['created_at'])) ?>
                    </td>
                    <td>
                        <div class="toggle-wrap">
                            <label class="toggle">
                                <input type="checkbox"
                                    <?= $v['ativo'] ? 'checked' : '' ?>
                                    onchange="toggleVaga(<?= (int)$v['id'] ?>, this.checked)">
                                <span class="slider"></span>
                            </label>
                            <span class="toggle-label" id="label-<?= (int)$v['id'] ?>">
                                <?= $v['ativo'] ? 'Ativa' : 'Inativa' ?>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="icon-btn danger" onclick="excluirVaga(<?= (int)$v['id'] ?>)" title="Excluir vaga">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div id="paginationBar"></div>
        </div>
    <?php endif; ?>
</main>

<!-- Modal Nova Vaga -->
<div class="modal-backdrop" id="modalBackdrop">
    <div class="modal">
        <button class="modal-close" id="modalClose">&times;</button>
        <h3><i class="fa-solid fa-briefcase" style="margin-right:8px;"></i>Nova Vaga</h3>

        <div class="modal-field">
            <label>Empresa</label>
            <select id="novaEmpresa">
                <option value="">Selecione...</option>
                <?php foreach ($empresas as $emp): ?>
                    <option value="<?= htmlspecialchars($emp) ?>"><?= htmlspecialchars($emp) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="modal-field">
            <label>Cargo / Vaga</label>
            <input type="text" id="novoCargo" placeholder="Ex: Auxiliar Administrativo" maxlength="120">
        </div>

        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeModal()">Cancelar</button>
            <button class="btn-modal-save" id="btnSalvar" onclick="cadastrarVaga()">
                <i class="fa-solid fa-save" style="margin-right:6px;"></i> Cadastrar
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="assets/js/pagination.js"></script>
<script>
"use strict";
const baseUrl = 'index.php?section=vagas';

/* ── Toast ── */
function toast(msg, type) {
    const container = document.getElementById('toastContainer');
    const div = document.createElement('div');
    div.className = 'toast-msg ' + (type || 'success');
    div.textContent = msg;
    container.appendChild(div);
    setTimeout(() => div.remove(), 3200);
}

/* ── Modal ── */
function openModal() {
    document.getElementById('modalBackdrop').classList.add('open');
    document.getElementById('novaEmpresa').value = '';
    document.getElementById('novoCargo').value = '';
}
function closeModal() {
    document.getElementById('modalBackdrop').classList.remove('open');
}
document.getElementById('modalClose').addEventListener('click', closeModal);
document.getElementById('modalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

/* ── Cadastrar ── */
async function cadastrarVaga() {
    const empresa = document.getElementById('novaEmpresa').value.trim();
    const cargo   = document.getElementById('novoCargo').value.trim();

    if (!empresa || !cargo) { toast('Preencha empresa e cargo.', 'error'); return; }

    const btn = document.getElementById('btnSalvar');
    btn.disabled = true;

    const fd = new FormData();
    fd.append('empresa', empresa);
    fd.append('cargo', cargo);

    try {
        const res  = await fetch(baseUrl + '&action=store', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) { toast(data.message, 'error'); return; }
        toast('Vaga cadastrada com sucesso!');
        closeModal();
        setTimeout(() => location.reload(), 800);
    } catch (e) {
        toast('Erro ao cadastrar', 'error');
    } finally {
        btn.disabled = false;
    }
}

/* ── Toggle ── */
async function toggleVaga(id, ativo) {
    const fd = new FormData();
    fd.append('id', id);
    fd.append('ativo', ativo ? '1' : '0');

    const res = await fetch(baseUrl + '&action=toggle', { method: 'POST', body: fd });
    const data = await res.json();

    if (!data.success) { toast('Erro ao atualizar vaga.', 'error'); return; }

    const label = document.getElementById('label-' + id);
    if (label) label.textContent = ativo ? 'Ativa' : 'Inativa';

    const row = document.getElementById('row-' + id);
    if (row) row.setAttribute('data-ativo', ativo ? '1' : '0');

    toast(ativo ? 'Vaga ativada.' : 'Vaga desativada.');
}

/* ── Excluir ── */
async function excluirVaga(id) {
    if (!confirm('Deseja excluir esta vaga permanentemente?')) return;

    const fd = new FormData();
    fd.append('id', id);

    const res = await fetch(baseUrl + '&action=delete_vaga', { method: 'POST', body: fd });
    const data = await res.json();

    if (!data.success) { toast('Erro ao excluir.', 'error'); return; }

    const row = document.getElementById('row-' + id);
    if (row) row.remove();
    toast('Vaga excluída.');
}

/* ── Filtro ── */
let pager;
function filtrar() {
    if (pager) pager.reset();
    const texto   = document.getElementById('filtroCargo').value.toLowerCase().trim();
    const empresa = document.getElementById('filtroEmpresa').value;
    const status  = document.getElementById('filtroStatus').value;

    document.querySelectorAll('#tabelaVagas tbody tr').forEach(function(tr) {
        const ok =
            (!texto   || (tr.getAttribute('data-cargo')   || '').indexOf(texto) > -1) &&
            (!empresa || tr.getAttribute('data-empresa') === empresa) &&
            (!status  || tr.getAttribute('data-ativo')   === status);
        tr.style.display = ok ? '' : 'none';
    });
    if (pager) pager.apply();
}
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('tabelaVagas')) {
        pager = new TablePaginator({ tableId: 'tabelaVagas', containerId: 'paginationBar' });
        pager.apply();
    }
});

/* ── Tabs ── */
function setTab(val) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('filtroEmpresa').value = val === 'todas' ? '' : val;
    filtrar();
}
</script>
</body>
</html>
