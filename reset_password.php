<?php
require_once "db.php";

if (!isset($_GET["token"])) die("Token inválido");

$token = $_GET["token"];

$sql = "SELECT * FROM password_resets WHERE token = :token AND expiracao >= NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute([":token" => $token]);
$reset = $stmt->fetch();

if (!$reset) die("Token inválido ou expirado");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nova_senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    $sql = "UPDATE usuarios SET senha = :senha WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":senha" => $nova_senha,
        ":email" => $reset["email"]
    ]);

    $sql = "DELETE FROM password_resets WHERE token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":token" => $token]);

    echo "<div class='alert alert-success'>Senha redefinida com sucesso! <a href='index.html'>Login</a></div>";
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Redefinir Senha</title>
<link href="https
