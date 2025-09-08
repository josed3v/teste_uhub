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

    <!-- Header compartilhado (inclui navbar e deve conter o Bootstrap JS bundle) -->
    <?php include "header.php"; ?>

<div class="container mt-4">
    <h2 class="mb-4">Feed de Projetos</h2>

    <?php foreach($projetos as $projeto): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($projeto['titulo']) ?></h5>
                <h6 class="card-subtitle text-muted mb-2">por <?= htmlspecialchars($projeto['autor']) ?> em <?= date("d/m/Y H:i", strtotime($projeto['data_publicacao'])) ?></h6>
                <p class="card-text"><?= nl2br(htmlspecialchars($projeto['descricao'])) ?></p>
                
                <!-- Buscar imagens do projeto -->
                <?php
                $stmtImg = $pdo->prepare("SELECT imagem FROM projeto_imagens WHERE projeto_id = ?");
                $stmtImg->execute([$projeto['id']]);
                $imagens = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="d-flex flex-wrap gap-2">
                    <?php foreach($imagens as $img): ?>
                        <img src="<?= htmlspecialchars($img['imagem']) ?>" class="project-photo rounded" data-bs-toggle="modal" data-bs-target="#imgModal" data-src="<?= htmlspecialchars($img['imagem']) ?>" alt="Imagem do projeto" style="max-height:150px; object-fit:cover;">
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal para expandir imagem -->
<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0 d-flex justify-content-center align-items-center">
      <img id="modalImage" src="" class="img-fluid rounded shadow" alt="Imagem ampliada">
    </div>
  </div>
</div>

<!-- Script específico da página (NÃO inlcuir bootstrap.bundle aqui: header.php já deve carregar) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imgModal = document.getElementById('imgModal');
    if (!imgModal) return;

    imgModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const src = button.getAttribute('data-src');
        const modalImage = document.getElementById('modalImage');
        if (modalImage && src) modalImage.setAttribute('src', src);
    });

    // limpa a imagem ao fechar para evitar exibição residual
    imgModal.addEventListener('hidden.bs.modal', function () {
        const modalImage = document.getElementById('modalImage');
        if (modalImage) modalImage.removeAttribute('src');
    });
});
</script>

</body>
</html>
