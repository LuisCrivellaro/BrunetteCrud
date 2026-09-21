// Bora+ — comportamento do painel admin
(function () {
    'use strict';

    // Confirmação antes de excluir
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                ev.preventDefault();
            }
        });
    });

    // Pré-visualização da imagem escolhida
    document.querySelectorAll('[data-preview-input]').forEach(function (input) {
        var preview = input.closest('.upload').querySelector('[data-preview]');
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file || !preview) return;
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        });
    });

    // Mensagens de sucesso somem sozinhas
    document.querySelectorAll('.alert--success[data-autodismiss]').forEach(function (el) {
        setTimeout(function () {
            el.classList.add('is-hiding');
            setTimeout(function () { el.remove(); }, 400);
        }, 4000);
    });
})();
