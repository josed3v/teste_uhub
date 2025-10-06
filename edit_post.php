<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.html");
    exit;
}

require_once "db.php";

if (!isset($_GET['id'])) exit("Projeto não especificado.");
$projeto_id = (int)$_GET['id'];

// Buscar projeto
$stmt = $pdo->prepare("SELECT * FROM projetos WHERE id = ? AND usuario_id = ?");
$stmt->execute([$projeto_id, $_SESSION['user_id']]);
$projeto = $stmt->fetch();
if (!$projeto) exit("Projeto não encontrado.");

// Buscar imagens
$stmtImg = $pdo->prepare("SELECT * FROM projeto_imagens WHERE projeto_id = ?");
$stmtImg->execute([$projeto_id]);
$imagens = $stmtImg->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);

    // Atualizar título e descrição
    $stmtUpd = $pdo->prepare("UPDATE projetos SET titulo = ?, descricao = ? WHERE id = ?");
    $stmtUpd->execute([$titulo, $descricao, $projeto_id]);

    // Upload novas imagens
    if (isset($_FILES['imagens'])) {
        $uploads_dir = 'uploads/';
        foreach ($_FILES['imagens']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['imagens']['error'][$key] == 0) {
                $ext = pathinfo($_FILES['imagens']['name'][$key], PATHINFO_EXTENSION);
                $nome_arquivo = uniqid() . '.' . $ext;
                move_uploaded_file($tmp_name, $uploads_dir . $nome_arquivo);
                $stmtImg = $pdo->prepare("INSERT INTO projeto_imagens (projeto_id, imagem) VALUES (?, ?)");
                $stmtImg->execute([$projeto_id, $uploads_dir . $nome_arquivo]);
            }
        }
    }

    header("Location: profile.php");
    exit;
}

// Deletar imagem individual
if (isset($_GET['delete_img'])) {
    $img_id = (int)$_GET['delete_img'];
    $stmtImgDel = $pdo->prepare("SELECT imagem FROM projeto_imagens WHERE id = ? AND projeto_id = ?");
    $stmtImgDel->execute([$img_id, $projeto_id]);
    $img = $stmtImgDel->fetch();
    if ($img) {
        if (file_exists($img['imagem'])) unlink($img['imagem']);
        $stmtDel = $pdo->prepare("DELETE FROM projeto_imagens WHERE id = ?");
        $stmtDel->execute([$img_id]);
    }
    header("Location: edit_post.php?id=" . $projeto_id);
    exit;
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Projeto</title>
<link rel="stylesheet" href="css/css/bootstrap.min.css">
    <link href="css/styles.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include "header.php"; ?>
    <div class="container mt-5">
        <h2>Editar Projeto</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control" required value="<?= htmlspecialchars($projeto['titulo']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" class="form-control" rows="5" required><?= htmlspecialchars($projeto['descricao']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Imagens existentes</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($imagens as $img): ?>
                        <div class="text-center">
                            <img src="<?= $img['imagem'] ?>" class="project-photo mb-1" style="max-height:150px; border-radius:5px;">
                            <a href="?id=<?= $projeto_id ?>&delete_img=<?= $img['id'] ?>" class="btn btn-sm btn-danger d-block">Excluir</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Adicionar novas imagens</label>
                <input type="file" name="imagens[]" class="form-control" multiple>
            </div>
            <button type="submit" class="btn btn-success">Salvar Alterações</button>
            <a href="profile.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
<script src="js/js/bootstrap.bundle.min.js"></script>
</body>

</html>