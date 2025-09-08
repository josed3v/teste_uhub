<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['projeto_id'])) {
    echo json_encode(['status'=>'error','msg'=>'Dados inválidos']);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$projeto_id = (int)$_POST['projeto_id'];

// Verifica se já existe pedido
$stmt = $pdo->prepare("SELECT * FROM colaboracoes WHERE usuario_id=? AND projeto_id=?");
$stmt->execute([$usuario_id,$projeto_id]);
$pedido = $stmt->fetch();

if($pedido){
    echo json_encode(['status'=>'error','msg'=>'Você já solicitou colaboração neste projeto.']);
    exit;
}

// Insere pedido
$stmtIns = $pdo->prepare("INSERT INTO colaboracoes(usuario_id,projeto_id,status) VALUES(?,?,?)");
$stmtIns->execute([$usuario_id,$projeto_id,'pendente']);

echo json_encode(['status'=>'ok','msg'=>'Pedido de colaboração enviado!']);
