<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

// Recebe dados JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['colab_id'], $data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$colab_id = (int)$data['colab_id'];
$status = $data['status'];
$usuario_id = $_SESSION['user_id'];

// Verifica se o usuário é dono do projeto
$stmt = $pdo->prepare("
    SELECT c.* , p.usuario_id AS dono_projeto, p.id AS projeto_id
    FROM colaboracoes c
    JOIN projetos p ON c.projeto_id = p.id
    WHERE c.id = ? AND p.usuario_id = ?
");
$stmt->execute([$colab_id, $usuario_id]);
$colab = $stmt->fetch();

if (!$colab) {
    echo json_encode(['success' => false, 'message' => 'Colaboração não encontrada']);
    exit;
}

// Atualiza o status
$stmtUpdate = $pdo->prepare("UPDATE colaboracoes SET status = ? WHERE id = ?");
$stmtUpdate->execute([$status, $colab_id]);

// Se aceito, podemos garantir que o projeto apareça no perfil do colaborador
if ($status === 'aceito') {
    // Checa se já existe (para evitar duplicação)
    $stmtCheck = $pdo->prepare("SELECT * FROM colaboracoes WHERE projeto_id = ? AND usuario_id = ?");
    $stmtCheck->execute([$colab['projeto_id'], $colab['usuario_id']]);
    if (!$stmtCheck->fetch()) {
        // Insere registro confirmando colaboração aceita
        $stmtInsert = $pdo->prepare("INSERT INTO colaboracoes (projeto_id, usuario_id, status) VALUES (?, ?, 'aceito')");
        $stmtInsert->execute([$colab['projeto_id'], $colab['usuario_id']]);
    }
}

echo json_encode([
    'success' => true,
    'message' => $status === 'aceito' ? 'Colaboração aceita!' : 'Colaboração rejeitada!'
]);
