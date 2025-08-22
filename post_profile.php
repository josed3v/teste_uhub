<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.html");
    exit;
}

require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);

    // Inserir projeto
    $stmt = $pdo->prepare("INSERT INTO projetos (usuario_id, titulo, descricao) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $titulo, $descricao]);
    $projeto_id = $pdo->lastInsertId();

    // Upload de imagens com foco
    if(isset($_FILES['imagens'])){
        $uploads_dir = 'uploads/';
        foreach($_FILES['imagens']['tmp_name'] as $key => $tmp_name){
            if($_FILES['imagens']['error'][$key] == 0){
                $ext = pathinfo($_FILES['imagens']['name'][$key], PATHINFO_EXTENSION);
                $nome_arquivo = uniqid().'.'.$ext;
                move_uploaded_file($tmp_name, $uploads_dir.$nome_arquivo);

                $focusX = isset($_POST['focusX'][$key]) ? floatval($_POST['focusX'][$key]) : 50;
                $focusY = isset($_POST['focusY'][$key]) ? floatval($_POST['focusY'][$key]) : 50;

                $stmtImg = $pdo->prepare("INSERT INTO projeto_imagens (projeto_id, imagem, focusX, focusY) VALUES (?, ?, ?, ?)");
                $stmtImg->execute([$projeto_id, $uploads_dir.$nome_arquivo, $focusX, $focusY]);
            }
        }
    }

    header("Location: profile.php");
    exit;
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Postar Projeto</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="styles.css" rel="stylesheet">
<link href="post_profile.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Postar Projeto</h2>
    <form method="post" enctype="multipart/form-data" id="projectForm">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" rows="5" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Imagens (várias)</label>
            <input type="file" id="imageInput" multiple accept="image/*" class="form-control">
        </div>
        <div id="imagePreviewContainer" class="d-flex flex-wrap gap-3"></div>

        <button type="submit" class="btn btn-success mt-3">Postar</button>
        <a href="profile.php" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="post_profile.js"></script>
</body>
</html>
