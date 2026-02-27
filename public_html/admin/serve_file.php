<?php
/**
 * Serve arquivos do storage (fora do public_html) com autenticação.
 * Uso: serve_file.php?path=uploads/12345678901234/docContratoSocial.pdf
 */
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../verificar_login.php';

$path = $_GET['path'] ?? '';

if (empty($path)) {
    http_response_code(400);
    exit('Parâmetro path obrigatório.');
}

// Previne path traversal
$path = str_replace(['..', "\0"], '', $path);
$path = ltrim($path, '/\\');

$storagePath = dirname(__DIR__, 2) . '/app/storage/' . $path;
$realPath    = realpath($storagePath);
$storageRoot = realpath(dirname(__DIR__, 2) . '/app/storage');

// Garante que o arquivo está dentro de storage/
if (!$realPath || !$storageRoot || strpos($realPath, $storageRoot) !== 0) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

if (!is_file($realPath)) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

// Content-type por extensão
$ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
$mimeTypes = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'svg'  => 'image/svg+xml',
    'bmp'  => 'image/bmp',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'txt'  => 'text/plain',
    'zip'  => 'application/zip',
];

$contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($realPath));
header('Content-Disposition: inline; filename="' . basename($realPath) . '"');
header('Cache-Control: private, max-age=3600');

readfile($realPath);
exit;
