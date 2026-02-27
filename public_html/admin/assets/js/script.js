"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const backdrop = document.querySelector(".modal-backdrop");
    const modal = document.querySelector(".modal");
    const modalBody = document.querySelector(".modal-body");

    if (!backdrop || !modal || !modalBody) {
        return;
    }

    const esc = (value) => String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    document.querySelectorAll(".js-view").forEach((btn) => {
        btn.addEventListener("click", () => {
            const data = JSON.parse(btn.dataset.contract);
            const docs = data.documentos || {};
            const docsList = Object.keys(docs).length
                ? Object.entries(docs).map(([label, file]) => `<li>${esc(label)}: ${esc(file)}</li>`).join("")
                : "<li>Nenhum</li>";

            modalBody.innerHTML = `
                <p><strong>ID:</strong> ${esc(data.id)}</p>
                <p><strong>Razão Social:</strong> ${esc(data.razao_social)}</p>
                <p><strong>CNPJ:</strong> ${esc(data.cnpj)}</p>
                <p><strong>Endereço Empresa:</strong> ${esc(data.endereco)}, ${esc(data.numero)} - ${esc(data.bairro)} - ${esc(data.cidade)}/${esc(data.uf)}</p>
                <p><strong>CEP Empresa:</strong> ${esc(data.cep)}</p>
                <p><strong>Contato Empresa:</strong> ${esc(data.telefone)} / ${esc(data.celular)} - ${esc(data.email_empresa)}</p>
                <p><strong>Banco:</strong> ${esc(data.banco)} - Ag: ${esc(data.agencia)} - Conta: ${esc(data.conta)}</p>
                <p><strong>PIX:</strong> ${esc(data.pix)}</p>
                <p><strong>Sócio:</strong> ${esc(data.nome_socio)} - ${esc(data.cpf)} / ${esc(data.rg)} (${esc(data.orgao_expedidor)})</p>
                <p><strong>Nascimento:</strong> ${esc(data.nascimento)}</p>
                <p><strong>Nacionalidade:</strong> ${esc(data.nacionalidade)}</p>
                <p><strong>Estado Civil:</strong> ${esc(data.estado_civil)}</p>
                <p><strong>Profissão:</strong> ${esc(data.profissao)}</p>
                <p><strong>Email Sócio:</strong> ${esc(data.email_socio)}</p>
                <p><strong>Endereço Sócio:</strong> ${esc(data.endereco_socio)}, ${esc(data.numero_socio)} - ${esc(data.bairro_socio)} - ${esc(data.cidade_socio)}/${esc(data.uf_socio)}</p>
                <p><strong>CEP Sócio:</strong> ${esc(data.cep_socio)}</p>
                <p><strong>Status:</strong> ${esc(data.status)}</p>
                <p><strong>Data Cadastro:</strong> ${esc(data.data_cadastro)}</p>
                <p><strong>Documentos anexados:</strong></p>
                <ul>${docsList}</ul>
            `;

            backdrop.style.display = "block";
            modal.style.display = "block";
        });
    });

    const closeBtn = document.querySelector(".js-close-modal");
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            backdrop.style.display = "none";
            modal.style.display = "none";
        });
    }

    backdrop.addEventListener("click", () => {
        backdrop.style.display = "none";
        modal.style.display = "none";
    });
});
