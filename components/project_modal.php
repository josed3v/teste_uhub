<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- Subtítulo do projeto -->
        <h6 id="modalSubtitle" class="text-muted"></h6>

        <!-- Corpo do projeto (imagens, texto etc.) -->
        <div id="modalBody"></div>

        <h6 class="card-subtitle text-muted mb-3 mt-1">
          por <?= htmlspecialchars($projeto['autor']) ?>
          em <?= date("d/m/Y H:i", strtotime($projeto['data_publicacao'])) ?>
        </h6>
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
  /* Fundo escuro do lightbox */
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
    user-select: none;
    z-index: 1061;
  }

  .lightbox-btn:hover {
    background: rgba(255,255,255,0.2);
  }

  .lightbox-btn.prev { left: 20px; }
  .lightbox-btn.next { right: 20px; }

    @media (max-width: 480px) {
    .lightbox-img {
      max-width: 100vw;
      max-height: 65vh;
    }
    .lightbox-btn {
      font-size: 1.8rem;
      background: rgba(0,0,0,0.7);
    }
</style>

<script>
  let images = [];
  let currentIndex = 0;

  // Detecta cliques nas imagens dentro do modal
  document.addEventListener('click', function(e) {
    if (e.target.tagName === 'IMG' && e.target.closest('#projectModal')) {
      images = Array.from(document.querySelectorAll('#projectModal img'));
      currentIndex = images.indexOf(e.target);
      openLightbox(images[currentIndex].src);
    }
  });

  // Abre o lightbox
  function openLightbox(src) {
    const overlay = document.getElementById('imageLightbox');
    const img = document.getElementById('lightboxImage');
    img.src = src;
    overlay.classList.remove('d-none');
  }

  // Fecha o lightbox ao clicar fora da imagem
  document.getElementById('imageLightbox').addEventListener('click', function(e) {
    if (e.target.id === 'imageLightbox') {
      this.classList.add('d-none');
    }
  });

  // Botão anterior
  document.getElementById('prevBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    document.getElementById('lightboxImage').src = images[currentIndex].src;
  });

  // Botão próximo
  document.getElementById('nextBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    currentIndex = (currentIndex + 1) % images.length;
    document.getElementById('lightboxImage').src = images[currentIndex].src;
  });

  // Fecha com ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      document.getElementById('imageLightbox').classList.add('d-none');
    } else if (e.key === 'ArrowRight') {
      document.getElementById('nextBtn').click();
    } else if (e.key === 'ArrowLeft') {
      document.getElementById('prevBtn').click();
    }
  });
</script>
