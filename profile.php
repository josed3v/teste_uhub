<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.html");
    exit;
}

require_once "db.php";

// Deletar projeto
if(isset($_GET['delete'])){
    $delete_id = (int)$_GET['delete'];
    $stmtCheck = $pdo->prepare("SELECT * FROM projetos WHERE id = ? AND usuario_id = ?");
    $stmtCheck->execute([$delete_id, $_SESSION['user_id']]);
    $projDel = $stmtCheck->fetch();
    if($projDel){
        $stmtImg = $pdo->prepare("SELECT imagem FROM projeto_imagens WHERE projeto_id = ?");
        $stmtImg->execute([$delete_id]);
        $imagens = $stmtImg->fetchAll();
        foreach($imagens as $img){
            if(file_exists($img['imagem'])) unlink($img['imagem']);
        }
        $stmtDelImg = $pdo->prepare("DELETE FROM projeto_imagens WHERE projeto_id = ?");
        $stmtDelImg->execute([$delete_id]);
        $stmtDelProj = $pdo->prepare("DELETE FROM projetos WHERE id = ?");
        $stmtDelProj->execute([$delete_id]);
    }
    header("Location: profile.php");
    exit;
}

// Dados do usuário
$stmt = $pdo->prepare("SELECT nome, email, curso, semestre, foto_perfil FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch();
if (!$user) exit(header("Location: logout.php"));
$_SESSION["nome"] = $user["nome"];
$_SESSION["email"] = $user["email"];
$_SESSION["curso"] = $user["curso"];
$_SESSION["semestre"] = $user["semestre"];
$_SESSION["foto"] = $user["foto_perfil"];

// Projetos e imagens
$stmtProj = $pdo->prepare("
    SELECT p.*, GROUP_CONCAT(CONCAT(pi.imagem, '::', pi.focus) SEPARATOR '|') AS imagens
    FROM projetos p
    LEFT JOIN projeto_imagens pi ON p.id = pi.projeto_id
    WHERE p.usuario_id = ?
    GROUP BY p.id ORDER BY p.data_publicacao DESC
");
$stmtProj->execute([$_SESSION["user_id"]]);
$projetos = $stmtProj->fetchAll();
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seu Perfil</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="styles.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body text-center">
            <?php if(!empty($_SESSION["foto"])): ?>
                <img src="<?= $_SESSION["foto"] ?>" alt="Foto" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover;">
            <?php endif; ?>
            <h2>Bem-vindo, <?= $_SESSION["nome"] ?>!</h2>
            <p><strong>Curso:</strong> <?= $_SESSION["curso"] ?></p>
            <p><strong>Semestre:</strong> <?= (int)$_SESSION["semestre"] ?></p>
            <div class="mt-3">
                <a href="edit_profile.php" class="btn btn-primary me-2">Editar Perfil</a>
                <a href="post_profile.php" class="btn btn-success me-2">Postar Projeto</a>
                <a href="logout.php" class="btn btn-secondary">Sair</a>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
<h3>Seus Projetos</h3>
<div class="row">
<?php foreach($projetos as $proj): 
    $imagens = $proj['imagens'] ? explode('|', $proj['imagens']) : [];
?>
<div class="col-md-4 mb-3">
    <div class="card project-card" data-bs-toggle="modal" data-bs-target="#projectModal" 
         data-id="<?= $proj['id'] ?>"
         data-titulo="<?= htmlspecialchars($proj['titulo'],ENT_QUOTES) ?>"
         data-descricao="<?= htmlspecialchars($proj['descricao'],ENT_QUOTES) ?>"
         data-imagens="<?= htmlspecialchars($proj['imagens'],ENT_QUOTES) ?>">
        <?php if($imagens): 
            list($imgSrc, $imgFocus) = explode('::', $imagens[0]);
        ?>
            <img src="<?= $imgSrc ?>" alt="Imagem do projeto" style="object-fit:cover; object-position:<?= $imgFocus ?>;">
        <?php endif; ?>
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($proj['titulo']) ?></h5>
            <p class="card-text"><?= nl2br(htmlspecialchars(substr($proj['descricao'],0,100))) ?>...</p>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Modal Bootstrap -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <!-- Menu de ações -->
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
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const projectModal = document.getElementById('projectModal');
let currentProjectId = null;

projectModal.addEventListener('show.bs.modal', event => {
    const card = event.relatedTarget;
    currentProjectId = card.getAttribute('data-id');
    const titulo = card.getAttribute('data-titulo');
    const descricao = card.getAttribute('data-descricao');
    const imagensStr = card.getAttribute('data-imagens');
    const imagens = imagensStr ? imagensStr.split('|').map(i=>i.split('::')) : [];

    document.getElementById('modalTitle').textContent = titulo;

    let html = '';
    if(imagens.length > 0){
        html += `<div id="carouselProject" class="carousel slide mb-3" data-bs-ride="carousel">
                    <div class="carousel-inner">`;
        imagens.forEach(([src, focus], index)=>{
            html += `<div class="carousel-item ${index===0?'active':''}">
                        <img src="${src}" class="d-block w-100" style="max-height:500px; object-fit:cover; object-position:${focus};">
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

    document.getElementById('editProject').href = 'edit_post.php?id=' + currentProjectId;
    document.getElementById('deleteProject').onclick = () => {
        if(confirm('Deseja realmente excluir este projeto?')){
            window.location.href = 'profile.php?delete=' + currentProjectId;
        }
    };
    document.getElementById('shareProject').onclick = () => {
        navigator.clipboard.writeText(window.location.href + '?project=' + currentProjectId);
        alert('Link copiado para a área de transferência!');
    };
});
</script>

</body>
</html>
