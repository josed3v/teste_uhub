<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($_SESSION['user_id']) || !isset($data['projeto_id'])){
    echo json_encode(['liked'=>false,'total'=>0]);
    exit;
}

$projeto_id = (int)$data['projeto_id'];
$usuario_id = $_SESSION['user_id'];

// Verifica se já curtiu
$stmt = $pdo->prepare("SELECT id FROM curtidas WHERE projeto_id=? AND usuario_id=?");
$stmt->execute([$projeto_id, $usuario_id]);
$curtida = $stmt->fetch();

if($curtida){
    // Descurtir
    $stmtDel = $pdo->prepare("DELETE FROM curtidas WHERE id=?");
    $stmtDel->execute([$curtida['id']]);
    $liked = false;
} else {
    // Curtir
    $stmtIns = $pdo->prepare("INSERT INTO curtidas (projeto_id, usuario_id) VALUES (?,?)");
    $stmtIns->execute([$projeto_id, $usuario_id]);
    $liked = true;
}

// Total atualizado
$stmtTotal = $pdo->prepare("SELECT COUNT(*) as total FROM curtidas WHERE projeto_id=?");
$stmtTotal->execute([$projeto_id]);
$total = $stmtTotal->fetch()['total'];

echo json_encode(['liked'=>$liked,'total'=>$total]);
