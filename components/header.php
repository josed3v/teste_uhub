<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "db.php";

$user_id = $_SESSION["user_id"] ?? null;
if (!$user_id) {
    header("Location: feed.php");
    exit;
}

// Contar solicitações pendentes
$stmtPendentes = $pdo->prepare("
    SELECT COUNT(*) 
    FROM colaboracoes c
    JOIN projetos p ON c.projeto_id = p.id
    WHERE p.usuario_id = ? AND c.status='pendente'
");
$stmtPendentes->execute([$user_id]);
$pendentesCount = $stmtPendentes->fetchColumn();
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    
    <!-- Marca -->
    <a class="navbar-brand fw-bold text-primary" href="feed.php">UHub</a>

    <!-- Botão de menu mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Conteúdo do menu -->
    <div class="collapse navbar-collapse" id="navbarContent">

      <!-- 🔍 Barra de Pesquisa -->
      <form class="d-flex mx-auto my-2 my-lg-0 search-form" method="GET" action="search.php" role="search">
        <div class="input-group">
          <input 
            class="form-control" 
            type="search" 
            name="q" 
            placeholder="Buscar projetos ou usuários..." 
            aria-label="Buscar"
            required>
          <button class="btn btn-outline-primary" type="submit">
            <i class="bi bi-search"></i> Buscar
          </button>
        </div>
      </form>

      <!-- Menu à direita -->
      <ul class="navbar-nav ms-auto align-items-center">

        <li class="nav-item me-3">
          <a class="nav-link fw-semibold" href="feed.php">Feed</a>
        </li>

        <li class="nav-item me-3">
          <a class="nav-link fw-semibold" href="profile.php">
            <?= htmlspecialchars($_SESSION["nome"]) ?>
          </a>
        </li>

        <!-- Notificações -->
        <li class="nav-item dropdown me-3">
          <button class="btn btn-outline-primary position-relative" id="notifBtn" data-bs-toggle="dropdown">
            🔔
            <?php if ($pendentesCount > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge">
                <?= $pendentesCount ?>
              </span>
            <?php endif; ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" id="notifDropdown">
            <li class="dropdown-item text-center">
                <a href="colaboracoes.php" class="text-decoration-none">Ver todas solicitações</a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="btn btn-outline-secondary" href="logout.php">Sair</a>
        </li>

      </ul>
    </div>
  </div>
</nav>

<!-- Script de notificações -->
<script>
function carregarSolicitacoes() {
    fetch('get_solicitacoes.php')
    .then(res => res.json())
    .then(data => {
        const dropdown = document.getElementById('notifDropdown');
        const badge = document.getElementById('notifBadge');

        dropdown.innerHTML = '<li class="dropdown-item text-center"><a href="colaboracoes.php" class="text-decoration-none">Ver todas solicitações</a></li>';

        if (!data.length) {
            if (badge) badge.style.display = 'none';
            return;
        }

        if (badge) {
            badge.style.display = 'inline-block';
            badge.textContent = data.length;
        }

        data.forEach(solic => {
            const li = document.createElement('li');
            li.className = 'dropdown-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
                <span>${solic.solicitante} quer colaborar em "${solic.projeto}"</span>
                <div>
                    <button class="btn btn-sm btn-success me-1" onclick="responder(${solic.colab_id},'aceito')">✔</button>
                    <button class="btn btn-sm btn-danger" onclick="responder(${solic.colab_id},'rejeitado')">✖</button>
                </div>
            `;
            dropdown.appendChild(li);
        });
    });
}

function responder(colab_id, status) {
    fetch('responder_colab.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({colab_id, status})
    })
    .then(res => res.json())
    .then(res => {
        alert(res.message);
        carregarSolicitacoes();
    });
}

carregarSolicitacoes();
setInterval(carregarSolicitacoes, 10000);
</script>

<!-- Ícones do Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* 🌐 Ajustes visuais extras */
.navbar-brand {
  font-size: 1.5rem;
  letter-spacing: 0.5px;
}

.search-form {
  width: 100%;
  max-width: 450px;
}

@media (max-width: 991px) {
  .search-form {
    width: 100%;
    margin-top: 10px;
  }
  .navbar-nav {
    text-align: center;
  }
  .navbar-nav .btn {
    width: 100%;
    margin-top: 5px;
  }
}
</style>
