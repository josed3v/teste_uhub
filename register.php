<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $curso = trim($_POST["curso"]);
    $semestre = (int) $_POST["semestre"];

    $sql = "INSERT INTO usuarios (nome, email, senha, curso, semestre) 
            VALUES (:nome, :email, :senha, :curso, :semestre)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":nome" => $nome,
        ":email" => $email,
        ":senha" => $senha,
        ":curso" => $curso,
        ":semestre" => $semestre
    ]);

    $msg = "Usuário registrado com sucesso!";
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro de Usuário</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-5">
<div class="card shadow-sm">
<div class="card-body">
<h3 class="card-title mb-4 text-center">Registrar</h3>

<?php if(!empty($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>

<form method="post">
<div class="mb-3">
<label class="form-label">Nome completo</label>
<input type="text" name="nome" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Senha</label>
<input type="password" name="senha" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Curso</label>
<input type="text" name="curso" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Semestre</label>
<input type="number" name="semestre" min="1" max="12" class="form-control" required>
</div>
<button type="submit" class="btn btn-primary w-100">Registrar</button>
</form>

<p class="mt-3 text-center">
Já tem conta? <a href="login.php">Entrar</a>
</p>
</div>
</div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
