<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBodyContainer">
        <!-- Conteúdo será gerado dinamicamente via JS -->
      </div>
    </div>
  </div>
</div>

<!-- Lightbox (zoom) -->
<div id="imageLightbox" class="lightbox-overlay d-none">
  <button id="prevBtn" class="lightbox-btn prev">&#10094;</button>
  <img id="lightboxImage" class="lightbox-img" src="" alt="Imagem expandida">
  <button id="nextBtn" class="lightbox-btn next">&#10095;</button>
</div>

<style>
  .lightbox-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1060;
  }
  .lightbox-img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.07);
    transition: transform 0.2s ease-in-out;
  }
  .lightbox-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: 2rem;
    color: white;
    background: rgba(0,0,0,0.5);
    border: none;
    padding: 0.5rem 0.8rem;
    cursor: pointer;
    border-radius: 50%;
    z-index: 1061;
  }
  .lightbox-btn:hover { background: rgba(255,255,255,0.2); }
  .lightbox-btn.prev { left: 20px; }
  .lightbox-btn.next { right: 20px; }
  @media (max-width: 480px) {
    .lightbox-img { max-width: 100vw; max-height: 65vh; }
    .lightbox-btn { font-size: 1.8rem; background: rgba(0,0,0,0.7); }
  }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const projectModal = document.getElementById("projectModal");
    let currentProjectId = null;

    function atualizarLikeUI(id, data) {
        $('#like-count-' + id).text(data.count);
        $('.like-btn[data-id="' + id + '"]').toggleClass('btn-primary', data.user_like).toggleClass('btn-outline-primary', !data.user_like);
        if (currentProjectId == id) {
            $('#modalLikeCount').text(data.count);
            $('#modalLikeBtn').toggleClass('btn-primary', data.user_like).toggleClass('btn-outline-primary', !data.user_like);
        }
    }

    if (projectModal) {
        projectModal.addEventListener("show.bs.modal", event => {
            const card = event.relatedTarget;
            currentProjectId = card.getAttribute("data-id");
            const titulo = card.getAttribute("data-titulo");
            const descricao = card.getAttribute("data-descricao");
            const imagensStr = card.getAttribute("data-imagens");
            const imagens = imagensStr ? imagensStr.split("|").map(i => i.split("::")) : [];
            const totalLikes = card.getAttribute("data-likes");
            const userLike = card.getAttribute("data-userlike") === "1";
            const userColab = card.getAttribute("data-usercolab") === "1";
            const autor = card.getAttribute("data-autor");
            const dataPub = new Date(card.getAttribute("data-data"));
            const dataFormatada = dataPub.toLocaleDateString("pt-BR") + " " + dataPub.toLocaleTimeString("pt-BR",{hour:'2-digit',minute:'2-digit'});

            document.getElementById("modalTitle").textContent = titulo;

            let html = "";

            // Carousel
            if (imagens.length > 0) {
                html += `<div id="carouselProject" class="carousel slide mb-3" data-bs-ride="carousel"><div class="carousel-inner">`;
                imagens.forEach(([src, focus], index) => {
                    focus = focus || "center";
                    html += `<div class="carousel-item ${index === 0 ? "active" : ""}">
                                <img src="${src}" class="d-block w-100 modal-image" style="max-height:500px; object-fit:cover; object-position:${focus};">
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

            // Autor e Data
            html += `<h6 class="text-muted mb-2">Por <strong>${autor}</strong> em ${dataFormatada}</h6>`;

            // Botões
            html += `<div class="d-flex gap-2 mb-3">
                        <button id="modalLikeBtn" class="btn ${userLike ? "btn-primary" : "btn-outline-primary"}">
                            ❤️ <span id="modalLikeCount">${totalLikes}</span>
                        </button>
                        <button id="modalColabBtn" class="btn ${userColab ? "btn-success" : "btn-outline-success"}">
                            🤝
                        </button>
                     </div>`;

            html += `<p>${descricao.replace(/\n/g,"<br>")}</p>`;
            document.getElementById("modalBodyContainer").innerHTML = html;

            // Like modal
            $('#modalLikeBtn').click(() => {
                fetch("like.php", {
                    method: "POST",
                    headers: {"Content-Type":"application/x-www-form-urlencoded"},
                    body: "id=" + currentProjectId
                }).then(res=>res.json()).then(data=>{if(data.status==="ok") atualizarLikeUI(currentProjectId,data)});
            });

            // Colab modal
            $('#modalColabBtn').click(() => {
                fetch("solicitar_colaboracao.php", {
                    method: "POST",
                    headers: {"Content-Type":"application/x-www-form-urlencoded"},
                    body: "id=" + currentProjectId
                }).then(res=>res.json()).then(data=>{
                    if(data.status==="ok"){
                        $('#modalColabBtn').text("🤝").prop("disabled",true).removeClass('btn-outline-success').addClass('btn-success');
                    } else { alert(data.msg); }
                });
            });
        });
    }

    // Like nos cards
    $(".like-btn").click(function () {
        const id = $(this).data("id");
        fetch("like.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + id
        }).then(res=>res.json()).then(data=>{if(data.status==="ok") atualizarLikeUI(id,data)});
    });

    // Colaboração nos cards
    $(".colab-btn").click(function () {
        const btn = $(this);
        const id = btn.data("id");
        fetch("solicitar_colaboracao.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + id
        }).then(res=>res.json()).then(data=>{
            if(data.status==="ok"){
                btn.text("🤝").prop("disabled",true).removeClass('btn-outline-success').addClass('btn-success');
            } else { alert(data.msg); }
        });
    });

    // Lightbox
    let images = [];
    let currentIndex = 0;
    document.addEventListener('click', function(e) {
        if(e.target.tagName==='IMG' && e.target.closest('#projectModal')){
            images = Array.from(document.querySelectorAll('#projectModal img'));
            currentIndex = images.indexOf(e.target);
            openLightbox(images[currentIndex].src);
        }
    });
    function openLightbox(src){document.getElementById('lightboxImage').src=src;document.getElementById('imageLightbox').classList.remove('d-none');}
    document.getElementById('imageLightbox').addEventListener('click', e=>{if(e.target.id==='imageLightbox') e.target.classList.add('d-none');});
    document.getElementById('prevBtn').addEventListener('click', e=>{e.stopPropagation(); currentIndex=(currentIndex-1+images.length)%images.length; document.getElementById('lightboxImage').src=images[currentIndex].src;});
    document.getElementById('nextBtn').addEventListener('click', e=>{e.stopPropagation(); currentIndex=(currentIndex+1)%images.length; document.getElementById('lightboxImage').src=images[currentIndex].src;});
    document.addEventListener('keydown', e=>{
        if(e.key==='Escape') document.getElementById('imageLightbox').classList.add('d-none');
        else if(e.key==='ArrowRight') document.getElementById('nextBtn').click();
        else if(e.key==='ArrowLeft') document.getElementById('prevBtn').click();
    });
});
</script>
