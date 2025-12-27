document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.acf-emoji-picker-button');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            let picker = document.querySelector('.acf-emoji-picker-popup');

            // Si ja existeix, eliminar-lo
            if (picker) picker.remove();

            // Crear nou popup
            picker = document.createElement('div');
            picker.classList.add('acf-emoji-picker-popup');
            picker.style.position = 'absolute';
            picker.style.background = '#fff';
            picker.style.border = '1px solid #ddd';
            picker.style.borderRadius = '8px';
            picker.style.padding = '10px';
            picker.style.width = '300px';
            picker.style.height = '200px';
            picker.style.overflowY = 'scroll';
            picker.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
            picker.style.zIndex = '9999';

            // Posicionar popup
            const rect = this.getBoundingClientRect();
            picker.style.left = rect.left + 'px';
            picker.style.top = rect.bottom + 'px';

            document.body.appendChild(picker);

            // Llista d'emojis (Twemoji + Unicode)
            const emojis = [
                '😀','😁','😂','🤣','😃','😄','😅','😆','😉','😊','😋','😎','😍','😘','🥰',
                '😗','😙','😚','🙂','🤗','🤩','🤔','🤨','😐','😑','😶','🙄','😏','😣','😥','😮',
                '🤐','😯','😪','😫','🥱','😴','😌','😛','😜','😝','🤤','😒','😓','😔','😕',
                '🙃','🤑','😲','☹️','🙁','😖','😞','😟','😤','😢','😭','😦','😧','😨','😩',
                '🤯','😬','😰','😱','🥵','🥶','😳','🤪','😵','🥴','😠','😡','🤬','😷','🤒','🤕'
            ];

            // Renderitzar emojis
            emojis.forEach(emoji => {
                const span = document.createElement('span');
                span.textContent = emoji;
                span.style.fontSize = '22px';
                span.style.margin = '4px';
                span.style.cursor = 'pointer';
                span.addEventListener('click', () => {
                    const input = button.previousElementSibling;
                    input.value = emoji;
                    picker.remove();
                });
                picker.appendChild(span);
            });

            // Tancar popup quan es faci clic fora
            document.addEventListener('click', function closePopup(e) {
                if (!picker.contains(e.target) && e.target !== button) {
                    picker.remove();
                    document.removeEventListener('click', closePopup);
                }
            });
        });
    });

    // Aplicar Twemoji (si està carregat)
    if (typeof twemoji !== 'undefined') {
        twemoji.parse(document.body, {
            folder: 'svg',
            ext: '.svg'
        });
    }
});
