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