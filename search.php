<?php
require_once "db.php";
session_start();

$query = trim($_GET['q'] ?? '');

if ($query === '') {
    header("Location: feed.php");
    exit;
}

// Busca em usuários e projetos
$stmt = $pdo->prepare("
    SELECT 'usuario' AS tipo, id, nome AS titulo, email AS descricao 
    FROM usuarios
    WHERE nome LIKE ? OR email LIKE ?
    UNION
    SELECT 'projeto' AS tipo, id, titulo, descricao
    FROM projetos
    WHERE titulo LIKE ? OR descricao LIKE ?
");
$stmt->execute(["%$query%", "%$query%", "%$query%", "%$query%"]);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Resultado da busca - UHub</title>
  <link rel="stylesheet" href="css/css/bootstrap.min.css">
</head>
<body>
<?php include 'components/header.php'; ?>

<div class="container mt-4">
  <h4>Resultados para: <em><?= htmlspecialchars($query) ?></em></h4>
  <hr>

  <?php if (empty($resultados)): ?>
      <p class="text-muted">Nenhum resultado encontrado.</p>
  <?php else: ?>
      <div class="list-group">
          <?php foreach ($resultados as $item): ?>
              <?php if ($item['tipo'] === 'usuario'): ?>
                  <a href="profile.php?id=<?= $item['id'] ?>" class="list-group-item list-group-item-action">
                      👤 <strong><?= htmlspecialchars($item['titulo']) ?></strong><br>
                      <small><?= htmlspecialchars($item['descricao']) ?></small>
                  </a>
              <?php else: ?>
                  <a href="projeto.php?id=<?= $item['id'] ?>" class="list-group-item list-group-item-action">
                      📁 <strong><?= htmlspecialchars($item['titulo']) ?></strong><br>
                      <small><?= htmlspecialchars($item['descricao']) ?></small>
                  </a>
              <?php endif; ?>
          <?php endforeach; ?>
      </div>
  <?php endif; ?>
</div>

<script src="js/js/bootstrap.bundle.min.js"></script>
</body>
</html>
