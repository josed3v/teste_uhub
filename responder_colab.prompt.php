<?php
session_start();
require_once "db.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($_SESSION['user_id']) || !isset($data['colab_id']) || !isset($data['status'])){
    echo json_encode(['message'=>'Erro!']);
    exit;
}

$colab_id = (int)$data['colab_id'];
$status = $data['status'];

if(!in_array($status,['aceito','rejeitado'])){
    echo json_encode(['message'=>'Status inválido']);
    exit;
}

// Confirma que o projeto pertence ao usuário
$stmt = $pdo->prepare("SELECT p.usuario_id FROM colaboracoes c JOIN projetos p ON c.projeto_id = p.id WHERE c.id=?");
$stmt->execute([$colab_id]);
$projeto = $stmt->fetch();

if(!$projeto || $projeto['usuario_id'] != $_SESSION['user_id']){
    echo json_encode(['message'=>'Permissão negada']);
    exit;
}

// Atualiza status
$stmtUpd = $pdo->prepare("UPDATE colaboracoes SET status=? WHERE id=?");
$stmtUpd->execute([$status, $colab_id]);

echo json_encode(['message'=>'Solicitação ' . $status]);
