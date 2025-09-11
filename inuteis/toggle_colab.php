<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($_SESSION['user_id']) || !isset($data['projeto_id'])){
    echo json_encode(['message'=>'Erro!']);
    exit;
}

$projeto_id = (int)$data['projeto_id'];
$usuario_id = $_SESSION['user_id'];

// Verifica se já existe pedido pendente
$stmt = $pdo->prepare("SELECT id, status FROM colaboracoes WHERE projeto_id=? AND usuario_id=?");
$stmt->execute([$projeto_id, $usuario_id]);
$colab = $stmt->fetch();

if($colab){
    // Cancelar pedido
    $stmtDel = $pdo->prepare("DELETE FROM colaboracoes WHERE id=?");
    $stmtDel->execute([$colab['id']]);
    $msg = "Pedido de colaboração cancelado.";
} else {
    // Criar pedido
    $stmtIns = $pdo->prepare("INSERT INTO colaboracoes (projeto_id, usuario_id, status) VALUES (?,?,?)");
    $stmtIns->execute([$projeto_id, $usuario_id, 'pendente']);
    $msg = "Pedido de colaboração enviado!";
}

echo json_encode(['message'=>$msg]);
