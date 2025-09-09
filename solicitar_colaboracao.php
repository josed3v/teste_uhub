<?php
session_start();
if (!isset($_SESSION['user_id'])) exit(json_encode(['status'=>'error','msg'=>'Usuário não logado']));

require_once "db.php";

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id'])){
    $projeto_id = (int)$_POST['id'];
    $usuario_id = $_SESSION['user_id'];

    // Verifica se já existe colaboração pendente ou aceita
    $stmt = $pdo->prepare("SELECT id FROM colaboracoes WHERE projeto_id=? AND usuario_id=?");
    $stmt->execute([$projeto_id,$usuario_id]);
    if($stmt->fetch()){
        exit(json_encode(['status'=>'error','msg'=>'Solicitação já existente']));
    }

    // Insere solicitação pendente
    $stmt = $pdo->prepare("INSERT INTO colaboracoes (projeto_id, usuario_id, status) VALUES (?,?,?)");
    $stmt->execute([$projeto_id,$usuario_id,'pendente']);

    echo json_encode(['status'=>'ok']);
}
