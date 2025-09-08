<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.html");
    exit;
}

require_once "db.php";

// Buscar todos os projetos com informações do usuário e imagens
$stmt = $pdo->query("
    SELECT p.id, p.titulo, p.descricao, p.data_publicacao, u.nome AS autor,
    (SELECT COUNT(*) FROM curtidas c WHERE c.projeto_id = p.id) AS total_curtidas,
    (SELECT COUNT(*) FROM colaboracoes col WHERE col.projeto_id = p.id AND col.status = 'pendente') AS total_pedidos
    FROM projetos p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.data_publicacao DESC
");
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feed</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="styles.css" rel="stylesheet">
<style>
.project-photo { cursor: pointer; max-height: 200px; object-fit: cover; margin-bottom: 5px; }
.btn-like.active { color: red; }
.img-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.8); display:flex; justify-content:center; align-items:center; z-index:1055; }
.img-overlay-img { max-width:90%; max-height:90%; cursor:pointer; }
</style>
</head>
<body class="bg-light">

<?php include "header.php"; ?>

<div class="container mt-4">
    <h2 class="mb-4">Feed de Projetos</h2>

    <div class="row">
    <?php foreach($projetos as $projeto): ?>
        <?php
        // Buscar imagens do projeto
        $stmtImg = $pdo->prepare("SELECT imagem, focus FROM projeto_imagens WHERE projeto_id = ?");
        $stmtImg->execute([$projeto['id']]);
        $imagens = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="col-md-4 mb-4">
            <div class="card project-card" data-bs-toggle="modal" data-bs-target="#projectModal"
                 data-id="<?= $projeto['id'] ?>"
                 data-titulo="<?= htmlspecialchars($projeto['titulo'],ENT_QUOTES) ?>"
                 data-descricao="<?= htmlspecialchars($projeto['descricao'],ENT_QUOTES) ?>"
                 data-total="<?= $projeto['total_curtidas'] ?>"
                 data-pedidos="<?= $projeto['total_pedidos'] ?>"
                 data-imagens="<?= htmlspecialchars(implode('|', array_map(fn($i)=>$i['imagem'].'::'.$i['focus'],$imagens)),ENT_QUOTES) ?>">
                <?php if($imagens): ?>
                    <img src="<?= $imagens[0]['imagem'] ?>" class="card-img-top" style="object-fit:cover; object-position:<?= htmlspecialchars($imagens[0]['focus']) ?>;">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($projeto['titulo']) ?></h5>
                    <p class="mb-1"><span class="text-muted" id="like-count-<?= $projeto['id'] ?>"><?= $projeto['total_curtidas'] ?> curtidas</span> | <?= $projeto['total_pedidos'] ?> pedidos</p>
                    <h6 class="card-subtitle text-muted mb-2">por <?= htmlspecialchars($projeto['autor']) ?> em <?= date("d/m/Y H:i", strtotime($projeto['data_publicacao'])) ?></h6>
                    <p class="card-text"><?= nl2br(htmlspecialchars(substr($projeto['descricao'],0,100))) ?>...</p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- Modal para projetos -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <div class="dropdown">
          <button class="menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">&#9776;</button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" id="editProject" href="#">Editar</a></li>
            <li><a class="dropdown-item" id="deleteProject" href="#">Excluir</a></li>
            <li><a class="dropdown-item" id="shareProject" href="#">Compartilhar</a></li>
          </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody"></div>
      <div class="modal-footer">
        <button class="btn btn-outline-primary" id="btnLike">Curtir</button>
        <button class="btn btn-outline-success" id="btnColab">Pedir colaboração</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const projectModal = document.getElementById('projectModal');
let currentProjectId = null;
let likeActive = false;

projectModal.addEventListener('show.bs.modal', event => {
    const card = event.relatedTarget;
    currentProjectId = card.getAttribute('data-id');
    const titulo = card.getAttribute('data-titulo');
    const descricao = card.getAttribute('data-descricao');
    const imagensStr = card.getAttribute('data-imagens');
    const totalCurtidas = parseInt(card.getAttribute('data-total')) || 0;

    const imagens = imagensStr ? imagensStr.split('|').map(i=>i.split('::')) : [];
    document.getElementById('modalTitle').textContent = titulo;

    let html = '';
    if (imagens.length > 0) {
        html += `<div id="carouselProject" class="carousel slide mb-3" data-bs-ride="carousel">
                    <div class="carousel-inner">`;
        imagens.forEach(([src, focus], index) => {
            html += `<div class="carousel-item ${index===0?'active':''}">
                        <img src="${src}" class="d-block w-100 modal-image" style="max-height:500px; object-fit:cover; object-position:${focus?focus:'center'};" alt="Imagem do projeto">
                     </div>`;
        });
        html += `</div>
                 <button class="carousel-control-prev" type="button" data-bs-target="#carouselProject" data-bs-slide="prev">
                     <span class="carousel-control-prev-icon"></span>
                 </button>
                 <button class="carousel-control-next" type="button" data-bs-target="#carouselProject" data-bs-slide="next">
                     <span class="carousel-control-next-icon"></span>
                 </button>
                 </div>`;
    }
    html += `<p>${descricao.replace(/\n/g,"<br>")}</p>`;
    document.getElementById('modalBody').innerHTML = html;

    document.querySelectorAll('#modalBody img.modal-image').forEach(img => {
        img.addEventListener('click', () => openImageOverlay(img.src));
        img.style.cursor = 'zoom-in';
    });

    // Atualiza botão de curtida
    fetch('check_like.php?projeto_id='+currentProjectId)
        .then(res=>res.json())
        .then(data=>{
            likeActive = data.liked;
            document.getElementById('btnLike').classList.toggle('active', likeActive);
            document.getElementById('btnLike').textContent = likeActive ? 'Descurtir' : 'Curtir';
        });
});

// Botão de curtida
document.getElementById('btnLike').addEventListener('click', e => {
    e.stopPropagation();
    fetch('toggle_like.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({projeto_id: currentProjectId})
    }).then(res=>res.json()).then(data=>{
        likeActive = data.liked;
        document.getElementById('btnLike').classList.toggle('active', likeActive);
        document.getElementById('btnLike').textContent = likeActive ? 'Descurtir' : 'Curtir';
        document.getElementById('like-count-'+currentProjectId).textContent = data.total+' curtidas';
    });
});

// Botão de colaboração
document.getElementById('btnColab').addEventListener('click', e => {
    e.stopPropagation();
    fetch('toggle_colab.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({projeto_id: currentProjectId})
    }).then(res=>res.json()).then(data=>{
        alert(data.message);
    });
});

// Overlay para expandir imagem
function openImageOverlay(src){
    const existing = document.querySelector('.img-overlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.className = 'img-overlay';
    overlay.setAttribute('role','dialog');
    overlay.setAttribute('aria-modal','true');

    const img = document.createElement('img');
    img.src = src;
    img.alt = 'Imagem ampliada';
    img.className = 'img-overlay-img';
    img.title = 'Clique para fechar';

    overlay.appendChild(img);
    document.body.appendChild(overlay);

    overlay.addEventListener('click', () => overlay.remove());

    document.addEventListener('keydown', function escHandler(e){
        if (e.key === 'Escape') { overlay.remove(); document.removeEventListener('keydown', escHandler);}
    });
}
</script>

</body>
</html>
