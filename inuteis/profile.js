const projectModal = document.getElementById('projectModal');
let currentProjectId = null;

// Função para atualizar curtidas no card e modal
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

if (projectModal) {
    // Modal
    projectModal.addEventListener('show.bs.modal', event => {
        const card = event.relatedTarget;
        currentProjectId = card.getAttribute('data-id');
        const titulo = card.getAttribute('data-titulo');
        const descricao = card.getAttribute('data-descricao');
        const imagensStr = card.getAttribute('data-imagens');
        const imagens = imagensStr ? imagensStr.split('|').map(i => i.split('::')) : [];
        const totalLikes = card.getAttribute('data-likes');
        const userLike = card.getAttribute('data-userlike') === "1";

        document.getElementById('modalTitle').textContent = titulo;

        let html = '';

        // Carousel de imagens
        if (imagens.length > 0) {
            html += `<div id="carouselProject" class="carousel slide mb-3" data-bs-ride="carousel">
                        <div class="carousel-inner">`;
            imagens.forEach(([src, focus], index) => {
                const safeFocus = focus ? focus : 'center';
                html += `<div class="carousel-item ${index === 0 ? 'active' : ''}">
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

        // Botões de curtida e colaboração
        html += `
            <div class="d-flex gap-2 mb-3">
                <button id="modalLikeBtn" class="btn ${userLike ? 'btn-primary' : 'btn-outline-primary'}">
                    ❤️ <span id="modalLikeCount">${totalLikes}</span>
                </button>
                <button id="modalColabBtn" class="btn btn-outline-success">🤝</button>
            </div>
        `;
        html += `<p>${descricao.replace(/\n/g, "<br>")}</p>`;
        document.getElementById('modalBody').innerHTML = html;

        // Curtida no modal
        $('#modalLikeBtn').click(() => {
            fetch('like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + currentProjectId
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') atualizarLikeUI(currentProjectId, data);
            });
        });

        // Colaboração no modal
        $('#modalColabBtn').click(() => {
            fetch('solicitar_colaboracao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + currentProjectId
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    $('#modalColabBtn').text('🤝').prop('disabled', true);
                } else {
                    alert(data.msg);
                }
            });
        });
    });
}

// Curtidas e colaboração nos cards
$('.like-btn').click(function () {
    const id = $(this).data('id');
    fetch('like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') atualizarLikeUI(id, data);
    });
});

$('.colab-btn').click(function () {
    const btn = $(this);
    const id = btn.data('id');
    fetch('solicitar_colaboracao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') btn.prop('disabled', true);
        else alert(data.msg);
    });
});
