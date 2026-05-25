<?php
session_start();
include '../../config.php';
include '../../functions.php';

// --- LOGIKA PENGATURAN BAHASA PERSISTEN ---
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'id';
}

$translations = [
    'id' => [
        'title' => 'Daftar Member',
        'desc' => 'Bergabunglah dengan kami untuk mulai memesan karya terbaik.',
        'btn_reg' => 'BUAT AKUN MEMBER',
        'back' => 'Kembali ke Login',
        'user_label' => 'USERNAME BARU',
        'user_placeholder' => 'Masukkan username...',
        'pass_label' => 'PASSWORD BARU',
        'pass_placeholder' => 'Masukkan password...',
        'name_label' => 'NAMA LENGKAP',
        'name_placeholder' => 'Masukkan nama lengkap sesuai KTP...',
        'wa_label' => 'NOMOR WHATSAPP AKTIF',
        'wa_placeholder' => 'Contoh: 081234567890', 
        'address_label' => 'ALAMAT TEMPAT TINGGAL',
        'address_placeholder' => 'Tuliskan alamat lengkap pengiriman & penagihan...', 
        'photo_label' => 'FOTO PROFIL (WAJIB UNGGAH)',
        'already_member' => 'Sudah punya akun?',
        'info_title' => 'Mengapa data ini diperlukan?',
        'info_text' => 'Nomor WhatsApp digunakan untuk mempercepat konfirmasi pesanan Anda, sementara alamat diperlukan sebagai validasi resmi data penagihan (invoice). Foto profil rasio 1:1 (minimal 500x500px) diperlukan untuk personalisasi kartu member resmi NaufaRu.'
    ],
    'en' => [
        'title' => 'Register Member',
        'desc' => 'Join us to start ordering the best works.',
        'btn_reg' => 'CREATE MEMBER ACCOUNT',
        'back' => 'Back to Login',
        'user_label' => 'NEW USERNAME',
        'user_placeholder' => 'Enter username...',
        'pass_label' => 'NEW PASSWORD',
        'pass_placeholder' => 'Enter password...',
        'name_label' => 'FULL NAME',
        'name_placeholder' => 'Enter your full name...',
        'wa_label' => 'ACTIVE WHATSAPP NUMBER',
        'wa_placeholder' => 'e.g. 081234567890', 
        'address_label' => 'RESIDENTIAL ADDRESS',
        'address_placeholder' => 'Enter your complete billing address...', 
        'photo_label' => 'PROFILE PHOTO (REQUIRED)',
        'already_member' => 'Already have an account?',
        'info_title' => 'Why is this data required?',
        'info_text' => 'Your WhatsApp number is used for fast order updates, and your address is required for official billing (invoice) details. A 1:1 ratio profile photo (min 500x500px) is mandatory for your official NaufaRu member card personalization.'
    ],
    'jp' => [
        'title' => 'メンバー登録',
        'desc' => '最高の作品を注文するために参加してください。',
        'btn_reg' => 'アカウントを作成する',
        'back' => 'ログインに戻る',
        'user_label' => 'ユーザー名',
        'user_placeholder' => 'ユーザー名を入力してください...',
        'pass_label' => 'パスワード',
        'pass_placeholder' => 'パスワードを入力してください...',
        'name_label' => 'フルネーム',
        'name_placeholder' => 'フルネームを入力してください...',
        'wa_placeholder' => '有効なWhatsApp番号', 
        'wa_label' => 'WHATSAPP番号',
        'address_label' => '居住住所', 
        'address_placeholder' => '請求書の住所を入力してください...',
        'photo_label' => 'プロフィール写真（必須）',
        'already_member' => 'すでにアカウントをお持ちですか？',
        'info_title' => 'なぜこのデータが必要なのですか？',
        'info_text' => 'WhatsApp番号は注文の迅速な通知に使用され、住所は公式な請求書（インボイス）のデータ登録に必要となります。公式NaufaRu会員カードのパーソナライズには、1:1の比率のプロフィール写真（最小500x500px）が必須です。'
    ]
];

$text = $translations[$lang];

// --- TARIK DATA WALLPAPER AKTIF DARI DATABASE ---
$wallpapers = [];
$q_wall = mysqli_query($conn, "SELECT image_path FROM site_wallpaper WHERE is_active = 1 ORDER BY id ASC");
if ($q_wall && mysqli_num_rows($q_wall) > 0) {
    while ($row = mysqli_fetch_assoc($q_wall)) {
        $wallpapers[] = "../../assets/imgs/" . $row['image_path'];
    }
} else {
    $wallpapers[] = "../../assets/imgs/bg-1.jpg";
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NaufaRu | Register</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --accent: #EF4C4D;
            --accent-hover: #d43f40;
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        /* FIX UTAMA 1: Bersihkan scrollbar dari seluruh core engine browser */
        body, html {
            margin: 0; padding: 0; height: 100%;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #000;
            overflow: hidden !important; /* Blokir scrollbar horizontal & vertikal */
        }

        /* Sembunyikan scrollbar untuk Chrome, Safari, dan Opera */
        body::-webkit-scrollbar, html::-webkit-scrollbar, .login-container::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        .login-bg-container {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; overflow: hidden;
        }
        
        .login-bg-layer {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-size: cover; background-position: center;
            opacity: 0;
            transform: scale(1);
            transition: opacity 2s ease-in-out, transform 8s ease-in-out;
        }

        .login-bg-layer.active {
            opacity: 1;
            transform: scale(1.05);
        }
        
        .overlay-dark {
            position: absolute; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7); z-index: 2;
        }

        .login-container {
            position: relative; z-index: 3;
            height: 100vh; display: flex;
            justify-content: center; align-items: center;
            overflow: hidden !important;
            padding: 20px 0;
        }

        .register-card-member {
            background: rgba(20, 10, 10, 0.4);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 30px 40px;
            border-radius: 35px;
            border: 1px solid var(--glass-border);
            width: 92%;
            max-width: 800px; 
            text-align: center;
            color: white;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
            margin: auto;
        }

        .register-two-column-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; 
            gap: 20px;
            text-align: left;
        }

        .form-group-custom {
            width: 100%;
            margin-bottom: 14px; 
            text-align: left;
        }

        /* FIX UTAMA 2: Visualisasi Desain Label Penjelas Di Atas Textbox */
        .form-group-custom label {
            font-size: 0.68rem; 
            color: #ffc107; /* Warna emas tipis agar kontras & informatif */
            font-weight: 800;
            letter-spacing: 0.8px;
            margin-left: 4px;
            margin-bottom: 6px;
            display: block;
            text-transform: uppercase;
        }

        .login-input {
            box-sizing: border-box;
            width: 100%; 
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid var(--glass-border) !important;
            padding: 10px 18px;
            border-radius: 14px;
            color: white !important;
            outline: none;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .login-input:focus {
            border-color: var(--accent) !important;
            background: rgba(239, 76, 77, 0.1) !important;
        }

        .login-input[type="file"] {
            padding: 6px 12px;
            cursor: pointer;
        }

        .login-input::file-selector-button {
            background: var(--accent);
            color: white;
            border: none;
            padding: 4px 10px;
            border-radius: 8px;
            margin-right: 10px;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: bold;
            transition: 0.3s;
        }

        .login-input::file-selector-button:hover {
            background: var(--accent-hover);
        }

        .info-box-custom {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 14px;
            padding: 12px 16px;
            margin-top: 5px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .info-box-custom i {
            color: var(--accent);
            font-size: 0.95rem;
            margin-top: 2px;
        }

        .info-box-text-wrapper {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .info-box-title {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
            color: rgba(255, 255, 255, 0.9);
            text-align: left;
        }

        .info-box-desc {
            font-size: 0.7rem;
            line-height: 1.4;
            color: rgba(255, 255, 255, 0.55);
            text-align: justify !important; /* Rata kanan-kiri murni */
        }

        .btn-register-action {
            box-sizing: border-box;
            width: 100%;
            background: var(--accent) !important;
            border: none !important;
            color: white !important;
            font-weight: 700;
            padding: 13px;
            border-radius: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            grid-column: span 2; 
            margin-top: 10px;
        }

        .btn-register-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239, 76, 77, 0.4);
        }

        .footer-text { font-size: 0.8rem; margin-top: 20px; opacity: 0.8; }
        .footer-link { color: var(--accent); font-weight: bold; text-decoration: none; }

        .swal2-popup {
            background: rgba(25, 25, 25, 0.95) !important;
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 25px !important;
            color: white !important;
        }
        .swal2-confirm { background-color: var(--accent) !important; border-radius: 12px !important; }
        .swal2-timer-progress-bar { background: var(--accent) !important; }

        @media (max-width: 768px) {
            body, html, .login-container {
                overflow-y: auto !important; /* Longgarkan untuk layar hp pendek agar form tidak terpotong */
            }
            .register-card-member {
                max-width: 420px;
                padding: 25px 25px;
            }
            .register-two-column-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .btn-register-action {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>

    <!-- CONTAINER MULTI-LAYER BACKDROP BACKGROUND SLIDESHOW -->
    <div class="login-bg-container">
        <?php foreach ($wallpapers as $index => $imageUrl): ?>
            <div class="login-bg-layer <?php echo $index === 0 ? 'active' : ''; ?>" 
                 style="background-image: url('<?php echo htmlspecialchars($imageUrl); ?>');">
            </div>
        <?php endforeach; ?>
        <div class="overlay-dark"></div>
    </div>

    <div class="login-container">
        <div class="register-card-member animate__animated animate__fadeInUp">
            <h3 class="font-weight-bold mb-1" style="font-size: 1.4rem;"><?php echo $text['title']; ?></h3>
            <p style="font-size: 0.75rem; opacity: 0.6; margin-bottom: 25px;"><?php echo $text['desc']; ?></p>
            
            <form id="registerForm" enctype="multipart/form-data">
                <div class="register-two-column-grid">
                    
                    <!-- Kiri Sektor: Informasi Otentikasi & Kontak -->
                    <div>
                        <div class="form-group-custom">
                            <label><?php echo $text['user_label'] ?? 'USERNAME'; ?></label>
                            <input type="text" name="username" class="login-input" 
                                placeholder="<?php echo $text['user_placeholder']; ?>" required>
                        </div>
                        <div class="form-group-custom">
                            <label><?php echo $text['pass_label'] ?? 'PASSWORD'; ?></label>
                            <input type="password" name="password" class="login-input" 
                                placeholder="<?php echo $text['pass_placeholder']; ?>" required>
                        </div>
                        <div class="form-group-custom">
                            <label><?php echo $text['name_label'] ?? 'NAMA LENGKAP'; ?></label>
                            <input type="text" name="nama_lengkap" class="login-input" 
                                placeholder="<?php echo $text['name_placeholder']; ?>" required>
                        </div>
                        <div class="form-group-custom">
                            <label><?php echo $text['wa_label'] ?? 'WHATSAPP'; ?></label>
                            <input type="text" 
                                name="no_hp" 
                                class="login-input" 
                                placeholder="<?php echo $text['wa_placeholder']; ?>" 
                                id="inputWA"
                                maxlength="13" 
                                required>
                        </div>
                    </div>

                    <!-- Kanan Sektor: Berkas Validasi & Deskripsi Panduan -->
                    <div>
                        <div class="form-group-custom">
                            <label><?php echo $text['address_label'] ?? 'ALAMAT'; ?></label>
                            <textarea name="alamat" class="login-input" placeholder="<?php echo $text['address_placeholder']; ?>" rows="2" style="height: 104px; resize: none;" required></textarea>
                        </div>
                        <div class="form-group-custom">
                            <label><?php echo $text['photo_label']; ?></label>
                            <input type="file" name="foto_profil" class="login-input" accept="image/*" required>
                        </div>
                        
                        <div class="info-box-custom">
                            <i class="fas fa-info-circle"></i>
                            <div class="info-box-text-wrapper">
                                <span class="info-box-title"><?php echo $text['info_title']; ?></span>
                                <span class="info-box-desc"><?php echo $text['info_text']; ?></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-register-action">
                        <?php echo $text['btn_reg']; ?>
                    </button>
                    
                </div>
            </form>

            <div class="footer-text">
                <?php echo $text['already_member']; ?> 
                <a href="login_member.php" class="footer-link"><?php echo $text['back']; ?></a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Logika Animasi Pergantian Background Slideshow Otomatis
            const layers = document.querySelectorAll('.login-bg-layer');
            let currentLayerIndex = 0;
            const changeInterval = 6000;

            function nextSlideBackground() {
                if (layers.length <= 1) return;
                layers[currentLayerIndex].classList.remove('active');
                currentLayerIndex = (currentLayerIndex + 1) % layers.length;
                layers[currentLayerIndex].classList.add('active');
            }

            if (layers.length > 1) {
                setInterval(nextSlideBackground, changeInterval);
            }

            // AJAX Form Submit Handler
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: 'proses_register.php', 
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        let res = response.trim();
                        if(res === "success") {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Akun Anda berhasil dibuat.',
                                icon: 'success',
                                confirmButtonColor: '#2ecc71'
                            }).then(() => { window.location.href = "login_member.php"; });
                        } else if(res === "exists") {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Username Terpakai',
                                text: 'Username tersebut sudah digunakan orang lain. Gunakan username lain.',
                                confirmButtonColor: '#EF4C4D'
                            });
                        } else if(res === "wa_too_long") {
                            Swal.fire({
                                icon: 'error',
                                title: 'WA Tidak Valid',
                                text: 'Nomor WhatsApp terlalu panjang (Max 13 angka).',
                                confirmButtonColor: '#EF4C4D'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Terjadi kesalahan sistem atau database error.',
                                confirmButtonColor: '#EF4C4D'
                            });
                        }
                    }
                });
            });
        });

        document.getElementById('inputWA').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>