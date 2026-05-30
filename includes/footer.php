<?php
/**
 * Footer Template
 * Inventory Management System
 */
?>
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p class="mb-0">
                &copy; <?php echo date('Y'); ?> <?php echo getSetting('store_name', APP_NAME); ?> v<?php echo APP_VERSION; ?> | 
                SJA | 
                Developed for Small Shops, Pharmacies & Supermarkets
            </p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Local Bootstrap JavaScript Fallback -->
    <script>
        // Check if Bootstrap JavaScript loaded, if not use local fallback
        function checkBootstrapJS() {
            if (typeof bootstrap === 'undefined') {
                const script = document.createElement('script');
                script.src = '<?php echo BASE_URL; ?>/assets/js/bootstrap-local.js';
                document.head.appendChild(script);
                console.log('Bootstrap CDN blocked, using local fallback');
            } else {
                console.log('Bootstrap CDN loaded successfully');
            }
        }
        
        // Check immediately and also after DOM loads
        checkBootstrapJS();
        document.addEventListener('DOMContentLoaded', checkBootstrapJS);
    </script>
    
    <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
    
    <!-- Auto-refresh for dashboard (every 30 seconds) -->
    <?php if (basename($_SERVER['PHP_SELF']) === 'dashboard.php'): ?>
        <script>
            setTimeout(function() {
                window.location.reload();
            }, 30000);
        </script>
    <?php endif; ?>
</body>
</html>
