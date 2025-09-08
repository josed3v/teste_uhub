<?php
session_start();
require_once "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: index.html");
    exit;
}

$usuario_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT c.id AS colab_id, c.usuario_id, c.status, u.nome, u.foto_perfil, p.titulo, p.id AS projeto_id
                       FROM colaboracoes c
                       JOIN usuarios u ON c.usuario_id = u.id
                       JOIN projetos p ON c.projeto_id = p.id
                       WHERE p.usuario_id=? AND c.status='pendente'");
$stmt->execute([$usuario_id]);
$solicitacoes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Solicitações de Colaboração</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "header.php"; ?>

<div class="container py-5">
    <h2>Solicitações de Colaboração</h2>

    <?php if(empty($solicitacoes)): ?>
        <p>Nenhuma solicitação pendente.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach($solicitacoes as $s): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <img src="<?= htmlspecialchars($s['foto_perfil']) ?>" alt="Foto" width="40" height="40" class="rounded-circle me-2" style="object-fit:cover;">
                    <strong><?= htmlspecialchars($s['nome']) ?></strong> quer colaborar no projeto <em><?= htmlspecialchars($s['titulo']) ?></em>
                </div>
                <div>
                    <button class="btn btn-success btn-sm me-1" onclick="responderColab(<?= $s['colab_id'] ?>,'aceito')">Aceitar</button>
                    <button class="btn btn-danger btn-sm" onclick="responderColab(<?= $s['colab_id'] ?>,'rejeitado')">Rejeitar</button>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function responderColab(colab_id, status){
    fetch('responder_colab.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({colab_id, status})
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}
</script>
</body>
</html>
