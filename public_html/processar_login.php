<?php
session_start();

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Recebe e sanitiza os dados
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$senha = $_POST['senha'] ?? '';

// Validações básicas
if (empty($email) || empty($senha)) {
    $_SESSION['erro_login'] = 'Por favor, preencha todos os campos.';
    header('Location: login.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro_login'] = 'E-mail inválido.';
    header('Location: login.php');
    exit;
}

try {
    // Conecta no banco (path corrigido)
    $pdo = require __DIR__ . '/../app/config/database.php';
    
    // Busca o usuário
    $stmt = $pdo->prepare('
        SELECT id, nome, email, senha, ativo 
        FROM usuarios 
        WHERE email = ? 
        LIMIT 1
    ');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    // Verifica se o usuário existe e a senha está correta
    if (!$usuario || !password_verify($senha, $usuario['senha'])) {
        $_SESSION['erro_login'] = 'E-mail ou senha incorretos.';
        header('Location: login.php');
        exit;
    }
    
    // Verifica se o usuário está ativo
    if ($usuario['ativo'] != 1) {
        $_SESSION['erro_login'] = 'Sua conta está desativada. Contate o administrador.';
        header('Location: login.php');
        exit;
    }
    
    // Login bem-sucedido - cria a sessão
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['last_activity'] = time();
    
    // Atualiza o último acesso
    $stmt = $pdo->prepare('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?');
    $stmt->execute([$usuario['id']]);
    
    // Redireciona para o painel admin
    header('Location: admin/index.php');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro no login: ' . $e->getMessage());
    $_SESSION['erro_login'] = 'Erro ao processar login. Tente novamente.';
    header('Location: login.php');
    exit;
}
