<?php
require_once __DIR__ . '/../includes/public/header_public.php';
require_once __DIR__ . '/../modules/crud.php';

// Get featured gedung
$featured_gedung = read('gedung', ['status' => 'tersedia'], 3);

// Get stats
$total_gedung = count_records('gedung');
$total_booking = count_records('booking');
?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Animated Background -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 opacity-90"></div>
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1920&q=80')] bg-cover bg-center mix-blend-overlay"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <div class="animate-fade-in-up">
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 leading-tight">
                Temukan Gedung <br>
                <span class="bg-gradient-to-r from-yellow-300 to-orange-400 bg-clip-text text-transparent">
                    Impian Anda
                </span>
            </h1>
            <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Solusi terbaik untuk acara pernikahan, seminar, meeting, dan berbagai keperluan Anda
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/situs-rental-gedung/public/gedung/" 
                    class="px-8 py-4 rounded-2xl bg-white text-blue-600 font-bold shadow-2xl hover:shadow-white/30 hover:translate-y-[-4px] transition-all text-lg">
                    <i class="fa-solid fa-building mr-2"></i>Lihat Gedung
                </a>
                <a href="/situs-rental-gedung/user/register/" 
                    class="px-8 py-4 rounded-2xl bg-gradient-to-r from-orange-400 to-pink-500 text-white font-bold shadow-2xl shadow-orange-500/50 hover:shadow-orange-500/70 hover:translate-y-[-4px] transition-all text-lg">
                    <i class="fa-solid fa-rocket mr-2"></i>Daftar Sekarang
                </a>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-16 max-w-4xl mx-auto">
            <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-6 border border-white/20">
                <div class="text-4xl font-bold text-white mb-2"><?= $total_gedung ?>+</div>
                <div class="text-blue-100 text-sm">Gedung Tersedia</div>
            </div>
            <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-6 border border-white/20">
                <div class="text-4xl font-bold text-white mb-2"><?= $total_booking ?>+</div>
                <div class="text-blue-100 text-sm">Booking Selesai</div>
            </div>
            <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-6 border border-white/20">
                <div class="text-4xl font-bold text-white mb-2">98%</div>
                <div class="text-blue-100 text-sm">Kepuasan</div>
            </div>
            <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-6 border border-white/20">
                <div class="text-4xl font-bold text-white mb-2">24/7</div>
                <div class="text-blue-100 text-sm">Support</div>
            </div>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <i class="fa-solid fa-chevron-down text-white text-2xl"></i>
    </div>
</section>

<!-- Featured Gedung -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-slate-800 mb-4">
                Gedung <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Terpopuler</span>
            </h2>
            <p class="text-slate-600 text-lg">Pilihan favorit untuk berbagai acara</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($featured_gedung)): ?>
                <div class="col-span-3 text-center py-12">
                    <i class="fa-solid fa-building text-6xl text-slate-300 mb-4"></i>
                    <p class="text-slate-500">Belum ada gedung tersedia</p>
                </div>
            <?php else: ?>
                <?php foreach ($featured_gedung as $gedung): ?>
                <div class="group bg-white rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-300 hover:translate-y-[-8px]">
                    <div class="relative h-64 overflow-hidden">
                        <img src="/situs-rental-gedung/<?= htmlspecialchars($gedung['foto_utama'] ?? 'uploads/gedung/default.jpg') ?>" 
                            alt="<?= htmlspecialchars($gedung['nama']) ?>" 
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            onerror="this.src='https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80'">
                        <div class="absolute top-4 right-4 px-4 py-2 rounded-full bg-white/90 backdrop-blur-sm font-bold text-emerald-600">
                            <i class="fa-solid fa-check-circle mr-1"></i>Tersedia
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-slate-800 mb-2"><?= htmlspecialchars($gedung['nama']) ?></h3>
                        <p class="text-slate-600 text-sm mb-4 line-clamp-2">
                            <?= htmlspecialchars(substr($gedung['deskripsi'] ?? 'Gedung berkualitas untuk berbagai acara Anda.', 0, 100)) ?>...
                        </p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-4 text-sm text-slate-500">
                                <?php if ($gedung['kapasitas_orang']): ?>
                                <span><i class="fa-solid fa-users text-blue-500 mr-1"></i><?= $gedung['kapasitas_orang'] ?> Orang</span>
                                <?php endif; ?>
                                <?php if ($gedung['luas_m2']): ?>
                                <span><i class="fa-solid fa-ruler-combined text-blue-500 mr-1"></i><?= $gedung['luas_m2'] ?> m²</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <div>
                                <p class="text-xs text-slate-500">Mulai dari</p>
                                <p class="text-2xl font-bold text-blue-600">
                                    Rp <?= number_format($gedung['harga_per_hari'], 0, ',', '.') ?>
                                </p>
                                <p class="text-xs text-slate-500">per hari</p>
                            </div>
                            <a href="/situs-rental-gedung/public/gedung-detail/?id=<?= $gedung['id'] ?>" 
                                class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="/situs-rental-gedung/public/gedung/" 
                class="inline-flex items-center px-8 py-4 rounded-2xl bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:translate-y-[-4px] transition-all">
                <i class="fa-solid fa-building mr-2"></i>Lihat Semua Gedung
            </a>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-20 bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-slate-800 mb-4">Mengapa Pilih Kami?</h2>
            <p class="text-slate-600 text-lg">Keunggulan layanan rental gedung kami</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white rounded-3xl p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:translate-y-[-8px] transition-all">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mb-6 shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-shield-halved text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-3">Terpercaya</h3>
                <p class="text-slate-600">Layanan terpercaya dengan sistem booking yang aman dan transparan.</p>
            </div>
            
            <div class="bg-white rounded-3xl p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:translate-y-[-8px] transition-all">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center mb-6 shadow-lg shadow-purple-500/30">
                    <i class="fa-solid fa-clock text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-3">Booking Cepat</h3>
                <p class="text-slate-600">Proses booking mudah dan cepat, konfirmasi instant.</p>
            </div>
            
            <div class="bg-white rounded-3xl p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:translate-y-[-8px] transition-all">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-pink-500 to-pink-600 flex items-center justify-center mb-6 shadow-lg shadow-pink-500/30">
                    <i class="fa-solid fa-hand-holding-dollar text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-3">Harga Terjangkau</h3>
                <p class="text-slate-600">Harga kompetitif dengan fasilitas lengkap dan berkualitas.</p>
            </div>
            
            <div class="bg-white rounded-3xl p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:translate-y-[-8px] transition-all">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center mb-6 shadow-lg shadow-orange-500/30">
                    <i class="fa-solid fa-headset text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-3">Support 24/7</h3>
                <p class="text-slate-600">Tim support siap membantu Anda kapan saja.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>
    
    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
            Siap Booking Gedung Impian Anda?
        </h2>
        <p class="text-xl text-blue-100 mb-8">
            Daftar sekarang dan dapatkan penawaran spesial untuk booking pertama Anda!
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/situs-rental-gedung/user/register/" 
                class="px-8 py-4 rounded-2xl bg-white text-blue-600 font-bold shadow-2xl hover:shadow-white/30 hover:translate-y-[-4px] transition-all text-lg">
                <i class="fa-solid fa-user-plus mr-2"></i>Daftar Gratis
            </a>
            <a href="/situs-rental-gedung/public/gedung/" 
                class="px-8 py-4 rounded-2xl bg-transparent border-2 border-white text-white font-bold hover:bg-white hover:text-blue-600 transition-all text-lg">
                <i class="fa-solid fa-building mr-2"></i>Lihat Gedung
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/public/footer_public.php'; ?>