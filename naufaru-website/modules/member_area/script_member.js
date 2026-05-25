/**
 * File: member_area/script_member.js
 * Deskripsi: Logika interaktif khusus Dashboard Member NaufaRu
 */

// Scramble Effect
document.addEventListener("DOMContentLoaded", () => {
    const scrambleElements = document.querySelectorAll(".scramble-text");
    const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    scrambleElements.forEach(el => {
        let interval = null;
        let iteration = 0;
        const originalValue = el.dataset.value;

        clearInterval(interval);

        interval = setInterval(() => {
            el.innerText = originalValue
                .split("")
                .map((letter, index) => {
                    if (index < iteration) {
                        return originalValue[index];
                    }
                    return letters[Math.floor(Math.random() * 26)];
                })
                .join("");

            if (iteration >= originalValue.length) {
                clearInterval(interval);
            }
            iteration += 1 / 3;
        }, 30);
    });
});

// 1. Fungsi Pembantu Internal (Menghindari ketergantungan luar yang merusak sistem)
const updateMemberModeUI = (isDark) => {
    const icon = $('#modeIcon');
    const text = $('#modeText');
    const btn = $('#modeToggleBtn');
    
    if (btn.length > 0) {
        const textDark = btn.data('dark') || 'Mode Gelap';
        const textLight = btn.data('light') || 'Mode Terang';
        
        if (text.length > 0) text.text(isDark ? textLight : textDark);
        if (icon.length > 0) icon.attr('class', isDark ? 'fas fa-sun' : 'fas fa-circle-half-stroke');
    }
};

$(document).ready(function() {
    // Inisialisasi UI Mode saat load
    const isDark = $('body').hasClass('dark-mode');
    updateMemberModeUI(isDark);

    // 2. Inisialisasi Fungsi Tutup Semua Dropdown
    window.closeAllDropdowns = () => {
        $('#profileDropdownContent').fadeOut(200);
        $('#navDropdownContent').fadeOut(200);
        $('#burgerToggleBtn').removeClass('is-active');
        $('#navDropdownContent').removeClass('show-lang'); 
    };

    // 3. Logika Klik Tombol Profil
    $('#profileToggleBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const isOpen = $('#profileDropdownContent').is(':visible');
        window.closeAllDropdowns(); 

        if (!isOpen) {
            $('#profileDropdownContent').stop(true, true).fadeIn(300);
        }
    });

    // 4. Logika Klik Tombol Burger (Navigasi)
    $('#burgerToggleBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const isOpen = $('#navDropdownContent').is(':visible');
        window.closeAllDropdowns(); 

        if (!isOpen) {
            $(this).addClass('is-active');
            $('#navDropdownContent').stop(true, true).fadeIn(300);
        }
    });

    // 5. Logika Submenu Bahasa
    $('#openLangBtn').on('click', function(e) {
        e.stopPropagation();
        $('#navDropdownContent').addClass('show-lang');
    });

    $('#backToMenuBtn').on('click', function(e) {
        e.stopPropagation();
        $('#navDropdownContent').removeClass('show-lang');
    });

    // 6. Proteksi Klik Internal Menu
    $('#profileDropdownContent, #navDropdownContent').on('click', function(e) {
        e.stopPropagation();
    });

    // 7. Klik Global untuk Menutup Dropdown
    $(document).on('click', function() {
        window.closeAllDropdowns();
    });
});

// 8. FUNGSI MODAL PROFIL (Global agar bisa dipanggil via onclick HTML)
function openProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'auto'; 
    }
}

function closeProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; 
    }
}

// Tutup modal jika klik di area overlay
window.addEventListener('click', function(event) {
    const modal = document.getElementById('profileModal');
    if (event.target == modal) {
        closeProfileModal();
    }
});

/**
 * 9. FUNGSI POPUP KATALOG (SweetAlert2)
 */
function showProductDetail(data) {
    if (typeof Swal === 'undefined') {
        alert("Error: SweetAlert2 belum termuat.");
        return;
    }

    Swal.fire({
        title: `<span style="color: #fff;">${data.title}</span>`,
        html: `
            <div style="text-align: center;">
                <img src="${data.img}" class="img-fluid rounded-4 mb-3" 
                     style="max-height: 250px; width: 100%; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);"
                     onerror="this.src='../../assets/imgs/placeholder.png'">
                <div style="margin-bottom: 15px;">
                    <span class="badge" style="background: rgba(239, 76, 77, 0.1); color: #EF4C4D; border: 1px solid #EF4C4D; padding: 8px 15px; border-radius: 50px;">${data.category}</span>
                </div>
                <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem; line-height: 1.6;">${data.desc || 'Tidak ada deskripsi tersedia.'}</p>
                <h3 style="color: #4cd137; font-weight: 800; margin-top: 20px;">${data.price}</h3>
            </div>
        `,
        background: 'rgba(20, 20, 20, 0.95)',
        backdrop: 'rgba(0, 0, 0, 0.8) blur(10px)',
        showConfirmButton: false,
        showCloseButton: true,
        width: '450px',
        customClass: {
            popup: 'glass-card'
        }
    });
}

// Grid 5 Kolom

// Fungsi Minimalis untuk Modal Profil dan Detail
    function openDetail(data) {
        // Logika untuk menampilkan SweetAlert atau Modal Produk
        Swal.fire({
            title: data.product_name,
            text: data.deskripsi,
            imageUrl: "../../../assets/imgs/img-catalog/" + data.gambar_produk,
            imageWidth: 400,
            imageHeight: 400,
            imageAlt: 'Product Image',
            background: '#1a1a1a',
            color: '#fff',
            confirmButtonColor: '#EF4C4D',
            confirmButtonText: 'Tutup'
        });
    }

    // Dropdown Profil Member (Mencegah Error Konsol)
    document.addEventListener('DOMContentLoaded', function() {
        const profileBtn = document.getElementById('profileToggleBtn');
        const profileMenu = document.getElementById('profileDropdownContent');
        
        if(profileBtn && profileMenu) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileMenu.classList.toggle('active');
            });
        }
    });

function openNaufaruModal(data) {
    const modal = document.getElementById('naufaruModal');
    
    const imgEl = document.getElementById('m-img');
    const titleEl = document.getElementById('m-title');
    const catEl = document.getElementById('m-cat-text');
    const descEl = document.getElementById('m-desc');
    const priceEl = document.getElementById('m-price');
    const btnAddToCart = document.getElementById('btn-add-to-cart'); // Targetkan tombol baru

    if(imgEl) imgEl.src = "../../../assets/imgs/img-catalog/" + data.gambar_produk;
    if(titleEl) titleEl.innerText = data.product_name;
    if(catEl) catEl.innerText = data.kategori || "PRODUK";
    if(descEl) descEl.innerText = data.deskripsi;
    if(priceEl) priceEl.innerText = "Rp " + parseInt(data.price).toLocaleString('id-ID');

    // Tambahkan ID Produk ke attribute tombol agar bisa dibaca fungsi addToCart
    if(btnAddToCart) {
        btnAddToCart.setAttribute('onclick', `addToCart(${data.id})`);
    }

    modal.style.display = 'flex';
    
    setTimeout(() => {
        modal.classList.add('active');
        document.documentElement.classList.add('modal-active-naufaru');
        document.body.classList.add('modal-active-naufaru');
    }, 10);
}

function closeNaufaruModal() {
    const modal = document.getElementById('naufaruModal');
    
    // Mulai animasi tutup
    modal.classList.remove('active');
    
    setTimeout(() => {
        modal.style.display = 'none';
        
        // Menghapus class pengunci scroll
        document.documentElement.classList.remove('modal-active-naufaru');
        document.body.classList.remove('modal-active-naufaru');
        
        // Halaman tidak akan bergulir ke atas karena posisi window.scrollY tidak pernah berubah
    }, 400); 
}

/**
 * Fungsi Menutup Modal dengan Animasi Out
 */
function closePortfolioDetail() {
    const modal = $('#portfolioModal');
    
    // Tambahkan class closing untuk memicu animasi CSS Out
    modal.addClass('closing');
    
    // Tunggu animasi selesai (300ms sesuai durasi transition di CSS)
    setTimeout(function() {
        modal.fadeOut(10, function() {
            $(this).css('display', 'none').removeClass('closing');
            $('body').removeClass('modal-open-blur');
        });
    }, 300);
}

// Pastikan Event Listener memanggil fungsi yang sama
$(document).on('click', '.close-modal, .portfolio-modal-overlay', function(e) {
    // Jika yang diklik adalah overlay atau tombol close
    if (e.target === this || $(e.target).hasClass('close-modal-naufaru') || $(e.target).closest('.close-modal-naufaru').length) {
        closePortfolioDetail();
    }
});

function closeProfileModal() {
    const modal = document.getElementById('profileModal'); // Sesuaikan dengan ID modal Anda
    
    // 1. Tambahkan class closing untuk memicu animasi CSS
    modal.classList.add('closing');
    
    // 2. Beri jeda 400ms (sesuai durasi transition di CSS) sebelum benar-benar sembunyi
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.remove('closing');
        document.body.classList.remove('modal-open-naufaru');
    }, 400);
}

// Tambahkan trigger pada tombol silang (X)
document.querySelector('.close-btn-glass').addEventListener('click', closeProfileModal);

// Tambahkan trigger klik di area luar (overlay)
window.onclick = function(event) {
    const modal = document.getElementById('profileModal');
    if (event.target == modal) {
        closeProfileModal();
    }
}

/**
 * SISTEM KERANJANG NAUFARU (AJAX)
 */

// A. Fungsi Memuat Isi Keranjang ke Section "Pesanan Saya"
function loadCart() {
    $('#cart-content').load('proses_keranjang.php?action=view', function() {
        const totalItems = $('.cart-item-row').length;
        $('#total-orders-count').text(totalItems);
    });
}

// B. Fungsi Tambah ke Keranjang
function addToCart(productId) {
    const isDarkMode = document.body.classList.contains('dark-mode');

    $.ajax({
        url: 'proses_keranjang.php',
        type: 'POST',
        data: { action: 'add', product_id: productId },
        success: function(response) {
            try {
                const res = JSON.parse(response);
                if(res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: res.title,   // Mengambil judul terjemahan aktif dari JSON server
                        text: res.message,  // Mengambil deskripsi terjemahan aktif dari JSON server
                        timer: 1500,
                        showConfirmButton: false,
                        background: isDarkMode ? 'rgba(25, 25, 25, 0.98)' : '#ffffff',
                        color: isDarkMode ? '#ffffff' : '#222222',
                        customClass: {
                            popup: isDarkMode ? 'glass-card border border-secondary' : 'shadow-lg border-0 rounded-4'
                        }
                    });
                    closeNaufaruModal();
                    loadCart(); // Refresh section Pesanan Saya secara real-time
                }
            } catch (e) {
                console.error("Gagal membaca enkapsulasi respon JSON:", e);
            }
        }
    });
}

// C. Fungsi Update Quantity
function updateQty(cartId, change) {
    $.post('proses_keranjang.php', { action: 'update', cart_id: cartId, change: change }, function() {
        loadCart();
    });
}

// D. Fungsi Hapus Item
function removeFromCart(cartId, alertTitle, confirmText, cancelText) {
    if (typeof Swal === 'undefined') return;
    const isDarkMode = document.body.classList.contains('dark-mode');

    Swal.fire({
        title: alertTitle, // Menampilkan judul terjemahan (Hapus item? / Delete item? / アイテムを削除しますか？)
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4C4D',
        cancelButtonColor: '#666',
        confirmButtonText: confirmText, // Label tombol konfirmasi dari JSON
        cancelButtonText: cancelText,   // Label tombol batal dari JSON
        background: isDarkMode ? 'rgba(25, 25, 25, 0.98)' : '#ffffff',
        color: isDarkMode ? '#ffffff' : '#222222',
        customClass: {
            popup: isDarkMode ? 'glass-card border border-secondary' : 'shadow-lg border-0 rounded-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'proses_keranjang.php',
                type: 'POST',
                data: { action: 'delete', cart_id: cartId },
                success: function() {
                    if (typeof loadCart === "function") {
                        loadCart(); // Sinkronisasi ulang data setelah penghapusan
                    }
                }
            });
        }
    });
}

// E. Jalankan loadCart saat halaman pertama kali dibuka
$(document).ready(function() {
    loadCart();
});

// MENJADI INI:
function checkoutToAdmin(alertTitle, confirmText, cancelText) {
    const isDarkMode = document.body.classList.contains('dark-mode');
    
    Swal.fire({
        title: alertTitle || 'Kirim ke Admin?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4cd137',
        cancelButtonColor: '#666',
        confirmButtonText: confirmText || 'Kirim Sekarang',
        cancelButtonText: cancelText || 'Batal',
        background: isDarkMode ? 'rgba(25, 25, 25, 0.98)' : '#ffffff',
        color: isDarkMode ? '#ffffff' : '#222222',
        customClass: {
            popup: isDarkMode ? 'glass-card border border-secondary' : 'shadow-lg border-0 rounded-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('proses_buat_pesanan.php', function(response) {
                try {
                    const res = JSON.parse(response);
                    if(res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                } catch(e) {
                    location.reload();
                }
            });
        }
    });
}

// Fungsi untuk testimonial member

