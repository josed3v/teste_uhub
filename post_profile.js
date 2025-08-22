const imageInput = document.getElementById('imageInput');
const previewContainer = document.getElementById('imagePreviewContainer');
const form = document.getElementById('projectForm');

imageInput.addEventListener('change', () => {
    previewContainer.innerHTML = '';
    Array.from(imageInput.files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = e => {
            const cropperDiv = document.createElement('div');
            cropperDiv.className = 'image-cropper';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.dataset.focusX = 50;
            img.dataset.focusY = 50;
            img.dataset.scale = 1;
            cropperDiv.appendChild(img);

            previewContainer.appendChild(cropperDiv);

            // Arrastar
            let isDragging = false, startX, startY;
            img.addEventListener('mousedown', evt => {
                isDragging = true;
                startX = evt.clientX - parseFloat(img.style.left || 0);
                startY = evt.clientY - parseFloat(img.style.top || 0);
                img.style.cursor = 'grabbing';
            });
            document.addEventListener('mouseup', () => { isDragging = false; img.style.cursor='grab'; });
            document.addEventListener('mousemove', evt => {
                if(!isDragging) return;
                const rect = cropperDiv.getBoundingClientRect();
                let left = evt.clientX - startX;
                let top = evt.clientY - startY;
                img.style.left = left+'px';
                img.style.top = top+'px';

                const focusX = ((rect.width/2 - left)/rect.width)*100;
                const focusY = ((rect.height/2 - top)/rect.height)*100;
                img.dataset.focusX = Math.max(0, Math.min(100, focusX));
                img.dataset.focusY = Math.max(0, Math.min(100, focusY));
            });

            // Zoom pelo scroll
            cropperDiv.addEventListener('wheel', evt => {
                evt.preventDefault();
                let scale = parseFloat(img.dataset.scale);
                if(evt.deltaY < 0) scale += 0.05;
                else scale = Math.max(1, scale - 0.05);
                img.dataset.scale = scale;
                img.style.transform = `scale(${scale})`;
            });
        };
        reader.readAsDataURL(file);
    });
});

form.addEventListener('submit', e => {
    // Limpar inputs antigos
    const oldFiles = form.querySelectorAll('input[name="imagens[]"]');
    oldFiles.forEach(f => f.remove());
    const oldFx = form.querySelectorAll('input[name="focusX[]"]');
    oldFx.forEach(f => f.remove());
    const oldFy = form.querySelectorAll('input[name="focusY[]"]');
    oldFy.forEach(f => f.remove());

    Array.from(previewContainer.children).forEach((cropperDiv, index) => {
        const img = cropperDiv.querySelector('img');
        const file = imageInput.files[index];

        const fileField = document.createElement('input');
        fileField.type = 'file';
        fileField.name = 'imagens[]';
        Object.defineProperty(fileField, 'files', { value: [file] });
        form.appendChild(fileField);

        const fx = document.createElement('input');
        fx.type = 'hidden';
        fx.name = 'focusX[]';
        fx.value = img.dataset.focusX;
        form.appendChild(fx);

        const fy = document.createElement('input');
        fy.type = 'hidden';
        fy.name = 'focusY[]';
        fy.value = img.dataset.focusY;
        form.appendChild(fy);
    });
});
