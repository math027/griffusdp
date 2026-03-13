<?php
/**
 * Retorna as vagas ativas em JSON para o select do formulário.
 */
header('Content-Type: application/json; charset=utf-8');

$db = require __DIR__ . '/../../app/config/database.php';

$stmt = $db->query("SELECT DISTINCT cargo FROM vagas WHERE ativo = 1 ORDER BY cargo ASC");
$vagas = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($vagas);
