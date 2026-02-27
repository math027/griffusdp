<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos — Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="assets/images/icone.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .badge-tipo {
            display: inline-block;
            font-size: .72rem;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .badge-tipo-rca { background: #e3f2fd; color: #1565c0; }
        .badge-tipo-pj  { background: #f3e5f5; color: #6a1b9a; }

        /* btn-primary: usa o estilo global de assets/css/style.css */
        .pre-box {
            white-space: pre-wrap; background: #fafafa;
            padding: 10px; border-radius: 6px; border: 1px solid #eee;
            font-size: .85rem; margin: 4px 0 0;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar__logo">
            GRIFFUS<span>DP</span>
        </div>
        <nav class="sidebar__menu">
            <a href="index.php?section=dashboard" class="sidebar__item">Dashboard</a>
            <a href="index.php?section=contratos" class="sidebar__item is-active">Contratos</a>
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
            <div>
                <h1>Gestão de Contratos</h1>
                <p>Gerencie as solicitações de representantes</p>
            </div>
                <a class="btn-primary" href="../contratos/" target="_blank">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir Formulário
            </a>
        </div>

        <div class="filters">
            <input type="text" id="filtroBusca" placeholder="Buscar por nome ou CNPJ..." oninput="filtrarContratos()" style="flex:1;min-width:200px;padding:9px 13px;border:1px solid #ddd;border-radius:8px;font-size:.9rem;outline:none;">
            <select id="filtroStatus" onchange="filtrarContratos()" style="padding:9px 13px;border:1px solid #ddd;border-radius:8px;font-size:.9rem;outline:none;">
                <option value="">Todos os Status</option>
                <option value="novo">Novo</option>
                <option value="pendente">Pendente</option>
                <option value="aprovado">Aprovado</option>
                <option value="rejeitado">Rejeitado</option>
                <option value="baixado">Baixado</option>
            </select>
            <select id="filtroTipo" onchange="filtrarContratos()" style="padding:9px 13px;border:1px solid #ddd;border-radius:8px;font-size:.9rem;outline:none;">
                <option value="">Todos os Tipos</option>
                <option value="rca">RCA</option>
                <option value="pj">PJ</option>
            </select>
        </div>

        <?php if (empty($contracts)) : ?>
            <div class="card empty-state">Nenhum contrato encontrado.</div>
        <?php else : ?>
            <div class="card table-card">
                <table class="contracts-table" id="tabelaContratos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Representante / Empresa</th>
                            <th>Tipo</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($contracts as $contract) : ?>
                        <?php
                            $statusKey = strtolower(trim((string)($contract['status'] ?? '')));
                        ?>
                        <tr data-razao="<?= e(strtolower($contract['razao_social'] ?? '')) ?>"
                            data-cnpj="<?= e($contract['cnpj'] ?? '') ?>"
                            data-status="<?= e($statusKey) ?>"
                            data-tipo="<?= e(strtolower($contract['tipo_contrato'] ?? '')) ?>">
                            <td>#<?= (int)$contract['id']; ?></td>
                            <td>
                                <div class="primary"><?= e($contract['razao_social'] ?? ''); ?></div>
                                <div class="secondary"><?= e($contract['nome_socio'] ?? ''); ?></div>
                            </td>
                            <td>
                                <?php
                                    $tipo = $contract['tipo_contrato'] ?? '';
                                    if ($tipo === 'RCA') {
                                        echo '<span class="badge-tipo badge-tipo-rca">RCA</span>';
                                    } elseif ($tipo === 'PJ') {
                                        echo '<span class="badge-tipo badge-tipo-pj">PJ</span>';
                                    } else {
                                        echo '<span style="color:#bbb;font-size:.8rem">—</span>';
                                    }
                                ?>
                            </td>
                            <td><?= e($contract['data_cadastro'] ?? ''); ?></td>
                            <td>
                                <span class="badge badge-<?= e($statusKey); ?>"><?= e($contract['status'] ?? ''); ?></span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="icon-btn" href="index.php?section=contratos&action=edit&id=<?= (int)$contract['id']; ?>" title="Visualizar">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <form method="post" action="index.php?section=contratos&action=delete" onsubmit="return confirm('Deseja excluir este contrato?');">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken); ?>">
                                        <input type="hidden" name="id" value="<?= (int)$contract['id']; ?>">
                                        <button class="icon-btn danger" type="submit" title="Excluir">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                    <a class="icon-btn" href="index.php?section=contratos&action=download&id=<?= (int)$contract['id']; ?>" title="Download">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <script>
    function filtrarContratos() {
        const texto  = (document.getElementById("filtroBusca").value || "").toLowerCase();
        const status = document.getElementById("filtroStatus").value;
        const tipo   = document.getElementById("filtroTipo").value;

        document.querySelectorAll("#tabelaContratos tbody tr").forEach(tr => {
            const razao  = tr.dataset.razao  || "";
            const cnpj   = tr.dataset.cnpj   || "";
            const stat   = tr.dataset.status || "";
            const tipoTr = (tr.dataset.tipo  || "").toLowerCase();

            const matchTexto  = !texto  || razao.includes(texto) || cnpj.includes(texto);
            const matchStatus = !status || stat === status;
            const matchTipo   = !tipo   || tipoTr === tipo;

            tr.style.display = (matchTexto && matchStatus && matchTipo) ? "" : "none";
        });
    }
    </script>
    <script src="../assets/js/toast.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>