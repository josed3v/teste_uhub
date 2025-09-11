<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['colaboracao_id']) || !isset($_POST['acao'])) {
    echo json_encode(['status'=>'error','msg'=>'Dados inválidos']);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$colaboracao_id = (int)$_POST['colaboracao_id'];
$acao = $_POST['acao']; // aceitar ou rejeitar

// Verifica se o pedido pertence a um projeto do usuário logado
$stmt = $pdo->prepare("
    SELECT c.*, p.usuario_id 
    FROM colaboracoes c 
    JOIN projetos p ON c.projeto_id = p.id 
    WHERE c.id=?");
$stmt->execute([$colaboracao_id]);
$pedido = $stmt->fetch();

if(!$pedido || $pedido['usuario_id'] != $usuario_id){
    echo json_encode(['status'=>'error','msg'=>'Ação inválida']);
    exit;
}

// Atualiza status
$status = $acao === 'aceitar' ? 'aceito' : 'rejeitado';
$stmtUpd = $pdo->prepare("UPDATE colaboracoes SET status=? WHERE id=?");
$stmtUpd->execute([$status,$colaboracao_id]);

echo json_encode(['status'=>'ok','msg'=>'Pedido '.$status.'!']);
