    // Ubah Bahasa
    function changeMainLang(langCode) {
    // Ambil URL saat ini tanpa parameter query
    const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    
    // Arahkan browser ke URL baru dengan parameter bahasa
    window.location.href = baseUrl + "?lang=" + langCode;
}
    
    // Scramble Text Effect
    class TextScramble {
        constructor(el) {
            this.el = el;
            this.chars = '!<>-_\\/[]{}—=+*^?#________'; // Karakter acak yang akan muncul
            this.update = this.update.bind(this);
        }
        setText(newText) {
            const oldText = this.el.innerText;
            const length = Math.max(oldText.length, newText.length);
            const promise = new Promise((resolve) => (this.resolve = resolve));
            this.queue = [];
            for (let i = 0; i < length; i++) {
            const from = oldText[i] || '';
            const to = newText[i] || '';
            const start = Math.floor(Math.random() * 40); // Kecepatan acak per huruf
            const end = start + Math.floor(Math.random() * 40);
            this.queue.push({ from, to, start, end });
            }
            cancelAnimationFrame(this.frameRequest);
            this.frame = 0;
            this.update();
            return promise;
        }
        update() {
            let output = '';
            let complete = 0;
            for (let i = 0, n = this.queue.length; i < n; i++) {
            let { from, to, start, end, char } = this.queue[i];
            if (this.frame >= end) {
                complete++;
                output += to;
            } else if (this.frame >= start) {
                if (!char || Math.random() < 0.28) {
                char = this.randomChar();
                this.queue[i].char = char;
                }
                output += `<span class="dud">${char}</span>`;
            } else {
                output += from;
            }
            }
            this.el.innerHTML = output;
            if (complete === this.queue.length) {
            this.resolve();
            } else {
            this.frameRequest = requestAnimationFrame(this.update);
            this.frame++;
            }
        }
        randomChar() {
            return this.chars[Math.floor(Math.random() * this.chars.length)];
        }
        }

        // Scramble Section
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Logika untuk Section Lain (Hero, dsb) tetap aman
            const generalScramble = document.querySelectorAll('.scramble-text:not(.scramble-service)');
            generalScramble.forEach(el => {
                const fx = new TextScramble(el);
                el.addEventListener('mouseenter', () => fx.setText(el.innerText));
            });

            // 2. LOGIKA KHUSUS SECTION LAYANAN (Diakali lewat Hover Card)
            const serviceCards = document.querySelectorAll('.custom-card');
            serviceCards.forEach((card) => {
                const textEl = card.querySelector('.scramble-service');
                if (textEl) {
                    const fxService = new TextScramble(textEl);
                    const originalText = textEl.innerText;

                    // Trigger-nya adalah Casing Card-nya
                    card.addEventListener('mouseenter', () => {
                        fxService.setText(originalText);
                    });
                }
            });
        });

        // Reveal Effect
        document.addEventListener("DOMContentLoaded", function() {
            const observerOptions = {
                root: null,
                // rootMargin: '0px 0px -50px 0px' memastikan elemen tidak 
                // langsung muncul tepat di garis bawah layar (memberi jarak 50px)
                rootMargin: '0px 0px -50px 0px',
                threshold: 0.1
            };

            let revealQueue = [];
            let isProcessing = false;

            // Fungsi untuk memproses antrean animasi
            function processQueue() {
                if (revealQueue.length === 0) {
                    isProcessing = false;
                    return;
                }

                isProcessing = true;
                const nextElement = revealQueue.shift();
                
                // Tambahkan class active
                nextElement.classList.add("active");

                // Tunggu 200ms sebelum memunculkan elemen berikutnya (Efek Bertahap)
                setTimeout(processQueue, 200);
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        // Masukkan elemen ke dalam antrean jika belum ada class active
                        if (!entry.target.classList.contains("active")) {
                            revealQueue.push(entry.target);
                            observer.unobserve(entry.target); // Berhenti amati setelah masuk antrean

                            // Jalankan pemrosesan jika belum berjalan
                            if (!isProcessing) {
                                processQueue();
                            }
                        }
                    }
                });
            }, observerOptions);

            // Amati semua elemen reveal
            document.querySelectorAll(".reveal").forEach((el) => {
                observer.observe(el);
            });
        });

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.scramble-text');
            
            elements.forEach((el) => {
                const fx = new TextScramble(el);
                const originalText = el.innerText;
                
                // Jalankan efek saat hover
                el.addEventListener('mouseenter', () => {
                    fx.setText(originalText);
                });
            });
        });

    $(document).ready(function() {
        // Dropdown Toggle
        $('#burgerToggle').on('click', function(e) {
            e.stopPropagation();
            const menu = $('#menuDrop');
            const btn = $(this);

            // Toggle class untuk icon silang & animasi menu
            btn.toggleClass('is-active');
            menu.toggleClass('active');

            // Gunakan slideDown untuk efek bergulir dari bawah navbar
            if (menu.is(':visible')) {
                menu.fadeOut(300);
            } else {
                menu.slideDown(400).removeClass('show-lang');
            }
        });

        // Perbaikan sub-menu bahasa agar tetap rapi di mobile
        $('#openLang').on('click', function(e) {
            e.stopPropagation();
            $('#menuDrop').addClass('show-lang');
            // Reset opacity untuk item di dalam submenu agar muncul juga
            $('.lang-submenu .menu-item').css({'opacity': '1', 'transform': 'none'});
        });

        $('#backToMain').on('click', function(e) {
            e.stopPropagation();
            $('#menuDrop').removeClass('show-lang');
        });

        // Menutup menu saat klik area luar (khusus jika tidak full screen di desktop)
        $(document).on('click', function() { 
            $('#menuDrop').fadeOut(300).removeClass('active');
            $('#burgerToggle').removeClass('is-active');
        });
    });

    // Fungsi untuk mematikan/menghidupkan mode gelap
    function toggleMainMode() {
        const body = $('body');
        const icon = $('#modeIcon');
        const text = $('#modeText');
        
        body.toggleClass('dark-mode');
        const isDark = body.hasClass('dark-mode');

        // --- PEMBARUAN: Simpan pilihan ke localStorage ---
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        
        // Update teks dan ikon
        updateModeUI(isDark);
    }

    // Fungsi pembantu untuk update UI (ikon & teks)
    function updateModeUI(isDark) {
        const icon = $('#modeIcon');
        const text = $('#modeText');
        const btn = $('#modeToggleBtn'); // Ambil elemen tombol
        
        // Ambil teks dari data attribute yang sudah diisi PHP tadi
        const textDark = btn.data('dark');
        const textLight = btn.data('light');
        
        text.text(isDark ? textLight : textDark);
        icon.attr('class', isDark ? 'fas fa-sun' : 'fas fa-circle-half-stroke');
    }

    // --- PEMBARUAN: Logika saat halaman di-load/refresh ---
    $(document).ready(function() {
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme === 'dark') {
            $('body').addClass('dark-mode');
            updateModeUI(true);
        } else {
            $('body').removeClass('dark-mode');
            updateModeUI(false);
        }
    });

        $(document).ready(function() {
            // Pastikan Swiper terdefinisi sebelum inisialisasi
            if (typeof Swiper !== 'undefined') {
                const masterSwiper = new Swiper('.master-wpap-slider', {
                    loop: true, // Berputar terus
                    speed: 1000, // Kecepatan transisi 1 detik
                    autoplay: {
                        delay: 10000, // Bergeser otomatis setiap 10 detik
                        disableOnInteraction: false, // Tetap autoplay meski user menggeser manual
                    },
                    effect: 'slide', // Efek bergeser standar karosel
                    grabCursor: true, // Mengubah kursor jadi tangan saat hover
                });
            } else {
                console.error("Swiper.js belum termuat. Pastikan CDN sudah diletakkan sebelum script ini.");
        }

    });

    
    

    function startStatsAnimation() {
        // 1. Sembunyikan tombol, Munculkan kontainer data
        document.getElementById('triggerStats').classList.add('d-none');
        const statsData = document.getElementById('statsData');
        statsData.classList.remove('d-none');

        // 2. Animasi Angka (Typewriting/Counter Effect)
        const counters = document.querySelectorAll('.stat-number');
        const speed = 50; // Kecepatan animasi dalam ms

        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const suffix = counter.innerText.includes('+') || counter.getAttribute('data-target') == "150" ? "+" : "";
            let count = 0;

            const updateCount = setInterval(() => {
                // Logika pertambahan angka agar terasa seperti mengetik cepat
                const inc = Math.ceil(target / 20); 
                
                if (count < target) {
                    count += inc;
                    if (count > target) count = target;
                    counter.innerText = count + (count === target ? suffix : "");
                } else {
                    clearInterval(updateCount);
                }
            }, speed);
        });

        // 3. Animasi Teks Label (Fade In Up)
        setTimeout(() => {
            document.querySelectorAll('.stat-label').forEach((label, index) => {
                setTimeout(() => {
                    label.classList.add('show');
                }, index * 200); // Delay bertahap antar label
            });
        }, 300);
    }

    // About Section
    document.addEventListener("DOMContentLoaded", function () {
        const flipBox = document.querySelector(".flip-box");
        let flipInterval;

        // Fungsi untuk menjalankan Flip
        function startAutoFlip() {
            flipInterval = setInterval(() => {
                // Toggle class flipped untuk memicu rotasi
                flipBox.classList.toggle("flipped");
                
                // Jika ingin flip balik sebentar setelah muncul (looping murni):
                // Kita biarkan toggle saja agar 10 detik depan, 10 detik belakang.
            }, 10000); // 10000ms = 10 detik
        }

        // Jalankan auto flip saat pertama kali load
        startAutoFlip();

        // Logika Hover: Jika user arahkan kursor, hentikan auto flip sementara
        flipBox.addEventListener("mouseenter", () => {
            clearInterval(flipInterval);
            flipBox.classList.remove("flipped"); // Kembalikan ke kontrol hover CSS
        });

        // Saat kursor keluar, jalankan kembali auto flip
        flipBox.addEventListener("mouseleave", () => {
            startAutoFlip();
        });
    });

    function toggleAboutMobile() {
        const moreText = document.getElementById("moreAboutText");
        const btn = document.getElementById("readMoreBtn");
        
        if (moreText.classList.contains("open")) {
            // Tutup
            moreText.classList.remove("open");
            btn.innerHTML = 'Lihat Selengkapnya <i class="fas fa-chevron-down ms-1"></i>';
            
            // Scroll kembali ke atas judul about agar user tidak tersesat
            document.getElementById("about").scrollIntoView({ behavior: 'smooth' });
        } else {
            // Buka
            moreText.classList.add("open");
            btn.innerHTML = 'Sembunyikan <i class="fas fa-chevron-up ms-1"></i>';
        }
    }

    // Update fungsi toggle agar sinkron
    function toggleReadMore() {
        const btn = document.getElementById("readMoreBtn");
        const btnText = document.getElementById("btnText");
        const extraContent = document.getElementById("extraContent");
        const btnIcon = document.getElementById("btnIcon");
        
        // Ambil teks translasi dari attribute data yang diisi PHP
        const textMore = btn.getAttribute('data-more');
        const textLess = btn.getAttribute('data-less');

        if (extraContent.classList.contains("open")) {
            extraContent.classList.remove("open");
            btnText.innerText = textMore; // Akan mengambil "詳しく見る" jika mode JP
            btnIcon.classList.replace("fa-chevron-up", "fa-chevron-down");
            document.getElementById("about").scrollIntoView({ behavior: 'smooth' });
        } else {
            extraContent.classList.add("open");
            btnText.innerText = textLess; // Akan mengambil "閉じる" jika mode JP
            btnIcon.classList.replace("fa-chevron-down", "fa-chevron-up");
        }
    }

    // Inisialisasi teks tombol saat halaman dimuat
    window.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById("readMoreBtn");
        const btnText = document.getElementById("btnText");
        if (btn && btnText) {
            // Ambil teks 'more' yang sudah disiapkan PHP sesuai bahasa aktif
            btnText.innerText = btn.getAttribute('data-more');
        }
    });

    // Promo Section
    document.addEventListener('DOMContentLoaded', function() {
        var tutupBtn = document.querySelector('.tutup-btn');
        var promoSection = document.getElementById('promo-section');
        var serviceContainer = document.getElementById('service-container'); // Ambil kontainer service
        
        if (tutupBtn && promoSection) {
            tutupBtn.addEventListener('click', function() {
                // Animasi tutup promo seperti sebelumnya
                promoSection.style.opacity = '0';
                promoSection.style.height = '0';
                
                // --- AKAL-AKALAN DINAMIS ---
                // Begitu promo ditutup, kecilkan padding atas service secara otomatis
                if (serviceContainer) {
                    serviceContainer.style.paddingTop = '20px'; 
                    serviceContainer.style.transition = 'padding 0.5s ease'; // Biar gerakannya halus
                }
                
                setTimeout(function() {
                    promoSection.style.display = 'none';
                }, 500);
            });
        }
    });

    // Skills Section
    document.addEventListener("DOMContentLoaded", function() {
        const skills = document.querySelectorAll('.skill.reveal');

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    const target = mutation.target;
                    if (target.classList.contains('active')) {
                        const bar = target.querySelector('.skill-percentage');
                        if (bar) {
                            const pct = bar.getAttribute('data-percentage');
                            // Jalankan animasi dengan mengubah style width langsung
                            // setTimeout memberikan sedikit jeda agar class active benar-benar terpasang
                            setTimeout(() => {
                                bar.style.setProperty('width', pct, 'important');
                            }, 100);
                        }
                    }
                }
            });
        });

        skills.forEach(skill => {
            observer.observe(skill, { attributes: true });
        });
    });
    
// === PORTFOLIO SECTION FINAL (WITH ANIMATION) ===
$(document).ready(function() {
    
    // === 0. Manual Dropdown Trigger dengan Animasi ===
    $(document).on('click', '.custom-dropdown .dropdown-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const parent = $(this).parent();
        const menu = parent.find('.dropdown-menu');
        
        // Tutup dropdown lain dengan animasi fadeOut
        $('.dropdown-menu').not(menu).removeClass('show');
        $('.dropdown').not(parent).removeClass('show');

        // Toggle dropdown yang diklik
        parent.toggleClass('show');
        menu.toggleClass('show');
    });

    // Tutup dropdown jika klik di luar area menu
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.custom-dropdown').length) {
            $('.dropdown-menu, .dropdown').removeClass('show');
        }
    });

    // === 1. Grid Switcher ===
    $(document).on('click', '.grid-switcher', function(e) {
        e.preventDefault();
        const gridValue = $(this).data('grid');
        const container = $('#portfolio-container-parent');
        
        // Beri animasi transisi saat grid berubah
        container.fadeOut(200, function() {
            container.find('.portfolio-item')
                .removeClass('col-lg-6 col-lg-4 col-lg-3')
                .addClass('col-lg-' + gridValue);
            container.fadeIn(300);
        });

        // Update teks tombol
        $(this).closest('.dropdown').find('.dropdown-toggle').html('<i class="fas fa-th-large me-2"></i>' + $(this).text());
        $('.dropdown-menu, .dropdown').removeClass('show');
    });

    // === 2. Filter Katalog ===
    $(document).on('click', '.filter-portfolio-item', function(e) {
        e.preventDefault();
        const filter = $(this).data('filter');

        $('.filter-portfolio-item').removeClass('active');
        $(this).addClass('active');
        
        $('#filterDropdown').html('<i class="fas fa-filter me-2"></i>' + $(this).text());

        if (filter === 'all') {
            $('.portfolio-item').stop(true, true).fadeIn(400);
        } else {
            $('.portfolio-item').stop(true, true).hide();
            $(`.portfolio-item[data-filter-type="${filter}"]`).stop(true, true).fadeIn(400);
        }

        $('.dropdown-menu, .dropdown').removeClass('show');
    });

    // === 3. Sorting Engine (Paten: Dinamis Multi-Bahasa) ===
    $(document).on('click', '.sort-portfolio', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const container = $('#portfolio-container-parent');
        const items = container.children('.portfolio-item').get();
        const currentSort = $btn.data('sort');
        
        // AMBIL TEKS DARI ATRIBUT DATA (HASIL PHP JSON)
        const txtNewest = $btn.attr('data-lang-newest');
        const txtOldest = $btn.attr('data-lang-oldest');

        $btn.css('pointer-events', 'none');

        container.animate({ opacity: 0, marginTop: '-20px' }, 400, function() {
            container.hide();

            items.sort(function(a, b) {
                const keyA = parseInt($(a).data('id'));
                const keyB = parseInt($(b).data('id'));
                return (currentSort === 'newest') ? (keyA - keyB) : (keyB - keyA);
            });

            $.each(items, function(i, li) { container.append(li); });

            // UPDATE TEKS BERDASARKAN BAHASA JSON
            if (currentSort === 'newest') {
                $btn.data('sort', 'oldest').html('<i class="fas fa-sort-amount-down-alt me-2"></i>' + txtOldest);
            } else {
                $btn.data('sort', 'newest').html('<i class="fas fa-sort-amount-down me-2"></i>' + txtNewest);
            }

            container.show().css('marginTop', '20px').animate({ opacity: 1, marginTop: '0px' }, 500, function() {
                $btn.css('pointer-events', 'auto');
            });
        });
    });

    // === 4. Modal Handlers (Tetap Paten) ===
    $(document).on('click', '.close-modal, .portfolio-modal-overlay', function(e) {
        if (e.target !== this && !$(e.target).hasClass('close-modal')) return;
        closePortfolioDetail();
    });
});

/**
 * 5. Global Functions (Show/Close Portfolio)
 */
function showPortfolioDetail(data) {
    $('#modalImg').attr('src', '').hide(); 
    $('#modalTitle').text(data.title);
    $('#modalCat').text(data.category); // Menampilkan kategori yang sudah diterjemahkan
    $('#modalDesc').text(data.desc || 'No description available.');
    $('#modalPrice').text(data.price);
    
    // Update Teks Tombol Modal
    $('#modalLink').text(data.btnText); 
    
    $('#modalImg').attr('src', data.img).on('load', function() { $(this).fadeIn(500); });
    
    if(data.link && data.link !== '#') $('#modalLink').attr('href', data.link).show();
    else $('#modalLink').hide();
    
    $('#portfolioModal').css('display', 'flex').hide().fadeIn(300);
    $('body').addClass('modal-open-blur');
}

function closePortfolioDetail() {
    $('#portfolioModal').fadeOut(300, function() {
        $(this).css('display', 'none');
        $('body').removeClass('modal-open-blur');
    });
}

// Opsional: Logika agar Alert tetap tertutup dalam satu sesi (Session Storage)
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-naufaru');
    alerts.forEach((alert, index) => {
        const alertId = "alert_closed_" + index; // Sederhana, bisa diganti dengan ID dari DB
        if (sessionStorage.getItem(alertId)) {
            alert.style.display = 'none';
        }

        alert.querySelector('.btn-close-custom').addEventListener('click', () => {
            sessionStorage.setItem(alertId, 'true');
        });
    });
});

// Cukup gunakan skrip ini jika ingin fungsi close bekerja standar tanpa menyimpan status 'hidden'
document.addEventListener('DOMContentLoaded', function() {
    // Tidak ada sessionStorage.setItem di sini.
    // Alert akan hilang saat di-klik (fungsi bawaan bootstrap), 
    // tapi karena tidak disimpan di storage, saat refresh PHP akan merender ulang dari DB.
    console.log("Alerts system ready. Data fetched from DB.");
});

// Close Button
document.addEventListener('DOMContentLoaded', function() {
    // Menangani penutupan alert secara manual jika Bootstrap JS tidak termuat sempurna
    const closeButtons = document.querySelectorAll('.btn-close-naufaru');
    
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alertBox = this.parentElement;
            alertBox.classList.remove('show');
            setTimeout(() => {
                alertBox.remove();
            }, 300);
        });
    });
});

// Load More
$(document).ready(function() {
    const itemsPerPage = 10;
    let currentVisible = itemsPerPage;

    function updateGalleryVisibility() {
        // Ambil semua item yang tidak sedang disembunyikan oleh FILTER kategori
        const filteredItems = $('.portfolio-item').filter(function() {
            return $(this).css('display') !== 'none' || $(this).hasClass('d-none-load-more');
        });

        // Sembunyikan semua item terlebih dahulu dari sistem load more
        filteredItems.addClass('d-none-load-more');

        // Tampilkan hanya sebanyak currentVisible
        filteredItems.slice(0, currentVisible).removeClass('d-none-load-more').addClass('animate-load-more');

        // Sembunyikan tombol jika item yang tersisa sudah habis
        if (currentVisible >= filteredItems.length) {
            $('#load-more-container').fadeOut();
        } else {
            $('#load-more-container').fadeIn();
        }
    }

    // Inisialisasi awal saat refresh (Kembali ke 10)
    updateGalleryVisibility();

    // Event Klik Tombol Lihat Selengkapnya
    $('#btn-load-more').on('click', function() {
        currentVisible += itemsPerPage;
        updateGalleryVisibility();
    });

    // SINKRONISASI DENGAN FILTER (Penting!)
    // Update fungsi filter Anda yang sudah ada agar memanggil updateGalleryVisibility(true)
    $(document).on('click', '.filter-portfolio-item', function() {
        // ... (kode filter lama Anda) ...
        
        // Reset pandangan ke 10 setiap kali ganti filter
        currentVisible = itemsPerPage;
        setTimeout(updateGalleryVisibility, 450); // Delay sedikit menunggu animasi fade filter selesai
    });
});
 
