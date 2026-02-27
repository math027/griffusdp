"use strict";

// ─────────────────────────────────────────────────────────────────────────────
// Comprime e converte uma imagem para WebP no navegador usando Canvas API.
// PDFs e outros arquivos não-imagem são retornados sem alteração.
// ─────────────────────────────────────────────────────────────────────────────
async function compressImageToWebP(file, maxSide = 1920, quality = 0.72) {
    if (!file.type.startsWith('image/')) return file; // PDF ou outro: sem alteração

    return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(file);

        img.onload = () => {
            URL.revokeObjectURL(url);

            let { width, height } = img;
            const scale = Math.min(maxSide / width, maxSide / height, 1.0);
            if (scale < 1.0) {
                width = Math.round(width * scale);
                height = Math.round(height * scale);
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(img, 0, 0, width, height);

            canvas.toBlob(
                (blob) => {
                    if (blob) {
                        const nomeSemExt = file.name.replace(/\.[^.]+$/, '');
                        resolve(new File([blob], nomeSemExt + '.webp', { type: 'image/webp' }));
                    } else {
                        resolve(file);
                    }
                },
                'image/webp',
                quality
            );
        };

        img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
        img.src = url;
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const btnEnableEdit = document.getElementById("btnEnableEdit");
    const btnCancelEdit = document.getElementById("btnCancelEdit");
    const btnSave = document.getElementById("btnSave");
    const editableFields = document.querySelectorAll(".editable-field");
    const docFileInputs = document.querySelectorAll(".doc-file-input");
    const docRemoveButtons = document.querySelectorAll(".doc-btn-remove");
    const editOnlyUploads = document.querySelectorAll(".edit-only-upload");
    const form = document.getElementById("formEditContrato");

    // Armazena os valores originais para restaurar ao cancelar
    const originalValues = {};
    editableFields.forEach(field => {
        originalValues[field.name] = field.value;
    });

    // Função para verificar alterações nos inputs de texto
    function checkFieldChange(field) {
        if (field.value !== originalValues[field.name]) {
            field.classList.add("field-changed");
        } else {
            field.classList.remove("field-changed");
        }
    }

    // Função para verificar alterações nos arquivos
    function checkFileChange(fileInput) {
        const docItem = fileInput.closest(".document-item");
        const statusDiv = docItem.querySelector(".document-status");

        if (fileInput.files && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const sizeMB = file.size / (1024 * 1024);
            const limit = 5;

            if (sizeMB > limit) {
                Toast.error(`Arquivo muito grande! Limite: ${limit}MB`);
                fileInput.value = '';
                if (statusDiv) statusDiv.textContent = '';
                docItem.classList.remove("doc-changed");
                const fileNameDiv = docItem.querySelector(".document-filename") || docItem.querySelector(".document-empty");
                if (fileNameDiv && fileNameDiv.dataset.originalText) {
                    fileNameDiv.textContent = fileNameDiv.dataset.originalText;
                    if (fileNameDiv.dataset.originalText.includes("Nenhum arquivo")) {
                        fileNameDiv.classList.add("document-empty");
                        fileNameDiv.classList.remove("document-filename");
                    }
                }
                return;
            }

            docItem.classList.add("doc-changed");
            if (statusDiv) {
                statusDiv.textContent = `Arquivo selecionado (${sizeMB.toFixed(2)}MB)`;
                statusDiv.classList.add('changed');
            }

            const fileNameDiv = docItem.querySelector(".document-filename") || docItem.querySelector(".document-empty");
            if (fileNameDiv) {
                if (!fileNameDiv.dataset.originalText) {
                    fileNameDiv.dataset.originalText = fileNameDiv.textContent;
                }
                fileNameDiv.textContent = "Novo: " + fileInput.files[0].name;
                fileNameDiv.classList.remove("document-empty");
                fileNameDiv.classList.add("document-filename");
            }
        } else {
            docItem.classList.remove("doc-changed");
            if (statusDiv) {
                statusDiv.textContent = '';
                statusDiv.classList.remove('changed');
            }
            const fileNameDiv = docItem.querySelector(".document-filename") || docItem.querySelector(".document-empty");
            if (fileNameDiv && fileNameDiv.dataset.originalText) {
                fileNameDiv.textContent = fileNameDiv.dataset.originalText;
            }
        }
    }

    // Função para habilitar modo de edição
    function enableEditMode() {
        editableFields.forEach(field => {
            field.disabled = false;
            field.addEventListener("input", () => checkFieldChange(field));
            field.addEventListener("change", () => checkFieldChange(field));
        });

        docFileInputs.forEach(input => {
            input.disabled = false;
            input.addEventListener("change", () => checkFileChange(input));
        });
        docRemoveButtons.forEach(btn => {
            btn.disabled = false;
            btn.style.display = "inline-flex";
        });
        editOnlyUploads.forEach(div => div.style.display = "inline-flex");

        btnEnableEdit.style.display = "none";
        btnCancelEdit.style.display = "inline-block";
        btnSave.style.display = "inline-block";

        document.querySelector(".content-body").classList.add("editing-mode");
    }

    // Função para desabilitar modo de edição
    function disableEditMode() {
        editableFields.forEach(field => {
            field.value = originalValues[field.name];
            field.disabled = true;
            field.classList.remove("field-changed");
        });

        docFileInputs.forEach(input => {
            input.value = "";
            input.disabled = true;
            const docItem = input.closest(".document-item");
            if (docItem) {
                docItem.classList.remove("doc-changed");
                docItem.classList.remove("doc-removed");
                const fileNameDiv = docItem.querySelector(".document-filename") || docItem.querySelector(".document-empty");
                if (fileNameDiv && fileNameDiv.dataset.originalText) {
                    fileNameDiv.textContent = fileNameDiv.dataset.originalText;
                }
            }
        });

        // Restaura hidden inputs dos documentos marcados para remoção
        docRemoveButtons.forEach(btn => {
            btn.disabled = true;
            btn.style.display = "none";
            btn.title = "Remover documento";
            btn.innerHTML = '<i class="fa-solid fa-trash"></i>';
            const field = btn.dataset.field;
            const hiddenInput = form.querySelector(`input[type="hidden"][name="${field}"]`);
            if (hiddenInput && hiddenInput.dataset.originalValue !== undefined) {
                hiddenInput.value = hiddenInput.dataset.originalValue;
            }
        });

        editOnlyUploads.forEach(div => div.style.display = "none");

        btnEnableEdit.style.display = "inline-block";
        btnCancelEdit.style.display = "none";
        btnSave.style.display = "none";

        document.querySelector(".content-body").classList.remove("editing-mode");
    }

    // Event listeners
    if (btnEnableEdit) {
        btnEnableEdit.addEventListener("click", enableEditMode);
    }

    if (btnCancelEdit) {
        btnCancelEdit.addEventListener("click", disableEditMode);
    }

    // =========================================================================
    // REMOÇÃO DE DOCUMENTOS
    // =========================================================================
    docRemoveButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const field = btn.dataset.field;
            const docItem = btn.closest(".document-item");
            const hiddenInput = form.querySelector(`input[type="hidden"][name="${field}"]`);

            if (!hiddenInput) return;

            // Guarda o valor original na primeira vez
            if (hiddenInput.dataset.originalValue === undefined) {
                hiddenInput.dataset.originalValue = hiddenInput.value;
            }

            // Toggle: se já está marcado para remoção, desfaz
            if (docItem.classList.contains("doc-removed")) {
                docItem.classList.remove("doc-removed");
                hiddenInput.value = hiddenInput.dataset.originalValue;
                btn.title = "Remover documento";
                btn.innerHTML = '<i class="fa-solid fa-trash"></i>';

                // Restaura o texto do nome do arquivo
                const fileNameDiv = docItem.querySelector(".document-filename");
                if (fileNameDiv && fileNameDiv.dataset.originalText) {
                    fileNameDiv.textContent = fileNameDiv.dataset.originalText;
                }
            } else {
                docItem.classList.add("doc-removed");
                docItem.classList.remove("doc-changed");
                hiddenInput.value = ""; // Limpa — o backend salvará vazio no banco
                btn.title = "Desfazer remoção";
                btn.innerHTML = '<i class="fa-solid fa-rotate-left"></i>';

                // Limpa o file input caso tenha sido selecionado
                const fileInput = docItem.querySelector(".doc-file-input");
                if (fileInput) fileInput.value = "";

                // Atualiza texto visual
                const fileNameDiv = docItem.querySelector(".document-filename");
                if (fileNameDiv) {
                    if (!fileNameDiv.dataset.originalText) {
                        fileNameDiv.dataset.originalText = fileNameDiv.textContent;
                    }
                    fileNameDiv.textContent = "Marcado para remoção";
                }
            }
        });
    });

    // =========================================================================
    // SUBMIT VIA AJAX COM COMPRESSÃO DE IMAGENS NO NAVEGADOR
    // =========================================================================
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const submitBtn = document.getElementById("btnSave");
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Comprimindo imagens...';
            submitBtn.disabled = true;

            try {
                // Monta FormData com todos os campos do formulário
                const formData = new FormData(form);

                // Comprime imagens de cada input de arquivo no navegador
                for (const input of docFileInputs) {
                    if (input.files && input.files.length > 0) {
                        const fileOriginal = input.files[0];
                        const fileWebP = await compressImageToWebP(fileOriginal);
                        formData.set(input.name, fileWebP, fileWebP.name);
                    }
                }

                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando...';

                const response = await fetch(form.action, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Erro ao salvar contrato.");
                }

                Toast.success(result.message || "Contrato atualizado com sucesso!");

                // Redireciona após 1.5s para o usuário ver o toast
                setTimeout(() => {
                    window.location.href = "index.php?section=contratos";
                }, 1500);

            } catch (error) {
                Toast.error("Erro: " + error.message);
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    }

    // =========================================================================
    // VISUALIZAÇÃO DE DOCUMENTOS
    // =========================================================================

    const documentModal = document.getElementById("documentModal");
    const documentModalTitle = document.getElementById("documentModalTitle");
    const documentModalImage = document.getElementById("documentModalImage");
    const documentModalFrame = document.getElementById("documentModalFrame");
    const documentModalError = document.getElementById("documentModalError");
    const documentModalDownload = document.getElementById("documentModalDownload");
    const documentModalNewTab = document.getElementById("documentModalNewTab");
    const closeDocumentModal = document.getElementById("closeDocumentModal");
    const documentModalOverlay = document.querySelector(".document-modal-overlay");

    function openDocumentModal(filePath, label) {
        documentModalTitle.textContent = label;
        documentModalImage.style.display = "none";
        documentModalFrame.style.display = "none";
        documentModalError.style.display = "none";
        documentModalDownload.href = filePath;
        documentModalNewTab.href = filePath;

        const extension = filePath.split('.').pop().toLowerCase();
        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        const pdfExtensions = ['pdf'];

        if (imageExtensions.includes(extension)) {
            documentModalImage.src = filePath;
            documentModalImage.style.display = "block";
            documentModalImage.onerror = () => {
                documentModalImage.style.display = "none";
                documentModalError.style.display = "block";
            };
        } else if (pdfExtensions.includes(extension)) {
            documentModalFrame.src = filePath;
            documentModalFrame.style.display = "block";
        } else {
            documentModalError.style.display = "block";
        }

        documentModal.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        documentModal.classList.remove("active");
        document.body.style.overflow = "";
        documentModalImage.src = "";
        documentModalFrame.src = "";
    }

    document.querySelectorAll(".doc-btn-view").forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            const filePath = btn.dataset.file;
            const label = btn.dataset.label;
            openDocumentModal(filePath, label);
        });
    });

    if (closeDocumentModal) {
        closeDocumentModal.addEventListener("click", closeModal);
    }

    if (documentModalOverlay) {
        documentModalOverlay.addEventListener("click", closeModal);
    }

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && documentModal.classList.contains("active")) {
            closeModal();
        }
    });
});