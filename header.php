<?php
require_once "db.php";

$usuario_id = $_SESSION['user_id'] ?? 0;
$pendentes = 0;

if ($usuario_id) {
  $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM colaboracoes c
                           JOIN projetos p ON c.projeto_id = p.id
                           WHERE p.usuario_id=? AND c.status='pendente'");
  $stmt->execute([$usuario_id]);
  $pendentes = $stmt->fetch()['total'];
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="feed.php">MeuHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
          <a class="nav-link" href="feed.php">Feed</a>
        </li>
        <a class="nav-link" href="profile.php">Perfil</a>
        </li>
        <li class="nav-item position-relative">
          <a class="nav-link" href="colaboracoes.php">
            Colaborações
            <?php if ($pendentes > 0): ?>
              <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                <?= $pendentes ?>
              </span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Sair</a>
        </li>
      </ul>
    </div>
  </div>
</nav>