<footer class="bg-slate-900 text-white pt-16 pb-8 border-t border-slate-800">
        <div class="container mx-auto px-4 md:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12 mb-12">
                
                <div class="space-y-4">
                    <a href="<?= $base_url ?>/" class="flex items-center gap-2 mb-4">
                        <?php if(!empty($settings['logo_url'])): ?>
                            <img src="<?= $base_url . '/' . $settings['logo_url'] ?>" alt="Logo" class="h-8 w-auto brightness-200 grayscale contrast-200">
                        <?php else: ?>
                            <div class="h-8 w-8 bg-brand-500 rounded-lg flex items-center justify-center text-white font-bold">
                                <?= substr($settings['nama_website'] ?? 'R', 0, 1) ?>
                            </div>
                        <?php endif; ?>
                        <span class="text-xl font-bold text-white tracking-tight"><?= $settings['nama_website'] ?? 'Rental Gedung' ?></span>
                    </a>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        <?= $settings['nama_panjang'] ?? 'Sistem Reservasi Gedung Serbaguna Profesional & Terpercaya.' ?>
                    </p>
                    <div class="flex gap-3 pt-2">
                        <?php if(!empty($settings['social_instagram'])): ?>
                        <a href="<?= $settings['social_instagram'] ?>" target="_blank" class="h-9 w-9 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-brand-600 hover:text-white transition-all">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if(!empty($settings['social_facebook'])): ?>
                        <a href="<?= $settings['social_facebook'] ?>" target="_blank" class="h-9 w-9 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-blue-600 hover:text-white transition-all">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6">Menu Cepat</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="<?= $base_url ?>/" class="hover:text-brand-500 transition-colors">Beranda</a></li>
                        <li><a href="<?= $base_url ?>/public/gedung/" class="hover:text-brand-500 transition-colors">Daftar Gedung</a></li>
                        <li><a href="<?= $base_url ?>/public/cek-jadwal.php" class="hover:text-brand-500 transition-colors">Cek Ketersediaan</a></li>
                        <li><a href="<?= $base_url ?>/user/login/" class="hover:text-brand-500 transition-colors">Login Member</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6">Hubungi Kami</h4>
                    <ul class="space-y-4 text-sm text-slate-400">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-1 text-brand-500"></i>
                            <span><?= nl2br(htmlspecialchars($settings['company_address'] ?? 'Alamat belum diatur')) ?></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-phone text-brand-500"></i>
                            <span><?= htmlspecialchars($settings['company_phone'] ?? '-') ?></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-envelope text-brand-500"></i>
                            <span><?= htmlspecialchars($settings['company_email'] ?? '-') ?></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-regular fa-clock text-brand-500"></i>
                            <span>Senin - Jumat, 08.00 - 16.00</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6">Info Terbaru</h4>
                    <p class="text-slate-400 text-sm mb-4">Dapatkan informasi promo dan update fasilitas terbaru.</p>
                    <form class="flex flex-col gap-2" onsubmit="event.preventDefault(); Swal.fire('Terima Kasih', 'Anda telah berlangganan newsletter.', 'success');">
                        <input type="email" placeholder="Email Anda" class="bg-slate-800 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all text-sm">
                        <button class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 rounded-lg transition-all shadow-lg shadow-brand-900/20">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center text-slate-500 text-sm gap-4">
                <p><?= htmlspecialchars($settings['footer_copyright'] ?? '© 2025 Rental Gedung. All rights reserved.') ?></p>
                <p>Designed for <span class="text-brand-500 font-semibold"><?= htmlspecialchars($settings['instansi_nama'] ?? 'Instansi') ?></span></p>
            </div>
        </div>
    </footer>

    <script>
        const navbar = document.getElementById('navbar');
        
        // Fungsi efek scroll
        window.addEventListener('scroll', () => {
            if (window.scrollY > 10) {
                navbar.classList.add('navbar-scrolled');
                // Kecilkan padding saat scroll
                navbar.classList.remove('py-4', 'lg:py-6');
                navbar.classList.add('py-3');
            } else {
                navbar.classList.remove('navbar-scrolled');
                navbar.classList.remove('py-3');
                navbar.classList.add('py-4', 'lg:py-6');
            }
        });
    </script>
</body>
</html>