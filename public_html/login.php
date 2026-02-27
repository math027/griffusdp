<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: admin/index.php');
    exit;
}

$erro = $_SESSION['erro_login'] ?? '';
unset($_SESSION['erro_login']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="admin/assets/images/icone.png" type="image/x-icon">
    <title>Login - Griffus</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #FF69B4 0%, #FF1493 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(255, 20, 147, 0.3);
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-text {
            font-size: 42px;
            font-weight: bold;
            background: linear-gradient(135deg, #FF69B4, #FF1493);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 2px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #FFB6D9;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #FFF5FA;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #FF69B4;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #FF69B4, #FF1493);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(255, 20, 147, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 20, 147, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-message {
            background: #FFE6EE;
            border-left: 4px solid #FF1493;
            color: #C71585;
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-icon {
            font-size: 20px;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #FFB6D9, transparent);
            margin: 30px 0;
        }

        .footer-text {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 30px;
        }

        /* Responsivo */
        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
            }

            .logo-text {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo-text">GRIFFUS</div>
            <div class="subtitle">Painel Administrativo</div>
        </div>

        <?php if ($erro): ?>
        <div class="error-message">
            <span class="error-icon">⚠️</span>
            <span><?= htmlspecialchars($erro) ?></span>
        </div>
        <?php endif; ?>

        <form action="processar_login.php" method="POST">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="seu@email.com"
                    required 
                    autocomplete="email"
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    placeholder="••••••••"
                    required 
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn-login">
                Entrar
            </button>
        </form>

        <div class="divider"></div>
        
        <div class="footer-text">
            © <?= date('Y') ?> Griffus. Todos os direitos reservados.
        </div>
    </div>
</body>
</html>
