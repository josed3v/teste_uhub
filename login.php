<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":email" => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user["senha"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["nome"] = $user["nome"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["curso"] = $user["curso"];
        $_SESSION["semestre"] = $user["semestre"];

        header("Location: feed.php");
        exit;
    } else {
        $error = "Email ou senha inválidos!";
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="css/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-5">
<div class="card shadow-sm">
<form method="post" class="card-body">
<h3 class="card-title mb-4 text-center">Login</h3>

<?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

<div class="mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Senha</label>
<input type="password" name="senha" class="form-control" required>
</div>
<button type="submit" class="btn btn-success w-100">Entrar</button>

<p class="mt-3 text-center">
<a href="forgot_password.php">Esqueci minha senha</a> | 
Não tem conta? <a href="register.php">Registrar</a>
</p>
</form>
</div>
</div>
</div>
</div>
<script src="js/js/bootstrap.bundle.min.js"></script>
</body>
</html>
