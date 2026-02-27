<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<?php
if (!isset($contract)) {
    die('Contrato não carregado.');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Contrato</title>
    <link rel="shortcut icon" href="assets/images/icone.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/edit-form.css">
</head>
<body class="edit-form-page">
    <div class="main-container">
        <div class="header-strip">
            <h2>Contrato #<?= (int)$contract['id']; ?></h2>
            <div class="header-actions">
                <button type="button" class="btn btn-edit" id="btnEnableEdit">
                    <i class="fa-solid fa-pen-to-square"></i> Editar
                </button>
                <button type="button" class="btn btn-secondary" id="btnCancelEdit" style="display: none;">
                    <i class="fa-solid fa-xmark"></i> Cancelar Edição
                </button>
            </div>
        </div>

        <form method="post" action="index.php?section=contratos&action=update" class="content-body" enctype="multipart/form-data" id="formEditContrato">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken); ?>">
            <input type="hidden" name="id" value="<?= (int)$contract['id']; ?>">

            <!-- Tipo do Contrato -->
            <fieldset class="form-section">
                <legend class="section-title">Tipo de Contrato</legend>
                <div class="section-fields">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo_contrato" class="editable-field" disabled>
                            <option value="">— Não informado —</option>
                            <option value="RCA" <?= ($contract['tipo_contrato'] ?? '') === 'RCA' ? 'selected' : '' ?>>RCA — Representante Comercial Autônomo</option>
                            <option value="PJ"  <?= ($contract['tipo_contrato'] ?? '') === 'PJ'  ? 'selected' : '' ?>>PJ — Promoção de Vendas</option>
                        </select>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section section-empresa">
                <legend class="section-title">Dados da Empresa</legend>
                <div class="section-fields">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Razão Social</label>
                            <input type="text" name="razao_social" value="<?= e($contract['razao_social']); ?>" class="editable-field" disabled>
                        </div>
                        <div class="form-group">
                            <label>CNPJ</label>
                            <input type="text" name="cnpj" value="<?= e($contract['cnpj']); ?>" placeholder="00.000.000/0000-00" class="editable-field" disabled>
                        </div>
                        <div class="form-group">
                            <label>CEP</label>
                            <input type="text" name="cep" value="<?= e($contract['cep']); ?>" placeholder="00.000-000" class="editable-field" disabled>
                        </div>
                        <div class="form-group col-full">
                            <label>Endereço</label>
                            <input type="text" name="endereco" value="<?= e($contract['endereco']); ?>" class="editable-field" disabled>
                        </div>
                        <div class="form-group">
                            <label>Número</label>
                            <input type="text" name="numero" value="<?= e($contract['numero']); ?>" class="editable-field" disabled>
                        </div>
                        <div class="form-group">
                            <label>Bairro</label>
                            <input type="text" name="bairro" value="<?= e($contract['bairro']); ?>" class="editable-field" disabled>
                        </div>
                        <div class="form-group">
                            <label>Cidade</label>
                            <input type="text" name="cidade" value="<?= e($contract['cidade']); ?>" class="editable-field" disabled>
                        </div>
                    </div>
                    <div class="row-uf-celular">
                        <div class="form-group">
                            <label>UF</label>
                            <input type="text" name="uf" value="<?= e($contract['uf']); ?>" class="editable-field" disabled>
                        </div>
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" name="telefone" value="<?= e($contract['telefone']); ?>" class="editable-field" disabled>
                        </div>
                        <div class="form-group">
                            <label>Celular</label>
                            <input type="text" name="celular" value="<?= e($contract['celular']); ?>" placeholder="(00) 90000-0000" class="editable-field" disabled>
                        </div>
                    </div>
                    <div class="form-grid" style="margin-top: 20px;">
                        <div class="form-group col-full">
                            <label>Email</label>
                            <input type="text" name="email_empresa" value="<?= e($contract['email_empresa']); ?>" class="editable-field" disabled>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend class="section-title">Dados Bancários</legend>
                <div class="section-fields form-grid">
                    <div class="form-group">
                        <label>Banco</label>
                        <input type="text" name="banco" value="<?= e($contract['banco']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>Agência</label>
                        <input type="text" name="agencia" value="<?= e($contract['agencia']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>Conta</label>
                        <input type="text" name="conta" value="<?= e($contract['conta']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group col-full">
                        <label>PIX</label>
                        <input type="text" name="pix" value="<?= e($contract['pix']); ?>" class="editable-field" disabled>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend class="section-title">Dados do Sócio</legend>
                <div class="section-fields form-grid">
                    <div class="form-group">
                        <label>Nome Sócio</label>
                        <input type="text" name="nome_socio" value="<?= e($contract['nome_socio']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" name="cpf" value="<?= e($contract['cpf']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>RG</label>
                        <input type="text" name="rg" value="<?= e($contract['rg']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>Órgão Expedidor</label>
                        <input type="text" name="orgao_expedidor" value="<?= e($contract['orgao_expedidor']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>Nascimento</label>
                        <input type="date" name="nascimento" value="<?= e($contract['nascimento']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>Nacionalidade</label>
                        <input type="text" name="nacionalidade" value="<?= e($contract['nacionalidade']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>Estado Civil</label>
                        <input type="text" name="estado_civil" value="<?= e($contract['estado_civil']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>Profissão</label>
                        <input type="text" name="profissao" value="<?= e($contract['profissao']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group col-full">
                        <label>Email Sócio</label>
                        <input type="text" name="email_socio" value="<?= e($contract['email_socio']); ?>" class="editable-field" disabled>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend class="section-title">Endereço do Sócio</legend>
                <div class="section-fields form-grid">
                    <div class="form-group">
                        <label>CEP</label>
                        <input type="text" name="cep_socio" value="<?= e($contract['cep_socio']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group col-span-2">
                        <label>Endereço</label>
                        <input type="text" name="endereco_socio" value="<?= e($contract['endereco_socio']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>Número</label>
                        <input type="text" name="numero_socio" value="<?= e($contract['numero_socio']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>Bairro</label>
                        <input type="text" name="bairro_socio" value="<?= e($contract['bairro_socio']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>Cidade</label>
                        <input type="text" name="cidade_socio" value="<?= e($contract['cidade_socio']); ?>" class="editable-field" disabled>
                    </div>
                    <div class="form-group">
                        <label>UF</label>
                        <input type="text" name="uf_socio" value="<?= e($contract['uf_socio']); ?>" class="editable-field" disabled>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend class="section-title">Documentos Anexados</legend>
                <div class="section-fields">
                    <div class="documents-grid">
                        <?php
                        $documents = [
                            ['label' => 'Contrato Social', 'field' => 'doc_contrato_social'],
                            ['label' => 'Comprovante End. Empresa', 'field' => 'doc_end_empresa'],
                            ['label' => 'Cartão CNPJ', 'field' => 'doc_cartao_cnpj'],
                            ['label' => 'CORE', 'field' => 'doc_core'],
                            ['label' => 'CPF do Sócio', 'field' => 'doc_cpf_socio'],
                            ['label' => 'Identidade do Sócio', 'field' => 'doc_identidade_socio'],
                            ['label' => 'Comprovante End. Sócio', 'field' => 'doc_end_socio_comp'],
                        ];
                        
                        foreach ($documents as $doc):
                            $filePath = $contract[$doc['field']] ?? '';
                            $hasFile = !empty($filePath);
                        ?>
                        <div class="document-item <?= $hasFile ? 'has-file' : 'no-file' ?>">
                            <div class="document-info">
                                <i class="fa-regular fa-file-lines document-icon"></i>
                                <div class="document-details">
                                    <div class="document-label"><?= e($doc['label']); ?></div>
                                    <?php if ($hasFile): ?>
                                        <div class="document-filename"><?= e(basename($filePath)); ?></div>
                                    <?php else: ?>
                                        <div class="document-empty">Não anexado</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="document-actions">
                                <div class="edit-only-upload">
                                    <label for="upload_<?= $doc['field']; ?>" class="doc-btn doc-btn-upload" title="Anexar arquivo">
                                        <i class="fa-solid fa-upload"></i>
                                    </label>
                                    <input type="file" 
                                           id="upload_<?= $doc['field']; ?>" 
                                           name="upload_<?= $doc['field']; ?>" 
                                           class="doc-file-input" 
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           style="display:none;" 
                                           disabled>
                                </div>

                                <?php if ($hasFile): ?>
                                    <?php
                                        $docUrl = 'serve_file.php?path=' . urlencode($filePath);
                                    ?>
                                    <button type="button" class="doc-btn doc-btn-view" 
                                            data-file="<?= e($docUrl); ?>" 
                                            data-label="<?= e($doc['label']); ?>"
                                            title="Visualizar documento">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    <a href="<?= e($docUrl); ?>" 
                                       class="doc-btn doc-btn-download" 
                                       download
                                       title="Baixar documento">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                    <button type="button" 
                                            class="doc-btn doc-btn-remove edit-only-action" 
                                            data-field="<?= e($doc['field']); ?>"
                                            title="Remover documento"
                                            style="display:none;"
                                            disabled>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="<?= e($doc['field']); ?>" value="<?= e($filePath); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend class="section-title">Status</legend>
                <div class="section-fields form-grid">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="editable-field" disabled>
                            <?php
                            // Defina aqui as opções de status do seu sistema
                            $status_options = [
                                'Pendente', 
                                'Ativo', 
                                'Em Análise', 
                                'Cancelado', 
                                'Concluído'
                            ];
                            
                            // Verifica se o status atual está na lista, se não, adiciona para não quebrar
                            if (!in_array($contract['status'], $status_options) && !empty($contract['status'])) {
                                $status_options[] = $contract['status'];
                            }

                            foreach ($status_options as $opt):
                                $selected = ($contract['status'] === $opt) ? 'selected' : '';
                            ?>
                                <option value="<?= e($opt); ?>" <?= $selected; ?>><?= e($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Data Cadastro</label>
                        <input type="text" value="<?= e($contract['data_cadastro']); ?>" disabled>
                        
                        <input type="hidden" name="data_cadastro" value="<?= e($contract['data_cadastro']); ?>">
                    </div>
                </div>
            </fieldset>

            <div class="button-container">
                <a class="btn btn-secondary" href="index.php?section=contratos">Voltar</a>
                <button type="submit" class="btn" id="btnSave" style="display: none;">Salvar</button>
            </div>
        </form>
    </div>

    <div class="document-modal" id="documentModal">
        <div class="document-modal-overlay"></div>
        <div class="document-modal-content">
            <div class="document-modal-header">
                <h3 id="documentModalTitle">Documento</h3>
                <button type="button" class="document-modal-close" id="closeDocumentModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="document-modal-body">
                <img id="documentModalImage" src="" alt="Documento">
                <iframe id="documentModalFrame" src="" style="display: none;"></iframe>
                <div id="documentModalError" style="display: none;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>Não foi possível carregar o documento.</p>
                </div>
            </div>
            <div class="document-modal-footer">
                <a id="documentModalDownload" href="" download class="btn btn-download">
                    <i class="fa-solid fa-download"></i> Baixar
                </a>
                <a id="documentModalNewTab" href="" target="_blank" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir em nova aba
                </a>
            </div>
        </div>
    </div>

    <script src="../assets/js/toast.js"></script>
    <script src="assets/js/edit-form.js"></script>
</body>
</html>