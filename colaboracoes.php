<?php
session_start();
require_once "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Busca solicitações pendentes do usuário dono do projeto
$stmt = $pdo->prepare("
    SELECT c.id AS colab_id, c.usuario_id, c.status, u.nome, u.foto_perfil, 
           p.titulo, p.id AS projeto_id
    FROM colaboracoes c
    JOIN usuarios u ON c.usuario_id = u.id
    JOIN projetos p ON c.projeto_id = p.id
    WHERE p.usuario_id=? AND c.status='pendente'
");
$stmt->execute([$usuario_id]);
$solicitacoes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Solicitações de Colaboração</title>
<link rel="stylesheet" href="css/css/bootstrap.min.css">
</head>
<body>
<?php include "components/header.php"; ?>

<div class="container py-5">
    <h2>Solicitações de Colaboração</h2>

    <?php if(empty($solicitacoes)): ?>
        <p>Nenhuma solicitação pendente.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach($solicitacoes as $s): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <!-- FOTO + NOME viram link para o perfil do usuário -->
                    <a href="profile_view.php?id=<?= $s['usuario_id'] ?>" class="d-flex align-items-center text-decoration-none text-dark">
                        <img src="<?= htmlspecialchars($s['foto_perfil'] ?: 'uploads/default.png') ?>" 
                             alt="Foto" width="40" height="40" 
                             class="rounded-circle me-2" style="object-fit:cover;">
                        <strong><?= htmlspecialchars($s['nome']) ?></strong>
                    </a>
                    <span class="ms-2">quer colaborar no projeto <em><?= htmlspecialchars($s['titulo']) ?></em></span>
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

<script src="js/js/bootstrap.bundle.min.js"></script>
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
