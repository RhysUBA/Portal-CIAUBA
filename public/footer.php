    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; <?php echo date('Y'); ?></p>
        <p>Contacto: rhysuba@gmail.com</p>
    </footer>

    <button class="scroll-to-top" id="scrollToTopBtn" title="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Menú hamburguesa
        const menuToggle = document.getElementById('menuToggle');
        const mainNav = document.getElementById('mainNav');
        if (menuToggle && mainNav) {
            menuToggle.addEventListener('click', () => {
                mainNav.classList.toggle('active');
            });
        }

        // Botón scroll to top
        const scrollBtn = document.getElementById('scrollToTopBtn');
        if (scrollBtn) {
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollBtn.classList.add('show');
                } else {
                    scrollBtn.classList.remove('show');
                }
            });
            scrollBtn.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    </script>

    <?php if (isset($extra_js)): ?>
        <script><?php echo $extra_js; ?></script>
    <?php endif; ?>
</body>
</html>