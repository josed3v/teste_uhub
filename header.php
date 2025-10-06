<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "db.php";

$user_id = $_SESSION["user_id"] ?? null;
if (!$user_id) {
    header("Location: index.html");
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

<nav class="navbar navbar-expand-lg navbar-light bg-gradient-light shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="feed.php">UHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item me-3">
          <a class="nav-link" href="feed.php">Feed</a>
        </li>
        <li class="nav-item ms-3 me-3">
          <a class="nav-link" href="profile.php"><?= htmlspecialchars($_SESSION["nome"]) ?></a>
        </li>
        <li class="nav-item dropdown">
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
        <li class="nav-item ms-4">
          <a class="btn btn-secondary" href="logout.php">Sair</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script>
function carregarSolicitacoes() {
    fetch('get_solicitacoes.php')
    .then(res => res.json())
    .then(data => {
        const dropdown = document.getElementById('notifDropdown');
        const badge = document.getElementById('notifBadge');

        // Limpa itens (mantendo o link "Ver todas solicitações")
        dropdown.innerHTML = '<li class="dropdown-item text-center"><a href="colaboracoes.php" class="text-decoration-none">Ver todas solicitações</a></li>';

        if (data.length === 0) {
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
                <span>${solic.solicitante} quer colaborar no projeto "${solic.projeto}"</span>
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

// Atualiza a cada 10s
carregarSolicitacoes();
setInterval(carregarSolicitacoes, 10000);
</script>
