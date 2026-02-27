<?php
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destrói o cookie de sessão
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destrói a sessão
session_destroy();

// Redireciona para o login
header('Location: login.php');
exit;
