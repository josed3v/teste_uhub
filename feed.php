<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.html");
    exit;
}

require_once "db.php";

// Buscar todos os projetos com informações do usuário, imagens e curtidas
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
<title>Feed</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include "header.php"; ?>

<div class="container mt-4">
    <h2 class="mb-4">Feed de Projetos</h2>
    <div class="row">
        <?php foreach($projetos as $projeto): ?>
            <?php include "components/project_card.php"; ?>
        <?php endforeach; ?>
    </div>
</div>

<?php include "components/project_modal.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/project.js"></script>
</body>
</html>
