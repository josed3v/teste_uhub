<?php
session_start();
require_once "db.php";

$user_id = $_SESSION["user_id"];
$colab_id = intval($_POST['colab_id']);
$acao = $_POST['acao']; // 'aceitar' ou 'rejeitar'

if(!in_array($acao,['aceitar','rejeitar'])) exit;

$status = ($acao==='aceitar') ? 'aceita' : 'rejeitada';

$stmt = $pdo->prepare("
    UPDATE colaboracoes c
    JOIN projetos p ON c.projeto_id = p.id
    SET c.status = ?
    WHERE c.id = ? AND p.usuario_id = ?
");
$stmt->execute([$status, $colab_id, $user_id]);

echo json_encode(['success'=>true]);
