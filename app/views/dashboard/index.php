<?php
declare(strict_types=1);

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$totalContratos     = $data['totalContratos']     ?? 0;
$contratosNovos     = $data['contratosNovos']     ?? 0;
$contratosPorStatus = $data['contratosPorStatus'] ?? [];
$contratosRecentes  = $data['contratosRecentes']  ?? [];

$totalFichas        = $data['totalFichas']        ?? 0;
$fichasNovas        = $data['fichasNovas']        ?? 0;
$fichasPorStatus    = $data['fichasPorStatus']    ?? [];
$fichasRecentes     = $data['fichasRecentes']     ?? [];
$fichasPorEmpresa   = $data['fichasPorEmpresa']   ?? [];

$totalFuncionarios    = $data['totalFuncionarios']    ?? 0;
$funcPorTipo          = $data['funcPorTipo']          ?? [];
$aniversariantesHoje  = $data['aniversariantesHoje']  ?? [];
$aniversariantesMes   = $data['aniversariantesMes']   ?? [];

$MONTHS = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
           'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

function statusLabel(string $status): array {
    $map = [
        'novo'       => ['label' => 'Novo',        'bg' => '#e91e63', 'color' => '#fff'],
        'pendente'   => ['label' => 'Pendente',     'bg' => '#ff9800', 'color' => '#fff'],
        'em_analise' => ['label' => 'Em Análise',   'bg' => '#ff9800', 'color' => '#fff'],
        'aprovado'   => ['label' => 'Aprovado',     'bg' => '#4caf50', 'color' => '#fff'],
        'ativo'      => ['label' => 'Ativo',        'bg' => '#4caf50', 'color' => '#fff'],
        'rejeitado'  => ['label' => 'Rejeitado',    'bg' => '#f44336', 'color' => '#fff'],
        'reprovado'  => ['label' => 'Reprovado',    'bg' => '#f44336', 'color' => '#fff'],
        'cancelado'  => ['label' => 'Cancelado',    'bg' => '#9e9e9e', 'color' => '#fff'],
        'arquivado'  => ['label' => 'Arquivado',    'bg' => '#9e9e9e', 'color' => '#fff'],
        'baixado'    => ['label' => 'Baixado',      'bg' => '#2196f3', 'color' => '#fff'],
        'concluido'  => ['label' => 'Concluído',    'bg' => '#00bcd4', 'color' => '#fff'],
    ];
    $key = strtolower(trim($status));
    return $map[$key] ?? ['label' => ucfirst($status), 'bg' => '#757575', 'color' => '#fff'];
}

function badge(string $status): string {
    $s = statusLabel($status);
    return sprintf(
        '<span style="background:%s;color:%s;padding:3px 10px;border-radius:12px;font-size:0.78rem;font-weight:600;">%s</span>',
        $s['bg'], $s['color'], $s['label']
    );
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Admin Griffus</title>
    <link rel="shortcut icon" href="assets/images/icone.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ====== Dashboard Styles ====== */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 18px;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #f0f0f0;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .stat-icon.pink    { background: linear-gradient(135deg, #fce4ec, #f8bbd0); color: #e91e63; }
        .stat-icon.orange  { background: linear-gradient(135deg, #fff3e0, #ffe0b2); color: #ff9800; }
        .stat-icon.green   { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); color: #4caf50; }
        .stat-icon.blue    { background: linear-gradient(135deg, #e3f2fd, #bbdefb); color: #2196f3; }
        .stat-icon.purple  { background: linear-gradient(135deg, #f3e5f5, #e1bee7); color: #9c27b0; }

        .stat-info { flex: 1; }
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #212121;
            line-height: 1;
        }
        .stat-label {
            font-size: 0.85rem;
            color: #888;
            margin-top: 4px;
        }

        /* Sections */
        .dashboard-section {
            margin-bottom: 30px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .section-header h2 {
            font-size: 1.15rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-header h2 i { color: #e91e63; font-size: 1rem; }
        .section-header .btn-link {
            font-size: 0.85rem;
            color: #e91e63;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .section-header .btn-link:hover { opacity: 0.7; }

        /* Tables */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        .dash-table thead tr {
            background: #fafafa;
        }
        .dash-table th {
            padding: 11px 14px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #eee;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .dash-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #f0f0f0;
            color: #444;
            vertical-align: middle;
        }
        .dash-table tr:hover td {
            background: #fff8fb;
        }
        .dash-table .primary {
            font-weight: 600;
            color: #212121;
        }
        .dash-table .secondary {
            font-size: 0.8rem;
            color: #999;
            margin-top: 2px;
        }

        /* Status breakdown */
        .status-breakdown {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
            background: #f5f5f5;
            color: #555;
            border: 1px solid #e0e0e0;
        }
        .status-chip .count {
            background: #e91e63;
            color: #fff;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
        }

        /* Empresa badges */
        .empresa-badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 10px;
            font-size: 0.76rem;
            font-weight: 600;
            color: #fff;
        }
        .empresa-griffus { background: #e91e63; }
        .empresa-belmax  { background: #5b9bd5; }

        /* Two column layout */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        @media (max-width: 900px) {
            .two-col { grid-template-columns: 1fr; }
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #f0f0f0;
        }

        .empty-table {
            text-align: center;
            padding: 30px;
            color: #aaa;
            font-size: 0.9rem;
        }

        /* Welcome */
        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-size: 1.5rem; color: #212121; }
        .page-header p { color: #888; font-size: 0.9rem; margin-top: 4px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar__logo">GRIFFUS<span>DP</span></div>
        <nav class="sidebar__menu">
            <a href="index.php?section=dashboard" class="sidebar__item is-active">Dashboard</a>
            <a href="index.php?section=contratos" class="sidebar__item">Contratos</a>
            <a href="index.php?section=selecao" class="sidebar__item">Fichas de Seleção</a>
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
            <h1>Dashboard</h1>
            <p>Visão geral do sistema — <?= date('d/m/Y') ?></p>
        </div>

        <!-- ====== CARDS DE RESUMO ====== -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon pink">
                    <i class="fa-solid fa-file-contract"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $totalContratos ?></div>
                    <div class="stat-label">Contratos Total</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $contratosNovos ?></div>
                    <div class="stat-label">Contratos Novos</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $totalFichas ?></div>
                    <div class="stat-label">Fichas de Seleção</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $fichasNovas ?></div>
                    <div class="stat-label">Fichas Novas</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $totalFuncionarios ?></div>
                    <div class="stat-label">Funcionários</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#fff3e0,#ffe0b2);color:#e65100;">
                    <i class="fa-solid fa-cake-candles"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= count($aniversariantesMes) ?></div>
                    <div class="stat-label">Aniversariantes do Mês</div>
                </div>
            </div>
        </div>

        <!-- ====== STATUS BREAKDOWN ====== -->
        <div class="two-col">
            <div class="card">
                <div class="section-header">
                    <h2><i class="fa-solid fa-chart-pie"></i> Contratos por Status</h2>
                </div>
                <?php if (empty($contratosPorStatus)) : ?>
                    <p style="color:#aaa;font-size:0.9rem;">Nenhum dado.</p>
                <?php else : ?>
                    <div class="status-breakdown">
                        <?php foreach ($contratosPorStatus as $st => $count) :
                            $s = statusLabel($st);
                        ?>
                            <span class="status-chip">
                                <?= $s['label'] ?>
                                <span class="count" style="background:<?= $s['bg'] ?>"><?= $count ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="section-header">
                    <h2><i class="fa-solid fa-chart-pie"></i> Fichas por Status</h2>
                </div>
                <?php if (empty($fichasPorStatus)) : ?>
                    <p style="color:#aaa;font-size:0.9rem;">Nenhum dado.</p>
                <?php else : ?>
                    <div class="status-breakdown">
                        <?php foreach ($fichasPorStatus as $st => $count) :
                            $s = statusLabel($st);
                        ?>
                            <span class="status-chip">
                                <?= $s['label'] ?>
                                <span class="count" style="background:<?= $s['bg'] ?>"><?= $count ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($fichasPorEmpresa)) : ?>
                        <div style="margin-top:16px;padding-top:14px;border-top:1px solid #f0f0f0;">
                            <span style="font-size:0.82rem;color:#888;font-weight:600;">Por empresa:</span>
                            <div class="status-breakdown" style="margin-top:8px;">
                                <?php foreach ($fichasPorEmpresa as $emp => $count) : ?>
                                    <span class="status-chip">
                                        <span class="empresa-badge empresa-<?= strtolower(e($emp)) ?>"><?= e($emp) ?></span>
                                        <span class="count" style="background:#555"><?= $count ?></span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ====== TABELAS RECENTES ====== -->
        <div class="two-col">
            <!-- Últimos Contratos -->
            <div class="card">
                <div class="section-header">
                    <h2><i class="fa-solid fa-file-contract"></i> Últimos Contratos</h2>
                    <a href="index.php?section=contratos" class="btn-link">Ver todos →</a>
                </div>
                <?php if (empty($contratosRecentes)) : ?>
                    <div class="empty-table">Nenhum contrato cadastrado.</div>
                <?php else : ?>
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Empresa</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($contratosRecentes as $c) : ?>
                            <tr>
                                <td><?= (int)$c['id'] ?></td>
                                <td>
                                    <div class="primary"><?= e($c['razao_social'] ?? '') ?></div>
                                    <div class="secondary"><?= e($c['nome_socio'] ?? '') ?></div>
                                </td>
                                <td><?= badge($c['status'] ?? 'novo') ?></td>
                                <td><?= e($c['data_cadastro'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Últimas Fichas -->
            <div class="card">
                <div class="section-header">
                    <h2><i class="fa-solid fa-users"></i> Últimas Fichas</h2>
                    <a href="index.php?section=selecao" class="btn-link">Ver todas →</a>
                </div>
                <?php if (empty($fichasRecentes)) : ?>
                    <div class="empty-table">Nenhuma ficha recebida.</div>
                <?php else : ?>
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Candidato</th>
                                <th>Empresa</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($fichasRecentes as $f) : ?>
                            <tr>
                                <td><?= (int)$f['id'] ?></td>
                                <td>
                                    <div class="primary"><?= e($f['nome_completo'] ?? '') ?></div>
                                    <div class="secondary"><?= e($f['cargo'] ?? '') ?></div>
                                </td>
                                <td>
                                    <span class="empresa-badge empresa-<?= strtolower(e($f['empresa'] ?? '')) ?>"><?= e($f['empresa'] ?? '') ?></span>
                                </td>
                                <td><?= badge($f['status'] ?? 'novo') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ====== ANIVERSARIANTES ====== -->
        <?php if (!empty($aniversariantesHoje)) : ?>
        <div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,#fff8e1,#fff3e0);border:1px solid #ffe0b2;">
            <div class="section-header">
                <h2><i class="fa-solid fa-cake-candles" style="color:#e65100;"></i> Aniversariantes de Hoje 🎉</h2>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:12px;">
                <?php foreach (array_slice($aniversariantesHoje, 0, 5) as $a) : ?>
                <div style="display:flex;align-items:center;gap:10px;background:#fff;padding:10px 16px;border-radius:10px;border:1px solid #ffe0b2;">
                    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#ff4fa3,#e91e63);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.85rem;">
                        🎂
                    </div>
                    <div>
                        <div style="font-weight:600;color:#333;font-size:0.9rem;"><?= e((string)($a['nome'] ?? '')) ?></div>
                        <div style="font-size:0.78rem;color:#888;"><?= e((string)($a['setor'] ?? '')) ?> · <?= e((string)($a['tipo'] ?? '')) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="two-col">
            <!-- Aniversariantes do Mês -->
            <div class="card">
                <div class="section-header">
                    <h2><i class="fa-solid fa-cake-candles"></i> Aniversariantes — <?= $MONTHS[(int)date('n')] ?></h2>
                    <a href="index.php?section=aniversariantes" class="btn-link">Ver card →</a>
                </div>
                <?php if (empty($aniversariantesMes)) : ?>
                    <div class="empty-table">Nenhum aniversariante este mês.</div>
                <?php else : ?>
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Dia</th>
                                <th>Nome</th>
                                <th>Setor</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach (array_slice($aniversariantesMes, 0, 5) as $a) : ?>
                            <tr<?= ((int)$a['dia'] === (int)date('j')) ? ' style="background:#fff8e1;"' : '' ?>>
                                <td style="font-weight:700;color:#e91e63;"><?= str_pad((string)$a['dia'], 2, '0', STR_PAD_LEFT) ?></td>
                                <td><div class="primary"><?= e((string)($a['nome'] ?? '')) ?></div></td>
                                <td><?= e((string)($a['setor'] ?? '')) ?></td>
                                <td>
                                    <span style="background:<?= strtoupper($a['tipo'] ?? '') === 'PJ' ? '#ff9800' : '#4caf50' ?>;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.78rem;font-weight:600;">
                                        <?= e((string)($a['tipo'] ?? '')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (count($aniversariantesMes) > 5) : ?>
                        <div style="text-align:center;padding-top:10px;">
                            <a href="index.php?section=aniversariantes" class="btn-link">+<?= count($aniversariantesMes) - 5 ?> mais →</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Resumo Funcionários -->
            <div class="card">
                <div class="section-header">
                    <h2><i class="fa-solid fa-id-badge"></i> Funcionários por Tipo</h2>
                    <a href="index.php?section=funcionarios" class="btn-link">Gerenciar →</a>
                </div>
                <div style="display:flex;gap:16px;margin-top:8px;">
                    <div style="flex:1;padding:20px;border-radius:12px;background:linear-gradient(135deg,#e8f5e9,#c8e6c9);text-align:center;">
                        <div style="font-size:2rem;font-weight:700;color:#2e7d32;"><?= $funcPorTipo['CLT'] ?? 0 ?></div>
                        <div style="font-size:0.85rem;color:#555;font-weight:600;margin-top:4px;">CLT</div>
                    </div>
                    <div style="flex:1;padding:20px;border-radius:12px;background:linear-gradient(135deg,#fff3e0,#ffe0b2);text-align:center;">
                        <div style="font-size:2rem;font-weight:700;color:#e65100;"><?= $funcPorTipo['PJ'] ?? 0 ?></div>
                        <div style="font-size:0.85rem;color:#555;font-weight:600;margin-top:4px;">PJ</div>
                    </div>
                    <div style="flex:1;padding:20px;border-radius:12px;background:linear-gradient(135deg,#f3e5f5,#e1bee7);text-align:center;">
                        <div style="font-size:2rem;font-weight:700;color:#7b1fa2;"><?= $totalFuncionarios ?></div>
                        <div style="font-size:0.85rem;color:#555;font-weight:600;margin-top:4px;">Total</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
