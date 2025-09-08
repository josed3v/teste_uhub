<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="styles.css" rel="stylesheet">
</head>
<body>

<!-- Navbar fixa no topo -->
<nav class="navbar navbar-light bg-light shadow-sm px-3">
  <a class="navbar-brand fw-bold" href="feed.php">UHUB</a>
  
  <div class="dropdown ms-auto">
    <button class="menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      &#9776;
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" href="feed.php">Feed</a></li>
      <li><a class="dropdown-item" href="profile.php">Perfil</a></li>
      <li><a class="dropdown-item text-danger" href="logout.php">Sair</a></li>
    </ul>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
