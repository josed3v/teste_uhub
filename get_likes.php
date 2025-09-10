<?php
session_start();
require_once "db.php";

if(!isset($_SESSION["user_id"])) {
    echo json_encode(["status"=>"error", "msg"=>"Usuário não logado"]);
    exit;
}

if(!isset($_POST["id"])) {
    echo json_encode(["status"=>"error", "msg"=>"ID inválido"]);
    exit;
}

$usuario_id = $_SESSION["user_id"];
$projeto_id = intval($_POST["id"]);

// Contar curtidas totais
$stmt = $pdo->prepare("SELECT COUNT(*) FROM curtidas WHERE projeto_id=?");
$stmt->execute([$projeto_id]);
$count = $stmt->fetchColumn();

// Verificar se o usuário curtiu
$stmt = $pdo->prepare("SELECT 1 FROM curtidas WHERE projeto_id=? AND usuario_id=?");
$stmt->execute([$projeto_id, $usuario_id]);
$user_like = $stmt->fetch() ? true : false;

echo json_encode(["status"=>"ok","count"=>$count,"user_like"=>$user_like]);
