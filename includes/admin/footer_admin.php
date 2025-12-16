<footer class="mt-auto py-6">
                    <div class="text-center border-t border-slate-200/60 pt-6">
                        <p class="text-xs text-slate-400">
                            <?= $settings['footer_copyright'] ?? '&copy; ' . date('Y') . ' ' . htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') . '. All rights reserved.' ?>
                        </p>
                        <p class="text-[10px] text-slate-300 mt-1">
                            System v1.0.0 &bull; Design with <i class="fa-solid fa-heart text-red-400"></i> using TailwindCSS
                        </p>
                    </div>
                </footer>

            </main> </div> </div> <script>
        const btn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        
        // Logic Sidebar Toggle Mobile
        if (btn && sidebar) {
            btn.addEventListener('click', () => {
                if (sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('-translate-x-full');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth < 1024) { // Only on mobile
                    if (!sidebar.contains(e.target) && !btn.contains(e.target)) {
                        sidebar.classList.add('-translate-x-full');
                    }
                }
            });
        }
        
        // SweetAlert2 Helper Functions
        function showSuccess(message, title = 'Berhasil!') {
            Swal.fire({
                icon: 'success',
                title: title,
                text: message,
                confirmButtonColor: tailwind.config.theme.extend.colors.primary, // Dinamis sesuai tema
                confirmButtonText: 'OK',
                timer: 3000,
                timerProgressBar: true
            });
        }

        function showError(message, title = 'Oops...') {
            Swal.fire({
                icon: 'error',
                title: title,
                text: message,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
        }

        function confirmDelete(url, message = 'Data yang dihapus tidak dapat dikembalikan!', title = 'Apakah Anda yakin?') {
            Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
        
        // Auto-hide alerts logic
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('[data-auto-hide]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>