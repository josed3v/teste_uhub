<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['projeto_id'])) {
    echo json_encode(['status'=>'error','msg'=>'Dados inválidos']);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$projeto_id = (int)$_POST['projeto_id'];

// Verifica se já curtiu
$stmt = $pdo->prepare("SELECT * FROM curtidas WHERE usuario_id=? AND projeto_id=?");
$stmt->execute([$usuario_id,$projeto_id]);
$curtida = $stmt->fetch();

if($curtida){
    // Remove curtida
    $stmtDel = $pdo->prepare("DELETE FROM curtidas WHERE usuario_id=? AND projeto_id=?");
    $stmtDel->execute([$usuario_id,$projeto_id]);
    echo json_encode(['status'=>'ok','curtido'=>false]);
} else {
    // Adiciona curtida
    $stmtIns = $pdo->prepare("INSERT INTO curtidas(usuario_id,projeto_id) VALUES(?,?)");
    $stmtIns->execute([$usuario_id,$projeto_id]);
    echo json_encode(['status'=>'ok','curtido'=>true]);
}
