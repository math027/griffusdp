"use strict";

/* ═══════════════ MÁSCARA DE TELEFONE ═══════════════ */
(function () {
    var tel = document.getElementById('telefone');
    if (!tel) return;
    tel.addEventListener('input', function () {
        var v = this.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 6) {
            this.value = '(' + v.slice(0, 2) + ') ' + v.slice(2, 7) + '-' + v.slice(7);
        } else if (v.length > 2) {
            this.value = '(' + v.slice(0, 2) + ') ' + v.slice(2);
        } else if (v.length > 0) {
            this.value = '(' + v;
        }
    });
})();

/* ═══════════════ CARREGAR VAGAS ═══════════════ */
(function () {
    var select = document.getElementById('cargo_desejado');
    if (!select) return;

    fetch('get_vagas.php')
        .then(function (r) { return r.json(); })
        .then(function (vagas) {
            select.innerHTML = '<option value="">Selecione a vaga...</option>';
            vagas.forEach(function (cargo) {
                var opt = document.createElement('option');
                opt.value = cargo;
                opt.textContent = cargo;
                select.appendChild(opt);
            });
            // Opção "Outro"
            var outro = document.createElement('option');
            outro.value = '__outro__';
            outro.textContent = 'Outro (especificar)';
            select.appendChild(outro);
        })
        .catch(function () {
            select.innerHTML = '<option value="">Erro ao carregar vagas</option>';
        });

    select.addEventListener('change', function () {
        var grupo = document.getElementById('grupo-outro-cargo');
        var input = document.getElementById('cargoOutro');
        if (this.value === '__outro__') {
            grupo.style.display = '';
            input.required = true;
        } else {
            grupo.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    });
})();

/* ═══════════════ VALIDAÇÃO E ENVIO ═══════════════ */
(function () {
    var form = document.getElementById('formCurriculo');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Validar arquivo
        var fileInput = document.getElementById('curriculo');
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            Toast.error('Por favor, anexe seu currículo.');
            return;
        }

        var file = fileInput.files[0];
        var ext = file.name.split('.').pop().toLowerCase();
        if (!['pdf', 'doc', 'docx'].includes(ext)) {
            Toast.error('Formato não permitido. Use PDF, DOC ou DOCX.');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            Toast.error('O arquivo deve ter no máximo 5MB.');
            return;
        }

        // Cargo — se "Outro", usar o campo texto
        var cargoSelect = document.getElementById('cargo_desejado');
        var cargoOutro  = document.getElementById('cargoOutro');
        var cargoFinal  = cargoSelect.value === '__outro__' ? cargoOutro.value.trim() : cargoSelect.value;

        if (!cargoFinal) {
            Toast.error('Selecione ou informe o cargo desejado.');
            return;
        }

        // Monta FormData
        var fd = new FormData();
        fd.append('nome_completo', document.getElementById('nome_completo').value.trim());
        fd.append('telefone',      document.getElementById('telefone').value.trim());
        fd.append('email',         document.getElementById('email').value.trim());
        fd.append('cidade',        document.getElementById('cidade').value.trim());
        fd.append('cargo_desejado', cargoFinal);
        fd.append('curriculo',     file);

        // Loading
        var overlay = document.getElementById('griffus-loading-overlay');
        if (overlay) overlay.classList.add('active');

        var btn = document.getElementById('btn-submit');
        btn.disabled = true;

        try {
            var res  = await fetch('salvar_curriculo.php', { method: 'POST', body: fd });
            var data = await res.json();

            if (data.success) {
                window.location.href = '../obrigado.html';
            } else {
                Toast.error(data.message || 'Erro ao enviar currículo.');
            }
        } catch (err) {
            Toast.error('Erro de conexão. Tente novamente.');
        } finally {
            if (overlay) overlay.classList.remove('active');
            btn.disabled = false;
        }
    });
})();
