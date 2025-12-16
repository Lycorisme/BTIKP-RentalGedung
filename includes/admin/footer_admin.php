</main>
    </div>
</div>

<script>
// Mobile Menu Toggle
const btn = document.getElementById('mobile-menu-btn');
const sidebar = document.getElementById('sidebar');

btn.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    if (window.innerWidth < 1024) {
        if (!sidebar.contains(e.target) && !btn.contains(e.target)) {
            sidebar.classList.add('-translate-x-full');
        }
    }
});

// SweetAlert2 Helper Functions
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        confirmButtonColor: '#4F46E5',
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