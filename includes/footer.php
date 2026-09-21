</main>
<footer class="site-footer">
    <div class="container site-footer__inner">
        <span class="logo logo--sm">Bora<span>+</span></span>
        <div class="site-footer__center">
            <p>Direitos autorais © <?= date('Y') ?> Todos os direitos reservados - <?= e(strtoupper(rtrim(SITE_NAME, '+'))) ?></p>
            <nav class="site-footer__links" aria-label="Links do rodapé">
                <a href="#">Termos</a> - <a href="#">Privacidade</a> -<a href="#">Acessibilidade</a>
            </nav>
        </div>
    </div>
</footer>
<script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
