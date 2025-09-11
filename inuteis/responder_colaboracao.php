<?php
session_start();
header('Content-Type: application/json');
require_once "db.php";

if(!isset($_SESSION['user_id'])) exit(json_encode(['status'=>'error']));

$id = (int)($_POST['id']??0);
$acao = $_POST['acao']??'';

// Verifica se a colaboração existe e pertence ao projeto do usuário
$stmt = $pdo->prepare("SELECT c.*, p.usuario_id AS dono FROM colaboracoes c JOIN projetos p ON c.projeto_id=p.id WHERE c.id=?");
$stmt->execute([$id]);
$col = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$col || $col['dono'] != $_SESSION['user_id']) exit(json_encode(['status'=>'error']));

$status = $acao==='aceitar'?'aceito':'rejeitado';
$pdo->prepare("UPDATE colaboracoes SET status=? WHERE id=?")->execute([$status,$id]);

// Aqui poderia enviar notificação para o solicitante

echo json_encode(['status'=>'ok']);
