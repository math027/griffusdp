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

switch ($section) {
    case 'selecao':
    require_once APP_ROOT . '/controllers/SelecaoController.php';

    $ctrl = new SelecaoController($db);

    switch ($action) {

        // Altera status da ficha (AJAX)
        case 'status_selecao':
            $ctrl->changeStatus();
            break;

        // Salva dados da entrevista (AJAX)  ← NOVO
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
