// Bora+ — comportamento do site público
(function () {
    'use strict';

    document.documentElement.classList.add('js');

    // Sombra/linha no header ao rolar
    var header = document.querySelector('.site-header');
    var onScroll = function () {
        if (header) header.classList.toggle('is-scrolled', window.scrollY > 8);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    // Envia a busca automaticamente ao trocar o bairro
    document.querySelectorAll('form[data-autosubmit] select').forEach(function (select) {
        select.addEventListener('change', function () {
            select.form.submit();
        });
    });

    // Cards aparecem suavemente ao entrar na tela
    var cards = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });
        cards.forEach(function (card, i) {
            card.style.transitionDelay = Math.min(i % 6, 5) * 50 + 'ms';
            io.observe(card);
        });
    } else {
        cards.forEach(function (card) { card.classList.add('is-visible'); });
    }
})();
