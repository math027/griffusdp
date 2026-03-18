<?php
if (!function_exists('e')) {
    function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
$MONTHS = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
           'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

// Templates embutidos como base64 para uso seguro no canvas (evita tainted canvas)
$tplDir = dirname(__DIR__, 2) . '/storage/aniversario/';
$tplNormal = '';
$tplDayoff = '';
if (file_exists($tplDir . 'normal.png')) {
    $tplNormal = 'data:image/png;base64,' . base64_encode(file_get_contents($tplDir . 'normal.png'));
}
if (file_exists($tplDir . 'dayoff.png')) {
    $tplDayoff = 'data:image/png;base64,' . base64_encode(file_get_contents($tplDir . 'dayoff.png'));
}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
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
            max-width: 520px; width: 95%;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
            position: relative;
            max-height: 90vh; overflow-y: auto;
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

        /* ── Foto Avatar ── */
        .avatar-thumb {
            width: 36px; height: 36px; border-radius: 50%;
            object-fit: cover; border: 2px solid #f0f0f0;
        }
        .avatar-placeholder {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, #f8bbd0, #e91e63);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 14px;
        }

        /* ── Upload de foto no modal ── */
        .foto-upload-area {
            display: flex; align-items: center; gap: 16px;
        }
        .foto-preview {
            width: 80px; height: 80px; border-radius: 50%;
            object-fit: cover; border: 3px solid #f0f0f0;
            background: #fafafa;
        }
        .foto-preview-placeholder {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, #f8bbd0, #e91e63);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 28px;
        }
        .foto-upload-btns {
            display: flex; flex-direction: column; gap: 6px;
        }
        .btn-upload-foto {
            background: #f5f5f5; border: 1px solid #ddd; padding: 7px 14px;
            border-radius: 8px; font-size: .82rem; cursor: pointer;
            color: #555; transition: all .2s; font-family: inherit;
        }
        .btn-upload-foto:hover { background: #e8e8e8; color: #e91e63; }
        .btn-remove-foto {
            background: none; border: none; padding: 4px 0;
            font-size: .78rem; cursor: pointer; color: #e53935;
            text-align: left; font-family: inherit;
        }
        .btn-remove-foto:hover { text-decoration: underline; }

        /* ── Telefone col ── */
        .tel-link {
            color: #25d366; text-decoration: none; font-weight: 500;
        }
        .tel-link:hover { text-decoration: underline; }
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
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>Setor</th>
                        <th>Tipo</th>
                        <th>Aniversário</th>
                        <th>Telefone</th>
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
                        data-data="<?= e($f['data_aniversario']) ?>"
                        data-telefone="<?= e($f['telefone'] ?? '') ?>"
                        data-foto="<?= e($f['foto_path'] ?? '') ?>">
                        <td>
                            <?php if (!empty($f['foto_path'])): ?>
                                <img class="avatar-thumb"
                                     src="index.php?section=funcionarios&action=foto&id=<?= $f['id'] ?>"
                                     alt="<?= e($f['nome']) ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?= strtoupper(mb_substr($f['nome'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </td>
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
                            <?php if (!empty($f['telefone'])): ?>
                                <a class="tel-link" href="https://wa.me/<?= e($f['telefone']) ?>" target="_blank" title="Abrir WhatsApp">
                                    <i class="fa-brands fa-whatsapp"></i>
                                    <?= e(preg_replace('/(\d{2})(\d{2})(\d{5})(\d{4})/', '+$1 ($2) $3-$4', $f['telefone'])) ?>
                                </a>
                            <?php else: ?>
                                <span style="color:#bbb; font-size:.82rem;">—</span>
                            <?php endif; ?>
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
            <label>Foto do Funcionário</label>
            <div class="foto-upload-area">
                <div id="fotoPreviewWrap">
                    <div class="foto-preview-placeholder" id="fotoPlaceholder">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <img class="foto-preview" id="fotoPreview" style="display:none;" alt="Preview">
                </div>
                <div class="foto-upload-btns">
                    <button type="button" class="btn-upload-foto" onclick="document.getElementById('f-foto').click()">
                        <i class="fa-solid fa-upload" style="margin-right:4px;"></i> Escolher foto
                    </button>
                    <button type="button" class="btn-remove-foto" id="btnRemoveFoto" style="display:none;" onclick="removeFoto()">
                        <i class="fa-solid fa-xmark"></i> Remover foto
                    </button>
                    <input type="file" id="f-foto" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewFoto(this)">
                </div>
            </div>
        </div>

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
            <label>Telefone WhatsApp</label>
            <input type="tel" id="f-telefone" placeholder="(11) 99999-9999" maxlength="15" oninput="mascaraTel(this)">
            <span style="font-size:.72rem;color:#999;margin-top:2px;">Número com DDD para envio automático de mensagem</span>
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

<!-- Modal Cropper -->
<div id="cropperModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;justify-content:center;align-items:center;">
    <div style="background:#fff;border-radius:12px;padding:20px;max-width:520px;width:92%;box-shadow:0 10px 40px rgba(0,0,0,.5);display:flex;flex-direction:column;gap:16px;">
        <h3 style="margin:0;font-size:1.1rem;color:#333;"><i class="fa-solid fa-crop" style="margin-right:8px;"></i>Recortar Foto (1:1)</h3>
        <div style="width:100%;height:320px;background:#111;border-radius:8px;overflow:hidden;">
            <img id="cropperImage" style="max-width:100%;display:block;" alt="Crop">
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" onclick="closeCropper()" style="background:#f5f5f5;border:1px solid #ddd;padding:9px 18px;border-radius:6px;cursor:pointer;font-weight:600;color:#555;font-family:inherit;">Cancelar</button>
            <button type="button" onclick="applyCrop()" style="background:#e91e63;color:#fff;border:none;padding:9px 18px;border-radius:6px;cursor:pointer;font-weight:600;font-family:inherit;display:flex;align-items:center;gap:6px;"><i class="fa-solid fa-eye"></i>Recortar e Pré-visualizar</button>
        </div>
    </div>
</div>

<!-- Modal Preview Aniversário -->
<div id="previewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:10000;justify-content:center;align-items:flex-start;overflow-y:auto;padding:20px 0;">
    <div style="background:#fff;border-radius:14px;padding:24px;max-width:680px;width:94%;box-shadow:0 20px 60px rgba(0,0,0,.6);display:flex;flex-direction:column;gap:18px;margin:auto;">

        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:1.1rem;color:#e91e63;"><i class="fa-solid fa-birthday-cake" style="margin-right:8px;"></i>Preview — Cartão de Aniversário</h3>
            <button onclick="closePreview()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#999;line-height:1;">&times;</button>
        </div>

        <!-- Toggle Normal / Day Off -->
        <div style="display:flex;gap:8px;">
            <button id="btnNormal" onclick="setTemplate('normal')" style="flex:1;padding:9px;border-radius:8px;border:2px solid #e91e63;background:#fce4ec;color:#c2185b;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s;">
                <i class="fa-solid fa-calendar-check" style="margin-right:5px;"></i>Dia Normal
            </button>
            <button id="btnDayoff" onclick="setTemplate('dayoff')" style="flex:1;padding:9px;border-radius:8px;border:2px solid #ddd;background:#f5f5f5;color:#666;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s;">
                <i class="fa-solid fa-umbrella-beach" style="margin-right:5px;"></i>Day Off
            </button>
        </div>

        <!-- Canvas preview -->
        <div style="display:flex;justify-content:center;background:#1a1a1a;border-radius:10px;overflow:hidden;min-height:200px;align-items:center;">
            <canvas id="previewCanvas" style="display:block;max-width:100%;border-radius:8px;"></canvas>
        </div>

        <!-- Ações -->
        <div style="display:flex;gap:10px;">
            <button onclick="backToCropper()" style="flex:1;background:#f5f5f5;border:1px solid #ddd;padding:11px;border-radius:8px;cursor:pointer;font-weight:600;color:#555;font-family:inherit;">
                <i class="fa-solid fa-arrow-left" style="margin-right:5px;"></i>Reparar Recorte
            </button>
            <button onclick="confirmPhoto()" style="flex:2;background:linear-gradient(90deg,#e91e63,#ff4fa3);color:#fff;border:none;padding:11px;border-radius:8px;cursor:pointer;font-weight:700;font-family:inherit;font-size:.95rem;">
                <i class="fa-solid fa-check" style="margin-right:6px;"></i>Confirmar Foto
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
"use strict";

/* ════════════════════════════════════════════════
   Estado global
════════════════════════════════════════════════ */
let currentCropper   = null;
let croppedBlob      = null;
let croppedDataURL   = null;   // data URL da foto recortada
let fotoRemovida     = false;
let activeTemplate   = 'normal';

// Templates embutidos via PHP como data URLs (sem requisicao HTTP, sem tainted canvas)
const TEMPLATES = {
    normal: '<?= $tplNormal ?>',
    dayoff: '<?= $tplDayoff ?>'
};
const templateCache = {};

/* ════════════════════════════════════════════════
   Toast
════════════════════════════════════════════════ */
function toast(msg, type) {
    const c   = document.getElementById('toastContainer');
    const div = document.createElement('div');
    div.className   = 'toast-msg ' + (type || '');
    div.textContent = msg;
    c.appendChild(div);
    setTimeout(() => div.remove(), 3200);
}

/* ════════════════════════════════════════════════
   Máscara telefone
════════════════════════════════════════════════ */
function mascaraTel(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 11) v = v.slice(0, 11);
    if      (v.length > 6) v = '(' + v.slice(0,2) + ') ' + v.slice(2,7) + '-' + v.slice(7);
    else if (v.length > 2) v = '(' + v.slice(0,2) + ') ' + v.slice(2);
    else if (v.length > 0) v = '(' + v;
    input.value = v;
}

/* ════════════════════════════════════════════════
   Foto: abrir cropper
════════════════════════════════════════════════ */
function previewFoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('cropperImage').src = e.target.result;
        document.getElementById('cropperModal').style.display = 'flex';
        if (currentCropper) { currentCropper.destroy(); }
        currentCropper = new Cropper(document.getElementById('cropperImage'), {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 0.85,
        });
    };
    reader.readAsDataURL(input.files[0]);
    input.value = '';
}

function closeCropper() {
    document.getElementById('cropperModal').style.display = 'none';
    if (currentCropper) { currentCropper.destroy(); currentCropper = null; }
}

/* ────────────────────────────────────────────────
   Recortar → abre preview
──────────────────────────────────────────────── */
function applyCrop() {
    if (!currentCropper) return;
    currentCropper.getCroppedCanvas({ maxWidth: 1400, maxHeight: 1400 })
        .toBlob(function(blob) {
            croppedBlob = blob;
            const url   = URL.createObjectURL(blob);
            // também guarda como dataURL para o canvas
            const fr = new FileReader();
            fr.onload = ev => {
                croppedDataURL = ev.target.result;
                closeCropper();
                openPreview();
            };
            fr.readAsDataURL(blob);
        }, 'image/jpeg', 0.9);
}

function removeFoto() {
    document.getElementById('fotoPreview').style.display      = 'none';
    document.getElementById('fotoPlaceholder').style.display  = 'flex';
    document.getElementById('btnRemoveFoto').style.display    = 'none';
    document.getElementById('f-foto').value = '';
    fotoRemovida  = true;
    croppedBlob   = null;
    croppedDataURL= null;
}

/* ════════════════════════════════════════════════
   Preview Modal
════════════════════════════════════════════════ */
function loadTemplateImg(name) {
    return new Promise(resolve => {
        if (templateCache[name]) { resolve(templateCache[name]); return; }
        const src = TEMPLATES[name];
        if (!src) { resolve(null); return; }
        const img = new Image();
        img.onload  = () => { templateCache[name] = img; resolve(img); };
        img.onerror = () => resolve(null);
        img.src = src;
    });
}

function openPreview() {
    activeTemplate = 'normal';
    document.getElementById('previewModal').style.display = 'flex';
    syncTemplateButtons();
    updatePreview();
}

function closePreview() {
    document.getElementById('previewModal').style.display = 'none';
}

function backToCropper() {
    closePreview();
    // Re-abre o cropper com a mesma imagem original (já está no img tag)
    const img = document.getElementById('cropperImage');
    if (img.src) {
        document.getElementById('cropperModal').style.display = 'flex';
        if (currentCropper) currentCropper.destroy();
        currentCropper = new Cropper(img, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 0.85,
        });
    }
}

function confirmPhoto() {
    if (!croppedBlob) { closePreview(); return; }
    const url = URL.createObjectURL(croppedBlob);
    document.getElementById('fotoPreview').src             = url;
    document.getElementById('fotoPreview').style.display   = 'block';
    document.getElementById('fotoPlaceholder').style.display = 'none';
    document.getElementById('btnRemoveFoto').style.display  = 'block';
    fotoRemovida = false;
    closePreview();
}

function setTemplate(name) {
    activeTemplate = name;
    syncTemplateButtons();
    updatePreview();
}

function syncTemplateButtons() {
    const isNormal = activeTemplate === 'normal';
    document.getElementById('btnNormal').style.border     = isNormal ? '2px solid #e91e63' : '2px solid #ddd';
    document.getElementById('btnNormal').style.background = isNormal ? '#fce4ec' : '#f5f5f5';
    document.getElementById('btnNormal').style.color      = isNormal ? '#c2185b' : '#666';
    document.getElementById('btnDayoff').style.border     = !isNormal ? '2px solid #e91e63' : '2px solid #ddd';
    document.getElementById('btnDayoff').style.background = !isNormal ? '#fce4ec' : '#f5f5f5';
    document.getElementById('btnDayoff').style.color      = !isNormal ? '#c2185b' : '#666';
}

/* ── Renderizacao do canvas ── */
async function updatePreview() {
    const canvas = document.getElementById('previewCanvas');
    const ctx    = canvas.getContext('2d');

    // Dimensoes base do template (1080x1350)
    const BASE_W = 1080;
    const BASE_H = 1350;

    // Tamanho de exibicao responsivo
    const container = canvas.parentElement;
    const DISP_W = Math.min(380, (container ? container.offsetWidth : 400) - 20);
    const SCALE  = DISP_W / BASE_W;
    const DISP_H = BASE_H * SCALE;

    canvas.width  = DISP_W;
    canvas.height = DISP_H;

    // Posicao fixa da foto (medida com editor de posicao)
    const cx = 546;
    const cy = 450;
    const d  = 670;

    // 1. Fundo branco/cinza claro (simula fundo real do post)
    ctx.fillStyle = '#f0f0f0';
    ctx.fillRect(0, 0, DISP_W, DISP_H);

    // 2. Carrega template (async, sem crossOrigin pois e mesma origem)
    const tpl = await loadTemplateImg(activeTemplate);

    // 3. Desenha a foto do funcionario como QUADRADO atras do template
    //    O template RGBA vai naturalmente mascarar a foto pela area transparente
    if (croppedDataURL) {
        await new Promise(resolve => {
            const userImg = new Image();
            userImg.onload = () => {
                const half = (d / 2) * SCALE;
                const px   = cx * SCALE;
                const py   = cy * SCALE;
                // Foto quadrada sem clip - o template cobre o excesso
                ctx.drawImage(userImg, px - half, py - half, half * 2, half * 2);
                resolve();
            };
            userImg.onerror = () => resolve();
            userImg.src = croppedDataURL;
        });
    } else {
        // Placeholder cinza com icone para indicar onde a foto ira aparecer
        const half = (d / 2) * SCALE;
        const px   = cx * SCALE;
        const py   = cy * SCALE;
        ctx.fillStyle = 'rgba(180,180,180,0.6)';
        ctx.fillRect(px - half, py - half, half * 2, half * 2);
        ctx.fillStyle = 'rgba(100,100,100,0.5)';
        ctx.font = `${Math.round(half * 0.4)}px sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('foto', px, py);
    }

    // 4. Sobreposicao do template (pixels opacos cobrem o que nao e area da foto)
    if (tpl) {
        ctx.drawImage(tpl, 0, 0, DISP_W, DISP_H);
    } else {
        // Template nao carregou: mostra aviso
        ctx.fillStyle = 'rgba(233,30,99,0.15)';
        ctx.fillRect(0, 0, DISP_W, DISP_H);
        ctx.fillStyle = '#e91e63';
        ctx.font = '13px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('Template nao carregado', DISP_W / 2, DISP_H / 2);
    }

}


/* ════════════════════════════════════════════════
   Tipo toggle
════════════════════════════════════════════════ */
let selectedTipo = 'CLT';
function setTipo(tipo) {
    selectedTipo = tipo;
    document.getElementById('f-tipo').value = tipo;
    document.getElementById('btn-clt').className = 'tipo-btn' + (tipo === 'CLT' ? ' active-clt' : '');
    document.getElementById('btn-pj').className  = 'tipo-btn' + (tipo === 'PJ'  ? ' active-pj'  : '');
}

/* ════════════════════════════════════════════════
   Modal funcionário
════════════════════════════════════════════════ */
function openModal(editId) {
    document.getElementById('modalBackdrop').classList.add('open');
    if (!editId) clearForm();
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
    if (e.key === 'Escape') {
        if (document.getElementById('previewModal').style.display === 'flex') { closePreview(); return; }
        if (document.getElementById('cropperModal').style.display === 'flex') { closeCropper(); return; }
        closeModal();
    }
});

function clearForm() {
    document.getElementById('f-id').value        = '';
    document.getElementById('f-nome').value      = '';
    document.getElementById('f-setor').value     = '';
    document.getElementById('f-data').value      = '';
    document.getElementById('f-telefone').value  = '';
    document.getElementById('f-foto').value      = '';
    setTipo('CLT');
    removeFoto();
    fotoRemovida   = false;
    croppedBlob    = null;
    croppedDataURL = null;
    document.getElementById('modalTitle').innerHTML       = '<i class="fa-solid fa-user-plus" style="margin-right:8px;"></i>Novo Funcionário';
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

    const tel = row.getAttribute('data-telefone') || '';
    if (tel) { const inp = document.getElementById('f-telefone'); inp.value = tel; mascaraTel(inp); }
    else document.getElementById('f-telefone').value = '';

    const foto = row.getAttribute('data-foto') || '';
    if (foto) {
        document.getElementById('fotoPreview').src             = 'index.php?section=funcionarios&action=foto&id=' + id;
        document.getElementById('fotoPreview').style.display   = 'block';
        document.getElementById('fotoPlaceholder').style.display = 'none';
        document.getElementById('btnRemoveFoto').style.display  = 'block';
    } else { removeFoto(); }

    fotoRemovida   = false;
    croppedBlob    = null;
    croppedDataURL = null;

    document.getElementById('modalTitle').innerHTML       = '<i class="fa-solid fa-user-pen" style="margin-right:8px;"></i>Editar Funcionário';
    document.getElementById('btnSalvarLabel').textContent = 'Salvar Alterações';
    openModal(id);
}

/* ── Salvar ── */
function saveFunc() {
    const id       = document.getElementById('f-id').value;
    const nome     = document.getElementById('f-nome').value.trim().toUpperCase();
    const setor    = document.getElementById('f-setor').value.trim().toUpperCase();
    const data     = document.getElementById('f-data').value;
    const tipo     = document.getElementById('f-tipo').value;
    const telefone = document.getElementById('f-telefone').value;

    if (!nome || !setor || !data) { toast('Preencha todos os campos obrigatórios', 'error'); return; }

    const body = new FormData();
    if (id) body.append('id', id);
    body.append('nome', nome);
    body.append('setor', setor);
    body.append('data_aniversario', data);
    body.append('tipo', tipo);
    body.append('telefone', telefone);
    if (croppedBlob) body.append('foto', croppedBlob, 'foto_recortada.jpg');

    const btn = document.getElementById('btnSalvar');
    btn.disabled = true;

    const deletePromise = (id && fotoRemovida)
        ? fetch('index.php?section=funcionarios&action=api&api_action=delete_foto', {
              method: 'POST',
              body: (() => { const fd = new FormData(); fd.append('id', id); return fd; })()
          })
        : Promise.resolve();

    deletePromise
        .then(() => fetch('index.php?section=funcionarios&action=api&api_action=save', { method: 'POST', body }))
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