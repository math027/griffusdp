<?php
declare(strict_types=1);

/**
 * Retorna uma conexão PDO segura usando prepared statements.
 */
return (function (): PDO {
    $host = getenv('DB_HOST') ?: 'localhost';
    $db = getenv('DB_NAME') ?: 'griffu80_dp';
    $user = getenv('DB_USER') ?: 'griffu80_dp';
    $pass = getenv('DB_PASS') ?: 'Griffus@26';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        error_log('Falha ao conectar no banco: ' . $e->getMessage());
        http_response_code(500);
        exit('Erro interno. Tente novamente mais tarde.');
    }
})();
