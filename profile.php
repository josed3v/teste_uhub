<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.html");
    exit;
}

require_once "db.php";

// Deletar projeto
if (isset($_GET['delete'])) {
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

// Dados do usuário
$stmt = $pdo->prepare("SELECT nome, email, curso, semestre, foto_perfil FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch();
if (!$user) exit(header("Location: logout.php"));

$_SESSION["nome"] = $user["nome"];
$_SESSION["email"] = $user["email"];
$_SESSION["curso"] = $user["curso"];
$_SESSION["semestre"] = $user["semestre"];
$_SESSION["foto"] = $user["foto_perfil"];

// Projetos do usuário
$stmt = $pdo->prepare("
    SELECT p.*, u.nome AS autor, u.foto_perfil, 
           GROUP_CONCAT(CONCAT(pi.imagem,'::',pi.focus) SEPARATOR '|') AS imagens,
           (SELECT COUNT(*) FROM curtidas c WHERE c.projeto_id=p.id) AS total_likes,
           (SELECT COUNT(*) FROM curtidas c WHERE c.projeto_id=p.id AND c.usuario_id=?) AS user_like
    FROM projetos p
    JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN projeto_imagens pi ON p.id = pi.projeto_id
    GROUP BY p.id
    ORDER BY p.data_publicacao DESC
");

$stmt->execute([$_SESSION['user_id']]);
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

<body class="bg-light">

    <?php include "header.php"; ?>

    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <img src="<?= !empty($_SESSION["foto"]) ? $_SESSION["foto"] : 'uploads/default.png' ?>"
                    alt="Foto" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover;">

                <h2>Bem-vindo, <?= htmlspecialchars($_SESSION["nome"]) ?>!</h2>
                <p><strong>Curso:</strong> <?= htmlspecialchars($_SESSION["curso"]) ?></p>
                <p><strong>Semestre:</strong> <?= (int)$_SESSION["semestre"] ?></p>
                <div class="mt-3">
                    <a href="edit_profile.php" class="btn btn-primary me-2">Editar Perfil</a>
                    <a href="post_profile.php" class="btn btn-success me-2">Postar Projeto</a>
                    <a href="logout.php" class="btn btn-secondary">Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <h3>Seus Projetos</h3>
        <div class="row">
            <?php foreach ($projetos as $proj):
                $projeto = $proj;
                include 'components/project_card.php';
            endforeach; ?>
        </div>
    </div>

    <?php include 'components/project_modal.php'; ?>

    <!-- Scripts externos -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/project.js"></script>

</body>

</html>