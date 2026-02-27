<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departamento Pessoal — Griffus / Belmax</title>
    <link rel="shortcut icon" href="admin/assets/images/icone.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #e91e63;
            --secondary: #5b9bd5;
            --dark: #212121;
            --light: #f8f9fa;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; color: #333; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .container { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); width: 100%; max-width: 480px; overflow: hidden; }
        .header { background: linear-gradient(135deg, var(--primary), #c2185b); color: #fff; padding: 40px 30px; text-align: center; }
        .header h1 { font-size: 1.8rem; font-weight: 700; margin-bottom: 8px; letter-spacing: 1px; }
        .header h1 span { font-weight: 300; opacity: 0.9; }
        .header p { font-size: 0.95rem; opacity: 0.8; }
        .content { padding: 30px; display: flex; flex-direction: column; gap: 16px; }
        .btn { display: flex; align-items: center; padding: 16px 20px; border-radius: 12px; text-decoration: none; color: #444; background: #fff; border: 1px solid #eee; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-color: #ddd; color: var(--primary); }
        .btn i { font-size: 1.5rem; margin-right: 16px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: var(--light); border-radius: 50%; color: #666; transition: color 0.2s, background 0.2s; }
        .btn:hover i { background: #fce4ec; color: var(--primary); }
        .btn-info h3 { font-size: 1rem; font-weight: 600; margin-bottom: 2px; }
        .btn-info p { font-size: 0.85rem; color: #888; }
        .footer { text-align: center; padding: 20px; font-size: 0.8rem; color: #aaa; border-top: 1px solid #f9f9f9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GRIFFUS<span>DP</span></h1>
            <p>Portal do Departamento Pessoal</p>
        </div>
        <div class="content">
            <a href="selecao/" class="btn">
                <i class="fa-solid fa-user-plus"></i>
                <div class="btn-info">
                    <h3>Ficha de Seleção</h3>
                    <p>Preenchimento de ficha para candidatos.</p>
                </div>
            </a>
            
            <a href="contratos/" class="btn">
                <i class="fa-solid fa-file-contract"></i>
                <div class="btn-info">
                    <h3>Envio de Contratos</h3>
                    <p>Cadastro e upload de documentos.</p>
                </div>
            </a>

            <a href="admin/" class="btn">
                <i class="fa-solid fa-lock"></i>
                <div class="btn-info">
                    <h3>Administração</h3>
                    <p>Acesso restrito para gestão.</p>
                </div>
            </a>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> Griffus / Belmax. Todos os direitos reservados.
        </div>
    </div>
</body>
</html>
