<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// --- Atualizar dados do perfil ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST["nome"] ?? '';
    $curso = $_POST["curso"] ?? '';
    $semestre = $_POST["semestre"] ?? '';
    $bio = $_POST["bio"] ?? '';

    // Se o usuário enviar uma nova foto
    if (!empty($_FILES["foto"]["name"])) {
        $target_dir = "uploads/";
        $file_name = time() . "_" . basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . $file_name;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "gif"];

        if (in_array($imageFileType, $allowed)) {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                $sql = "UPDATE usuarios SET nome=?, curso=?, semestre=?, bio=?, foto_perfil=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $curso, $semestre, $bio, $target_file, $user_id]);
            }
        } else {
            echo "Formato de imagem inválido. Use JPG, PNG ou GIF.";
        }
    } else {
        // Atualiza sem trocar a foto
        $sql = "UPDATE usuarios SET nome=?, curso=?, semestre=?, bio=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $curso, $semestre, $bio, $user_id]);
    }
}

// --- Buscar dados do usuário ---
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Evita erro de htmlspecialchars com valores nulos
$user = array_map(fn($v) => $v ?? '', $user);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="css/css/bootstrap.min.css">
</head>
<body>
<?php include "components/header.php"; ?>
<div class="container py-5">

    <h2>Perfil de <?php echo htmlspecialchars($user["nome"]); ?></h2>

    <?php if (!empty($user["foto_perfil"])): ?>
        <img src="<?php echo htmlspecialchars($user["foto_perfil"]); ?>" alt="Foto de perfil" class="rounded-circle mb-3" width="150" height="150">
    <?php else: ?>
        <img src="https://via.placeholder.com/150" alt="Sem foto" class="rounded-circle mb-3">
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow">
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" value="<?php echo htmlspecialchars($user["nome"]); ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Curso</label>
            <input type="text" name="curso" value="<?php echo htmlspecialchars($user["curso"]); ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Semestre</label>
            <input type="text" name="semestre" value="<?php echo htmlspecialchars($user["semestre"]); ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Biografia</label>
            <textarea name="bio" class="form-control" rows="4" placeholder="Fale um pouco sobre você..."><?php echo htmlspecialchars($user["bio"]); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Foto de Perfil</label>
            <input type="file" name="foto" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Atualizar Perfil</button>
    </form>

    <a href="profile.php" class="btn btn-danger mt-3">Voltar</a>
</div>
</body>
</html>
