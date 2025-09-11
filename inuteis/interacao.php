<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Não autorizado"]);
    exit;
}

$user_id = $_SESSION["user_id"];
$action = $_POST['action'] ?? '';
$projeto_id = (int)($_POST['projeto_id'] ?? 0);

if (!$projeto_id) {
    echo json_encode(["error" => "Projeto inválido"]);
    exit;
}

switch($action){
    case "curtir":
        $stmt = $pdo->prepare("INSERT IGNORE INTO projeto_curtidas (projeto_id, usuario_id) VALUES (?, ?)");
        $stmt->execute([$projeto_id, $user_id]);
        echo json_encode(["success"=>true]);
        break;

    case "pedir_colaboracao":
        $stmt = $pdo->prepare("INSERT IGNORE INTO projeto_colaboracoes (projeto_id, usuario_id) VALUES (?, ?)");
        $stmt->execute([$projeto_id, $user_id]);
        echo json_encode(["success"=>true]);
        break;

    case "aceitar_colaboracao":
        $stmt = $pdo->prepare("UPDATE projeto_colaboracoes SET status='aceito' WHERE projeto_id=? AND usuario_id=?");
        $stmt->execute([$projeto_id, $user_id]);
        echo json_encode(["success"=>true]);
        break;

    case "rejeitar_colaboracao":
        $stmt = $pdo->prepare("UPDATE projeto_colaboracoes SET status='rejeitado' WHERE projeto_id=? AND usuario_id=?");
        $stmt->execute([$projeto_id, $user_id]);
        echo json_encode(["success"=>true]);
        break;

    default:
        echo json_encode(["error"=>"Ação inválida"]);
}
