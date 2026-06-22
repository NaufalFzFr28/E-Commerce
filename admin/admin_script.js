// admin_script.js

class TextScramble {
  constructor(el) {
    this.el = el;
    this.chars = '!<>-_\\/[]{}—=+*^?#________';
    this.update = this.update.bind(this);
  }
  setText(newText) {
    const oldText = this.el.innerText;
    const length = Math.max(oldText.length, newText.length);
    const promise = new Promise((resolve) => this.resolve = resolve);
    this.queue = [];
    for (let i = 0; i < length; i++) {
      const from = oldText[i] || '';
      const to = newText[i] || '';
      const start = Math.floor(Math.random() * 40);
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

// Di dalam admin_script.js
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggleBtn');
    const body = document.body;

    // AMBIL STATUS TANPA RELOAD
    if (localStorage.getItem('adminSidebar') === 'collapsed') {
        body.classList.add('collapsed');
    }

    if (toggleBtn) {
        toggleBtn.onclick = function() {
            body.classList.toggle('collapsed');
            // Simpan status tanpa me-refresh halaman
            const status = body.classList.contains('collapsed') ? 'collapsed' : 'expanded';
            localStorage.setItem('adminSidebar', status);
        };
    }

    // --- LOGIKA SCRAMBLE TEXT ---
    const scrambleElements = document.querySelectorAll('.scramble-text');
    scrambleElements.forEach(el => {
        const fx = new TextScramble(el);
        const originalText = el.getAttribute('data-value');
        const parentLink = el.closest('.nav-link');
        if (parentLink) {
            parentLink.addEventListener('mouseenter', () => fx.setText(originalText));
        }
    });
});

//=== About Section ===//
let currentLangAbout = 0;

function moveLangAbout(direction) {
    const wrapper = document.getElementById('langWrapperAbout');
    const label = document.getElementById('currentLangLabelAbout');
    const dots = document.querySelectorAll('.dot-lang-about');
    
    // Konfigurasi Label & Warna
    const labels = ["BAHASA INDONESIA", "ENGLISH VERSION", "JAPANESE VERSION"];
    const colors = ["#dc3545", "#0d6efd", "#198754"]; 

    currentLangAbout += direction;

    // Proteksi Loop (0-2)
    if (currentLangAbout < 0) currentLangAbout = 2;
    if (currentLangAbout > 2) currentLangAbout = 0;

    // Eksekusi Geser Kontainer
    wrapper.style.transform = `translateX(-${currentLangAbout * 33.333}%)`;
    
    // Update Teks & Warna Dinamis
    label.innerText = labels[currentLangAbout];
    label.style.color = colors[currentLangAbout];

    // Update Indikator Titik
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentLangAbout);
    });
}

//=== Promo Section ===//

// Logika Kontrol Promo Section
document.addEventListener('DOMContentLoaded', function() {
    const promoSection = document.getElementById('promo-section');
    const tutupBtn = document.querySelector('.tutup-btn');

    // Cek apakah user sudah pernah menutup promo di sesi ini
    if (!sessionStorage.getItem('promoClosed')) {
        // Tampilkan promo dengan delay sedikit agar efek reveal terasa
        setTimeout(() => {
            if(promoSection) promoSection.classList.add('active');
        }, 2000);
    }

    if (tutupBtn) {
        tutupBtn.addEventListener('click', function() {
            promoSection.style.display = 'none';
            // Simpan status agar tidak muncul lagi hanya dalam sesi ini
            sessionStorage.setItem('promoClosed', 'true');
        });
    }
});

//=== Skills Section ===//

// Fungsi untuk menambah baris input skill baru secara dinamis di UI
function addNewSkillRow() {
    const container = document.getElementById('skills-list-container');
    const newRow = document.createElement('div');
    newRow.className = 'skill-row-item animate__animated animate__fadeInUp';
    newRow.style = 'background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 15px; border-radius: 12px; margin-bottom: 15px; position: relative;';

    newRow.innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 80px; gap: 10px; margin-bottom: 10px;">
            <input type="text" name="skill_name_id[]" class="input-glass" placeholder="Nama (ID)" required>
            <input type="text" name="skill_name_en[]" class="input-glass" placeholder="Name (EN)" required>
            <input type="text" name="skill_name_jp[]" class="input-glass" placeholder="名前 (JP)" required>
            <input type="number" name="percentage[]" class="input-glass" placeholder="%" min="0" max="100" style="text-align: center;" required>
        </div>
        <button type="button" class="btn-delete-img" style="position: absolute; top: -10px; right: -10px; width: 25px; height: 25px; padding: 0 !important; border-radius: 50%;" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(newRow);
}

// Validasi Input Persentase (Mencegah angka > 100 atau < 0)
document.addEventListener('input', function(e) {
    if (e.target.name === 'percentage[]' || e.target.name === 'new_percentage') {
        let val = parseInt(e.target.value);
        if (val > 100) e.target.value = 100;
        if (val < 0) e.target.value = 0;
    }
});

//=== Portfolio Section ===//

/**
 * 0. GLOBAL VARIABLES FOR MODAL SAFETY
 */
let isMouseDownInsideModal = false;

/**
 * 1. FUNGSI MODAL POPUP (EDIT)
 * Mengisi data secara otomatis dari database ke dalam form modal.
 */
function openEditModal(data) {
    const modal = document.getElementById('editModal');
    if (!modal) return;

    // Reset form untuk membersihkan input file dari sesi sebelumnya
    const form = modal.querySelector('form');
    if(form) form.reset();

    // Inject ID dan Harga
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_price_original').value = data.price_original || 0;
    
    // Inject Judul Multibahasa
    document.getElementById('edit_title_id').value = data.title_id || '';
    document.getElementById('edit_title_en').value = data.title_en || '';
    document.getElementById('edit_title_jp').value = data.title_jp || '';
    
    // Inject Link & Deskripsi Multibahasa
    document.getElementById('edit_link_url').value = data.link_url || '';
    document.getElementById('edit_desc_id').value = data.desc_id || '';
    document.getElementById('edit_desc_en').value = data.desc_en || '';
    document.getElementById('edit_desc_jp').value = data.desc_jp || '';

    /**
     * LOGIKA DROPDOWN KUSTOM DI MODAL (KATEGORI)
     * Sinkronisasi teks dropdown kustom dengan data kategori dari database.
     */
    const hiddenInput = document.getElementById('edit_product_id');
    const selectedText = document.getElementById('edit_selected_text');
    
    if(hiddenInput && selectedText) {
        hiddenInput.value = data.product_id;
        const option = document.querySelector(`#editProductSelect .custom-option[data-value="${data.product_id}"]`);
        if(option) {
            selectedText.innerText = option.innerText;
            // Update state visual terpilih di list opsi
            document.querySelectorAll('#editProductSelect .custom-option').forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
        }
    }

    // Tampilkan Modal dengan Animasi Bounce
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('active');
        document.body.classList.add('modal-open'); // Lock scroll body
    }, 10);
    
    // Reset posisi scroll modal ke atas setiap kali dibuka
    const scrollBody = modal.querySelector('.modal-body-scroll');
    if(scrollBody) scrollBody.scrollTop = 0;
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (!modal) return;

    modal.classList.add('closing'); // Trigger animasi zoom-out
    setTimeout(() => {
        modal.classList.remove('active', 'closing');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }, 350);
}

/**
 * 2. FUNGSI DELETE DENGAN KONFIRMASI (SWEETALERT2)
 */
function confirmDeletePortfolio(id) {
    Swal.fire({
        title: 'Hapus Katalog?',
        text: "Data dan file gambar akan dihapus permanen dari server.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4c4d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'proses_update_portfolio.php?delete_id=' + id;
        }
    });
}

/**
 * 3. FUNGSI GLOBAL CUSTOM SELECT (DROPDOWN)
 * Mengatur perilaku dropdown kustom dan filter kategori di galeri.
 */
function setupCustomSelect(wrapperId, isFilter = false) {
    const wrapper = document.getElementById(wrapperId);
    if (!wrapper) return;

    const trigger = wrapper.querySelector('.custom-select-trigger');
    const options = wrapper.querySelectorAll('.custom-option');
    const hiddenInput = wrapper.querySelector('input[type="hidden"]');
    const selectedText = wrapper.querySelector('span');

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        // Tutup dropdown lain yang terbuka agar tidak tumpang tindih
        document.querySelectorAll('.custom-select-wrapper').forEach(w => {
            if(w !== wrapper) w.classList.remove('open');
        });
        wrapper.classList.toggle('open');
    });

    options.forEach(option => {
        option.addEventListener('click', function() {
            const val = this.getAttribute('data-value');
            if(hiddenInput) hiddenInput.value = val;
            selectedText.innerText = this.innerText;
            
            options.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            wrapper.classList.remove('open');

            // Logika Filter Galeri Real-time
            if(isFilter) {
                const items = document.querySelectorAll('.gallery-item');
                items.forEach(item => {
                    if (val === 'all' || item.classList.contains(val)) {
                        item.style.display = 'block';
                        item.style.opacity = '0';
                        setTimeout(() => { item.style.opacity = '1'; }, 10);
                    } else { 
                        item.style.display = 'none'; 
                    }
                });
            }
        });
    });
}

/**
 * 4. INITIALIZATION (MENJALANKAN FUNGSI SAAT HALAMAN SIAP)
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Dropdowns (Luar & Dalam Modal)
    setupCustomSelect('productSelect');            // Form Tambah
    setupCustomSelect('galleryFilterWrapper', true); // Filter List Galeri
    setupCustomSelect('editProductSelect');        // Dropdown di Modal Edit

    // Penanganan Notifikasi SweetAlert berdasarkan Status URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        const status = urlParams.get('status');
        let config = { 
            timer: 3000, 
            showConfirmButton: false, 
            timerProgressBar: true, 
            confirmButtonColor: '#ef4c4d' 
        };
        
        const successList = ['success', 'success_portfolio', 'success_delete_portfolio', 'success_about', 'success_promo', 'success_skill'];

        if (successList.includes(status)) {
            config.icon = 'success';
            config.title = 'Berhasil!';
            if(status === 'success_portfolio') config.text = 'Katalog karya berhasil diperbarui.';
            else if(status === 'success_delete_portfolio') config.text = 'Katalog telah berhasil dihapus.';
            else config.text = 'Perubahan data berhasil disimpan.';
        } else if (status === 'error_empty') {
            config.icon = 'warning';
            config.title = 'Data Tidak Lengkap';
            config.text = 'Judul, Harga, dan Kategori wajib diisi.';
        } else {
            config.icon = 'error';
            config.title = 'Gagal!';
            config.text = 'Terjadi kesalahan sistem atau gagal upload gambar.';
            config.showConfirmButton = true; 
            config.timer = null;
        }

        Swal.fire(config).then(() => {
            // Bersihkan URL parameter tanpa reload halaman
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
});

/**
 * 5. GLOBAL CLICK LISTENERS (HANDLING KLIK LUAR ELEMEN)
 */
window.addEventListener('click', function(e) {
    // Tutup dropdown jika klik dilakukan di luar area dropdown
    if (!e.target.closest('.custom-select-wrapper')) {
        document.querySelectorAll('.custom-select-wrapper').forEach(w => w.classList.remove('open'));
    }
    
    // Tutup modal jika klik dilakukan pada area overlay (di luar box modal)
    const modal = document.getElementById('editModal');
    if (e.target === modal) {
        closeEditModal();
    }
});

let adminGalleryCurrentPage = 0;
const adminGalleryItemsPerPage = 10;
let adminGalleryFilteredItems = [];

function initAdminGallerySlider() {
    const rawItems = Array.from(document.querySelectorAll('#rawGalleryData .gallery-item'));
    adminGalleryFilteredItems = rawItems;
    setupAdminFilterDropdown();
    buildAdminGalleryPages();
}

function buildAdminGalleryPages() {
    const wrapper = document.getElementById('adminGalleryWrapper');
    const navControl = document.getElementById('adminGalleryNav');
    
    wrapper.innerHTML = ''; 
    adminGalleryCurrentPage = 0; 

    if (adminGalleryFilteredItems.length === 0) {
        wrapper.innerHTML = '<div style="width:100%; text-align:center; padding:50px; opacity:0.5;">Tidak ada produk ditemukan.</div>';
        navControl.style.display = 'none';
        adjustMainContainerHeight();
        return;
    }

    const totalPages = Math.ceil(adminGalleryFilteredItems.length / adminGalleryItemsPerPage);
    navControl.style.display = totalPages > 1 ? 'flex' : 'none';

    for (let i = 0; i < totalPages; i++) {
        const pageDiv = document.createElement('div');
        pageDiv.className = 'gallery-page';
        pageDiv.style.cssText = 'min-width: 100%; box-sizing: border-box;';

        const gridInner = document.createElement('div');
        gridInner.className = 'admin-katalog-grid-container';
        // Menggunakan stretch agar baris atas dan bawah tidak beradu saat teks berbeda panjang
        gridInner.style.cssText = 'display: flex; flex-wrap: wrap; margin: -15px; align-items: stretch;';

        const start = i * adminGalleryItemsPerPage;
        const pageItems = adminGalleryFilteredItems.slice(start, start + adminGalleryItemsPerPage);
        
        pageItems.forEach(item => {
            const col = item.cloneNode(true);
            // Memberikan jarak padding 15px sebagai "buffer" agar tidak nabrak
            col.style.cssText = 'flex: 0 0 50%; max-width: 50%; padding: 15px; box-sizing: border-box; display: flex;';
            const cardBox = col.querySelector('.admin-katalog-card-box');
            if(cardBox) cardBox.style.width = '100%';
            
            gridInner.appendChild(col);
        });

        pageDiv.appendChild(gridInner);
        wrapper.appendChild(pageDiv);
    }
    updateAdminGalleryUI();
}

function adjustMainContainerHeight() {
    const wrapper = document.getElementById('adminGalleryWrapper');
    const mainContainer = document.getElementById('adminGalleryMainContainer');
    
    setTimeout(() => {
        const activePage = wrapper.children[adminGalleryCurrentPage];
        if (activePage) {
            // Menghitung tinggi asli konten tanpa memaksa min-height besar
            const contentHeight = activePage.offsetHeight + 150; 
            mainContainer.style.height = contentHeight + "px";
        }
    }, 150);
}

function moveAdminGallery(dir) {
    const totalPages = Math.ceil(adminGalleryFilteredItems.length / adminGalleryItemsPerPage);
    adminGalleryCurrentPage += dir;
    if (adminGalleryCurrentPage >= totalPages) adminGalleryCurrentPage = 0;
    if (adminGalleryCurrentPage < 0) adminGalleryCurrentPage = totalPages - 1;
    updateAdminGalleryUI();
}

function updateAdminGalleryUI() {
    const wrapper = document.getElementById('adminGalleryWrapper');
    const label = document.getElementById('adminGalleryPageLabel');
    const dotContainer = document.getElementById('adminGalleryDots');
    const totalPages = Math.ceil(adminGalleryFilteredItems.length / adminGalleryItemsPerPage);

    // Transisi bergeser murni tanpa efek naik/turun
    wrapper.style.transform = `translateX(-${adminGalleryCurrentPage * 100}%)`;
    label.innerText = `HALAMAN ${adminGalleryCurrentPage + 1} DARI ${totalPages || 1}`;

    dotContainer.innerHTML = '';
    for (let i = 0; i < totalPages; i++) {
        const dot = document.createElement('div');
        dot.className = `dot-lang-about ${i === adminGalleryCurrentPage ? 'active' : ''}`;
        dot.onclick = () => { adminGalleryCurrentPage = i; updateAdminGalleryUI(); };
        dotContainer.appendChild(dot);
    }
    
    adjustMainContainerHeight();
}

function setupAdminFilterDropdown() {
    const wrapper = document.getElementById('adminGalleryFilter');
    if (!wrapper) return;
    const trigger = document.getElementById('adminFilterTrigger');
    const options = wrapper.querySelectorAll('.custom-option');
    const label = document.getElementById('admin_filter_label');

    trigger.onclick = function(e) {
        e.stopPropagation();
        wrapper.classList.toggle('open');
    };

    options.forEach(opt => {
        opt.onclick = function() {
            const val = this.getAttribute('data-value');
            label.innerText = this.innerText;
            options.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            wrapper.classList.remove('open');

            const allRawItems = Array.from(document.querySelectorAll('#rawGalleryData .gallery-item'));
            adminGalleryFilteredItems = (val === 'all') ? allRawItems : allRawItems.filter(i => i.classList.contains(val));
            buildAdminGalleryPages();
        };
    });
}

window.addEventListener('click', function(e) {
    const filter = document.getElementById('adminGalleryFilter');
    if (filter && !e.target.closest('#adminGalleryFilter')) {
        filter.classList.remove('open');
    }
});

document.addEventListener('DOMContentLoaded', initAdminGallerySlider);

/* =================================================== */
/* Script Admin Katalog */
/* =================================================== */

function openAddModal() {
    const modal = document.getElementById('addModal');
    modal.classList.remove('closing');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Lock scroll body saat modal buka
}

function closeAddModal() {
    const modal = document.getElementById('addModal');
    modal.classList.add('closing');
    
    // Tunggu animasi closing selesai (300ms) baru hilangkan display
    setTimeout(() => {
        modal.classList.remove('active');
        modal.classList.remove('closing');
        document.body.style.overflow = 'auto';
    }, 300);
}

// Tambahkan listener untuk menutup modal jika klik di luar area kartu (overlay)
window.onclick = function(event) {
    const modal = document.getElementById('addModal');
    if (event.target == modal) {
        closeAddModal();
    }
}

/* =================================================== */
/* Script Admin Team */
/* =================================================== */

/**
 * Fungsi Centralized Notifikasi Modul Team (Premium Dark Mode)
 * File Target: admin/admin_script.js (Murni JavaScript)
 */
function tampilkanAlertTeam(status) {
    // Konfigurasi dasar SweetAlert premium bertema gelap NaufaRu
    let config = {
        timer: 2500,
        showConfirmButton: false,
        timerProgressBar: true,
        background: '#1a1a1a',
        color: '#fff',
        confirmButtonColor: '#EF4C4D'
    };

    // 1. Deteksi Status Berhasil Tambah Anggota Tim
    if (status === 'success_team') {
        config.icon = 'success';
        config.title = 'Anggota Ditambahkan!';
        config.text = 'Profil baru anggota tim kolaborator sukses dipublikasikan.';

    // 2. Deteksi Status Berhasil Memperbarui Warna Gradasi Hover
    } else if (status === 'success_team_color') {
        config.icon = 'success';
        config.title = 'Tema Disimpan!';
        config.text = 'Variabel gradasi hover global tim sukses diperbarui.';

    // 3. Deteksi Status Berhasil Mengubah Visibilitas (Aktif/Sembunyi)
    } else if (status === 'success_team_toggle') {
        config.icon = 'success';
        config.title = 'Visibilitas Berubah!';
        config.text = 'Status grid anggota tim berhasil disesuaikan di layar utama.';

    // 4. Deteksi Status Berhasil Menghapus Anggota Permanen
    } else if (status === 'success_team_delete') {
        config.icon = 'success';
        config.title = 'Berhasil Dihapus!';
        config.text = 'Data anggota beserta file fotonya telah dibersihkan secara permanen.';

    // 5. UPDATE FITUR: Deteksi Status Berhasil Mengubah Rincian & Urutan Grid Anggota Tim (Edit Save)
    } else if (status === 'success_team_update') {
        config.icon = 'success';
        config.title = 'Perubahan Disimpan!';
        config.text = 'Rincian data keahlian dan profil urutan anggota tim berhasil diperbarui.';

    // 6. Deteksi Gagal dengan Pesan Error Dinamis dari HTML Bridge
    } else if (status === 'failed_team_msg') {
        // Ambil data error PHP yang sudah dititipkan di HTML Bridge
        const sessionBridge = document.getElementById('php-session-bridge');
        let errorMessage = 'Periksa kembali berkas atau data inputan Anda.';
        
        if (sessionBridge) {
            const phpError = sessionBridge.getAttribute('data-team-error');
            if (phpError && phpError.trim() !== '') {
                errorMessage = phpError;
            }
        }

        config.icon = 'error';
        config.title = 'Operasi Gagal!';
        config.html = `<span style="font-size:0.85rem; opacity:0.8;">${errorMessage}</span>`;
        config.showConfirmButton = true;
        config.timer = null; // Admin harus klik OK untuk menutup

    // 7. Deteksi Gagal Umum (Sistem/Database Error)
    } else if (status === 'failed_team') {
        config.icon = 'error';
        config.title = 'Sistem Error!';
        config.text = 'Terjadi kendala kueri database atau file pemroses hilang.';
    }

    // Jalankan SweetAlert jika status parameter valid
    if (status) {
        Swal.fire(config);
    }
}

/**
 * Pemicu Otomatis saat Halaman Selesai Dimuat (DOMContentLoaded)
 * Berfungsi mendeteksi parameter '?status=' pada URL browser
 */
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        const statusValue = urlParams.get('status');
        
        // Panggil fungsi alert utama
        tampilkanAlertTeam(statusValue);
        
        // Bersihkan parameter URL tanpa refresh halaman agar alert tidak muncul kembali saat di-refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});