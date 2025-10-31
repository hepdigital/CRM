<?php
// includes/footer.php
?>
            </div><!-- /.content -->
        </main>
    </div><!-- /.app-container -->
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/assets/js/theme.js"></script>
    <script src="/assets/js/scripts.js"></script>
    <script src="/assets/js/mobile.js"></script>
    
    <script>
        // Global JavaScript fonksiyonları
        function formatMoney(amount) {
            return new Intl.NumberFormat('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        }

        // DataTables ve diğer eklentiler için genel ayarlar
        $(document).ready(function() {
            // Bootstrap tooltip'lerini aktifleştir
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Search functionality
            $('.search-input').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.table tbody tr').each(function() {
                    const rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.includes(searchTerm));
                });
            });
        });
    </script>
</body>
</html>