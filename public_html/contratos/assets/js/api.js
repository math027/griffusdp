const fileLimits = {
    'docContratoSocial': 10,
    'docEndEmpresa': 5,
    'docCartaoCnpj': 3,
    'docCore': 5,
    'docCpfSocio': 3,
    'docIdentidadeSocio': 5,
    'docCnh': 5,
    'docEndSocioComp': 5
};

// Função para validar CPF
function validarCPF(cpf) {
    // Remove caracteres não numéricos
    cpf = cpf.replace(/[^\d]/g, '');

    // Verifica se tem 11 dígitos
    if (cpf.length !== 11) return false;

    // Verifica se todos os dígitos são iguais
    if (/^(\d)\1{10}$/.test(cpf)) return false;

    // Validação do primeiro dígito verificador
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(9))) return false;

    // Validação do segundo dígito verificador
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(10))) return false;

    return true;
}

// Função para buscar CEP
async function buscarCEP(cep, sufixo = '') {
    // Remove caracteres não numéricos
    cep = cep.replace(/[^\d]/g, '');

    if (cep.length !== 8) {
        return;
    }

    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const dados = await response.json();

        if (dados.erro) {
            Toast.error('CEP não encontrado!');
            return;
        }

        // Preenche os campos (sufixo: '' para empresa, 'Socio' para sócio)
        document.getElementById('endereco' + sufixo).value = dados.logradouro || '';
        document.getElementById('bairro' + sufixo).value = dados.bairro || '';
        document.getElementById('cidade' + sufixo).value = dados.localidade || '';
        document.getElementById('uf' + sufixo).value = dados.uf || '';

        // Foca no campo número
        document.getElementById('numero' + sufixo).focus();

    } catch (error) {
        Toast.error('Erro ao buscar CEP. Tente novamente.');
    }
}

// Função chamada pelo HTML (onchange)
function validateAndPreview(input) {
    const statusDiv = document.getElementById('status-' + input.id);
    // Tenta pegar o texto do label próximo
    let labelText = null;
    if (input.nextElementSibling) {
        labelText = input.nextElementSibling.querySelector('.upload-text');
    }

    const limitMB = fileLimits[input.id] ?? parseFloat(input.getAttribute('data-limit')) ?? 10;

    // Reseta visual
    if (statusDiv) {
        statusDiv.className = 'file-status';
        statusDiv.textContent = '';
    }

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMB = file.size / 1024 / 1024;

        if (labelText) {
            let fileName = file.name;
            if (fileName.length > 25) fileName = fileName.substring(0, 24) + '...';
            labelText.textContent = fileName;
        }

        if (sizeMB > limitMB) {
            if (statusDiv) {
                statusDiv.textContent = `Arquivo muito grande (${sizeMB.toFixed(1)}MB). Limite: ${limitMB}MB.`;
                statusDiv.classList.add('error');
            }
            input.value = ''; // Limpa o arquivo
            if (labelText) labelText.textContent = 'Selecionar arquivo...';
            Toast.error(`Arquivo muito grande! O limite é ${limitMB}MB.`);
        } else {
            if (statusDiv) {
                statusDiv.textContent = `Arquivo válido (${sizeMB.toFixed(1)}MB).`;
                statusDiv.classList.add('success');
            }
        }
    }
}

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
            const scale = Math.min(maxSide / width, maxSide / height, 1.0); // nunca amplia
            if (scale < 1.0) {
                width  = Math.round(width  * scale);
                height = Math.round(height * scale);
            }

            const canvas = document.createElement('canvas');
            canvas.width  = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(img, 0, 0, width, height);

            canvas.toBlob(
                (blob) => {
                    if (blob) {
                        const nomeSemExt = file.name.replace(/\.[^.]+$/, '');
                        resolve(new File([blob], nomeSemExt + '.webp', { type: 'image/webp' }));
                    } else {
                        resolve(file); // fallback: envia original
                    }
                },
                'image/webp',
                quality
            );
        };

        img.onerror = () => { URL.revokeObjectURL(url); resolve(file); }; // fallback
        img.src = url;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formContratos');

    // Adiciona máscara e validação de CPF
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        // Máscara de CPF
        cpfInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            }
        });

        // Validação ao sair do campo
        cpfInput.addEventListener('blur', (e) => {
            const cpf = e.target.value;
            const erro = document.getElementById('erro-cpf');

            if (cpf && !validarCPF(cpf)) {
                if (erro) erro.textContent = 'CPF inválido';
                e.target.style.borderColor = 'red';
            } else {
                if (erro) erro.textContent = '';
                e.target.style.borderColor = '';
            }
        });
    }

    // Adiciona máscara de CNPJ
    const cnpjInput = document.getElementById('cnpj');
    if (cnpjInput) {
        cnpjInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 14) {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });
    }

    // Busca CEP da Empresa
    const cepEmpresa = document.getElementById('cep');
    if (cepEmpresa) {
        cepEmpresa.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/\.(\d{3})(\d)/, '.$1-$2');
                e.target.value = value;
            }
        });

        cepEmpresa.addEventListener('blur', (e) => {
            buscarCEP(e.target.value, '');
        });
    }

    // Busca CEP do Sócio
    const cepSocio = document.getElementById('cepSocio');
    if (cepSocio) {
        cepSocio.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/\.(\d{3})(\d)/, '.$1-$2');
                e.target.value = value;
            }
        });

        cepSocio.addEventListener('blur', (e) => {
            buscarCEP(e.target.value, 'Socio');
        });
    }

    // Máscara de telefone
    const celularInput = document.getElementById('celular');
    if (celularInput) {
        celularInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Valida CPF antes de enviar
            const cpf = document.getElementById('cpf').value;
            if (!validarCPF(cpf)) {
                Toast.error('CPF inválido! Por favor, corrija antes de enviar.');
                document.getElementById('cpf').focus();
                return;
            }

            // Valida documentos obrigatórios (segunda camada de segurança)
            const docsObrigatorios = {
                'docContratoSocial':  'Contrato Social / Certificado MEI',
                'docEndEmpresa':      'Comprovante de Endereço (Empresa)',
                'docCartaoCnpj':      'Cartão CNPJ',
                'docIdentidadeSocio': 'Identidade do Sócio (RG)',
                'docEndSocioComp':    'Comprovante de Endereço (Sócio)'
            };

            const faltando = [];
            for (const [inputId, label] of Object.entries(docsObrigatorios)) {
                const input = document.getElementById(inputId);
                if (!input || !input.files || input.files.length === 0) {
                    faltando.push(label);
                    if (input) {
                        const wrapper = input.closest('.form-group');
                        if (wrapper) {
                            const uploadLabel = wrapper.querySelector('.file-upload-label');
                            if (uploadLabel) uploadLabel.classList.add('missing-doc');
                        }
                    }
                } else {
                    // Limpar destaque se documento presente
                    if (input) {
                        const wrapper = input.closest('.form-group');
                        if (wrapper) {
                            const uploadLabel = wrapper.querySelector('.file-upload-label');
                            if (uploadLabel) uploadLabel.classList.remove('missing-doc');
                        }
                    }
                }
            }

            if (faltando.length > 0) {
                Toast.error('Documentos obrigatórios faltando:\n• ' + faltando.join('\n• '));
                return;
            }

            // Valida tamanho dos arquivos
            for (const [inputId, limitMB] of Object.entries(fileLimits)) {
                const input = document.getElementById(inputId);
                if (input && input.files.length > 0) {
                    const fileSize = input.files[0].size / 1024 / 1024;
                    if (fileSize > limitMB) {
                        Toast.error(`Erro: O arquivo do campo ${inputId} ainda excede o limite de ${limitMB}MB.`);
                        return;
                    }
                }
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerText;
            const overlay = document.getElementById('griffus-loading-overlay');

            submitBtn.innerText = 'Comprimindo imagens...';
            submitBtn.disabled = true;
            if (overlay) overlay.classList.add('active');

            try {
                // ── Monta FormData com imagens já comprimidas no navegador ──
                const formData = new FormData(form);
                const imageInputIds = Object.keys(fileLimits);

                for (const inputId of imageInputIds) {
                    const input = document.getElementById(inputId);
                    if (input && input.files.length > 0) {
                        const fileOriginal = input.files[0];
                        const fileWebP = await compressImageToWebP(fileOriginal);
                        formData.set(inputId, fileWebP, fileWebP.name);
                    }
                }

                submitBtn.innerText = 'Enviando...';

                const response = await fetch('salvar_contrato.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Erro no servidor.');
                }

                // Redireciona para a página de obrigado
                window.location.href = '../obrigado.html';

            } catch (error) {
                if (overlay) overlay.classList.remove('active');
                Toast.error('Erro: ' + error.message);
                submitBtn.innerText = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    }
});