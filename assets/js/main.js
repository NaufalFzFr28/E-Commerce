/**
 * File: assets/js/main.js
 * Deskripsi: Logika interaktif utama untuk NaufaRu Website
 */

$(document).ready(function() {
    
    // --- 1. LOGIKA SLIDESHOW (Splash Screen) ---
    let slides = $('.slide');
    let currentSlide = 0;

    function nextSlide() {
        slides.eq(currentSlide).removeClass('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides.eq(currentSlide).addClass('active');
    }

    // Jalankan slideshow setiap 5 detik jika ada elemen slide
    if (slides.length > 0) {
        setInterval(nextSlide, 5000);
    }

    // --- 2. LOGIKA NIGHT MODE ---
    // Fungsi untuk mengganti mode malam secara instan
    window.toggleNightMode = function() {
        $('body').toggleClass('night-mode');
        
        // Simpan preferensi ke database via AJAX (opsional) atau Session
        const isNight = $('body').hasClass('night-mode');
        
        // Alert feedback menggunakan SweetAlert2
        Swal.fire({
            title: isNight ? 'Night Mode Aktif' : 'Day Mode Aktif',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    };

    // --- 3. ANIMASI FADE IN SAAT SCROLL ---
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                $(entry.target).addClass('fade-in-visible');
            }
        });
    }, observerOptions);

    $('.fade-in-element').each(function() {
        observer.observe(this);
    });

    // --- 4. BURGER MENU LOGIC ---
    $('#burger-btn').on('click', function() {
        $('.burger-menu-content').fadeToggle(300);
        $(this).toggleClass('open');
    });

    // --- 5. HANDLING CHAT ADMIN FORM ---
    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        
        // Simulasi pengiriman pesan
        Swal.fire({
            title: 'Pesan Terkirim!',
            text: 'Admin NaufaRu akan segera membalas pesan Anda.',
            icon: 'success',
            confirmButtonColor: '#0984e3'
        });
        
        $(this).trigger("reset");
    });

    // --- 6. DETEKSI ALERT DARI PHP (SESSION) ---
    // Jika ada alert yang dikirim dari functions.php via session
    if (typeof sessionAlert !== 'undefined') {
        Swal.fire({
            title: sessionAlert.title,
            text: sessionAlert.text,
            icon: sessionAlert.icon
        });
    }
});