<?php
session_start();
require_once "db.php";
$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT c.id AS colab_id, c.projeto_id, c.usuario_id, u.nome AS solicitante, p.titulo AS projeto
    FROM colaboracoes c
    JOIN usuarios u ON c.usuario_id = u.id
    JOIN projetos p ON c.projeto_id = p.id
    WHERE p.usuario_id = ? AND c.status='pendente'
    ORDER BY c.data_solicitacao DESC
");
$stmt->execute([$user_id]);
$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($solicitacoes);
