<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../verificar_login.php';

define('APP_ROOT', dirname(__DIR__, 2) . '/app');
define('VIEW_PATH', APP_ROOT . '/views');
define('STORAGE_PATH', APP_ROOT . '/storage');

$db = require APP_ROOT . '/config/database.php';

// Determina a seção ativa (dashboard, contratos, selecao)
$section = (string)($_GET['section'] ?? 'dashboard');
$action  = (string)($_GET['action'] ?? 'index');

// ── API de aniversariantes (JSON/imagem, sem HTML) ──
if ($section === 'birthday_api') {
    $apiAction = (string)($_GET['api_action'] ?? '');

    try {
        switch ($apiAction) {
            case 'check':
                header('Content-Type: application/json; charset=utf-8');
                $anoAtual = (int)date('Y');
                $stmt = $db->prepare(
                    "SELECT id, nome, telefone, foto_path
                     FROM aniversariantes
                     WHERE DAY(data_aniversario) = DAY(CURDATE())
                       AND MONTH(data_aniversario) = MONTH(CURDATE())
                       AND (telefone IS NOT NULL AND telefone != '')
                       AND (msg_enviada_ano IS NULL OR msg_enviada_ano < :ano)"
                );
                $stmt->execute([':ano' => $anoAtual]);
                echo json_encode(['aniversariantes' => $stmt->fetchAll()]);
                break;

            case 'mark_sent':
                header('Content-Type: application/json; charset=utf-8');
                $id = (int)($_POST['id'] ?? 0);
                if (!$id) { echo json_encode(['error' => 'ID inválido']); break; }
                $anoAtual = (int)date('Y');
                $stmt = $db->prepare("UPDATE aniversariantes SET msg_enviada_ano = :ano WHERE id = :id");
                $stmt->execute([':ano' => $anoAtual, ':id' => $id]);
                echo json_encode(['success' => true]);
                break;

            case 'generate_image':
                $id = (int)($_GET['id'] ?? 0);
                $template = ($_GET['template'] ?? 'normal') === 'dayoff' ? 'dayoff' : 'normal';

                if (!$id) { http_response_code(400); echo 'ID inválido'; exit; }

                $stmt = $db->prepare("SELECT nome, foto_path FROM aniversariantes WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $func = $stmt->fetch();

                if (!$func || !$func['foto_path']) {
                    http_response_code(404);
                    echo 'Funcionário ou foto não encontrado';
                    exit;
                }

                $templatePath = APP_ROOT . '/storage/aniversario/' . $template . '.png';
                $fotoPathAntigo = APP_ROOT . '/storage/uploads/' . $func['foto_path'];
                $fotoPathNovo = APP_ROOT . '/storage/' . $func['foto_path'];

                $fotoPath = file_exists($fotoPathNovo) ? $fotoPathNovo : $fotoPathAntigo;

                if (!file_exists($templatePath) || !file_exists($fotoPath)) {
                    http_response_code(404);
                    echo 'Arquivo não encontrado';
                    exit;
                }

                // Carregar template PNG (com transparência)
                $templateImg = imagecreatefrompng($templatePath);
                $templateW = imagesx($templateImg);
                $templateH = imagesy($templateImg);

                // Carregar foto do funcionário
                $fotoInfo = getimagesize($fotoPath);
                $fotoMime = $fotoInfo['mime'] ?? '';
                switch ($fotoMime) {
                    case 'image/jpeg': $fotoImg = imagecreatefromjpeg($fotoPath); break;
                    case 'image/png':  $fotoImg = imagecreatefrompng($fotoPath);  break;
                    case 'image/webp': $fotoImg = imagecreatefromwebp($fotoPath); break;
                    default:
                        http_response_code(400);
                        echo 'Formato de foto não suportado';
                        exit;
                }

                // ── Posição da foto no template (medida com o editor de posição) ──
                // Base: 1080 x 1350 px  |  cx=546, cy=450, tamanho=670
                $PHOTO_CX   = 546;
                $PHOTO_CY   = 450;
                $PHOTO_SIZE = 670;
                $photoX = $PHOTO_CX - (int)($PHOTO_SIZE / 2);   // 211
                $photoY = $PHOTO_CY - (int)($PHOTO_SIZE / 2);   // 115

                // Criar imagem final do tamanho do template com fundo branco
                $final = imagecreatetruecolor($templateW, $templateH);
                imagesavealpha($final, true);
                imagealphablending($final, false);
                $white = imagecolorallocate($final, 255, 255, 255);
                imagefill($final, 0, 0, $white);

                // 1. Redimensionar foto para o quadrado exato e colá-la na posição certa
                $fotoW = imagesx($fotoImg);
                $fotoH = imagesy($fotoImg);

                imagealphablending($final, true);
                imagecopyresampled(
                    $final, $fotoImg,
                    $photoX, $photoY,       // destino (x, y)
                    0, 0,                   // origem (x, y)
                    $PHOTO_SIZE, $PHOTO_SIZE, // destino (w, h)
                    $fotoW, $fotoH           // origem (w, h) — foto já é quadrada (1:1)
                );

                // 2. Desenhar template PNG por cima (pixels opacos cobrem o excesso)
                imagecopy($final, $templateImg, 0, 0, 0, 0, $templateW, $templateH);

                // Output
                header('Content-Type: image/png');
                $nomeSlug = preg_replace('/[^a-z0-9]/i', '_', strtolower($func['nome']));
                header('Content-Disposition: inline; filename="aniversario_' . $nomeSlug . '.png"');
                imagepng($final);

                imagedestroy($templateImg);
                imagedestroy($fotoImg);
                imagedestroy($final);
                break;

            default:
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Ação inválida']);
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ── Ações de API que retornam JSON/binário: rodar ANTES do ob_start ──
// (evita que o HTML do popup de aniversário seja injetado nas respostas JSON)
$apiActions = [
    'curriculos'  => ['get', 'update', 'status', 'delete', 'generate_token', 'talent_bank', 'view_cv'],
    'selecao'     => ['status_selecao', 'entrevista_selecao', 'delete_selecao', 'view_selecao'],
    'funcionarios'=> ['api', 'foto', 'template', 'import_excel'],
    'aniversariantes' => ['api'],
    'contratos'   => ['edit', 'update', 'status', 'delete', 'download'],
    'vagas'       => ['store', 'toggle', 'delete_vaga'],
];

if (isset($apiActions[$section]) && in_array($action, $apiActions[$section], true)) {
    switch ($section) {
        case 'curriculos':
            require_once APP_ROOT . '/controllers/CurriculoController.php';
            $ctrl = new CurriculoController($db);
            switch ($action) {
                case 'view_cv':        $ctrl->viewCv((int)($_GET['id'] ?? 0)); break;
                case 'get':            $ctrl->get();                           break;
                case 'update':         $ctrl->update();                        break;
                case 'status':         $ctrl->changeStatus();                  break;
                case 'delete':         $ctrl->delete();                        break;
                case 'generate_token': $ctrl->generateToken();                 break;
                case 'talent_bank':    $ctrl->talentBank();                    break;
            }
            break;

        case 'selecao':
            require_once APP_ROOT . '/controllers/SelecaoController.php';
            $ctrl = new SelecaoController($db);
            switch ($action) {
                case 'status_selecao':    $ctrl->changeStatus();                      break;
                case 'entrevista_selecao':$ctrl->saveInterview();                     break;
                case 'delete_selecao':    $ctrl->delete();                            break;
                case 'view_selecao':      $ctrl->view((int)($_GET['id'] ?? 0));       break;
            }
            break;

        case 'funcionarios':
            require_once APP_ROOT . '/controllers/FuncionariosController.php';
            $ctrl = new FuncionariosController($db);
            if ($action === 'api')      $ctrl->api();
            elseif ($action === 'foto') $ctrl->foto((int)($_GET['id'] ?? 0));
            elseif ($action === 'template')     $ctrl->template((string)($_GET['name'] ?? 'normal'));
            elseif ($action === 'import_excel') $ctrl->importExcel();
            break;

        case 'aniversariantes':
            require_once APP_ROOT . '/controllers/AniversariantesController.php';
            $ctrl = new AniversariantesController($db);
            $ctrl->api();
            break;

        case 'contratos':
            require APP_ROOT . '/models/Contract.php';
            require APP_ROOT . '/controllers/ContractController.php';
            $controller = new ContractController($db);
            switch ($action) {
                case 'edit':     $controller->edit((int)($_GET['id'] ?? 0)); break;
                case 'update':   $controller->update();                      break;
                case 'status':   $controller->changeStatus();                break;
                case 'delete':   $controller->delete();                      break;
                case 'download': $controller->download((int)($_GET['id'] ?? 0)); break;
            }
            break;

        case 'vagas':
            require_once APP_ROOT . '/controllers/VagasController.php';
            $ctrl = new VagasController($db);
            switch ($action) {
                case 'store':       $ctrl->store();        break;
                case 'toggle':      $ctrl->toggleStatus(); break;
                case 'delete_vaga': $ctrl->delete();       break;
            }
            break;
    }
    exit;
}

// ── Captura a saída da seção para injetar o popup ──
// Apenas páginas HTML completas passam por aqui
ob_start();

switch ($section) {
    case 'selecao':
    require_once APP_ROOT . '/controllers/SelecaoController.php';

    $ctrl = new SelecaoController($db);

    switch ($action) {

        // Altera status da ficha (AJAX)
        case 'status_selecao':
            $ctrl->changeStatus();
            break;

        // Salva dados da entrevista (AJAX)
        case 'entrevista_selecao':
            $ctrl->saveInterview();
            break;

        // Exclui ficha (POST normal)
        case 'delete_selecao':
            $ctrl->delete();
            break;

        // Visualiza ficha em página própria (nova janela)
        case 'view_selecao':
            $ctrl->view((int)($_GET['id'] ?? 0));
            break;

        // Listagem padrão
        default:
            $ctrl->index();
            break;
    }
    break;

    case 'vagas':
        require_once APP_ROOT . '/controllers/VagasController.php';
        $ctrl = new VagasController($db);
        switch ($action) {
            case 'store':       $ctrl->store();        break;
            case 'toggle':      $ctrl->toggleStatus(); break;
            case 'delete_vaga': $ctrl->delete();       break;
            default:            $ctrl->index();        break;
        }
        break;

    case 'curriculos':
        require_once APP_ROOT . '/controllers/CurriculoController.php';
        $ctrl = new CurriculoController($db);
        switch ($action) {
            case 'view_cv':        $ctrl->viewCv((int)($_GET['id'] ?? 0)); break;
            case 'get':            $ctrl->get();                           break;
            case 'update':         $ctrl->update();                        break;
            case 'status':         $ctrl->changeStatus();                  break;
            case 'delete':         $ctrl->delete();                        break;
            case 'generate_token': $ctrl->generateToken();                 break;
            case 'talent_bank':    $ctrl->talentBank();                    break;
            default:               $ctrl->index();                         break;
        }
        break;

    case 'contratos':
        require APP_ROOT . '/models/Contract.php';
        require APP_ROOT . '/controllers/ContractController.php';
        $controller = new ContractController($db);

        switch ($action) {
            case 'edit':
                $controller->edit((int)($_GET['id'] ?? 0));
                break;
            case 'update':
                $controller->update();
                break;
            case 'status':
                $controller->changeStatus();
                break;
            case 'delete':
                $controller->delete();
                break;
            case 'download':
                $controller->download((int)($_GET['id'] ?? 0));
                break;
            default:
                $controller->index();
                break;
        }
        break;

    case 'funcionarios':
        require_once APP_ROOT . '/controllers/FuncionariosController.php';
        $ctrl = new FuncionariosController($db);
        if ($action === 'api') {
            $ctrl->api();
        } elseif ($action === 'foto') {
            $ctrl->foto((int)($_GET['id'] ?? 0));
        } elseif ($action === 'template') {
            $ctrl->template((string)($_GET['name'] ?? 'normal'));
        } elseif ($action === 'import_excel') {
            $ctrl->importExcel();
        } else {
            $ctrl->index();
        }
        break;

    case 'aniversariantes':
        require_once APP_ROOT . '/controllers/AniversariantesController.php';
        $ctrl = new AniversariantesController($db);
        if ($action === 'api') {
            $ctrl->api();
        } else {
            $ctrl->index();
        }
        break;

    case 'dashboard':
    default:
        require APP_ROOT . '/controllers/DashboardController.php';
        $controller = new DashboardController($db);
        $controller->index();
        break;
}

$pageOutput = ob_get_clean();

// ── Injeta o popup de aniversariantes antes de </body> ──
$popupHtml = <<<'BIRTHDAY_POPUP'
<!-- ═══ Popup Aniversariantes do Dia ═══ -->
<div id="bdayOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9990;justify-content:center;align-items:center;">
<div style="background:#fff;border-radius:16px;padding:0;max-width:520px;width:94%;box-shadow:0 24px 80px rgba(0,0,0,.3);overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
    <!-- Header -->
    <div style="background:linear-gradient(135deg,#e91e63,#ff6f00);padding:22px 26px;color:#fff;position:relative;">
        <div style="font-size:2rem;margin-bottom:4px;">🎂</div>
        <h2 style="margin:0;font-size:1.15rem;font-weight:700;" id="bdayTitle">Aniversariante(s) do Dia!</h2>
        <p style="margin:4px 0 0;font-size:.82rem;opacity:.9;">Escolha o modelo, gere a imagem e envie pelo WhatsApp</p>
        <button onclick="closeBdayPopup()" style="position:absolute;top:14px;right:16px;background:rgba(255,255,255,.2);border:none;color:#fff;width:30px;height:30px;border-radius:50%;font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>
    <!-- Lista -->
    <div id="bdayList" style="padding:16px 20px;overflow-y:auto;flex:1;"></div>
    <!-- Footer -->
    <div style="padding:12px 20px;border-top:1px solid #f0f0f0;text-align:center;">
        <button onclick="closeBdayPopup()" style="background:#f5f5f5;border:1px solid #e0e0e0;padding:9px 28px;border-radius:8px;font-size:.88rem;cursor:pointer;color:#555;font-weight:600;font-family:inherit;">Fechar</button>
    </div>
</div>
</div>

<!-- Modal de Preview da Imagem -->
<div id="bdayImgModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9995;justify-content:center;align-items:center;">
<div style="background:#fff;border-radius:16px;padding:20px;max-width:500px;width:94%;box-shadow:0 24px 80px rgba(0,0,0,.4);text-align:center;max-height:90vh;overflow-y:auto;">
    <h3 style="margin:0 0 14px;color:#e91e63;font-size:1rem;">Imagem de Aniversário</h3>
    <img id="bdayImgPreview" style="width:100%;border-radius:10px;border:1px solid #eee;" alt="Preview">
    <!-- Aviso: imagem copiada para área de transferência -->
    <div id="bdayClipboardNotice" style="display:none;margin-top:12px;padding:10px 14px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;font-size:.84rem;color:#2e7d32;font-weight:600;text-align:left;gap:8px;align-items:center;">
        <i class="fa-solid fa-circle-check"></i>
        Imagem copiada! Cole no bate-papo do WhatsApp (Ctrl+V ou segurar e colar).
    </div>
    <div style="margin-top:16px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
        <a id="bdayImgDownload" href="#" download style="display:inline-flex;align-items:center;gap:6px;background:#e91e63;color:#fff;border:none;padding:10px 18px;border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none;cursor:pointer;">
            <i class="fa-solid fa-download"></i> Baixar Imagem
        </a>
        <button id="bdayImgWhatsapp" style="display:inline-flex;align-items:center;gap:6px;background:#25d366;color:#fff;border:none;padding:10px 18px;border-radius:8px;font-size:.85rem;font-weight:600;cursor:pointer;font-family:inherit;">
            <i class="fa-brands fa-whatsapp"></i> Enviar WhatsApp
        </button>
        <button onclick="closeBdayImgModal()" style="background:#f5f5f5;border:1px solid #e0e0e0;padding:10px 18px;border-radius:8px;font-size:.85rem;cursor:pointer;color:#555;font-weight:600;font-family:inherit;">Fechar</button>
    </div>
</div>
</div>

<style>
.bday-card{display:flex;align-items:center;gap:14px;padding:14px;border-radius:12px;border:1px solid #f0f0f0;margin-bottom:10px;transition:all .2s;}
.bday-card:hover{border-color:#f8bbd0;background:#fff8fb;}
.bday-avatar{width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid #f8bbd0;flex-shrink:0;}
.bday-avatar-ph{width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#f8bbd0,#e91e63);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:20px;flex-shrink:0;}
.bday-info{flex:1;min-width:0;}
.bday-name{font-weight:700;color:#212121;font-size:.95rem;margin-bottom:6px;}
.bday-template-btns{display:flex;gap:6px;flex-wrap:wrap;}
.bday-tpl-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;transition:all .2s;border:1.5px solid #e0e0e0;background:#fff;color:#555;font-family:inherit;}
.bday-tpl-btn:hover{border-color:#e91e63;color:#e91e63;background:#fff0f5;}
.bday-tpl-btn.normal{border-color:#7b1fa2;color:#7b1fa2;background:#f3e5f5;}
.bday-tpl-btn.dayoff{border-color:#ff6f00;color:#ff6f00;background:#fff3e0;}
.bday-actions{display:flex;gap:6px;flex-shrink:0;flex-direction:column;align-items:flex-end;}
.bday-btn-sent{display:inline-flex;align-items:center;gap:6px;background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;padding:8px 14px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;transition:all .2s;font-family:inherit;}
.bday-btn-sent:hover{background:#c8e6c9;}
.bday-done{opacity:.5;pointer-events:none;}
.bday-done .bday-card{border-color:#c8e6c9;background:#f1f8e9;}
</style>
<script>
(function(){
    function checkBirthdays(){
        fetch('index.php?section=birthday_api&api_action=check')
        .then(r=>r.json())
        .then(data=>{
            if(!data.aniversariantes||data.aniversariantes.length===0) return;
            renderPopup(data.aniversariantes);
        })
        .catch(()=>{});
    }

    function renderPopup(list){
        const container=document.getElementById('bdayList');
        container.innerHTML='';
        document.getElementById('bdayTitle').textContent=
            list.length===1?'1 Aniversariante Hoje!':list.length+' Aniversariantes Hoje!';

        list.forEach(function(p){
            const initials=p.nome.charAt(0).toUpperCase();

            const avatarHtml=p.foto_path
                ?'<img class="bday-avatar" src="index.php?section=funcionarios&action=foto&id='+p.id+'" alt="">'
                :'<div class="bday-avatar-ph">'+initials+'</div>';

            const hasFoto = !!p.foto_path;

            const div=document.createElement('div');
            div.className='bday-item';
            div.id='bday-item-'+p.id;
            div.innerHTML=
                '<div class="bday-card">'+
                    avatarHtml+
                    '<div class="bday-info">'+
                        '<div class="bday-name">'+escHtml(titleCase(p.nome))+'</div>'+
                        (hasFoto
                        ? '<div class="bday-template-btns">'+
                            '<button class="bday-tpl-btn normal" onclick="generateBdayImg('+p.id+',\'normal\',\''+escAttr(p.nome)+'\',\''+escAttr(p.telefone)+'\')">'+
                                '<i class="fa-solid fa-image"></i> Normal'+
                            '</button>'+
                            '<button class="bday-tpl-btn dayoff" onclick="generateBdayImg('+p.id+',\'dayoff\',\''+escAttr(p.nome)+'\',\''+escAttr(p.telefone)+'\')">'+
                                '<i class="fa-solid fa-umbrella-beach"></i> Day Off'+
                            '</button>'+
                          '</div>'
                        : '<span style="font-size:.75rem;color:#999;">Sem foto cadastrada</span>')+
                    '</div>'+
                    '<div class="bday-actions" id="bday-actions-'+p.id+'">'+
                    '</div>'+
                '</div>';
            container.appendChild(div);
        });

        document.getElementById('bdayOverlay').style.display='flex';
    }

    function titleCase(str){
        return str.toLowerCase().replace(/(?:^|\s)\S/g,function(a){return a.toUpperCase();});
    }

    function escHtml(s){
        const d=document.createElement('div');
        d.textContent=s||'';
        return d.innerHTML;
    }

    function escAttr(s){
        return (s||'').replace(/'/g,"\\'").replace(/"/g,'&quot;');
    }

    // Gera imagem e exibe preview
    window.generateBdayImg=function(id,template,nome,telefone){
        const imgUrl='index.php?section=birthday_api&api_action=generate_image&id='+id+'&template='+template;

        // Mostrar modal de preview
        document.getElementById('bdayImgPreview').src=imgUrl;
        document.getElementById('bdayImgDownload').href=imgUrl;
        document.getElementById('bdayImgDownload').download='aniversario_'+nome.toLowerCase().replace(/\s+/g,'_')+'.png';

        // Botão WhatsApp: copia a imagem para o clipboard e abre a conversa sem mensagem
        const tel=telefone.replace(/\D/g,'');
        const telFull=tel.length<=11?'55'+tel:tel;
        const waUrl='https://wa.me/'+telFull;

        document.getElementById('bdayImgWhatsapp').onclick=function(){
            const notice=document.getElementById('bdayClipboardNotice');

            // Busca a imagem já carregada e copia como PNG para o clipboard
            fetch(imgUrl)
                .then(r=>r.blob())
                .then(blob=>{
                    const pngBlob = blob.type === 'image/png' ? blob : new Blob([blob], {type:'image/png'});
                    return navigator.clipboard.write([
                        new ClipboardItem({'image/png': pngBlob})
                    ]);
                })
                .then(()=>{
                    // Mostra aviso de copiado
                    notice.style.display='flex';
                    // Após breve delay abre o WhatsApp
                    setTimeout(()=>{ window.open(waUrl,'_blank'); }, 400);
                    // Mostra botão de marcar como enviado
                    const actionsDiv=document.getElementById('bday-actions-'+id);
                    if(actionsDiv && !actionsDiv.querySelector('.bday-btn-sent')){
                        actionsDiv.innerHTML=
                            '<button class="bday-btn-sent" onclick="confirmBdaySent('+id+')">'+
                                '<i class="fa-solid fa-check"></i> Marcar enviado'+
                            '</button>';
                    }
                })
                .catch(()=>{
                    // Fallback: se clipboard falhar, abre WhatsApp mesmo assim
                    notice.textContent='⚠️ Não foi possível copiar automaticamente. Baixe a imagem e envie manualmente.';
                    notice.style.background='#fff3e0';
                    notice.style.borderColor='#ffcc80';
                    notice.style.color='#e65100';
                    notice.style.display='flex';
                    window.open(waUrl,'_blank');
                });
        };

        // Esconde aviso de clipboard ao abrir modal
        document.getElementById('bdayClipboardNotice').style.display='none';
        document.getElementById('bdayImgModal').style.display='flex';
    };

    window.closeBdayImgModal=function(){
        document.getElementById('bdayImgModal').style.display='none';
    };

    window.confirmBdaySent=function(id){
        const body=new FormData();
        body.append('id',id);
        fetch('index.php?section=birthday_api&api_action=mark_sent',{method:'POST',body:body})
        .then(r=>r.json())
        .then(function(res){
            if(res.error) return;
            const item=document.getElementById('bday-item-'+id);
            if(item){
                item.classList.add('bday-done');
                const actionsDiv=document.getElementById('bday-actions-'+id);
                if(actionsDiv) actionsDiv.innerHTML='<span style="color:#2e7d32;font-size:.82rem;font-weight:600;"><i class="fa-solid fa-circle-check"></i> Enviado</span>';
            }
            const remaining=document.querySelectorAll('.bday-item:not(.bday-done)');
            if(remaining.length===0){
                setTimeout(function(){closeBdayPopup();closeBdayImgModal();},1200);
            }
        })
        .catch(()=>{});
    };

    window.closeBdayPopup=function(){
        document.getElementById('bdayOverlay').style.display='none';
    };

    // Verifica ao carregar a página
    if(document.readyState==='loading'){
        document.addEventListener('DOMContentLoaded',function(){setTimeout(checkBirthdays,800);});
    } else {
        setTimeout(checkBirthdays,800);
    }
})();
</script>
BIRTHDAY_POPUP;

// Injeta antes de </body> se existir, senão concatena no final
if (stripos($pageOutput, '</body>') !== false) {
    echo str_ireplace('</body>', $popupHtml . "\n</body>", $pageOutput);
} else {
    echo $pageOutput . $popupHtml;
}