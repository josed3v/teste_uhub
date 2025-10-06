<?php
$projectId = $projeto['id'];
$totalLikes = $projeto['total_likes'];
$userLike = !empty($projeto['user_like']);
$userColab = !empty($projeto['user_colab']);
$imagensStr = $projeto['imagens'];
$imagensArr = $imagensStr ? explode('|', $imagensStr) : [];
$dataFormatada = date("d/m/Y H:i", strtotime($projeto['data_publicacao']));
$autorId = $projeto['usuario_id']; // ID do autor
?>

<div class="col-md-4 mb-4">
    <div class="card shadow-sm h-100" data-bs-toggle="modal" data-bs-target="#projectModal"
         data-id="<?= $projectId ?>"
         data-titulo="<?= htmlspecialchars($projeto['titulo']) ?>"
         data-descricao="<?= htmlspecialchars($projeto['descricao']) ?>"
         data-imagens="<?= htmlspecialchars($imagensStr) ?>"
         data-likes="<?= $totalLikes ?>"
         data-userlike="<?= $userLike ? 1 : 0 ?>"
         data-usercolab="<?= $userColab ? 1 : 0 ?>"
         data-autor="<?= htmlspecialchars($projeto['autor']) ?>"
         data-data="<?= $projeto['data_publicacao'] ?>"
    >
        <?php if (!empty($imagensArr)): ?>
            <?php
                list($firstImg, $focus) = explode('::', $imagensArr[0]);
                $focus = $focus ?: "center";
            ?>
            <img src="<?= htmlspecialchars($firstImg) ?>" class="card-img-top" style="height:200px; object-fit:cover; object-position:<?= htmlspecialchars($focus) ?>;">
        <?php else: ?>
            <img src="uploads/default.png" class="card-img-top" style="height:200px; object-fit:cover;">
        <?php endif; ?>

        <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($projeto['titulo']) ?></h5>
            <p class="text-muted small">
                Por 
                <a href="profile.php?id=<?= urlencode($autorId) ?>" class="text-decoration-none fw-bold">
                    <?= htmlspecialchars($projeto['autor']) ?>
                </a>
                <br>
                <small><?= $dataFormatada ?></small>
            </p>

            <p class="card-text text-truncate"><?= htmlspecialchars($projeto['descricao']) ?></p>

            <div class="mt-auto d-flex justify-content-between align-items-center">
                <button class="btn <?= $userLike ? 'btn-primary' : 'btn-outline-primary' ?> like-btn" data-id="<?= $projectId ?>">
                    ❤️ <span id="like-count-<?= $projectId ?>"><?= $totalLikes ?></span>
                </button>
                <button class="btn <?= $userColab ? 'btn-success' : 'btn-outline-success' ?> colab-btn" data-id="<?= $projectId ?>">
                    🤝
                </button>
            </div>
        </div>
    </div>
</div>
