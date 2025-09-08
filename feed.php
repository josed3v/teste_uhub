<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.html");
    exit;
}

require_once "db.php";

// Buscar todos os projetos com informações do usuário e imagens
$stmt = $pdo->query("
    SELECT p.id, p.titulo, p.descricao, p.data_publicacao, u.nome AS autor
    FROM projetos p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.data_publicacao DESC
");
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feed</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "header.php"; ?>

<div class="container mt-4">
    <h2 class="mb-4">Feed de Projetos</h2>

    <?php foreach($projetos as $projeto): 
        // Buscar imagens do projeto
        $stmtImg = $pdo->prepare("SELECT imagem FROM projeto_imagens WHERE projeto_id = ?");
        $stmtImg->execute([$projeto['id']]);
        $imagens = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

        // Curtidas
        $stmtCurtidas = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN usuario_id = ? THEN 1 ELSE 0 END) as curtiu FROM curtidas WHERE projeto_id = ?");
        $stmtCurtidas->execute([$_SESSION["user_id"], $projeto['id']]);
        $curtidaInfo = $stmtCurtidas->fetch();
        $curtiu = $curtidaInfo['curtiu'] > 0;
        $totalCurtidas = $curtidaInfo['total'];
    ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($projeto['titulo']) ?></h5>
                <p class="text-muted mb-1">
                    <span class="like-count"><?= $totalCurtidas ?></span> curtida(s)
                </p>
                <h6 class="card-subtitle text-muted mb-2">por <?= htmlspecialchars($projeto['autor']) ?> em <?= date("d/m/Y H:i", strtotime($projeto['data_publicacao'])) ?></h6>
                <p class="card-text"><?= nl2br(htmlspecialchars($projeto['descricao'])) ?></p>

                <div class="d-flex flex-wrap gap-2 mb-2">
                    <?php foreach($imagens as $img): ?>
                        <img src="<?= htmlspecialchars($img['imagem']) ?>" class="project-photo rounded" data-bs-toggle="modal" data-bs-target="#imgModal" data-src="<?= htmlspecialchars($img['imagem']) ?>">
                    <?php endforeach; ?>
                </div>

                <button class="btn btn-sm <?= $curtiu ? 'btn-primary' : 'btn-outline-primary' ?>" onclick="toggleLike(<?= $projeto['id'] ?>, this)">
                    <?= $curtiu ? 'Curtido' : 'Curtir' ?>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal para expandir imagem -->
<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0">
      <img id="modalImage" src="" class="img-fluid rounded shadow">
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Modal de imagens
const imgModal = document.getElementById('imgModal');
imgModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const src = button.getAttribute('data-src');
    document.getElementById('modalImage').setAttribute('src', src);
});

// Curtidas
function toggleLike(projetoId, btn) {
    fetch('toggle_like.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ projeto_id: projetoId })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success){
            const card = btn.closest('.card');
            card.querySelector('.like-count').textContent = data.total;
            if(data.curtiu){
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
                btn.textContent = 'Curtido';
            } else {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
                btn.textContent = 'Curtir';
            }
        }
    });
}
</script>

</body>
</html>
