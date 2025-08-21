<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.html");
    exit;
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title">Bem-vindo, <?php echo htmlspecialchars($_SESSION["nome"]); ?>!</h2><br>
<!--            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION["email"]); ?></p>-->
            <p><strong>Curso:</strong> <?php echo htmlspecialchars($_SESSION["curso"]); ?></p>
            <p><strong>Semestre:</strong> <?php echo (int)$_SESSION["semestre"]; ?></p>
            <a href="logout.php" class="btn btn-danger">Sair</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
