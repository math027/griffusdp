<?php
/**
 * Verificador de Autenticação
 * Inclua este arquivo no início de páginas que requerem login
 * 
 * Uso: require_once __DIR__ . '/../verificar_login.php';
 */

// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_email'])) {
    $_SESSION['erro_login'] = 'Você precisa estar logado para acessar esta página.';
    
    $redirect_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) 
        ? '../login.php' 
        : 'login.php';
    
    header('Location: ' . $redirect_path);
    exit;
}

// Verifica timeout de sessão (30 minutos de inatividade)
$timeout = 1800; // 30 minutos em segundos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['erro_login'] = 'Sua sessão expirou. Por favor, faça login novamente.';
    
    $redirect_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) 
        ? '../login.php' 
        : 'login.php';
    
    header('Location: ' . $redirect_path);
    exit;
}

// Atualiza o tempo de última atividade
$_SESSION['last_activity'] = time();

// Define variáveis globais para uso nas páginas
if (!defined('USUARIO_ID')) {
    define('USUARIO_ID', $_SESSION['usuario_id']);
    define('USUARIO_NOME', $_SESSION['usuario_nome']);
    define('USUARIO_EMAIL', $_SESSION['usuario_email']);
}
