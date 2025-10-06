<?php
require_once "db.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Caso use PHPMailer, inclua o autoload
// require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":email" => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiracao = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $sql = "INSERT INTO password_resets (email, token, expiracao) VALUES (:email, :token, :expiracao)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":email" => $email,
            ":token" => $token,
            ":expiracao" => $expiracao
        ]);

        $link = "http://seusite.com/reset_password.php?token=$token";

        // Aqui você enviaria o email com PHPMailer
        // Exemplo simples usando mail() do PHP:
        // mail($email, "Redefinição de senha", "Clique no link: $link");

        $msg = "Um link de redefinição de senha foi enviado para seu email.";
    } else {
        $error = "Email não cadastrado.";
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Senha</title>
<link rel="stylesheet" href="css/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-5">
<div class="card shadow-sm">
<div class="card-body">
<h3 class="card-title mb-4 text-center">Recuperar Senha</h3>

<?php if(!empty($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
<?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

<form method="post">
<div class="mb-3">
<label class="form-label">Digite seu email</label>
<input type="email" name="email" class="form-control" required>
</div>
<button type="submit" class="btn btn-primary w-100">Enviar link</button>
</form>

<p class="mt-3 text-center">
<a href="login.php">Voltar ao login</a>
</p>
</div>
</div>
</div>
</div>
</div>
<script src="js/js/bootstrap.bundle.min.js"></script>
</body>
</html>
