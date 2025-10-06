<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.html");
    exit;
}

require_once "db.php";

// --- Identificar se é perfil próprio ou de visita ---
if (isset($_GET['id']) && $_GET['id'] != $_SESSION['user_id']) {
    $profileId = (int)$_GET['id'];
    $isOwnProfile = false;
} else {
    $profileId = $_SESSION['user_id'];
    $isOwnProfile = true;
}

// --- Deletar projeto (só se for meu perfil) ---
if ($isOwnProfile && isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmtCheck = $pdo->prepare("SELECT * FROM projetos WHERE id = ? AND usuario_id = ?");
    $stmtCheck->execute([$delete_id, $_SESSION['user_id']]);
    $projDel = $stmtCheck->fetch();
    if ($projDel) {
        $stmtImg = $pdo->prepare("SELECT imagem FROM projeto_imagens WHERE projeto_id = ?");
        $stmtImg->execute([$delete_id]);
        $imagens = $stmtImg->fetchAll();
        foreach ($imagens as $img) {
            if (file_exists($img['imagem'])) unlink($img['imagem']);
        }
        $stmtDelImg = $pdo->prepare("DELETE FROM projeto_imagens WHERE projeto_id = ?");
        $stmtDelImg->execute([$delete_id]);
        $stmtDelProj = $pdo->prepare("DELETE FROM projetos WHERE id = ?");
        $stmtDelProj->execute([$delete_id]);
    }
    header("Location: profile.php");
    exit;
}

// --- Dados do usuário ---
$stmt = $pdo->prepare("SELECT id, nome, email, curso, semestre, foto_perfil FROM usuarios WHERE id = ?");
$stmt->execute([$profileId]);
$user = $stmt->fetch();
if (!$user) exit("Usuário não encontrado.");

// --- Guardar dados se for próprio perfil ---
if ($isOwnProfile) {
    $_SESSION["nome"] = $user["nome"];
    $_SESSION["email"] = $user["email"];
    $_SESSION["curso"] = $user["curso"];
    $_SESSION["semestre"] = $user["semestre"];
    $_SESSION["foto"] = $user["foto_perfil"];
}

// --- Projetos do usuário ---
$stmtProj = $pdo->prepare("
    SELECT p.*, u.nome AS autor, u.foto_perfil, 
           GROUP_CONCAT(CONCAT(pi.imagem,'::',pi.focus) SEPARATOR '|') AS imagens,
           (SELECT COUNT(*) FROM curtidas c WHERE c.projeto_id=p.id) AS total_likes,
           (SELECT COUNT(*) FROM curtidas c WHERE c.projeto_id=p.id AND c.usuario_id=?) AS user_like,
           (SELECT COUNT(*) FROM colaboracoes co WHERE co.projeto_id=p.id AND co.usuario_id=?) AS user_colab
    FROM projetos p
    JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN projeto_imagens pi ON p.id = pi.projeto_id
    WHERE p.usuario_id = ?
    GROUP BY p.id
    ORDER BY p.data_publicacao DESC
");
$stmtProj->execute([$_SESSION['user_id'], $_SESSION['user_id'], $profileId]);
$projetos = $stmtProj->fetchAll();

// --- Projetos que o usuário colabora ---
$stmtColab = $pdo->prepare("
    SELECT p.*, u.nome AS autor, u.foto_perfil,
           GROUP_CONCAT(CONCAT(pi.imagem,'::',pi.focus) SEPARATOR '|') AS imagens,
           (SELECT COUNT(*) FROM curtidas c WHERE c.projeto_id=p.id) AS total_likes,
           (SELECT COUNT(*) FROM curtidas c WHERE c.projeto_id=p.id AND c.usuario_id=?) AS user_like,
           1 AS user_colab
    FROM projetos p
    JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN projeto_imagens pi ON p.id = pi.projeto_id
    JOIN colaboracoes co ON co.projeto_id = p.id
    WHERE co.usuario_id = ?
    GROUP BY p.id
    ORDER BY p.data_publicacao DESC
");
$stmtColab->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$projetosColab = $stmtColab->fetchAll();
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isOwnProfile ? "Seu Perfil" : "Perfil de " . htmlspecialchars($user["nome"]) ?></title>
    <link rel="stylesheet" href="css/css/bootstrap.min.css">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include "header.php"; ?>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body text-center">
            <img src="<?= !empty($user["foto_perfil"]) ? $user["foto_perfil"] : 'uploads/default.png' ?>" alt="Foto" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover;">
            <h2><?= $isOwnProfile ? "Bem-vindo, " . htmlspecialchars($user["nome"]) : htmlspecialchars($user["nome"]) ?></h2>
            <p><strong>Curso:</strong> <?= htmlspecialchars($user["curso"]) ?></p>
            <p><strong>Semestre:</strong> <?= (int)$user["semestre"] ?></p>
            <?php if ($isOwnProfile): ?>
            <div class="mt-3">
                <a href="edit_profile.php" class="btn btn-primary me-2">Editar Perfil</a>
                <a href="post_profile.php" class="btn btn-success me-2">Postar Projeto</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container mt-4">
    <h3><?= $isOwnProfile ? "Seus Projetos" : "Projetos de " . htmlspecialchars($user["nome"]) ?></h3>
    <div class="row">
        <?php foreach ($projetos as $proj):
            $projeto = $proj;
            include 'components/project_card.php';
        endforeach; ?>
    </div>
</div>

<?php if (!empty($projetosColab)): ?>
<div class="container mt-4">
    <h3><?= $isOwnProfile ? "Colaborações" : "Colaborações de " . htmlspecialchars($user["nome"]) ?></h3>
    <div class="row">
        <?php foreach ($projetosColab as $proj):
            $projeto = $proj;
            include 'components/project_card.php';
        endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php include 'components/project_modal.php'; ?>

<script src="js/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/project.js"></script>
</body>
</html>
