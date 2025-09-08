<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($_SESSION['user_id']) || !isset($input['projeto_id'])) {
    echo json_encode(['success'=>false]);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$projeto_id = (int)$input['projeto_id'];

// Verificar se já existe curtida
$stmt = $pdo->prepare("SELECT * FROM curtidas WHERE usuario_id = ? AND projeto_id = ?");
$stmt->execute([$usuario_id, $projeto_id]);
$curtida = $stmt->fetch();

if($curtida){
    // Descurtir
    $stmt = $pdo->prepare("DELETE FROM curtidas WHERE id = ?");
    $stmt->execute([$curtida['id']]);
    $curtiu = false;
} else {
    // Curtir
    $stmt = $pdo->prepare("INSERT INTO curtidas (usuario_id, projeto_id) VALUES (?, ?)");
    $stmt->execute([$usuario_id, $projeto_id]);
    $curtiu = true;
}

// Atualizar total de curtidas
$stmtTotal = $pdo->prepare("SELECT COUNT(*) as total FROM curtidas WHERE projeto_id = ?");
$stmtTotal->execute([$projeto_id]);
$total = $stmtTotal->fetch()['total'];

echo json_encode(['success'=>true, 'curtiu'=>$curtiu, 'total'=>$total]);
