<?php
session_start();
if (!isset($_SESSION["user_id"])) header("Location: index.html");
require_once "db.php";

$usuario_id = $_SESSION["user_id"];

// Buscar todos os projetos com informações do usuário e imagens
$stmt = $pdo->query("
    SELECT p.*, u.nome AS autor, GROUP_CONCAT(CONCAT(pi.imagem,'::',pi.focus) SEPARATOR '|') AS imagens
    FROM projetos p
    JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN projeto_imagens pi ON p.id = pi.projeto_id
    GROUP BY p.id
    ORDER BY p.data_publicacao DESC
");
$projetos = $stmt->fetchAll();
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feed de Projetos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="styles.css" rel="stylesheet">
<style>
.project-photo{cursor:pointer;max-height:120px;}
.img-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:flex;justify-content:center;align-items:center;z-index:1050;}
.img-overlay-img{max-width:90%;max-height:90%;cursor:zoom-out;}
</style>
</head>
<body class="bg-light">
<?php include "header.php"; ?>

<div class="container mt-4">
<h2 class="mb-4">Feed de Projetos</h2>

<div class="row">
<?php foreach($projetos as $proj):
    $imagens = $proj['imagens']?explode('|',$proj['imagens']):[];
    
    // Curtidas
    $stmtLike = $pdo->prepare("SELECT COUNT(*) FROM curtidas WHERE projeto_id=?");
    $stmtLike->execute([$proj['id']]);
    $curtidasCount = $stmtLike->fetchColumn();

    $stmtUserLike = $pdo->prepare("SELECT COUNT(*) FROM curtidas WHERE projeto_id=? AND usuario_id=?");
    $stmtUserLike->execute([$proj['id'],$usuario_id]);
    $userCurtiu = $stmtUserLike->fetchColumn()>0;

    // Colaboração pendente
    $stmtCol = $pdo->prepare("SELECT COUNT(*) FROM colaboracoes WHERE projeto_id=? AND usuario_id=? AND status='pendente'");
    $stmtCol->execute([$proj['id'],$usuario_id]);
    $colPend = $stmtCol->fetchColumn()>0;
?>
<div class="col-md-4 mb-3">
    <div class="card project-card" data-bs-toggle="modal" data-bs-target="#projectModal"
         data-id="<?= $proj['id'] ?>"
         data-titulo="<?= htmlspecialchars($proj['titulo'],ENT_QUOTES) ?>"
         data-descricao="<?= htmlspecialchars($proj['descricao'],ENT_QUOTES) ?>"
         data-imagens="<?= htmlspecialchars($proj['imagens'],ENT_QUOTES) ?>"
         data-curtidas="<?= $curtidasCount ?>"
         data-userlike="<?= $userCurtiu ?>"
         data-colpend="<?= $colPend ?>">
        <?php if($imagens):
            list($imgSrc,$imgFocus) = explode('::',$imagens[0]);
        ?>
            <img src="<?= $imgSrc ?>" alt="Imagem do projeto" style="object-fit:cover;object-position:<?= htmlspecialchars($imgFocus) ?>;height:150px;width:100%;">
        <?php endif; ?>
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($proj['titulo']) ?></h5>
            <div>
                <button class="btn btn-sm <?= $userCurtiu?'btn-success':'btn-outline-success' ?> like-btn" data-id="<?= $proj['id'] ?>">
                    ❤️ <span class="like-count"><?= $curtidasCount ?></span>
                </button>
                <button class="btn btn-sm btn-outline-primary colaborar-btn" data-id="<?= $proj['id'] ?>" <?= $colPend?'disabled':'' ?>>
                    <?= $colPend?'Solicitação enviada':'Colaborar' ?>
                </button>
            </div>
            <h6 class="card-subtitle text-muted mt-2">por <?= htmlspecialchars($proj['autor']) ?> em <?= date("d/m/Y H:i", strtotime($proj['data_publicacao'])) ?></h6>
            <p class="card-text"><?= nl2br(htmlspecialchars(substr($proj['descricao'],0,100))) ?>...</p>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Modal de projeto -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <div class="dropdown">
          <button class="menu-btn" type="button" data-bs-toggle="dropdown">&#9776;</button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" id="editProject" href="#">Editar</a></li>
            <li><a class="dropdown-item" id="deleteProject" href="#">Excluir</a></li>
          </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Modal
const projectModal = document.getElementById('projectModal');
projectModal.addEventListener('show.bs.modal', event=>{
    const card = event.relatedTarget;
    const titulo = card.dataset.titulo;
    const descricao = card.dataset.descricao;
    const imagens = card.dataset.imagens ? card.dataset.imagens.split('|').map(i=>i.split('::')) : [];
    const id = card.dataset.id;

    document.getElementById('modalTitle').textContent = titulo;
    let html='';
    if(imagens.length>0){
        html+=`<div id="carouselProject" class="carousel slide mb-3" data-bs-ride="carousel"><div class="carousel-inner">`;
        imagens.forEach(([src,focus],index)=>{
            html+=`<div class="carousel-item ${index===0?'active':''}">
                <img src="${src}" class="d-block w-100 modal-image" style="max-height:500px;object-fit:cover;object-position:${focus||'center'}">
            </div>`;
        });
        html+=`</div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselProject" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselProject" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>`;
    }
    html+=`<p>${descricao.replace(/\n/g,'<br>')}</p>`;
    document.getElementById('modalBody').innerHTML = html;
});

// Curtidas
document.querySelectorAll('.like-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        const id = btn.dataset.id;
        fetch('like.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+id})
        .then(r=>r.json()).then(data=>{
            if(data.status==='ok'){
                btn.querySelector('.like-count').textContent = data.count;
                btn.classList.toggle('btn-success',data.user_like);
                btn.classList.toggle('btn-outline-success',!data.user_like);
            }
        });
    });
});

// Solicitação de colaboração
document.querySelectorAll('.colaborar-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        const id = btn.dataset.id;
        fetch('solicitar_colaboracao.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+id})
        .then(r=>r.json()).then(data=>{
            if(data.status==='ok'){
                btn.textContent='Solicitação enviada';
                btn.disabled=true;
            }
        });
    });
});
</script>
</body>
</html>
