<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
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

    // Upload múltiplo de imagens com foco padrão
    if(isset($_FILES['imagens'])){
        $uploads_dir = 'uploads/';
        foreach($_FILES['imagens']['tmp_name'] as $key => $tmp_name){
            if($_FILES['imagens']['error'][$key] == 0){
                $ext = pathinfo($_FILES['imagens']['name'][$key], PATHINFO_EXTENSION);
                $nome_arquivo = uniqid().'.'.$ext;
                move_uploaded_file($tmp_name, $uploads_dir.$nome_arquivo);

                // Define foco padrão (sempre "center")
                $focus = 'center';

                $stmtImg = $pdo->prepare("INSERT INTO projeto_imagens (projeto_id, imagem, focus) VALUES (?, ?, ?)");
                $stmtImg->execute([$projeto_id, $uploads_dir.$nome_arquivo, $focus]);
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
<link rel="stylesheet" href="css/css/bootstrap.min.css">
</head>
<body class="bg-light">
<?php include "components/header.php"; ?>
<div class="container mt-5">
    <h2>Postar Projeto</h2>
    <form method="post" enctype="multipart/form-data">
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
            <input type="file" name="imagens[]" class="form-control" multiple required>
        </div>
        <button type="submit" class="btn btn-success">Postar</button>
        <a href="profile.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<script src="js/js/bootstrap.bundle.min.js"></script>
</body>
</html>
