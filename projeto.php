<?php
session_start();
require_once "db.php";

if (!isset($_GET['id'])) {
    header("Location: feed.php");
    exit;
}

$projectId = (int)$_GET['id'];

// Busca o projeto e imagens
$stmt = $pdo->prepare("
    SELECT p.*, u.id AS autor_id, u.nome AS autor, u.foto_perfil,
           GROUP_CONCAT(CONCAT(pi.imagem,'::',pi.focus) SEPARATOR '|') AS imagens,
           (SELECT COUNT(*) FROM curtidas WHERE projeto_id=p.id) AS total_likes,
           (SELECT COUNT(*) FROM curtidas WHERE projeto_id=p.id AND usuario_id=?) AS user_like,
           (SELECT COUNT(*) FROM colaboracoes WHERE projeto_id=p.id AND usuario_id=?) AS user_colab
    FROM projetos p
    JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN projeto_imagens pi ON p.id = pi.projeto_id
    WHERE p.id = ?
    GROUP BY p.id
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $projectId]);
$projeto = $stmt->fetch();

if (!$projeto) {
    exit("Projeto não encontrado.");
}

$imagensArr = $projeto['imagens'] ? explode('|', $projeto['imagens']) : [];
$dataFormatada = date("d/m/Y H:i", strtotime($projeto['data_publicacao']));
$isOwner = $projeto['usuario_id'] == $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($projeto['titulo']) ?> - UHub</title>
    <link rel="stylesheet" href="css/css/bootstrap.min.css">
    <link href="css/styles.css" rel="stylesheet">

    <style>
        .project-carousel img {
            height: 420px;
            object-fit: cover;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .project-carousel img { height: 250px; }
        }

        /* Lightbox */
        .lightbox-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1060;
        }
        .lightbox-img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255,255,255,0.07);
            transition: transform 0.2s ease-in-out;
        }
        .lightbox-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: white;
            background: rgba(0,0,0,0.5);
            border: none;
            padding: 0.5rem 0.8rem;
            cursor: pointer;
            border-radius: 50%;
            z-index: 1061;
        }
        .lightbox-btn:hover { background: rgba(255,255,255,0.2); }
        .lightbox-btn.prev { left: 20px; }
        .lightbox-btn.next { right: 20px; }
        @media (max-width: 480px) {
            .lightbox-img { max-width: 100vw; max-height: 65vh; }
            .lightbox-btn { font-size: 1.8rem; background: rgba(0,0,0,0.7); }
        }
    </style>
</head>
<body class="bg-light">

<?php include __DIR__ . '/components/header.php'; ?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm border-0">
        <!-- 🖼️ Carrossel -->
        <?php if (!empty($imagensArr)): ?>
        <div id="carouselProjeto" class="carousel slide project-carousel" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($imagensArr as $index => $imgData): 
                    list($src, $focus) = explode('::', $imgData);
                    $focus = $focus ?: "center";
                ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= htmlspecialchars($src) ?>" 
                         class="d-block w-100 project-image"
                         style="object-position:<?= htmlspecialchars($focus) ?>"
                         alt="Imagem do projeto">
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($imagensArr) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselProjeto" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselProjeto" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="card-body">
            <h2 class="fw-bold"><?= htmlspecialchars($projeto['titulo']) ?></h2>
            <p class="text-muted mb-3">
                Por <a href="profile.php?id=<?= $projeto['autor_id'] ?>" class="fw-semibold text-decoration-none">
                    <?= htmlspecialchars($projeto['autor']) ?>
                </a> em <?= $dataFormatada ?>
            </p>

            <p class="lead"><?= nl2br(htmlspecialchars($projeto['descricao'])) ?></p>

            <div class="d-flex align-items-center mt-3">
                <button class="btn <?= $projeto['user_like'] ? 'btn-primary' : 'btn-outline-primary' ?> like-btn" data-id="<?= $projectId ?>">
                    ❤️ <span id="like-count"><?= $projeto['total_likes'] ?></span>
                </button>
                <button class="btn <?= $projeto['user_colab'] ? 'btn-success ms-2' : 'btn-outline-success ms-2' ?> colab-btn" data-id="<?= $projectId ?>">
                    🤝 Colaborar
                </button>
            </div>

            <?php if ($isOwner): ?>
            <div class="mt-4">
                <a href="edit_project.php?id=<?= $projectId ?>" class="btn btn-warning me-2">Editar</a>
                <a href="profile.php?delete=<?= $projectId ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este projeto?')">Excluir</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lightbox -->
<div id="imageLightbox" class="lightbox-overlay d-none">
  <button id="prevBtn" class="lightbox-btn prev">&#10094;</button>
  <img id="lightboxImage" class="lightbox-img" src="" alt="Imagem expandida">
  <button id="nextBtn" class="lightbox-btn next">&#10095;</button>
</div>

<script src="js/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/project.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const imgs = Array.from(document.querySelectorAll('.project-image'));
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImage');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    let currentIndex = 0;

    function openLightbox(index) {
        currentIndex = index;
        lightboxImg.src = imgs[currentIndex].src;
        lightbox.classList.remove('d-none');
    }

    imgs.forEach((img, i) => {
        img.addEventListener('click', () => openLightbox(i));
    });

    function showImage(index) {
        if (index < 0) index = imgs.length - 1;
        if (index >= imgs.length) index = 0;
        currentIndex = index;
        lightboxImg.src = imgs[currentIndex].src;
    }

    prevBtn.addEventListener('click', (e) => { e.stopPropagation(); showImage(currentIndex - 1); });
    nextBtn.addEventListener('click', (e) => { e.stopPropagation(); showImage(currentIndex + 1); });

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) lightbox.classList.add('d-none');
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') lightbox.classList.add('d-none');
        if (e.key === 'ArrowRight') showImage(currentIndex + 1);
        if (e.key === 'ArrowLeft') showImage(currentIndex - 1);
    });
});
</script>

</body>
</html>
