<?php
// Teste simples de PHP
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste PHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f0f0f0;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        h1 { color: #4CAF50; }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            margin: 20px 0;
        }
        .info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
            margin: 10px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #e91e63;
            color: white;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>✅ PHP está Funcionando!</h1>
        
        <div class="success">
            <strong>Parabéns!</strong> O PHP está rodando corretamente no seu XAMPP!
        </div>

        <h2>📊 Informações do PHP</h2>
        <table>
            <tr>
                <th>Configuração</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td><strong>Versão do PHP:</strong></td>
                <td><code><?php echo phpversion(); ?></code></td>
            </tr>
            <tr>
                <td><strong>Hora do Servidor:</strong></td>
                <td><?php echo date('d/m/Y H:i:s'); ?></td>
            </tr>
            <tr>
                <td><strong>Timezone:</strong></td>
                <td><?php echo date_default_timezone_get(); ?></td>
            </tr>
            <tr>
                <td><strong>Upload Max Size:</strong></td>
                <td><?php echo ini_get('upload_max_filesize'); ?></td>
            </tr>
            <tr>
                <td><strong>Post Max Size:</strong></td>
                <td><?php echo ini_get('post_max_size'); ?></td>
            </tr>
            <tr>
                <td><strong>Memory Limit:</strong></td>
                <td><?php echo ini_get('memory_limit'); ?></td>
            </tr>
            <tr>
                <td><strong>Display Errors:</strong></td>
                <td><?php echo ini_get('display_errors') ? 'ON' : 'OFF'; ?></td>
            </tr>
        </table>

        <h2>🔧 Extensões Importantes</h2>
        <div class="info">
            <strong>PDO:</strong> <?php echo extension_loaded('pdo') ? '✅ Instalado' : '❌ Não instalado'; ?><br>
            <strong>PDO MySQL:</strong> <?php echo extension_loaded('pdo_mysql') ? '✅ Instalado' : '❌ Não instalado'; ?><br>
            <strong>ZipArchive:</strong> <?php echo class_exists('ZipArchive') ? '✅ Instalado' : '❌ Não instalado'; ?><br>
            <strong>SimpleXML:</strong> <?php echo extension_loaded('simplexml') ? '✅ Instalado' : '❌ Não instalado'; ?><br>
            <strong>GD:</strong> <?php echo extension_loaded('gd') ? '✅ Instalado' : '❌ Não instalado'; ?>
        </div>

        <?php
// Verificar se o arquivo FuncionariosController.php existe
$controllerPath = dirname(__DIR__, 2) . '/app/controllers/FuncionariosController.php';
?>

        <h2>📁 Verificação de Arquivos</h2>
        <div class="info">
            <strong>FuncionariosController.php:</strong><br>
            <code><?php echo $controllerPath; ?></code><br>
            <?php if (file_exists($controllerPath)): ?>
                ✅ Arquivo encontrado!<br>
                <small>Tamanho: <?php echo round(filesize($controllerPath) / 1024, 2); ?> KB</small><br>
                <small>Modificado em: <?php echo date('d/m/Y H:i:s', filemtime($controllerPath)); ?></small>
            <?php
else: ?>
                ❌ Arquivo não encontrado!<br>
                <small style="color: red;">O arquivo precisa estar neste caminho exato.</small>
            <?php
endif; ?>
        </div>

        <h2>🔗 Próximo Passo</h2>
        <div class="success">
            Tudo está funcionando! Agora você pode:
            <ol>
                <li>Fechar esta janela</li>
                <li>Acessar o sistema normalmente</li>
                <li>Tentar a importação de Excel</li>
            </ol>
        </div>
    </div>
</body>
</html>