<?php
session_start();
require_once "db.php";

if (!isset($_GET['id'])) {
    header("Location: feed.php");
    exit;
}

$projectId = (int)$_GET['id'];

// Busca dados do projeto
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
</head>
<body class="bg-light">

<?php include __DIR__ . '/components/header.php'; ?>

<div class="container mt-5">
    <div class="card shadow-sm">
        <?php if (!empty($imagensArr)): ?>
            <?php
                list($firstImg, $focus) = explode('::', $imagensArr[0]);
                $focus = $focus ?: "center";
            ?>
            <img src="<?= $firstImg ?>" class="card-img-top" style="height:300px; object-fit:cover; object-position:<?= $focus ?>;">
        <?php endif; ?>

        <div class="card-body">
            <h2><?= htmlspecialchars($projeto['titulo']) ?></h2>
            <p class="text-muted">
                Por <a href="profile.php?id=<?= $projeto['autor_id'] ?>" class="fw-bold text-decoration-none"><?= htmlspecialchars($projeto['autor']) ?></a>
                em <?= $dataFormatada ?>
            </p>

            <p><?= nl2br(htmlspecialchars($projeto['descricao'])) ?></p>

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

<script src="js/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/project.js"></script>
</body>
</html>
