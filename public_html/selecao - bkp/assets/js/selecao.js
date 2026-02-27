"use strict";

/* ============================================
   Ficha de Seleção — JavaScript
   ============================================ */

document.addEventListener("DOMContentLoaded", () => {

    // ---- Carregar cargos por empresa ----
    function carregarCargos(empresa) {
        const sel = document.getElementById("cargo");
        sel.innerHTML = '<option value="">Carregando vagas...</option>';
        sel.disabled = true;

        const url = empresa ? `get_cargos.php?empresa=${encodeURIComponent(empresa)}` : 'get_cargos.php';

        fetch(url)
            .then(r => r.json())
            .then(data => {
                sel.innerHTML = '<option value="">Selecione...</option>';
                sel.disabled = false;
                if (data.cargos && data.cargos.length) {
                    data.cargos.forEach(c => {
                        const opt = document.createElement("option");
                        opt.value = c.nome;
                        opt.textContent = c.nome;
                        sel.appendChild(opt);
                    });
                }
                const optOutro = document.createElement("option");
                optOutro.value = "__outro__";
                optOutro.textContent = "Outro (não listado)";
                sel.appendChild(optOutro);
            })
            .catch(() => {
                sel.innerHTML = '<option value="">Erro ao carregar vagas</option>';
                sel.disabled = false;
                const optOutro = document.createElement("option");
                optOutro.value = "__outro__";
                optOutro.textContent = "Outro (não listado)";
                sel.appendChild(optOutro);
            });
    }

    // Carrega vagas ao mudar empresa
    document.getElementById("empresa").addEventListener("change", function () {
        carregarCargos(this.value);
    });

    // Carga inicial (sem empresa selecionada)
    carregarCargos('');

    // ---- Cargo "outro" ----
    document.getElementById("cargo").addEventListener("change", function () {
        const grupo = document.getElementById("grupo-outro-cargo");
        const input = document.getElementById("cargoOutro");
        if (this.value === "__outro__") {
            grupo.style.display = "flex";
            input.setAttribute("required", "required");
        } else {
            grupo.style.display = "none";
            input.removeAttribute("required");
            input.value = "";
        }
    });

    // ---- Filhos ----
    document.querySelectorAll('input[name="possuiFilhos"]').forEach(radio => {
        radio.addEventListener("change", function () {
            const grupo = document.getElementById("grupo-qtd-filhos");
            const input = document.getElementById("qtdFilhos");
            if (this.value === "sim") {
                grupo.style.display = "flex";
                input.setAttribute("required", "required");
            } else {
                grupo.style.display = "none";
                input.removeAttribute("required");
                input.value = "";
            }
        });
    });

    // ---- CNH ----
    document.querySelectorAll('input[name="possuiCnh"]').forEach(radio => {
        radio.addEventListener("change", function () {
            const grupo = document.getElementById("grupo-cnh");
            const select = document.getElementById("categoriaCnh");
            if (this.value === "sim") {
                grupo.style.display = "flex";
                select.setAttribute("required", "required");
            } else {
                grupo.style.display = "none";
                select.removeAttribute("required");
                select.value = "";
            }
        });
    });

    // ---- Disponibilidade horário ----
    document.querySelectorAll('input[name="disponibilidade_horario"]').forEach(radio => {
        radio.addEventListener("change", function () {
            const grupo = document.getElementById("grupo-disponibilidade-info");
            if (this.value === "nao") {
                grupo.style.display = "flex";
            } else {
                grupo.style.display = "none";
                document.getElementById("disponibilidade_info").value = "";
            }
        });
    });

    // ---- Máscara CPF ----
    document.getElementById("cpf").addEventListener("input", function () {
        let v = this.value.replace(/\D/g, "").slice(0, 11);
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        this.value = v;
        const erro = document.getElementById("erro-cpf");
        if (erro) { erro.textContent = ""; }
        this.style.borderColor = "";
    });

    // ---- Validação CPF no blur ----
    function validarCPF(cpf) {
        cpf = cpf.replace(/\D/g, "");
        if (cpf.length !== 11) return false;
        if (/^(\d)\1{10}$/.test(cpf)) return false;
        var soma = 0, resto;
        for (var i = 0; i < 9; i++) soma += parseInt(cpf.charAt(i)) * (10 - i);
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.charAt(9))) return false;
        soma = 0;
        for (var j = 0; j < 10; j++) soma += parseInt(cpf.charAt(j)) * (11 - j);
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.charAt(10))) return false;
        return true;
    }

    document.getElementById("cpf").addEventListener("blur", function () {
        var erro = document.getElementById("erro-cpf");
        if (this.value && !validarCPF(this.value)) {
            if (erro) erro.textContent = "CPF inválido";
            this.style.borderColor = "#e53935";
        } else {
            if (erro) erro.textContent = "";
            this.style.borderColor = "";
        }
    });

    // ---- Máscara Celular ----
    document.getElementById("celular").addEventListener("input", function () {
        let v = this.value.replace(/\D/g, "").slice(0, 11);
        if (v.length > 6) {
            v = `(${v.slice(0, 2)}) ${v.slice(2, 7)}-${v.slice(7)}`;
        } else if (v.length > 2) {
            v = `(${v.slice(0, 2)}) ${v.slice(2)}`;
        } else if (v.length > 0) {
            v = `(${v}`;
        }
        this.value = v;
    });

    // ---- CEP auto-fill ----
    const cepInput = document.getElementById("cep");

    cepInput.addEventListener("input", function () {
        let v = this.value.replace(/\D/g, "").slice(0, 8);
        if (v.length > 5) v = v.slice(0, 5) + "-" + v.slice(5);
        this.value = v;
    });

    cepInput.addEventListener("blur", buscarCep);
    cepInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter") { e.preventDefault(); buscarCep(); }
    });

    function buscarCep() {
        const cep = cepInput.value.replace(/\D/g, "");
        if (cep.length !== 8) return;
        const spinner = document.getElementById("cep-spinner");
        spinner.style.display = "inline";
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(r => r.json())
            .then(data => {
                spinner.style.display = "none";
                if (data.erro) {
                    cepInput.style.borderColor = "#e53935";
                    Toast.error('CEP não encontrado!');
                    return;
                }
                cepInput.style.borderColor = "";
                document.getElementById("endereco").value = data.logradouro || "";
                document.getElementById("bairro").value = data.bairro || "";
                document.getElementById("cidade").value = data.localidade || "";
                document.getElementById("uf").value = data.uf || "";
                document.getElementById("numero").focus();
            })
            .catch(() => { spinner.style.display = "none"; });
    }

    /* =============================================
       EXPERIÊNCIAS ANTERIORES
       ============================================= */

    const MAX_EXP = 3;
    let experiencias = []; // Array de objetos

    const listaEl    = document.getElementById("lista-experiencias");
    const btnAddExp  = document.getElementById("btn-add-exp");
    const counterMsg = document.getElementById("exp-counter-msg");
    const hiddenExp  = document.getElementById("experiencia");

    function atualizarCounter() {
        const n = experiencias.length;
        if (n === 0) {
            counterMsg.textContent = "";
        } else if (n === MAX_EXP) {
            counterMsg.textContent = `Máximo atingido (${n}/${MAX_EXP})`;
        } else {
            counterMsg.textContent = `${n}/${MAX_EXP} experiência(s)`;
        }
        btnAddExp.disabled = (n >= MAX_EXP);
        hiddenExp.value = JSON.stringify(experiencias);
    }

    function renderizarExperiencias() {
        listaEl.innerHTML = "";
        experiencias.forEach((exp, idx) => {
            const bloco = document.createElement("div");
            bloco.className = "exp-bloco";
            bloco.dataset.idx = idx;
            bloco.innerHTML = `
                <div class="exp-bloco-header">
                    <div class="exp-bloco-titulo">
                        <i class="fa-solid fa-briefcase"></i>
                        Experiência ${idx + 1}
                    </div>
                    <button type="button" class="btn-remover-exp" onclick="removerExp(${idx})">
                        <i class="fa-solid fa-xmark"></i> Remover
                    </button>
                </div>
                <div class="exp-grid">
                    <div class="form-group">
                        <label>Empresa <span class="obrigatorio">*</span></label>
                        <input type="text" placeholder="Nome da empresa" value="${esc(exp.empresa)}"
                               onchange="atualizarCampoExp(${idx}, 'empresa', this.value)" required>
                    </div>
                    <div class="form-group">
                        <label>Cargo <span class="obrigatorio">*</span></label>
                        <input type="text" placeholder="Cargo exercido" value="${esc(exp.cargo)}"
                               onchange="atualizarCampoExp(${idx}, 'cargo', this.value)" required>
                    </div>
                    <div class="form-group">
                        <label>Último Salário</label>
                        <input type="text" placeholder="Ex: R$ 2.000,00" value="${esc(exp.ultimo_salario)}"
                               onchange="atualizarCampoExp(${idx}, 'ultimo_salario', this.value)"
                               class="exp-salario-mask">
                    </div>
                    <div class="form-group">
                        <label>Data de Admissão <span class="obrigatorio">*</span></label>
                        <input type="date" value="${esc(exp.data_admissao)}"
                               onchange="atualizarCampoExp(${idx}, 'data_admissao', this.value)" required>
                    </div>
                    <div class="form-group">
                        <label>Data de Demissão</label>
                        <input type="date" value="${esc(exp.data_demissao)}"
                               onchange="atualizarCampoExp(${idx}, 'data_demissao', this.value)"
                               placeholder="Deixe em branco se atual">
                    </div>
                    <div class="form-group">
                        <label>Motivo de Saída</label>
                        <input type="text" placeholder="Ex: Crescimento profissional" value="${esc(exp.motivo_saida)}"
                               onchange="atualizarCampoExp(${idx}, 'motivo_saida', this.value)">
                    </div>
                </div>
            `;
            listaEl.appendChild(bloco);
        });
        atualizarCounter();
    }

    function esc(v) {
        if (!v) return "";
        return String(v)
            .replace(/&/g, "&amp;")
            .replace(/"/g, "&quot;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
    }

    // Expostos globalmente para uso nos onchange/onclick inline
    window.atualizarCampoExp = function(idx, campo, valor) {
        if (experiencias[idx] !== undefined) {
            experiencias[idx][campo] = valor;
            hiddenExp.value = JSON.stringify(experiencias);
        }
    };

    window.removerExp = function(idx) {
        experiencias.splice(idx, 1);
        renderizarExperiencias();
    };

    btnAddExp.addEventListener("click", () => {
        if (experiencias.length >= MAX_EXP) return;
        experiencias.push({
            empresa: "",
            cargo: "",
            data_admissao: "",
            data_demissao: "",
            ultimo_salario: "",
            motivo_saida: ""
        });
        renderizarExperiencias();
        // Foca no primeiro campo do novo bloco
        setTimeout(() => {
            const blocos = listaEl.querySelectorAll(".exp-bloco");
            const ultimo = blocos[blocos.length - 1];
            if (ultimo) {
                const inp = ultimo.querySelector("input");
                if (inp) inp.focus();
            }
        }, 50);
    });

    // Inicializa counter
    atualizarCounter();

    /* =============================================
       SUBMIT
       ============================================= */

    document.getElementById("formSelecao").addEventListener("submit", function (e) {
        e.preventDefault();

        // Valida campos obrigatórios nas experiências
        let expValida = true;
        experiencias.forEach((exp, idx) => {
            if (!exp.empresa.trim() || !exp.cargo.trim() || !exp.data_admissao) {
                expValida = false;
                Toast.error(`Preencha os campos obrigatórios da Experiência ${idx + 1} (Empresa, Cargo e Data de Admissão).`);
            }
        });
        if (!expValida) return;

        const btn = document.getElementById("btn-submit");
        const overlay = document.getElementById("griffus-loading-overlay");

        btn.disabled = true;
        btn.textContent = "Enviando...";
        if (overlay) overlay.classList.add("active");

        // Atualiza hidden antes de enviar
        hiddenExp.value = JSON.stringify(experiencias);

        // Monta cargo final
        const cargoSel = document.getElementById("cargo").value;
        const cargoFinal = cargoSel === "__outro__"
            ? document.getElementById("cargoOutro").value
            : cargoSel;

        const form = e.target;
        const formData = new FormData(form);
        formData.set("cargo", cargoFinal);

        fetch("salvar_selecao.php", {
            method: "POST",
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Redireciona para a página de obrigado
                    window.location.href = "../obrigado.html";
                } else {
                    if (overlay) overlay.classList.remove("active");
                    Toast.error(data.message || "Erro ao enviar. Tente novamente.");
                    btn.disabled = false;
                    btn.textContent = "Enviar Ficha";
                }
            })
            .catch(() => {
                if (overlay) overlay.classList.remove("active");
                Toast.error('Erro de conexão. Verifique sua internet e tente novamente.');
                btn.disabled = false;
                btn.textContent = "Enviar Ficha";
            });
    });

});