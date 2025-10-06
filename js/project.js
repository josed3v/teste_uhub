document.addEventListener("DOMContentLoaded", () => {
    const projectModal = document.getElementById("projectModal");
    let currentProjectId = null;

    // Atualiza UI de curtidas e colaboração
    function atualizarUI(id, data) {
        // Atualiza card
        const cardBtnLike = $('.like-btn[data-id="' + id + '"]');
        const cardBtnColab = $('.colab-btn[data-id="' + id + '"]');

        if (data.hasOwnProperty('count')) {
            $('#like-count-' + id).text(data.count);
            cardBtnLike.toggleClass('btn-primary', data.user_like);
            cardBtnLike.toggleClass('btn-outline-primary', !data.user_like);
        }

        if (data.hasOwnProperty('colab')) {
            cardBtnColab.toggleClass('btn-success', data.user_colab);
            cardBtnColab.toggleClass('btn-outline-success', !data.user_colab);
            cardBtnColab.prop('disabled', data.user_colab);
        }

        // Atualiza modal
        if (currentProjectId == id) {
            if ($('#modalLikeCount').length && data.hasOwnProperty('count')) {
                $('#modalLikeCount').text(data.count);
                const modalBtnLike = $('#modalLikeBtn');
                modalBtnLike.toggleClass('btn-primary', data.user_like);
                modalBtnLike.toggleClass('btn-outline-primary', !data.user_like);
            }
            if ($('#modalColabBtn').length && data.hasOwnProperty('colab')) {
                const modalBtnColab = $('#modalColabBtn');
                modalBtnColab.toggleClass('btn-success', data.user_colab);
                modalBtnColab.toggleClass('btn-outline-success', !data.user_colab);
                modalBtnColab.prop('disabled', data.user_colab);
            }
        }
    }

    // Abrir modal
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

            document.getElementById("modalTitle").textContent = titulo;

            let html = "";

            // Carousel de imagens
            if (imagens.length > 0) {
                html += `<div id="carouselProject" class="carousel slide mb-3" data-bs-ride="carousel">
                            <div class="carousel-inner">`;
                imagens.forEach(([src, focus], index) => {
                    const safeFocus = focus ? focus : "center";
                    html += `<div class="carousel-item ${index === 0 ? "active" : ""}">
                                <img src="${src}" class="d-block w-100 modal-image"
                                style="max-height:500px; object-fit:cover; object-position:${safeFocus};">
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

            // Botões de Like e Colab
            html += `
                <div class="d-flex gap-2 mb-3">
                    <button id="modalLikeBtn" class="btn ${userLike ? "btn-primary" : "btn-outline-primary"}">
                        ❤️ <span id="modalLikeCount">${totalLikes}</span>
                    </button>
                    <button id="modalColabBtn" class="btn ${userColab ? "btn-success" : "btn-outline-success"}" ${userColab ? "disabled" : ""}>
                        🤝
                    </button>
                </div>
            `;
            html += `<p>${descricao.replace(/\n/g, "<br>")}</p>`;
            document.getElementById("modalBody").innerHTML = html;

            // Like modal
            $('#modalLikeBtn').click(() => {
                fetch("like.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "id=" + currentProjectId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "ok") atualizarUI(currentProjectId, data);
                });
            });

            // Colab modal
            $('#modalColabBtn').click(() => {
                fetch("solicitar_colaboracao.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "id=" + currentProjectId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "ok") {
                        atualizarUI(currentProjectId, { user_colab: true, colab: true });
                    } else {
                        alert(data.msg);
                    }
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
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "ok") atualizarUI(id, data);
        });
    });

    // Colaboração nos cards
    $(".colab-btn").click(function () {
        const btn = $(this);
        const id = btn.data("id");
        fetch("solicitar_colaboracao.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + id
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "ok") {
                atualizarUI(id, { user_colab: true, colab: true });
            } else {
                alert(data.msg);
            }
        });
    });
});
