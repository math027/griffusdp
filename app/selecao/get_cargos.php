<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $pdo = require __DIR__ . '/../config/database.php';

    $empresa = trim((string)($_GET['empresa'] ?? ''));

    if ($empresa !== '') {
        $stmt = $pdo->prepare("SELECT id, cargo AS nome FROM vagas WHERE ativo = 1 AND empresa = ? ORDER BY cargo ASC");
        $stmt->execute([$empresa]);
    } else {
        $stmt = $pdo->query("SELECT id, cargo AS nome FROM vagas WHERE ativo = 1 ORDER BY empresa ASC, cargo ASC");
    }

    $cargos = [];
    while ($row = $stmt->fetch()) {
        $cargos[] = ['id' => (int)$row['id'], 'nome' => $row['nome']];
    }

    echo json_encode(['success' => true, 'cargos' => $cargos]);
} catch (Exception $e) {
    error_log('Erro ao buscar cargos: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'cargos' => [], 'message' => 'Erro ao buscar cargos.']);
}
