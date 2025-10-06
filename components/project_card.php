<?php
// $projeto deve ser definido antes de incluir este arquivo
$imagens = !empty($projeto['imagens']) ? explode('|', $projeto['imagens']) : [];
$user_colab = $projeto['user_colab'] ?? false; // true se o usuário já colaborou
?>

<div class="col-md-4 mb-3">
    <div class="card project-card position-relative"
        data-bs-toggle="modal" data-bs-target="#projectModal"
        data-id="<?= $projeto['id'] ?>"
        data-titulo="<?= htmlspecialchars($projeto['titulo'], ENT_QUOTES) ?>"
        data-descricao="<?= htmlspecialchars($projeto['descricao'], ENT_QUOTES) ?>"
        data-imagens="<?= htmlspecialchars($projeto['imagens'], ENT_QUOTES) ?>"
        data-likes="<?= $projeto['total_likes'] ?>"
        data-userlike="<?= $projeto['user_like'] ?>"
        data-usercolab="<?= $user_colab ?>">

        <!-- Tag de perfil -->
        <a href="profile.php?id=<?= $projeto['usuario_id'] ?>" class="profile-tag">
            <img src="uploads/profile_tag.png" alt="Tag" class="tag-bg">
            <img src="<?= htmlspecialchars($projeto['foto_perfil'] ?? 'uploads/default.png') ?>"
                alt="Foto do Autor" class="tag-avatar">
        </a>

        <!-- Primeira imagem do projeto -->
        <?php if (!empty($imagens)):
            list($imgSrc, $imgFocus) = explode('::', $imagens[0]);
        ?>
            <img src="<?= htmlspecialchars($imgSrc) ?>" 
                 alt="Imagem do projeto"
                 style="object-fit:cover; object-position:<?= htmlspecialchars($imgFocus) ?>; width:100%; height:200px;">
        <?php endif; ?>

        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($projeto['titulo']) ?></h5>
            <div>
                <button class="like-btn btn <?= $projeto['user_like'] ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm"
                        data-id="<?= $projeto['id'] ?>">❤️</button>
                <span class="like-count" id="like-count-<?= $projeto['id'] ?>"><?= $projeto['total_likes'] ?></span>

                <button class="colab-btn btn <?= $user_colab ? 'btn-success' : 'btn-outline-success' ?> btn-sm"
                        data-id="<?= $projeto['id'] ?>">🤝</button>
            </div>

            <h6 class="card-subtitle text-muted mb-3 mt-1">
                por <?= htmlspecialchars($projeto['autor']) ?>
                em <?= date("d/m/Y H:i", strtotime($projeto['data_publicacao'])) ?>
            </h6>

            <p class="card-text"><?= nl2br(htmlspecialchars(substr($projeto['descricao'], 0, 100))) ?>...</p>
        </div>
    </div>
</div>
