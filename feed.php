<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.html");
    exit;
}

require_once "db.php";

// Buscar todos os projetos com informações do usuário, imagens e curtidas
$stmt = $pdo->prepare("
    SELECT p.*, u.nome AS autor, 
           GROUP_CONCAT(CONCAT(pi.imagem,'::',pi.focus) SEPARATOR '|') AS imagens,
           (SELECT COUNT(*) FROM curtidas c WHERE c.projeto_id=p.id) AS total_likes,
           (SELECT COUNT(*) FROM curtidas c WHERE c.projeto_id=p.id AND c.usuario_id=?) AS user_like
    FROM projetos p
    JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN projeto_imagens pi ON p.id = pi.projeto_id
    GROUP BY p.id
    ORDER BY p.data_publicacao DESC
");
$stmt->execute([$_SESSION['user_id']]);
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
</head>
<body class="bg-light">

<?php include "header.php"; ?>

<div class="container mt-4">
    <h2 class="mb-4">Feed de Projetos</h2>
    <div class="row">
    <?php foreach($projetos as $projeto): 
        $imagens = $projeto['imagens'] ? explode('|', $projeto['imagens']) : [];
    ?>
        <div class="col-md-4 mb-3">
            <div class="card project-card" data-bs-toggle="modal" data-bs-target="#projectModal" 
                 data-id="<?= $projeto['id'] ?>"
                 data-titulo="<?= htmlspecialchars($projeto['titulo'],ENT_QUOTES) ?>"
                 data-descricao="<?= htmlspecialchars($projeto['descricao'],ENT_QUOTES) ?>"
                 data-imagens="<?= htmlspecialchars($projeto['imagens'],ENT_QUOTES) ?>"
                 data-likes="<?= $projeto['total_likes'] ?>"
                 data-userlike="<?= $projeto['user_like'] ?>">
                <?php if($imagens): 
                    list($imgSrc, $imgFocus) = explode('::', $imagens[0]);
                ?>
                    <img src="<?= $imgSrc ?>" alt="Imagem do projeto" style="object-fit:cover; object-position:<?= htmlspecialchars($imgFocus) ?>; width:100%; height:200px;">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($projeto['titulo']) ?></h5>
                    <div>
                        <button class="like-btn btn <?= $projeto['user_like'] ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm" data-id="<?= $projeto['id'] ?>">❤️</button>
                        <span class="like-count" id="like-count-<?= $projeto['id'] ?>"><?= $projeto['total_likes'] ?></span>
                        <button class="colab-btn btn btn-outline-success btn-sm" data-id="<?= $projeto['id'] ?>">🤝</button>
                    </div>
                    <h6 class="card-subtitle text-muted mb-2">por <?= htmlspecialchars($projeto['autor']) ?> em <?= date("d/m/Y H:i", strtotime($projeto['data_publicacao'])) ?></h6>
                    <p class="card-text"><?= nl2br(htmlspecialchars(substr($projeto['descricao'],0,100))) ?>...</p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- Modal para projeto -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
const projectModal = document.getElementById('projectModal');
let currentProjectId = null;

// Função para atualizar curtidas e UI
function atualizarLikeUI(id, data) {
    $('#like-count-' + id).text(data.count);
    const cardBtn = $('.like-btn[data-id="' + id + '"]');
    cardBtn.toggleClass('btn-primary', data.user_like);
    cardBtn.toggleClass('btn-outline-primary', !data.user_like);

    if (currentProjectId == id) {
        $('#modalLikeCount').text(data.count);
        const modalBtn = $('#modalLikeBtn');
        modalBtn.toggleClass('btn-primary', data.user_like);
        modalBtn.toggleClass('btn-outline-primary', !data.user_like);
    }
}

// Modal
projectModal.addEventListener('show.bs.modal', event => {
    const card = event.relatedTarget;
    currentProjectId = card.getAttribute('data-id');
    const titulo = card.getAttribute('data-titulo');
    const descricao = card.getAttribute('data-descricao');
    const imagensStr = card.getAttribute('data-imagens');
    const imagens = imagensStr ? imagensStr.split('|').map(i=>i.split('::')) : [];
    const totalLikes = card.getAttribute('data-likes');
    const userLike = card.getAttribute('data-userlike') === "1";

    document.getElementById('modalTitle').textContent = titulo;

    let html = '';
    if(imagens.length>0){
        html += `<div id="carouselProject" class="carousel slide mb-3" data-bs-ride="carousel">
                    <div class="carousel-inner">`;
        imagens.forEach(([src, focus], index)=>{
            const safeFocus = focus ? focus : 'center';
            html += `<div class="carousel-item ${index===0?'active':''}">
                        <img src="${src}" class="d-block w-100 modal-image" style="max-height:500px; object-fit:cover; object-position:${safeFocus};" alt="Imagem do projeto">
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

    html += `
        <div class="d-flex gap-2 mb-3">
            <button id="modalLikeBtn" class="btn ${userLike ? 'btn-primary' : 'btn-outline-primary'}">
                ❤️ <span id="modalLikeCount">${totalLikes}</span>
            </button>
            <button id="modalColabBtn" class="btn btn-outline-success">
                🤝
            </button>
        </div>
    `;
    html += `<p>${descricao.replace(/\n/g,"<br>")}</p>`;
    document.getElementById('modalBody').innerHTML = html;

    // Curtidas do modal
    $('#modalLikeBtn').click(()=>{
        fetch('like.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'id='+currentProjectId
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.status==='ok') atualizarLikeUI(currentProjectId, data);
        });
    });

    // Colaboração do modal
    $('#modalColabBtn').click(()=>{
        fetch('solicitar_colaboracao.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'id='+currentProjectId
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.status==='ok'){
                $('#modalColabBtn').text('🤝').prop('disabled', true);
            } else {
                alert(data.msg);
            }
        });
    });
});

// Curtidas nos cards
$('.like-btn').click(function(){
    const id = $(this).data('id');
    fetch('like.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id='+id
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.status==='ok') atualizarLikeUI(id, data);
    });
});

// Colaboração nos cards
$('.colab-btn').click(function(){
    const btn = $(this);
    const id = btn.data('id');
    fetch('solicitar_colaboracao.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id='+id
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.status==='ok'){
            btn.text('🤝').prop('disabled', true);
        } else {
            alert(data.msg);
        }
    });
});
</script>
</body>
</html>
