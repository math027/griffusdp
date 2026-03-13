<?php
if (!function_exists('e')) {
    function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
$MONTHS = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
           'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionários — Admin Griffus</title>
    <link rel="shortcut icon" href="assets/images/icone.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── Tabela ── */
        .func-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .func-table thead tr { background: #f8f9fa; }
        .func-table th {
            padding: 12px 14px; text-align: left;
            font-weight: 600; color: #555; border-bottom: 2px solid #eee;
        }
        .func-table td {
            padding: 11px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle;
        }
        .func-table tr:hover td { background: #fff8fb; }
        .primary   { font-weight: 600; color: #212121; }
        .secondary { font-size: 0.82rem; color: #888; margin-top: 2px; }

        /* ── Badges ── */
        .badge-tipo {
            display: inline-block; padding: 3px 10px; border-radius: 12px;
            font-size: 0.78rem; font-weight: 600; color: #fff;
        }
        .badge-clt { background: #4caf50; }
        .badge-pj  { background: #ff9800; }

        /* ── Filtros ── */
        .filters { display: flex; gap: 14px; margin-bottom: 20px; flex-wrap: wrap; }
        .filters input, .filters select {
            padding: 9px 13px; border: 1px solid #ddd; border-radius: 8px;
            font-size: .9rem; color: #333; outline: none; font-family: inherit;
        }
        .filters input:focus, .filters select:focus { border-color: #e91e63; }
        .filters input { flex: 1; min-width: 200px; }

        /* ── Botões ── */
        .icon-btn {
            background: none; border: none; cursor: pointer;
            padding: 6px 9px; border-radius: 6px; color: #666;
            transition: all .2s; font-size: .95rem;
        }
        .icon-btn:hover { background: #f0f0f0; color: #e91e63; }
        .icon-btn.danger:hover { background: #ffebee; color: #e53935; }
        .actions { display: flex; gap: 4px; }


        .empty-state {
            text-align: center; padding: 50px 20px; color: #888;
            background: #fff; border-radius: 12px; border: 1px dashed #ddd;
        }

        /* ── Modal ── */
        .modal-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 1000;
            justify-content: center; align-items: center;
        }
        .modal-backdrop.open { display: flex; }
        .modal-backdrop.open .modal {
            display: block;
            position: relative;
            inset: auto;
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
        .tipo-btns { display: flex; gap: 8px; }
        .tipo-btn {
            flex: 1; padding: 9px; border: 1.5px solid #e0e0e0; border-radius: 10px;
            background: #fff; font-size: .9rem; font-weight: 600;
            cursor: pointer; transition: all .2s; color: #666; text-align: center;
        }
        .tipo-btn.active-clt { border-color: #4caf50; background: #e8f5e9; color: #2e7d32; }
        .tipo-btn.active-pj  { border-color: #ff9800; background: #fff3e0; color: #e65100; }
        .modal-actions {
            display: flex; gap: 10px; margin-top: 8px;
        }
        .modal-actions button { flex: 1; }
        .btn-modal-save {
            background: linear-gradient(90deg, #e91e63, #ff4fa3); color: #fff;
            border: none; padding: 11px 20px; border-radius: 8px;
            font-size: .9rem; font-weight: 600; cursor: pointer;
            transition: opacity .2s;
        }
        .btn-modal-save:hover { opacity: .9; }
        .btn-modal-cancel {
            background: #f5f5f5; color: #555; border: 1px solid #e0e0e0;
            padding: 11px 20px; border-radius: 8px;
            font-size: .9rem; font-weight: 600; cursor: pointer;
        }
        .btn-modal-cancel:hover { background: #eee; }

        /* ── Toast ── */
        .toast-container {
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        }
        .toast-msg {
            padding: 12px 20px; border-radius: 10px; font-size: 13px;
            color: #fff; margin-top: 8px;
            animation: toastIn .3s ease, toastOut .3s ease 2.7s forwards;
        }
        .toast-msg.success { background: #2e7d32; }
        .toast-msg.error   { background: #c62828; }
        @keyframes toastIn  { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes toastOut { from { opacity: 1; } to { opacity: 0; } }

        /* ── Contagem ── */
        .count-badge {
            background: rgba(233,30,99,.1); color: #e91e63;
            border-radius: 20px; padding: 2px 10px;
            font-size: 12px; font-weight: 700; margin-left: 8px;
        }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar__logo">GRIFFUS<span>DP</span></div>
    <nav class="sidebar__menu">
        <a href="index.php?section=dashboard"       class="sidebar__item">Dashboard</a>
        <a href="index.php?section=contratos"        class="sidebar__item">Contratos</a>
        <a href="index.php?section=curriculos"       class="sidebar__item">Currículos</a>
        <a href="index.php?section=selecao"          class="sidebar__item">Fichas de Seleção</a>
        <a href="index.php?section=vagas"            class="sidebar__item">Vagas Disponíveis</a>
        <a href="index.php?section=funcionarios"     class="sidebar__item is-active">Funcionários</a>
        <a href="index.php?section=aniversariantes"  class="sidebar__item">Aniversariantes</a>
    </nav>
    <div class="sidebar__footer">
        <a href="../logout.php" class="sidebar__logout">Sair</a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <div>
            <h1>Funcionários</h1>
            <p>Cadastre e gerencie os funcionários da empresa</p>
        </div>
        <button class="btn-primary" onclick="openModal()">
            <i class="fa-solid fa-user-plus"></i> Novo Funcionário
        </button>
    </div>

    <!-- Filtros -->
    <div class="filters">
        <input type="text" id="filtroNome" placeholder="Buscar por nome..." oninput="filtrar()">
        <select id="filtroSetor" onchange="filtrar()">
            <option value="">Todos os Setores</option>
            <?php foreach ($setores as $s): ?>
                <option value="<?= e(strtolower((string)$s)); ?>"><?= e((string)$s); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="filtroMes" onchange="filtrar()">
            <option value="">Todos os Meses</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m; ?>"><?= $MONTHS[$m]; ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <?php if (empty($funcionarios)) : ?>
        <div class="empty-state">
            <i class="fa-regular fa-user" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
            <p>Nenhum funcionário cadastrado.</p>
        </div>
    <?php else : ?>
        <div class="card table-card">
            <table class="func-table" id="tabelaFunc">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Setor</th>
                        <th>Tipo</th>
                        <th>Aniversário</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($funcionarios as $f) : ?>
                    <tr data-nome="<?= e(strtolower($f['nome'])) ?>"
                        data-setor="<?= e(strtolower($f['setor'])) ?>"
                        data-mes="<?= (int)$f['mes'] ?>"
                        data-id="<?= (int)$f['id'] ?>"
                        data-tipo="<?= e($f['tipo']) ?>"
                        data-data="<?= e($f['data_aniversario']) ?>">
                        <td><?= (int)$f['id'] ?></td>
                        <td>
                            <div class="primary"><?= e($f['nome']) ?></div>
                        </td>
                        <td><?= e($f['setor']) ?></td>
                        <td>
                            <span class="badge-tipo badge-<?= strtolower($f['tipo']) ?>">
                                <?= e($f['tipo']) ?>
                            </span>
                        </td>
                        <td>
                            <?= str_pad((string)$f['dia'], 2, '0', STR_PAD_LEFT) ?>/<?= str_pad((string)$f['mes'], 2, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="icon-btn" title="Editar" onclick="editFunc(<?= (int)$f['id'] ?>)">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="icon-btn danger" title="Excluir" onclick="deleteFunc(<?= (int)$f['id'] ?>)">
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

<!-- Modal Adicionar / Editar -->
<div class="modal-backdrop" id="modalBackdrop">
    <div class="modal">
        <button class="modal-close" id="modalClose">&times;</button>
        <h3 id="modalTitle"><i class="fa-solid fa-user-plus" style="margin-right:8px;"></i>Novo Funcionário</h3>

        <input type="hidden" id="f-id">

        <div class="modal-field">
            <label>Nome do Funcionário</label>
            <input type="text" id="f-nome" placeholder="Ex: Ana Silva">
        </div>
        <div class="modal-field">
            <label>Setor</label>
            <input type="text" id="f-setor" placeholder="Ex: Logística">
        </div>
        <div class="modal-field">
            <label>Data de Aniversário</label>
            <input type="date" id="f-data">
        </div>
        <div class="modal-field">
            <label>Tipo</label>
            <div class="tipo-btns">
                <button type="button" class="tipo-btn active-clt" id="btn-clt" onclick="setTipo('CLT')">CLT</button>
                <button type="button" class="tipo-btn" id="btn-pj" onclick="setTipo('PJ')">PJ</button>
            </div>
            <input type="hidden" id="f-tipo" value="CLT">
        </div>

        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeModal()">Cancelar</button>
            <button class="btn-modal-save" id="btnSalvar" onclick="saveFunc()">
                <i class="fa-solid fa-save" style="margin-right:6px;"></i>
                <span id="btnSalvarLabel">Adicionar</span>
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
"use strict";

/* ── Toast ── */
function toast(msg, type) {
    const container = document.getElementById('toastContainer');
    const div = document.createElement('div');
    div.className = 'toast-msg ' + (type || '');
    div.textContent = msg;
    container.appendChild(div);
    setTimeout(() => div.remove(), 3200);
}

/* ── Tipo toggle ── */
let selectedTipo = 'CLT';
function setTipo(tipo) {
    selectedTipo = tipo;
    document.getElementById('f-tipo').value = tipo;
    document.getElementById('btn-clt').className = 'tipo-btn' + (tipo === 'CLT' ? ' active-clt' : '');
    document.getElementById('btn-pj').className  = 'tipo-btn' + (tipo === 'PJ'  ? ' active-pj'  : '');
}

/* ── Modal ── */
function openModal(editId) {
    document.getElementById('modalBackdrop').classList.add('open');
    if (!editId) {
        clearForm();
    }
}

function closeModal() {
    document.getElementById('modalBackdrop').classList.remove('open');
    clearForm();
}

document.getElementById('modalClose').addEventListener('click', closeModal);
document.getElementById('modalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

function clearForm() {
    document.getElementById('f-id').value   = '';
    document.getElementById('f-nome').value = '';
    document.getElementById('f-setor').value = '';
    document.getElementById('f-data').value = '';
    setTipo('CLT');
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-plus" style="margin-right:8px;"></i>Novo Funcionário';
    document.getElementById('btnSalvarLabel').textContent = 'Adicionar';
}

/* ── Editar ── */
function editFunc(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) return;

    document.getElementById('f-id').value    = id;
    document.getElementById('f-nome').value  = row.querySelector('.primary').textContent.trim();
    document.getElementById('f-setor').value = row.getAttribute('data-setor').toUpperCase();
    document.getElementById('f-data').value  = row.getAttribute('data-data');
    setTipo(row.getAttribute('data-tipo'));

    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-pen" style="margin-right:8px;"></i>Editar Funcionário';
    document.getElementById('btnSalvarLabel').textContent = 'Salvar Alterações';
    openModal(id);
}

/* ── Salvar ── */
function saveFunc() {
    const id   = document.getElementById('f-id').value;
    const nome = document.getElementById('f-nome').value.trim().toUpperCase();
    const setor= document.getElementById('f-setor').value.trim().toUpperCase();
    const data = document.getElementById('f-data').value;
    const tipo = document.getElementById('f-tipo').value;

    if (!nome || !setor || !data) { toast('Preencha todos os campos', 'error'); return; }

    const body = new FormData();
    if (id) body.append('id', id);
    body.append('nome', nome);
    body.append('setor', setor);
    body.append('data_aniversario', data);
    body.append('tipo', tipo);

    const btn = document.getElementById('btnSalvar');
    btn.disabled = true;

    fetch('index.php?section=funcionarios&action=api&api_action=save', { method: 'POST', body })
        .then(r => r.json())
        .then(res => {
            if (res.error) { toast(res.error, 'error'); return; }
            toast(id ? 'Funcionário atualizado!' : 'Funcionário adicionado!', 'success');
            closeModal();
            setTimeout(() => location.reload(), 800);
        })
        .catch(() => toast('Erro ao salvar', 'error'))
        .finally(() => { btn.disabled = false; });
}

/* ── Excluir ── */
function deleteFunc(id) {
    if (!confirm('Excluir este funcionário?')) return;
    const body = new FormData();
    body.append('id', id);
    fetch('index.php?section=funcionarios&action=api&api_action=delete', { method: 'POST', body })
        .then(r => r.json())
        .then(res => {
            if (res.error) { toast(res.error, 'error'); return; }
            toast('Funcionário excluído', 'success');
            setTimeout(() => location.reload(), 800);
        })
        .catch(() => toast('Erro ao excluir', 'error'));
}

/* ── Filtro client-side ── */
function filtrar() {
    const texto = document.getElementById('filtroNome').value.toLowerCase().trim();
    const setor = document.getElementById('filtroSetor').value;
    const mes   = document.getElementById('filtroMes').value;

    document.querySelectorAll('#tabelaFunc tbody tr').forEach(function(tr) {
        const ok =
            (!texto || (tr.getAttribute('data-nome') || '').indexOf(texto) > -1) &&
            (!setor || tr.getAttribute('data-setor') === setor) &&
            (!mes   || tr.getAttribute('data-mes')   === mes);

        tr.style.display = ok ? '' : 'none';
    });
}
</script>
</body>
</html>