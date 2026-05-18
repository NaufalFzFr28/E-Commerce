<?php
session_start(); // Memulai sesi agar pilihan bahasa tersimpan 

include 'db.php';
include 'functions.php';

// --- LOGIKA PENGATURAN BAHASA PERSISTEN ---
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Simpan pilihan ke session jika ada perubahan dari URL 
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang']; // Gunakan bahasa dari session jika sudah pernah memilih 
} else {
    $lang = 'id'; // Default awal Indonesia 
}

// Ambil data pengaturan site
$settings = $conn->query("SELECT * FROM site_settings WHERE id = 1")->fetch_assoc();

$translations = [
'id' => [
        'tagline' => 'Eksplorasi Kreativitas Tanpa Batas Melalui Lensa dan Teknologi.',
        'btn_main' => 'Website Utama',
        'btn_cv' => 'Curriculum Vitae',
        'btn_event' => 'Event Site',
        'menu_lang' => 'Pilih Bahasa',
        'menu_login' => 'Login',
        'back' => 'Kembali',
        'swal_success_title' => 'Akses Diterima',
        'swal_success_text' => 'Selamat datang kembali, Admin.',
        'swal_error_title' => 'Akses Ditolak',
        'swal_error_text' => 'Kesalahan memasukan username atau sandi, silahkan periksa kembali',
        'welcome_back' => 'Selamat Datang Kembali',
        'login_btn' => 'MASUK',
        'user_placeholder' => 'Nama Pengguna',
        'pass_placeholder' => 'Kata Sandi',
        // Update Baru Member Area
        'not_member' => 'Belum punya akun?',
        'create_account' => 'Buat akun di sini',
        'member_login' => 'MASUK SEBAGAI MEMBER',
        'not_admin' => 'Bukan Admin?'
    ],
    'en' => [
        'tagline' => 'Exploring Limitless Creativity Through Lens and Technology.',
        'btn_main' => 'Main Website',
        'btn_cv' => 'Curriculum Vitae',
        'btn_event' => 'Event Site',
        'menu_lang' => 'Select Language',
        'menu_login' => 'Login',
        'back' => 'Back',
        'swal_success_title' => 'Access Granted',
        'swal_success_text' => 'Welcome back, Admin.',
        'swal_error_title' => 'Access Denied',
        'swal_error_text' => 'Invalid credentials entered!',
        'welcome_back' => 'Welcome Back',
        'login_btn' => 'LOGIN',
        'user_placeholder' => 'Username',
        'pass_placeholder' => 'Password',
        // Update Baru Member Area
        'not_member' => "Don't have an account?",
        'create_account' => 'Create an account here',
        'member_login' => 'LOGIN AS MEMBER',
        'not_admin' => 'Not an Admin?'
    ],
    'jp' => [
        'tagline' => 'レンズとテクノロジーを通じて無限の創造性を探求する。',
        'btn_main' => 'メインサイト',
        'btn_cv' => '履歴書',
        'btn_event' => 'イベントサイト',
        'menu_lang' => '言語を選択',
        'menu_login' => 'ログイン',
        'back' => '戻る',
        'swal_success_title' => 'アクセス許可',
        'swal_success_text' => 'おかえりなさい、管理者。',
        'swal_error_title' => '拒否されました',
        'swal_error_text' => '資格情報が正しくありません。',
        'welcome_back' => 'おかえりなさい',
        'login_btn' => 'ログイン',
        'user_placeholder' => 'ユーザー名',
        'pass_placeholder' => 'パスワード',
        // Update Baru Member Area
        'not_member' => 'アカウントをお持ちではありませんか？',
        'create_account' => 'ここでアカウントを作成する',
        'member_login' => 'メンバーとしてログイン',
        'not_admin' => '管理者以外の方はこちら'
    ]
];

$text = $translations[$lang];

// Ambil data wallpaper
$slides = $conn->query("SELECT * FROM site_wallpaper WHERE is_active = 1 ORDER BY sort_order ASC");
$slides_data = [];
if ($slides && $slides->num_rows > 0) {
    while($row = $slides->fetch_assoc()) { $slides_data[] = $row['image_path']; }
} else {
    $slides_data = ['bg-1.jpg', 'bg-2.jpg', 'bg-3.jpg'];
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NaufaRu - Professional Identity</title>
    
    <link rel="stylesheet" href="assets/vendors/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --accent: #EF4C4D;
            --font-main: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --bg-glass: rgba(255, 255, 255, 0.1);
        }

        body, html {
            margin: 0; padding: 0; height: 100%;
            font-family: var(--font-main);
            overflow: hidden; background: #000;
        }

        /* HEADER & BURGER */
        .header-nav {
            position: fixed; 
            top: 30px; 
            left: 70px; 
            right: 70px;
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            z-index: 100; 
            transition: 0.5s ease;
        }
        body.login-active .header-nav { opacity: 0; pointer-events: none; transform: translateY(-20px); }
        
        .top-logo { max-width: 120px; height: auto; filter: drop-shadow(0 0 8px rgba(0,0,0,0.3)); }

        /* BURGER BUTTON (Sesuai Referensi) */
        .burger-btn {
            background: rgba(45, 45, 45, 0.6); 
            border: 1px solid rgba(255,255,255,0.15);
            color: white; width: 55px; height: 55px; border-radius: 15px;
            cursor: pointer; backdrop-filter: blur(10px); transition: 0.3s;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        /* MAIN MENU DROPDOWN (Hitam Solid, Rounded) */
        .main-menu {
            position: absolute; 
            top: 70px; /* Samakan jarak turunnya */
            right: 0;
            background: rgba(10, 10, 10, 0.8); /* Tetap gelap */
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px; /* Samakan lekukannya */
            width: 280px; 
            display: none; 
            overflow: hidden; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
            max-height: 80vh; /* Tambahkan limit tinggi */
        }

        .menu-slider { display: flex; width: 200%; transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .menu-content, .lang-submenu { width: 50%; height: auto; }
        .main-menu.show-lang .menu-slider { transform: translateX(-50%); }

        .menu-item {
            padding: 20px 25px; 
            color: #ffffff; 
            display: flex; 
            align-items: center;
            cursor: pointer; 
            transition: 0.2s; 
            text-decoration: none; 
            border: none; 
            background: none; 
            width: 100%; 
            font-size: 1.05rem; 
            font-weight: 500;
        }
        .menu-item:hover { background: rgba(255,255,255,0.05); color: var(--accent); }
        .menu-item i { width: 30px; font-size: 1.1rem; margin-right: 12px; }

        .menu-divider { border-top: 1px solid rgba(255,255,255,0.08); }

        /* WALLPAPER & OVERLAY */
        .splash-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
        .ken-burns-slide {
            position: absolute; width: 100%; height: 100%;
            background-size: cover; background-position: center;
            opacity: 0; transition: opacity 3s ease-in-out;
            transform: scale(1.1);
        }
        .ken-burns-slide.active { opacity: 1; animation: kenburns-infinite 20s linear infinite; }
        @keyframes kenburns-infinite { 0% { transform: scale(1); } 100% { transform: scale(1.3); } }
        .overlay-dark { position: absolute; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.55); z-index: 2; }

        /* CONTENT */
        .splash-content {
            position: relative; z-index: 3; height: 100vh;
            display: flex; justify-content: center; align-items: center; text-align: center; color: white;
            transition: 0.8s ease;
        }
        body.login-active .splash-content { filter: blur(15px); opacity: 0.3; transform: scale(0.95); }
        .typed-heading { font-size: 3.8rem; font-weight: 800; }
        #typed-text { color: var(--accent); }
        .tagline-desc { font-weight: 300; max-width: 650px; margin: 0 auto 30px; line-height: 1.6; }

        .btn-group-nav { display: flex; gap: 15px; justify-content: center; margin-top: 40px; }
        .btn-glass {
            background: var(--bg-glass); backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2); color: white;
            padding: 16px 32px; border-radius: 50px; text-decoration: none;
            transition: 0.4s; min-width: 180px; text-transform: uppercase; font-size: 0.85rem; font-weight: 600;
        }
        .btn-glass:hover { transform: translateY(-8px); border-color: var(--accent); color: white; }

        /* LOGIN OVERLAY */
        #loginOverlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 500; display: none; justify-content: center; align-items: center;
            background: rgba(0,0,0,0.6); /* Lebih gelap agar fokus ke card */
            backdrop-filter: blur(5px);
        }
        .login-card {
            background: rgba(239, 76, 77, 0.15); /* Merah transparan */
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 45px; 
            border-radius: 30px; 
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 90%; 
            max-width: 400px; 
            text-align: center; 
            color: white;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
        /* Penempatan teks dan input agar rata tengah sempurna */
        .login-card h5 {
            letter-spacing: 1px;
            font-size: 1.1rem;
            cursor: default;
        }

        .form-group-login {
            display: flex;
            flex-direction: column;
            align-items: center; /* Rata tengah input */
            width: 100%;
        }

        .login-card i { color: var(--accent); margin-bottom: 20px; }
        .login-input {
            width: 100%;
            max-width: 320px; /* Batasi lebar agar rapi */
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            padding: 14px;
            border-radius: 12px;
            color: white !important;
            margin-bottom: 15px;
            text-align: center; /* Teks di dalam input rata tengah */
            outline: none;
            transition: 0.3s;
        }
        .login-input:focus {
            border-color: var(--accent) !important;
            background: rgba(239, 76, 77, 0.1) !important;
            box-shadow: 0 0 15px rgba(239, 76, 77, 0.2);
        }

        /* Style Tombol Seragam */
        .btn-login-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 10px;
            width: 100%;
            max-width: 320px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-login-action {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        /* Warna Pembeda */
        .btn-masuk-glass {
            background: rgba(239, 76, 77, 0.3); /* Merah Glass */
            color: white;
        }

        .btn-masuk-glass:hover {
            background: rgba(239, 76, 77, 0.5);
            transform: translateY(-3px);
        }

        .btn-kembali-glass {
            background: rgba(255, 255, 255, 0.05); /* Putih Glass */
            color: rgba(255, 255, 255, 0.8);
        }

        .btn-kembali-glass:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateY(-3px);
        }

        .btn-masuk {
            background: var(--accent) !important;
            border: none !important;
            color: white !important;
            font-weight: 700 !important;
            padding: 12px !important;
            border-radius: 12px !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-masuk:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(239, 76, 77, 0.4);
            filter: brightness(1.1);
        }

        .btn-kembali {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            padding: 12px !important;
            border-radius: 12px !important;
            transition: 0.3s;
        }

        .btn-kembali:hover {
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .splash-footer {
            position: fixed; bottom: 20px; left: 0; width: 100%; text-align: center;
            z-index: 10; color: rgba(255,255,255,0.4); font-size: 0.8rem; pointer-events: none;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .header-nav { 
                top: 0; left: 0; right: 0;
                padding: 12px 20px; 
                height: 70px; 
                background: rgba(10, 10, 10, 0.9); 
            }
            /* Menu Full Screen */
            .main-menu { 
                position: fixed;
                top: 90px; /* Di bawah navbar */
                left: 0; right: 20px;
                width: 100vw; 
                height: calc(100vh - 70px);
                max-width: none;
                max-height: none;
                border-radius: 0;
                border: none;
                z-index: 999;
            }

            .menu-item {
                border-bottom: 1px solid rgba(255,255,255,0.05);
                padding: 18px 25px;
            }

            /* Animasi muncul satu per satu (Staggered) */
            .main-menu.active .menu-item {
                animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
                opacity: 0;
            }

            .menu-item:nth-child(1) { animation-delay: 0.1s; }
            .menu-item:nth-child(2) { animation-delay: 0.18s; }
            .menu-item:nth-child(3) { animation-delay: 0.26s; }

            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Ikon Burger ke Silang */
            .burger-btn.is-active i::before {
                content: "\f00d"; /* FontAwesome X */
            }
            .burger-btn.is-active i {
                transform: rotate(90deg);
                transition: 0.3s;
            }

            .typed-heading { font-size: 2.0rem; }
            .btn-group-nav { flex-direction: column; align-items: center; }
        }

        /* Custom SweetAlert agar serasi dengan NaufaRu */
        .swal2-popup {
            background: rgba(20, 20, 20, 0.9) !important;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px !important;
            color: white !important;
        }

        .swal2-title, .swal2-html-container {
            color: white !important;
        }

        .swal2-timer-progress-bar {
            background: var(--accent) !important; /* Warna merah NaufaRu pada loading bar */
        }

        /* Style tambahan untuk navigasi member di dalam modal */
        .member-links .btn-glass {
            background: rgba(255, 255, 255, 0.05);
            transition: 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .member-links .btn-glass:hover {
            background: rgba(239, 76, 77, 0.2);
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .divider {
            height: 1px;
            width: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
        }
    </style>
</head>
<body>

    <header class="header-nav">
        <img src="assets/imgs/logo-white.png" alt="Logo" class="top-logo">
        <div class="dropdown-wrapper">
            <button class="burger-btn" id="burgerToggle"><i class="fas fa-bars"></i></button>
            <div class="main-menu" id="menuDrop">
                <div class="menu-slider">
                    <div class="menu-content">
                        <div class="menu-item" id="openLang">
                            <i class="fas fa-language"></i> 
                            <?php echo $text['menu_lang']; ?> 
                            <i class="fas fa-chevron-right ms-auto" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="menu-divider"></div>
                        <div class="menu-item" onclick="openLogin()">
                            <i class="fas fa-user-shield"></i> <?php echo $text['menu_login']; ?>
                        </div>
                    </div>
                    <div class="lang-submenu">
                        <div class="menu-item" id="backToMain" style="background: rgba(255,255,255,0.03);">
                            <i class="fas fa-chevron-left"></i> <?php echo $text['back']; ?>
                        </div>
                        <div class="menu-divider"></div>
                        <div class="menu-item" onclick="changeLang('id')">Indonesia</div>
                        <div class="menu-divider"></div>
                        <div class="menu-item" onclick="changeLang('en')">English</div>
                        <div class="menu-divider"></div>
                        <div class="menu-item" onclick="changeLang('jp')">Japanese</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="splash-container">
        <div class="overlay-dark"></div>
        <?php foreach ($slides_data as $index => $img): ?>
            <div class="ken-burns-slide <?php echo ($index == 0) ? 'active' : ''; ?>" 
                 style="background-image: url('assets/imgs/<?php echo $img; ?>');">
            </div>
        <?php endforeach; ?>
    </div>

    <div class="splash-content">
        <div class="container">
            <h1 class="typed-heading">I'm <span id="typed-text"></span></h1>
            <p class="tagline-desc"><?php echo $text['tagline']; ?></p>
            <div class="btn-group-nav">
                <a href="modules/main_site/" class="btn-glass"><?php echo $text['btn_main']; ?></a>
                <a href="modules/cv_site/" class="btn-glass"><?php echo $text['btn_cv']; ?></a>
                <a href="modules/event_site/" class="btn-glass"><?php echo $text['btn_event']; ?></a>
            </div>
        </div>
    </div>

    <div id="loginOverlay">
        <div class="login-card animate__animated" id="loginCard">
            <i class="fas fa-user-shield fa-3x mb-3"></i>
            
            <h5 class="mb-4 font-weight-bold scramble-hover" data-value="<?php echo $text['welcome_back']; ?>">
                <?php echo $text['welcome_back']; ?>
            </h5>
            
            <!-- Form Admin (Tetap Mengarah ke proses_login.php) -->
            <form id="adminLoginForm" action="proses_login.php" method="POST">
                <div class="form-group-login">
                    <input type="text" name="username" class="login-input" 
                        placeholder="<?php echo $text['user_placeholder']; ?>" required>
                    <input type="password" name="password" class="login-input" 
                        placeholder="<?php echo $text['pass_placeholder']; ?>" required>
                </div>
                
                <div class="btn-login-container">
                    <button type="submit" class="btn-login-action btn-masuk-glass">
                        <?php echo $text['login_btn']; ?>
                    </button>
                    <button type="button" onclick="closeLogin()" class="btn-login-action btn-kembali-glass">
                        <?php echo $text['back']; ?>
                    </button>
                </div>
            </form>

            <!-- Tambahan Navigasi Member Baru -->
            <div class="divider" style="margin: 25px 0; border-top: 1px solid rgba(255,255,255,0.1);"></div>
            
            <div class="member-links">
                <p style="font-size: 0.85rem; opacity: 0.7; margin-bottom: 10px;">
                    <?php echo htmlspecialchars($text['not_admin'] ?? 'Bukan Admin?'); ?>
                </p>
                                
                <a href="modules/member_area/login_member.php" class="btn-glass" 
                style="display: block; padding: 12px; font-size: 0.75rem; margin-bottom: 15px; text-decoration: none; border-radius: 12px;">
                    <i class="fas fa-user-tag me-2"></i> <?php echo $text['member_login']; ?>
                </a>

                <p style="font-size: 0.75rem; opacity: 0.6;">
                    <?php echo $text['not_member']; ?> 
                    <a href="modules/member_area/register.php" style="color: var(--accent); text-decoration: none; font-weight: bold;">
                        <?php echo $text['create_account']; ?>
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="splash-footer">© 2026 NaufaRu Copyright.</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. Definisi Class TextScramble (Wajib ada agar fungsi hover & login bekerja)
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

    $(document).ready(function() {
        // 2. Inisialisasi Typed.js
        if ($('#typed-text').length) {
            new Typed('#typed-text', {
                strings: ["Photographer", "Photo Video Editor", "Graphics Designer"],
                typeSpeed: 50, backSpeed: 30, backDelay: 4000, loop: true
            });
        }

        // 3. Slideshow Logic
        let slides = $('.ken-burns-slide');
        let current = 0;
        if (slides.length > 0) {
            setInterval(() => {
                let next = (current + 1) % slides.length;
                slides.eq(current).css('opacity', '0').removeClass('active');
                slides.eq(next).addClass('active').css('opacity', '1');
                current = next;
            }, 8000);
        }

        // 4. Dropdown Toggle
        $('#burgerToggle').on('click', function(e) {
            e.stopPropagation();
            const menu = $('#menuDrop');
            $(this).toggleClass('is-active');
            
            if (menu.is(':visible')) {
                menu.fadeOut(300).removeClass('active');
            } else {
                menu.fadeIn(300).addClass('active').removeClass('show-lang');
            }
        });

        // Submenu Bahasa
        $('#openLang').on('click', function(e) {
            e.stopPropagation();
            $('#menuDrop').addClass('show-lang');
        });

        // Menutup menu saat klik di luar
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown-wrapper').length) {
                $('#menuDrop').fadeOut(300).removeClass('active');
                $('#burgerToggle').removeClass('is-active');
            }
        });

        // Tambahkan ini di dalam $(document).ready
        $('#backToMain').on('click', function(e) {
            e.stopPropagation(); // Mencegah menu tertutup otomatis
            $('#menuDrop').removeClass('show-lang');
        });

        // 5. Hover Scramble pada Judul Login
        const scrambleTitle = document.querySelector('.scramble-hover');
        if (scrambleTitle) {
            const fx = new TextScramble(scrambleTitle);
            const textValue = scrambleTitle.getAttribute('data-value');
            scrambleTitle.addEventListener('mouseenter', () => {
                fx.setText(textValue);
            });
        }

        // 6. Login Handler (Perbaikan Utama: Hitung Mundur Detik)
        $('#adminLoginForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'proses_login.php', 
                data: $(this).serialize(),
                success: function(response) {
                    if(response.trim() === "success") {
                        let timerInterval;
                        Swal.fire({
                            title: 'Berhasil Masuk',
                            html: 'Halaman akan dialihkan dalam <b id="timer-sec">3</b> detik.',
                            icon: 'success',
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: () => {
                                Swal.showLoading();
                                const b = Swal.getHtmlContainer().querySelector('#timer-sec');
                                timerInterval = setInterval(() => {
                                    // Mengonversi milidetik ke detik (dibulatkan ke atas)
                                    const secondsLeft = Math.ceil(Swal.getTimerLeft() / 1000);
                                    b.textContent = secondsLeft;
                                }, 100);
                            },
                            willClose: () => {
                                clearInterval(timerInterval);
                            }
                        }).then(() => {
                            window.location.href = "admin/admin_dashboard.php";
                        });
                    } else {
                        Swal.fire({ 
                            icon: 'error', 
                            title: 'Akses Ditolak', 
                            text: 'Kesalahan memasukan username atau sandi, silahkan periksa kembali',
                            confirmButtonColor: '#EF4C4D'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ 
                        icon: 'warning', 
                        title: 'Error ' + xhr.status, 
                        text: 'Terjadi kesalahan sistem atau file login tidak ditemukan.',
                        confirmButtonColor: '#EF4C4D'
                    });
                }
            });
        });
    });

    // 7. Fungsi Global (Di luar ready block)
    function changeLang(langCode) { 
        window.location.href = "?lang=" + langCode; 
    }

    function openLogin() {
        $('body').addClass('login-active');
        $('#loginOverlay').css('display', 'flex').hide().fadeIn(400);
        $('#loginCard').removeClass('animate__zoomOut').addClass('animate__zoomIn');
        
        // Ambil elemen judul yang sudah diterjemahkan oleh PHP
        const title = document.querySelector('.scramble-hover');
        if (title) {
            const fx = new TextScramble(title);
            // Efek scramble akan menggunakan teks sesuai bahasa yang aktif
            fx.setText(title.getAttribute('data-value'));
        }
    }
    
    function closeLogin() {
        $('#loginCard').removeClass('animate__zoomIn').addClass('animate__zoomOut');
        setTimeout(() => {
            $('#loginOverlay').hide();
            $('body').removeClass('login-active');
        }, 400);
    }
</script>
</body>
</html>