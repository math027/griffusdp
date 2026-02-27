-- ══════════════════════════════════════════════════════════════
--  GriffusDp — database.example.sql
--  Estrutura + dados FICTÍCIOS para desenvolvimento/testes.
--  Copie este arquivo, renomeie para database.sql e importe
--  no seu banco antes de rodar o projeto.
-- ══════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET time_zone = '-03:00';

-- ──────────────────────────────────────────
--  TABELA: vagas
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS vagas (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    empresa    VARCHAR(150) NOT NULL,
    cargo      VARCHAR(150) NOT NULL,
    ativo      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO vagas (empresa, cargo, ativo, created_at) VALUES
('Construtora Horizonte Ltda',   'Auxiliar Administrativo',  1, '2026-01-10 08:00:00'),
('Construtora Horizonte Ltda',   'Assistente Financeiro',    1, '2026-01-10 08:05:00'),
('Distribuidora Ponto Certo',    'Motorista Entregador',     1, '2026-01-15 09:30:00'),
('Distribuidora Ponto Certo',    'Operador de Empilhadeira', 0, '2026-01-15 09:35:00'),
('Clínica Saúde Plena',          'Recepcionista',            1, '2026-02-01 10:00:00'),
('Clínica Saúde Plena',          'Técnico de Enfermagem',    1, '2026-02-01 10:05:00'),
('Supermercado BomPreço',        'Operador de Caixa',        1, '2026-02-10 11:00:00'),
('Supermercado BomPreço',        'Repositor de Estoque',     1, '2026-02-10 11:05:00');


-- ──────────────────────────────────────────
--  TABELA: aniversariantes
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS aniversariantes (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    nome             VARCHAR(150) NOT NULL,
    setor            VARCHAR(100) NOT NULL,
    tipo             ENUM('CLT','PJ') NOT NULL DEFAULT 'CLT',
    data_aniversario DATE         NOT NULL,
    criado_em        DATETIME     DEFAULT CURRENT_TIMESTAMP,
    atualizado_em    DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO aniversariantes (nome, setor, tipo, data_aniversario) VALUES
('Ana Paula Rodrigues',    'Administrativo',   'CLT', '1990-03-15'),
('Carlos Eduardo Mendes',  'Financeiro',       'CLT', '1985-07-22'),
('Fernanda Souza Lima',    'RH',               'CLT', '1993-11-08'),
('João Vitor Almeida',     'Operações',        'PJ',  '1988-05-30'),
('Mariana Costa Ferreira', 'Comercial',        'CLT', '1995-09-14'),
('Rafael Oliveira Santos',  'TI',              'PJ',  '1992-01-27'),
('Tatiane Gomes Pinto',    'Administrativo',   'CLT', '1989-12-03'),
('Luciano Braga Nunes',    'Operações',        'CLT', '1991-06-19');


-- ──────────────────────────────────────────
--  TABELA: contratos
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contratos (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    tipo_contrato        VARCHAR(50)  NOT NULL DEFAULT '',
    razao_social         VARCHAR(200) NOT NULL,
    cnpj                 VARCHAR(20)  NOT NULL,
    cep                  VARCHAR(10)  NOT NULL,
    endereco             VARCHAR(200) NOT NULL,
    numero               VARCHAR(20)  NOT NULL,
    bairro               VARCHAR(100) NOT NULL,
    cidade               VARCHAR(100) NOT NULL,
    uf                   CHAR(2)      NOT NULL,
    telefone             VARCHAR(20)  NOT NULL DEFAULT '',
    celular              VARCHAR(20)  NOT NULL DEFAULT '',
    email_empresa        VARCHAR(150) NOT NULL DEFAULT '',
    banco                VARCHAR(100) NOT NULL DEFAULT '',
    agencia              VARCHAR(20)  NOT NULL DEFAULT '',
    conta                VARCHAR(30)  NOT NULL DEFAULT '',
    pix                  VARCHAR(150) NOT NULL DEFAULT '',
    nome_socio           VARCHAR(150) NOT NULL,
    cpf                  VARCHAR(20)  NOT NULL,
    rg                   VARCHAR(30)  NOT NULL,
    orgao_expedidor      VARCHAR(50)  NOT NULL DEFAULT '',
    nascimento           DATE         NOT NULL,
    nacionalidade        VARCHAR(50)  NOT NULL DEFAULT 'Brasileiro(a)',
    estado_civil         VARCHAR(30)  NOT NULL DEFAULT '',
    profissao            VARCHAR(100) NOT NULL DEFAULT '',
    email_socio          VARCHAR(150) NOT NULL DEFAULT '',
    cep_socio            VARCHAR(10)  NOT NULL DEFAULT '',
    endereco_socio       VARCHAR(200) NOT NULL DEFAULT '',
    numero_socio         VARCHAR(20)  NOT NULL DEFAULT '',
    bairro_socio         VARCHAR(100) NOT NULL DEFAULT '',
    cidade_socio         VARCHAR(100) NOT NULL DEFAULT '',
    uf_socio             CHAR(2)      NOT NULL DEFAULT '',
    doc_contrato_social  VARCHAR(255) NOT NULL DEFAULT '',
    doc_end_empresa      VARCHAR(255) NOT NULL DEFAULT '',
    doc_cartao_cnpj      VARCHAR(255) NOT NULL DEFAULT '',
    doc_core             VARCHAR(255) NOT NULL DEFAULT '',
    doc_cpf_socio        VARCHAR(255) NOT NULL DEFAULT '',
    doc_identidade_socio VARCHAR(255) NOT NULL DEFAULT '',
    doc_end_socio_comp   VARCHAR(255) NOT NULL DEFAULT '',
    status               VARCHAR(50)  NOT NULL DEFAULT 'pendente',
    data_cadastro        DATE         NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO contratos (
    tipo_contrato, razao_social, cnpj, cep, endereco, numero, bairro, cidade, uf,
    telefone, celular, email_empresa, banco, agencia, conta, pix,
    nome_socio, cpf, rg, orgao_expedidor, nascimento, nacionalidade, estado_civil, profissao, email_socio,
    cep_socio, endereco_socio, numero_socio, bairro_socio, cidade_socio, uf_socio,
    doc_contrato_social, doc_end_empresa, doc_cartao_cnpj, doc_core,
    doc_cpf_socio, doc_identidade_socio, doc_end_socio_comp,
    status, data_cadastro
) VALUES
(
    'Prestação de Serviços',
    'Horizonte Serviços Contábeis Ltda', '12.345.678/0001-90',
    '29000-000', 'Av. Jerônimo Monteiro', '500', 'Centro', 'Vitória', 'ES',
    '(27) 3222-1234', '(27) 99988-7766', 'contato@horizontecontabil.com.br',
    'Banco do Brasil', '1234-5', '00012345-6', 'cnpj@horizonte',
    'Roberto Henrique Alves', '123.456.789-00', '1.234.567 SSP/ES', 'SSP/ES',
    '1980-04-12', 'Brasileiro(a)', 'Casado(a)', 'Contador', 'roberto@horizontecontabil.com.br',
    '29040-000', 'Rua da Paz', '88', 'Praia do Canto', 'Vitória', 'ES',
    'contrato_social_1.pdf', 'comp_end_empresa_1.pdf', 'cartao_cnpj_1.pdf', 'core_1.pdf',
    'cpf_socio_1.pdf', 'identidade_socio_1.pdf', 'comp_end_socio_1.pdf',
    'ativo', '2026-01-20'
),
(
    'Consultoria',
    'PontoCerto Distribuição e Logística Eireli', '98.765.432/0001-11',
    '29060-000', 'Rua Sete de Setembro', '210', 'Bento Ferreira', 'Vitória', 'ES',
    '(27) 3333-5555', '(27) 99711-2233', 'adm@pontocerto.com.br',
    'Itaú', '5678-9', '00098765-1', 'pontocerto@pix',
    'Silvana Mara Duarte', '987.654.321-00', '9.876.543 SSP/ES', 'SSP/ES',
    '1975-09-30', 'Brasileiro(a)', 'Divorciada', 'Empresária', 'silvana@pontocerto.com.br',
    '29070-100', 'Rua Alagoana', '15', 'Santa Lúcia', 'Vitória', 'ES',
    'contrato_social_2.pdf', 'comp_end_empresa_2.pdf', 'cartao_cnpj_2.pdf', '',
    'cpf_socio_2.pdf', 'identidade_socio_2.pdf', '',
    'pendente', '2026-02-05'
),
(
    'Prestação de Serviços',
    'Clínica Saúde Plena S/S', '11.222.333/0001-44',
    '29100-000', 'Av. Marechal Mascarenhas de Morais', '1200', 'Jardim da Penha', 'Vitória', 'ES',
    '(27) 3256-7890', '(27) 99655-4411', 'clinica@saudeplena.med.br',
    'Bradesco', '3344-0', '00111222-3', '11.222.333/0001-44',
    'Marcos Antônio Pereira', '321.654.987-00', '3.216.549 CRM/ES', 'CRM/ES',
    '1968-12-05', 'Brasileiro(a)', 'Casado(a)', 'Médico', 'marcos@saudeplena.med.br',
    '29105-000', 'Rua João Pessoa', '300', 'Jardim da Penha', 'Vitória', 'ES',
    'contrato_social_3.pdf', 'comp_end_empresa_3.pdf', 'cartao_cnpj_3.pdf', 'core_3.pdf',
    'cpf_socio_3.pdf', 'identidade_socio_3.pdf', 'comp_end_socio_3.pdf',
    'ativo', '2026-02-15'
);


-- ──────────────────────────────────────────
--  TABELA: fichas_selecao
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS fichas_selecao (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    empresa                  VARCHAR(150) NOT NULL,
    cargo                    VARCHAR(150) NOT NULL,
    nome_completo            VARCHAR(150) NOT NULL,
    cpf                      VARCHAR(20)  NOT NULL,
    rg                       VARCHAR(30)  NOT NULL,
    orgao_expedidor          VARCHAR(50)  NOT NULL DEFAULT '',
    data_nascimento          DATE         NOT NULL,
    estado_civil             VARCHAR(30)  NOT NULL,
    naturalidade             VARCHAR(100) NOT NULL DEFAULT '',
    nacionalidade            VARCHAR(50)  NOT NULL DEFAULT 'Brasileiro(a)',
    possui_filhos            VARCHAR(5)   NOT NULL DEFAULT 'nao',
    qtd_filhos               INT          NOT NULL DEFAULT 0,
    possui_cnh               VARCHAR(5)   NOT NULL DEFAULT 'nao',
    categoria_cnh            VARCHAR(10)  NOT NULL DEFAULT '',
    cep                      VARCHAR(10)  NOT NULL,
    endereco                 VARCHAR(200) NOT NULL,
    numero                   VARCHAR(20)  NOT NULL,
    complemento              VARCHAR(100) NOT NULL DEFAULT '',
    bairro                   VARCHAR(100) NOT NULL,
    cidade                   VARCHAR(100) NOT NULL,
    uf                       CHAR(2)      NOT NULL,
    celular                  VARCHAR(20)  NOT NULL,
    email                    VARCHAR(150) NOT NULL,
    escolaridade             VARCHAR(80)  NOT NULL,
    curso                    VARCHAR(150) NOT NULL DEFAULT '',
    experiencia              JSON,
    habilidades              TEXT,
    motivacao                TEXT,
    lazer                    TEXT,
    qualidades_pessoais      TEXT,
    qualidades_profissionais TEXT,
    oportunidades_melhoria   TEXT,
    esporte                  VARCHAR(100) NOT NULL DEFAULT '',
    animal_domestico         VARCHAR(100) NOT NULL DEFAULT '',
    expectativas             TEXT,
    fatores_ambiente         TEXT,
    motivo_escolha           TEXT,
    disponibilidade_horario  VARCHAR(5)   NOT NULL DEFAULT 'sim',
    disponibilidade_info     TEXT,
    pretensao_salarial       VARCHAR(50)  NOT NULL,
    indicado_por             VARCHAR(150) NOT NULL DEFAULT '',
    data_inscricao           DATE         NOT NULL,
    status                   VARCHAR(50)  NOT NULL DEFAULT 'novo',
    data_entrevista          DATETIME     NULL,
    local_entrevista         VARCHAR(200) NULL,
    obs_entrevista           TEXT         NULL,
    resultado_entrevista     VARCHAR(50)  NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO fichas_selecao (
    empresa, cargo, nome_completo, cpf, rg, orgao_expedidor, data_nascimento,
    estado_civil, naturalidade, nacionalidade,
    possui_filhos, qtd_filhos, possui_cnh, categoria_cnh,
    cep, endereco, numero, complemento, bairro, cidade, uf,
    celular, email, escolaridade, curso,
    experiencia, habilidades,
    motivacao, lazer, qualidades_pessoais, qualidades_profissionais,
    oportunidades_melhoria, esporte, animal_domestico, expectativas,
    fatores_ambiente, motivo_escolha,
    disponibilidade_horario, disponibilidade_info, pretensao_salarial, indicado_por,
    data_inscricao, status, data_entrevista, local_entrevista, resultado_entrevista
) VALUES
(
    'Construtora Horizonte Ltda', 'Auxiliar Administrativo',
    'Beatriz Nascimento Silva', '456.789.123-00', '4.567.891 SSP/ES', 'SSP/ES',
    '1998-06-20', 'Solteira', 'Vitória/ES', 'Brasileiro(a)',
    'nao', 0, 'sim', 'B',
    '29045-000', 'Rua Anísio Fernandes Coelho', '77', 'Apto 302', 'Enseada do Suá', 'Vitória', 'ES',
    '(27) 99812-3344', 'beatriz.silva@email.com', 'Superior Completo', 'Administração de Empresas',
    '[{"empresa":"Escritório ABC","cargo":"Estagiária","data_admissao":"2020-03","data_demissao":"2021-12","ultimo_salario":"800,00","motivo_saida":"Término de contrato"}]',
    'Pacote Office, atendimento ao cliente, organização',
    'Busco crescimento profissional e estabilidade', 'Leitura e música',
    'Organizada, proativa e comunicativa', 'Trabalho em equipe e pontualidade',
    'Paciência em situações de pressão', 'Vôlei', 'Cachorro',
    'Aprender e crescer junto com a empresa', 'Ambiente colaborativo',
    'Identificação com a área administrativa',
    'sim', '', 'R$ 1.800,00', 'Ana Rodrigues',
    '2026-02-10', 'entrevista_agendada', '2026-02-28 14:00:00', 'Sede da empresa — Sala RH', NULL
),
(
    'Distribuidora Ponto Certo', 'Motorista Entregador',
    'Diego Fernandes Moura', '789.123.456-00', '7.891.234 SSP/ES', 'SSP/ES',
    '1990-03-11', 'Casado', 'Serra/ES', 'Brasileiro(a)',
    'sim', 2, 'sim', 'C',
    '29160-000', 'Rua das Palmeiras', '200', '', 'Laranjeiras', 'Serra', 'ES',
    '(27) 99744-5566', 'diego.moura@email.com', 'Ensino Médio Completo', '',
    '[{"empresa":"Trans Rápido Ltda","cargo":"Motorista","data_admissao":"2018-05","data_demissao":"2025-11","ultimo_salario":"2200,00","motivo_saida":"Demissão sem justa causa"}]',
    'Direção defensiva, roteiro urbano e metropolitano',
    'Sustentar minha família com dignidade', 'Futebol com amigos',
    'Responsável, honesto e comprometido', 'Pontualidade e conhecimento de rotas',
    'Leitura de mapas digitais', 'Futebol', 'Nenhum',
    'Emprego fixo com benefícios', 'Companheirismo e respeito',
    'Proximidade com minha área de experiência',
    'nao', 'Disponível somente para turno diurno', 'R$ 2.000,00', '',
    '2026-02-12', 'em_analise', NULL, NULL, NULL
),
(
    'Clínica Saúde Plena', 'Recepcionista',
    'Letícia Carvalho Pinheiro', '321.654.987-55', '3.216.549 SSP/ES', 'SSP/ES',
    '2000-11-25', 'Solteira', 'Cariacica/ES', 'Brasileiro(a)',
    'nao', 0, 'nao', '',
    '29140-000', 'Av. Central', '900', 'Bloco B', 'Nova Rosa da Penha', 'Cariacica', 'ES',
    '(27) 99633-7788', 'leticia.pinheiro@email.com', 'Superior em Andamento', 'Gestão em Saúde',
    '[]',
    'Atendimento ao público, agenda digital, CRM básico',
    'Trabalhar em área de saúde e ajudar pessoas', 'Dança e culinária',
    'Empática, sorridente e organizada', 'Comunicação e atendimento humanizado',
    'Lidar com situações de conflito', 'Pilates', 'Gato',
    'Atuar em ambiente de saúde e crescer na área', 'Ambiente calmo e respeitoso',
    'Identificação com o setor de saúde',
    'sim', '', 'R$ 1.600,00', '',
    '2026-02-18', 'novo', NULL, NULL, NULL
),
(
    'Supermercado BomPreço', 'Operador de Caixa',
    'Wagner Teixeira Braga', '654.321.098-77', '6.543.210 SSP/ES', 'SSP/ES',
    '1995-08-07', 'Solteiro', 'Viana/ES', 'Brasileiro(a)',
    'nao', 0, 'nao', '',
    '29130-000', 'Rua Ipiranga', '45', '', 'Centro', 'Viana', 'ES',
    '(27) 99522-9900', 'wagner.braga@email.com', 'Ensino Médio Completo', '',
    '[{"empresa":"Mercado Bela Vista","cargo":"Auxiliar de caixa","data_admissao":"2022-01","data_demissao":"2025-08","ultimo_salario":"1350,00","motivo_saida":"Fechamento da loja"}]',
    'Caixa rápido, conferência de troco, SAC básico',
    'Recolocação no mercado de trabalho', 'Jogos e séries',
    'Atencioso, calmo e focado', 'Agilidade e precisão numérica',
    'Impaciência em filas longas', 'Natação', 'Nenhum',
    'Estabilidade e benefícios', 'Organização e bom clima',
    'Tenho experiência direta com a função',
    'sim', '', 'R$ 1.450,00', 'Funcionário atual do supermercado',
    '2026-02-20', 'aprovado', '2026-02-25 10:00:00', 'Supermercado BomPreço — Gerência', 'aprovado_entrevista'
);
