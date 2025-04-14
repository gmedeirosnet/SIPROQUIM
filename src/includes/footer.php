    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <p>SIPROQUIM &copy; <?php echo date('Y'); ?> - Sistema de Controle de Produtos Químicos</p>
            </div>
        </div>
    </footer>

    <!-- Optional JavaScript -->
    <script>
        // Add any global JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any components that need JavaScript

            // Add active class to dropdowns if any child link is active
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                const links = dropdown.querySelectorAll('.dropdown-content a');
                links.forEach(link => {
                    if (link.pathname === window.location.pathname) {
                        dropdown.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>