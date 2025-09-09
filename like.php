<?php
session_start();
if (!isset($_SESSION['user_id'])) exit(json_encode(['status'=>'error','msg'=>'Usuário não logado']));

require_once "db.php";

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id'])) {
    $projeto_id = (int)$_POST['id'];
    $usuario_id = $_SESSION['user_id'];

    // Verifica se já curtiu
    $stmt = $pdo->prepare("SELECT id FROM curtidas WHERE projeto_id=? AND usuario_id=?");
    $stmt->execute([$projeto_id,$usuario_id]);
    $jaCurtiu = $stmt->fetch();

    if($jaCurtiu){
        // Remove a curtida
        $stmt = $pdo->prepare("DELETE FROM curtidas WHERE id=?");
        $stmt->execute([$jaCurtiu['id']]);
        $user_like = false;
    }else{
        // Adiciona a curtida
        $stmt = $pdo->prepare("INSERT INTO curtidas (projeto_id, usuario_id) VALUES (?,?)");
        $stmt->execute([$projeto_id,$usuario_id]);
        $user_like = true;
    }

    // Contagem atualizada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM curtidas WHERE projeto_id=?");
    $stmt->execute([$projeto_id]);
    $count = $stmt->fetchColumn();

    echo json_encode(['status'=>'ok','count'=>$count,'user_like'=>$user_like]);
}
