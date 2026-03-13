<?php
declare(strict_types=1);
if (!function_exists('e')) {
    function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aniversariantes — Admin Griffus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:ital,wght@0,400;0,700;0,900;1,400;1,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="shortcut icon" href="assets/images/icone.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── Layout Aniversariantes ── */
        .aniv-wrapper {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 20px;
            align-items: start;
            min-height: calc(100vh - 160px);
        }

        .aniv-panel {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(15,15,19,.06);
            padding: 22px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .aniv-panel__title {
            font-size: 11px;
            font-weight: 700;
            color: #ff4fa3;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-bottom: 1px solid #ffe0f0;
            padding-bottom: 10px;
            margin-bottom: 4px;
        }

        /* Lista lateral */
        .list-scroll { max-height: 320px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; }
        .aniv-list-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 10px; border-radius: 10px; background: #fafafa;
            border: 1px solid #f0f0f0; transition: background .15s;
        }
        .aniv-list-item:hover { background: #fff0f6; }
        .aniv-list-day {
            width: 32px; height: 32px; border-radius: 8px;
            background: linear-gradient(135deg, #ff4fa3, #e91e63);
            color: #fff; font-weight: 700; font-size: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .aniv-list-info { flex: 1; min-width: 0; }
        .aniv-list-name { font-weight: 600; font-size: 12px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .aniv-list-meta { font-size: 10px; color: #999; }
        .empty-list { text-align: center; padding: 16px; color: #bbb; font-size: 12px; }
        .empty-list i { display: block; font-size: 1.4rem; margin-bottom: 6px; }
        .aniv-count-badge {
            background: rgba(233,30,99,.1); color: #e91e63;
            border-radius: 20px; padding: 2px 8px;
            font-size: 11px; font-weight: 700;
        }

        /* Form fields */
        .field-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .field-group label {
            font-size: 11px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .field-group input,
        .field-group select {
            border: 1px solid #e8e9ef;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 13px;
            outline: none;
            transition: border-color .2s;
            width: 100%;
            font-family: inherit;
        }
        .field-group input:focus,
        .field-group select:focus {
            border-color: #ff4fa3;
            box-shadow: 0 0 0 3px rgba(255,79,163,.12);
        }

        .month-selector {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .month-selector label {
            font-size: 11px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .month-selector select {
            border: 1.5px solid #ff4fa3;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: 600;
            color: #222;
            outline: none;
            background: #fff9fc;
            width: 100%;
            font-family: inherit;
            cursor: pointer;
        }

        .tipo-btns {
            display: flex;
            gap: 8px;
        }
        .tipo-btn {
            flex: 1;
            padding: 8px;
            border: 1.5px solid #e8e9ef;
            border-radius: 10px;
            background: #fff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            color: #666;
        }
        .tipo-btn.active-clt {
            border-color: #4caf50;
            background: #e8f5e9;
            color: #2e7d32;
        }
        .tipo-btn.active-pj {
            border-color: #ff9800;
            background: #fff3e0;
            color: #e65100;
        }

        .divider {
            border: none;
            border-top: 1px solid #f0f0f5;
        }

        .btn-save {
            background: linear-gradient(90deg, #ff2d75, #ff4fa3);
            color: #fff;
            padding: 10px 16px;
            border-radius: 22px;
            box-shadow: 0 6px 14px rgba(255,45,117,.25);
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: none;
            width: 100%;
            transition: all .2s;
        }
        .btn-save:hover { opacity: .9; transform: translateY(-1px); }

        .btn-cancel {
            background: #f5f6fa;
            color: #555;
            padding: 8px 16px;
            border-radius: 22px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: 1px solid #e0e0e0;
            width: 100%;
        }
        .btn-cancel:hover { background: #ececec; }

        .aniv-count-badge {
            background: rgba(255,79,163,.1);
            color: #ff4fa3;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        /* ── MUDANÇA 1: Botão download reposicionado para baixo da lista ── */
        .btn-download {
            background: linear-gradient(90deg, #ff2d75, #ff4fa3);
            color: #fff;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 16px rgba(255,45,117,.28);
            transition: all .2s;
            width: 100%;
            margin-top: 4px;
        }
        .btn-download:hover { opacity: .9; transform: translateY(-1px); }

        /* ── Canvas central ── */
        .canvas-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 20px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(15,15,19,.06);
        }

        .canvas-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 16px;
        }

        .canvas-toolbar h3 {
            font-size: 13px;
            font-weight: 700;
            color: #333;
        }

        .canvas-scale-wrapper {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        #canvas-area {
            width: 700px;
            height: 900px;
            flex-shrink: 0;
            background-image: url(assets/images/fundo.png);
            background-size: cover;
            background-position: center;
            background-color: #fff;
            position: relative;
            box-shadow: 0 15px 50px rgba(0,0,0,.20);
            padding-top: 250px;
            padding-left: 40px;
            padding-right: 40px;
            box-sizing: border-box;
            transform: scale(var(--canvas-scale, 0.75));
            transform-origin: top center;
            border-radius: 4px;
        }

        /* Wrapper calculado dinamicamente via JS */
        .canvas-scale-wrapper {
            width: 100%;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            overflow: hidden;
        }

        /* Nome do mes — roxo original exato */
        #month-display {
            position: absolute;
            top: 110px;
            left: 250px;
            transform: translate(-50%, -50%);
            font-family: 'Montserrat', sans-serif;
            font-style: italic;
            color: rgb(114, 49, 109);
            font-size: 4rem;
            font-weight: 700;
            z-index: 10;
            white-space: nowrap;
            pointer-events: none;
        }

        :root { --row-spacing: 10px; }
        .birthday-list { display: flex; flex-direction: column; }

        .b-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: var(--row-spacing);
            position: relative;
            min-height: unset;
        }

        .pill-day {
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            background: rgba(220, 215, 230, 0.80);
            border-radius: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            /* ── MUDANÇA 3: cor mais escura e negrito ── */
            color: #2a1a3a;
            font-weight: 800;
            font-size: 0.95em;
            font-family: 'Montserrat', sans-serif;
            box-shadow: 1px 2px 5px rgba(0,0,0,0.18);
        }

        .pill-name {
            flex: 2;
            height: 34px;
            background: rgba(255, 255, 255, 0.88);
            border-radius: 50px;
            display: flex;
            align-items: center;
            padding: 0 14px;
            color: #2a1a3a;
            font-weight: 600;
            font-size: 0.78em;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-shadow: 1px 2px 5px rgba(0,0,0,0.10);
        }

        .pill-sector {
            flex: 1;
            height: 34px;
            background: rgba(255, 255, 255, 0.88);
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 12px;
            color: #2a1a3a;
            font-weight: 600;
            font-size: 0.75em;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-shadow: 1px 2px 5px rgba(0,0,0,0.10);
            text-align: center;
        }

        .pill-tipo {
            height: 34px;
            padding: 0 10px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 0.68em;
            white-space: nowrap;
            box-shadow: 1px 2px 5px rgba(0,0,0,0.10);
        }
        .pill-tipo.clt { background: rgba(200,240,210,0.88); color: #1a5c2a; }
        .pill-tipo.pj  { background: rgba(255,235,200,0.88); color: #7a3a00; }

        /* ── Lista lateral ── */
        .list-scroll {
            overflow-y: auto;
            flex: 1;
            max-height: 360px;
        }
        .list-scroll::-webkit-scrollbar { width: 6px; }
        .list-scroll::-webkit-scrollbar-track { background: #f5f5f5; border-radius: 4px; }
        .list-scroll::-webkit-scrollbar-thumb { background: #f8bbd0; border-radius: 4px; }

        .aniv-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #f0f0f5;
            border-left: 3px solid #e8e9ef;
            margin-bottom: 6px;
            background: #fafafa;
            transition: all .2s;
            gap: 8px;
        }
        .aniv-list-item:hover {
            border-left-color: #ff4fa3;
            background: #fff;
            box-shadow: 0 2px 8px rgba(255,79,163,.08);
        }

        .aniv-list-info { flex: 1; min-width: 0; }
        .aniv-list-name { font-size: 13px; font-weight: 600; color: #222; }
        .aniv-list-meta { font-size: 11px; color: #888; margin-top: 2px; }

        /* ── MUDANÇA 2: número do dia mais escuro e visível ── */
        .aniv-list-day {
            font-size: 18px;
            font-weight: 800;
            color: #c2185b;
            min-width: 28px;
            text-align: center;
            text-shadow: 0 1px 2px rgba(194,24,91,.15);
        }

        .badge-tipo {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
        }
        .badge-tipo.clt { background: #e8f5e9; color: #2e7d32; }
        .badge-tipo.pj  { background: #fff3e0; color: #e65100; }

        .list-actions { display: flex; gap: 4px; }

        .empty-list {
            text-align: center;
            color: #bbb;
            padding: 32px 16px;
            font-size: 13px;
        }
        .empty-list i { font-size: 32px; display: block; margin-bottom: 8px; color: #f8bbd0; }

        /* Toast */
        #toast {
            position: fixed;
            bottom: 24px; right: 24px;
            background: #222;
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 13px;
            z-index: 9999;
            opacity: 0;
            transform: translateY(10px);
            transition: all .3s;
            pointer-events: none;
        }
        #toast.show { opacity: 1; transform: translateY(0); }
        #toast.success { background: #2e7d32; }
        #toast.error   { background: #c62828; }

        /* Canvas empty */
        .canvas-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 60%;
            color: rgba(173,20,87,.3);
            font-family: 'Montserrat', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            pointer-events: none;
        }
        .canvas-empty i { font-size: 3rem; margin-bottom: 12px; }

        @media (max-width: 800px) {
            .aniv-wrapper { grid-template-columns: 1fr; }
            #canvas-area { transform: scale(0.4); }
            .canvas-scale-wrapper { height: 370px; }
        }
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
        <a href="index.php?section=vagas"           class="sidebar__item">Vagas Disponíveis</a>
        <a href="index.php?section=funcionarios"     class="sidebar__item">Funcionários</a>
        <a href="index.php?section=aniversariantes" class="sidebar__item is-active">Aniversariantes</a>
    </nav>
    <div class="sidebar__footer">
        <a href="../logout.php" class="sidebar__logout">Sair</a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <div>
            <h1>Aniversariantes</h1>
            <p>Gerencie e gere o card mensal de aniversariantes</p>
        </div>
        <a href="index.php?section=funcionarios" class="btn-primary">
            <i class="fa-solid fa-user-plus"></i> Novo Funcionário
        </a>
    </div>

    <div class="aniv-wrapper">

        <!-- ── Painel Esquerdo: Configurações + Lista ── -->
        <div class="aniv-panel">

            <div>
                <p class="aniv-panel__title">Mês de Referência</p>
                <div class="month-selector">
                    <select id="month-select" onchange="loadMonth()">
                        <option value="1">Janeiro</option>
                        <option value="2">Fevereiro</option>
                        <option value="3">Março</option>
                        <option value="4">Abril</option>
                        <option value="5">Maio</option>
                        <option value="6">Junho</option>
                        <option value="7">Julho</option>
                        <option value="8">Agosto</option>
                        <option value="9">Setembro</option>
                        <option value="10">Outubro</option>
                        <option value="11">Novembro</option>
                        <option value="12">Dezembro</option>
                    </select>
                </div>
            </div>

            <div>
                <p class="aniv-panel__title">Espaçamento entre Cards</p>
                <div class="field-group">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                        <label style="margin-bottom:0">Distância vertical</label>
                        <span id="spacing-val" style="font-size:12px;font-weight:700;color:#ff4fa3">10px</span>
                    </div>
                    <input type="range" id="spacing-range" min="2" max="40" value="10"
                           style="width:100%;accent-color:#ff4fa3;cursor:pointer"
                           oninput="updateSpacing(this.value)">
                    <div style="display:flex;justify-content:space-between;font-size:10px;color:#bbb;margin-top:2px">
                        <span>Compacto</span><span>Espaçado</span>
                    </div>
                </div>
            </div>

            <hr style="border:none;border-top:1px solid #f0f0f0;margin:4px 0;">

            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <p class="aniv-panel__title" style="margin-bottom:0;border-bottom:none;padding-bottom:0;">Lista do Mês</p>
                    <span class="aniv-count-badge" id="list-count">0</span>
                </div>
                <div class="list-scroll" id="list-container">
                    <div class="empty-list">
                        <i class="fas fa-birthday-cake"></i>
                        Selecione o mês
                    </div>
                </div>
            </div>

            <!-- ── MUDANÇA 1: Botão Salvar abaixo da lista ── -->
            <button class="btn-download" onclick="downloadImage()">
                <i class="fas fa-download"></i> Salvar Imagem
            </button>

        </div>

        <!-- ── Canvas Central ── -->
        <div class="canvas-container">
            <div class="canvas-toolbar">
                <h3>
                    Preview do Card
                    <span class="aniv-count-badge" id="count-badge">0</span>
                </h3>
            </div>
            <div class="canvas-scale-wrapper" id="canvas-wrapper">
                <div id="canvas-area">
                    <div id="month-display">Janeiro</div>
                    <div class="birthday-list" id="birthday-display">
                        <div class="canvas-empty">
                            <i class="fas fa-birthday-cake"></i>
                            Selecione o mês
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<div id="toast"></div>

<script>
"use strict";

const MONTHS = ['', 'Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                    'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

let people = [];
let currentMonth = new Date().getMonth() + 1;
let selectedTipo = 'CLT';

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('month-select');
    sel.value = currentMonth;
    fitCanvas();
    window.addEventListener('resize', fitCanvas);
    loadMonth();
});

// ── Fit canvas to available width ──
function fitCanvas() {
    const wrapper = document.getElementById('canvas-wrapper');
    const available = wrapper.clientWidth - 8;
    const scale = Math.min(available / 700, 0.92);
    document.documentElement.style.setProperty('--canvas-scale', scale);
    wrapper.style.height = Math.round(900 * scale) + 'px';
}

// ── Load by month ──
function loadMonth() {
    const month = parseInt(document.getElementById('month-select').value);
    currentMonth = month;
    document.getElementById('month-display').innerText = MONTHS[month];

    fetch(`index.php?section=aniversariantes&action=api&api_action=list_by_month&month=${month}`)
        .then(r => r.json())
        .then(data => {
            people = data || [];
            render();
        })
        .catch(() => toast('Erro ao carregar dados', 'error'));
}

// ── Spacing control ──
function updateSpacing(val) {
    document.documentElement.style.setProperty('--row-spacing', val + 'px');
    document.getElementById('spacing-val').textContent = val + 'px';
}

// ── Render ──
function render() {
    const display = document.getElementById('birthday-display');
    const list    = document.getElementById('list-container');
    const count   = people.length;

    document.getElementById('count-badge').textContent = count;
    document.getElementById('list-count').textContent  = count;

    // Canvas
    display.innerHTML = '';
    if (count === 0) {
        display.innerHTML = `<div class="canvas-empty"><i class="fas fa-birthday-cake"></i>Nenhum aniversariante</div>`;
    } else {
        let daySize, pillH, fontSize;
        if (count <= 7)       { daySize='42px'; pillH='36px'; fontSize='0.82em'; }
        else if (count <= 12) { daySize='36px'; pillH='30px'; fontSize='0.72em'; }
        else if (count <= 18) { daySize='30px'; pillH='26px'; fontSize='0.64em'; }
        else                  { daySize='24px'; pillH='22px'; fontSize='0.56em'; }

        people.forEach(p => {
            const tipoClass = p.tipo === 'PJ' ? 'pj' : 'clt';
            const row = document.createElement('div');
            row.className = 'b-row';
            row.innerHTML = `
                <div class="pill-day" style="width:${daySize};height:${daySize};font-size:${fontSize}">${pad(p.dia)}</div>
                <div class="pill-name" style="height:${pillH};font-size:${fontSize}">${esc(p.nome)}</div>
                <div class="pill-sector" style="height:${pillH};font-size:${fontSize}">${esc(p.setor)}</div>
            `;
            display.appendChild(row);
        });
    }

    // Sidebar list
    list.innerHTML = '';
    if (count === 0) {
        list.innerHTML = `<div class="empty-list"><i class="fas fa-birthday-cake"></i>Nenhum aniversariante neste mês</div>`;
    } else {
        people.forEach(p => {
            const li = document.createElement('div');
            li.className = 'aniv-list-item';
            li.innerHTML = `
                <div class="aniv-list-day">${pad(p.dia)}</div>
                <div class="aniv-list-info">
                    <div class="aniv-list-name">${esc(p.nome)}</div>
                    <div class="aniv-list-meta">${esc(p.setor)} &nbsp;|&nbsp; ${esc(p.tipo)}</div>
                </div>
            `;
            list.appendChild(li);
        });
    }
}


// ── Download ──
function downloadImage() {
    const el = document.getElementById('canvas-area');
    html2canvas(el, {
        scale: 2,
        useCORS: true,
        backgroundColor: null,
        onclone: (doc) => { doc.getElementById('canvas-area').style.transform = 'none'; }
    }).then(canvas => {
        const a = document.createElement('a');
        a.download = `aniversariantes_${MONTHS[currentMonth]}.png`;
        a.href = canvas.toDataURL('image/png');
        a.click();
    });
}

// ── Helpers ──
function pad(n) { return String(n).padStart(2, '0'); }
function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

let toastTimer;
function toast(msg, type = '') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'show' + (type ? ' ' + type : '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { el.className = ''; }, 3000);
}
</script>
</body>
</html>