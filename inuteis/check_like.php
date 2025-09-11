<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

if(!isset($_SESSION['user_id']) || !isset($_GET['projeto_id'])){
    echo json_encode(['liked'=>false]);
    exit;
}

$projeto_id = (int)$_GET['projeto_id'];
$usuario_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT 1 FROM curtidas WHERE projeto_id=? AND usuario_id=?");
$stmt->execute([$projeto_id, $usuario_id]);
$liked = $stmt->fetch() ? true : false;

echo json_encode(['liked'=>$liked]);
