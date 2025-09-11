<?php
$imagens = $projeto['imagens'] ? explode('|', $projeto['imagens']) : [];
?>
<div class="col-md-4 mb-3">
    <div class="card project-card" data-bs-toggle="modal" data-bs-target="#projectModal" 
         data-id="<?= $projeto['id'] ?>"
         data-titulo="<?= htmlspecialchars($projeto['titulo'],ENT_QUOTES) ?>"
         data-descricao="<?= htmlspecialchars($projeto['descricao'],ENT_QUOTES) ?>"
         data-imagens="<?= htmlspecialchars($projeto['imagens'],ENT_QUOTES) ?>"
         data-likes="<?= $projeto['total_likes'] ?>"
         data-userlike="<?= $projeto['user_like'] ?>">
        <?php if($imagens): 
            list($imgSrc, $imgFocus) = explode('::', $imagens[0]);
        ?>
            <img src="<?= $imgSrc ?>" alt="Imagem do projeto" style="object-fit:cover; object-position:<?= htmlspecialchars($imgFocus) ?>; width:100%; height:200px;">
        <?php endif; ?>
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($projeto['titulo']) ?></h5>
            <div>
                <button class="like-btn btn <?= $projeto['user_like'] ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm" data-id="<?= $projeto['id'] ?>">❤️</button>
                <span class="like-count" id="like-count-<?= $projeto['id'] ?>"><?= $projeto['total_likes'] ?></span>
                <button class="colab-btn btn btn-outline-success btn-sm" data-id="<?= $projeto['id'] ?>">🤝</button>
            </div>
            <h6 class="card-subtitle text-muted mb-3 mt-1">por <?= htmlspecialchars($projeto['autor']) ?> em <?= date("d/m/Y H:i", strtotime($projeto['data_publicacao'])) ?></h6>
            <p class="card-text"><?= nl2br(htmlspecialchars(substr($projeto['descricao'],0,100))) ?>...</p>
        </div>
    </div>
</div>
