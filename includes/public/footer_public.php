<?php
$settings = get_all_settings();
$instansi_nama = $settings['instansi_nama'] ?? 'PT. Rental Gedung';
$instansi_alamat = $settings['instansi_alamat'] ?? '';
$instansi_telepon = $settings['instansi_telepon'] ?? '';
$instansi_email = $settings['instansi_email'] ?? '';
?>
</main>

<!-- Footer -->
<footer class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- About -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center">
                        <i class="fa-solid fa-building text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?></h3>
                </div>
                <p class="text-slate-300 text-sm leading-relaxed">
                    <?= htmlspecialchars($settings['nama_panjang'] ?? 'Solusi terbaik untuk kebutuhan rental gedung acara Anda.') ?>
                </p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h4 class="text-lg font-bold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a href="/situs-rental-gedung/public/" class="text-slate-300 hover:text-primary-400 transition-colors text-sm"><i class="fa-solid fa-angle-right mr-2"></i>Beranda</a></li>
                    <li><a href="/situs-rental-gedung/public/gedung/" class="text-slate-300 hover:text-primary-400 transition-colors text-sm"><i class="fa-solid fa-angle-right mr-2"></i>Daftar Gedung</a></li>
                    <li><a href="/situs-rental-gedung/laporan/" class="text-slate-300 hover:text-primary-400 transition-colors text-sm"><i class="fa-solid fa-angle-right mr-2"></i>Laporan</a></li>
                    <li><a href="/situs-rental-gedung/user/login/" class="text-slate-300 hover:text-primary-400 transition-colors text-sm"><i class="fa-solid fa-angle-right mr-2"></i>Login</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h4 class="text-lg font-bold mb-4">Kontak</h4>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-location-dot text-primary-400 mt-1"></i>
                        <span class="text-slate-300"><?= nl2br(htmlspecialchars($instansi_alamat)) ?></span>
                    </li>
                    <?php if (!empty($instansi_telepon)): ?>
                    <li class="flex items-center gap-3">
                        <i class="fa-solid fa-phone text-primary-400"></i>
                        <span class="text-slate-300"><?= htmlspecialchars($instansi_telepon) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($instansi_email)): ?>
                    <li class="flex items-center gap-3">
                        <i class="fa-solid fa-envelope text-primary-400"></i>
                        <span class="text-slate-300"><?= htmlspecialchars($instansi_email) ?></span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Social Media -->
            <div>
                <h4 class="text-lg font-bold mb-4">Follow Us</h4>
                <div class="flex gap-3">
                    <a href="#" class="h-10 w-10 rounded-xl bg-slate-700 hover:bg-primary-600 flex items-center justify-center transition-all hover:translate-y-[-4px]">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="h-10 w-10 rounded-xl bg-slate-700 hover:bg-primary-600 flex items-center justify-center transition-all hover:translate-y-[-4px]">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="h-10 w-10 rounded-xl bg-slate-700 hover:bg-primary-600 flex items-center justify-center transition-all hover:translate-y-[-4px]">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="h-10 w-10 rounded-xl bg-slate-700 hover:bg-primary-600 flex items-center justify-center transition-all hover:translate-y-[-4px]">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="border-t border-slate-700 mt-8 pt-8 text-center">
            <p class="text-slate-400 text-sm">
                &copy; <?= date('Y') ?> <?= htmlspecialchars($instansi_nama) ?>. All rights reserved. Powered by TailwindCSS & SweetAlert2.
            </p>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scroll-top" class="fixed bottom-8 right-8 h-12 w-12 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-white shadow-xl shadow-primary-500/30 flex items-center justify-center opacity-0 pointer-events-none transition-all hover:translate-y-[-4px] z-40">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<script>
// Mobile Menu Toggle
document.getElementById('mobile-menu-btn').addEventListener('click', function() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
});

// Scroll to Top
const scrollTopBtn = document.getElementById('scroll-top');
window.addEventListener('scroll', function() {
    if (window.scrollY > 300) {
        scrollTopBtn.classList.remove('opacity-0', 'pointer-events-none');
    } else {
        scrollTopBtn.classList.add('opacity-0', 'pointer-events-none');
    }
});

scrollTopBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// SweetAlert2 Helper Functions
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        confirmButtonColor: '#0ea5e9',
        confirmButtonText: 'OK'
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'OK'
    });
}

function confirmDelete(url, message = 'Data yang dihapus tidak dapat dikembalikan!') {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>

</body>
</html>