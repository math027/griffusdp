<?php
declare(strict_types=1);
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

/* ---------- helpers ---------- */
function limpar(string $v): string {
    return trim(strip_tags($v));
}

function campo(string $key): string {
    return limpar((string)($_POST[$key] ?? ''));
}

/* ---------- conexão ---------- */
try {
    $pdo = require __DIR__ . '/../config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Falha na conexão com o banco.']);
    exit;
}

/* ---------- validação básica ---------- */
$obrigatorios = [
    'empresa', 'cargo', 'nomeCompleto', 'cpf', 'rg', 'dataNascimento',
    'estadoCivil', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'uf',
    'celular', 'email', 'escolaridade', 'pretensao_salarial'
];

foreach ($obrigatorios as $campo_key) {
    if (empty(campo($campo_key))) {
        echo json_encode(['success' => false, 'message' => "Campo obrigatório não preenchido: {$campo_key}"]);
        exit;
    }
}

/* ---------- dados base ---------- */
$empresa         = campo('empresa');
$cargo           = campo('cargo');
$nomeCompleto    = campo('nomeCompleto');
$cpf             = campo('cpf');
$rg              = campo('rg');
$orgaoExpedidor  = campo('orgaoExpedidor');
$dataNascimento  = campo('dataNascimento');
$estadoCivil     = campo('estadoCivil');
$naturalidade    = campo('naturalidade');
$nacionalidade   = campo('nacionalidade');
$possuiFilhos    = campo('possuiFilhos');
$qtdFilhos       = ($possuiFilhos === 'sim') ? (int)campo('qtdFilhos') : 0;
$possuiCnh       = campo('possuiCnh');
$categoriaCnh    = ($possuiCnh === 'sim') ? campo('categoriaCnh') : '';
$cep             = campo('cep');
$endereco        = campo('endereco');
$numero          = campo('numero');
$complemento     = campo('complemento');
$bairro          = campo('bairro');
$cidade          = campo('cidade');
$uf              = strtoupper(campo('uf'));
$celular         = campo('celular');
$email           = campo('email');
$escolaridade    = campo('escolaridade');
$curso           = campo('curso');
$habilidades     = campo('habilidades');

/* ---------- experiências (JSON) ---------- */
$experienciaRaw = trim((string)($_POST['experiencia'] ?? '[]'));
$experiencias   = json_decode($experienciaRaw, true);
if (!is_array($experiencias)) {
    $experiencias = [];
}
// Limita a 3 e sanitiza cada entrada
$experiencias = array_slice($experiencias, 0, 3);
foreach ($experiencias as &$exp) {
    $exp['empresa']        = limpar((string)($exp['empresa']        ?? ''));
    $exp['cargo']          = limpar((string)($exp['cargo']          ?? ''));
    $exp['data_admissao']  = limpar((string)($exp['data_admissao']  ?? ''));
    $exp['data_demissao']  = limpar((string)($exp['data_demissao']  ?? ''));
    $exp['ultimo_salario'] = limpar((string)($exp['ultimo_salario'] ?? ''));
    $exp['motivo_saida']   = limpar((string)($exp['motivo_saida']   ?? ''));
}
unset($exp);
$experienciaJson = json_encode($experiencias, JSON_UNESCAPED_UNICODE);

/* ---------- questionário ---------- */
$motivacao               = campo('motivacao');
$lazer                   = campo('lazer');
$qualidades_pessoais     = campo('qualidades_pessoais');
$qualidades_profissionais = campo('qualidades_profissionais');
$oportunidades_melhoria  = campo('oportunidades_melhoria');
$esporte                 = campo('esporte');
$animal_domestico        = campo('animal_domestico');
$expectativas            = campo('expectativas');
$fatores_ambiente        = campo('fatores_ambiente');
$motivo_escolha          = campo('motivo_escolha');

/* ---------- disponibilidade & pretensão ---------- */
$disponibilidade_horario = campo('disponibilidade_horario');
$disponibilidade_info    = ($disponibilidade_horario === 'nao') ? campo('disponibilidade_info') : '';
$pretensao_salarial      = campo('pretensao_salarial');
$indicado_por            = campo('indicado_por');

$dataHoje = date('Y-m-d');
$status   = 'novo';

$sql = "INSERT INTO fichas_selecao (
    empresa, cargo, nome_completo, cpf, rg, orgao_expedidor, data_nascimento,
    estado_civil, naturalidade, nacionalidade,
    possui_filhos, qtd_filhos, possui_cnh, categoria_cnh,
    cep, endereco, numero, complemento, bairro, cidade, uf,
    celular, email,
    escolaridade, curso, experiencia, habilidades,
    motivacao, lazer, qualidades_pessoais, qualidades_profissionais,
    oportunidades_melhoria, esporte, animal_domestico, expectativas,
    fatores_ambiente, motivo_escolha,
    disponibilidade_horario, disponibilidade_info, pretensao_salarial, indicado_por,
    data_inscricao, status
) VALUES (
    :empresa, :cargo, :nome_completo, :cpf, :rg, :orgao_expedidor, :data_nascimento,
    :estado_civil, :naturalidade, :nacionalidade,
    :possui_filhos, :qtd_filhos, :possui_cnh, :categoria_cnh,
    :cep, :endereco, :numero, :complemento, :bairro, :cidade, :uf,
    :celular, :email,
    :escolaridade, :curso, :experiencia, :habilidades,
    :motivacao, :lazer, :qualidades_pessoais, :qualidades_profissionais,
    :oportunidades_melhoria, :esporte, :animal_domestico, :expectativas,
    :fatores_ambiente, :motivo_escolha,
    :disponibilidade_horario, :disponibilidade_info, :pretensao_salarial, :indicado_por,
    :data_inscricao, :status
)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':empresa'                  => $empresa,
        ':cargo'                    => $cargo,
        ':nome_completo'            => $nomeCompleto,
        ':cpf'                      => $cpf,
        ':rg'                       => $rg,
        ':orgao_expedidor'          => $orgaoExpedidor,
        ':data_nascimento'          => $dataNascimento,
        ':estado_civil'             => $estadoCivil,
        ':naturalidade'             => $naturalidade,
        ':nacionalidade'            => $nacionalidade,
        ':possui_filhos'            => $possuiFilhos,
        ':qtd_filhos'               => $qtdFilhos,
        ':possui_cnh'               => $possuiCnh,
        ':categoria_cnh'            => $categoriaCnh,
        ':cep'                      => $cep,
        ':endereco'                 => $endereco,
        ':numero'                   => $numero,
        ':complemento'              => $complemento,
        ':bairro'                   => $bairro,
        ':cidade'                   => $cidade,
        ':uf'                       => $uf,
        ':celular'                  => $celular,
        ':email'                    => $email,
        ':escolaridade'             => $escolaridade,
        ':curso'                    => $curso,
        ':experiencia'              => $experienciaJson,
        ':habilidades'              => $habilidades,
        ':motivacao'                => $motivacao,
        ':lazer'                    => $lazer,
        ':qualidades_pessoais'      => $qualidades_pessoais,
        ':qualidades_profissionais' => $qualidades_profissionais,
        ':oportunidades_melhoria'   => $oportunidades_melhoria,
        ':esporte'                  => $esporte,
        ':animal_domestico'         => $animal_domestico,
        ':expectativas'             => $expectativas,
        ':fatores_ambiente'         => $fatores_ambiente,
        ':motivo_escolha'           => $motivo_escolha,
        ':disponibilidade_horario'  => $disponibilidade_horario,
        ':disponibilidade_info'     => $disponibilidade_info,
        ':pretensao_salarial'       => $pretensao_salarial,
        ':indicado_por'             => $indicado_por,
        ':data_inscricao'           => $dataHoje,
        ':status'                   => $status,
    ]);

    echo json_encode(['success' => true, 'message' => 'Ficha enviada com sucesso!']);
} catch (Exception $e) {
    error_log('Erro ao salvar ficha: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar. Tente novamente.']);
}