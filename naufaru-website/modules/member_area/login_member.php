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
        'welcome' => 'Selamat Datang Kembali',
        'desc' => 'Silakan masuk untuk mengelola pesanan Anda.',
        'login_btn' => 'MASUK KE DASHBOARD',
        'back' => 'Kembali ke Beranda',
        'user_placeholder' => 'Username',
        'pass_placeholder' => 'Password',
        'not_member' => 'Belum punya akun?',
        'register' => 'Daftar Sekarang'
    ],
    'en' => [
        'welcome' => 'Welcome Back',
        'desc' => 'Please login to manage your orders.',
        'login_btn' => 'LOGIN TO DASHBOARD',
        'back' => 'Back to Home',
        'user_placeholder' => 'Username',
        'pass_placeholder' => 'Password',
        'not_member' => "Don't have an account?",
        'register' => 'Register Now'
    ],
    'jp' => [
        'welcome' => 'おかえりなさい',
        'desc' => 'ログインして注文を管理してください。',
        'login_btn' => 'ダッシュボードにログイン',
        'back' => 'ホームに戻る',
        'user_placeholder' => 'ユーザー名',
        'pass_placeholder' => 'パスワード',
        'not_member' => 'アカウントをお持ちではありませんか？',
        'register' => '今すぐ登録'
    ]
];

$text = $translations[$lang];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NaufaRu | Member Login</title>
    
    <link rel="stylesheet" href="../../assets/vendors/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --accent: #EF4C4D;
            --accent-hover: #d43f40;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body, html {
            margin: 0; padding: 0; height: 100%;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #000;
            overflow: hidden;
        }

        /* Perbaikan Background & Transisi */
        .login-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: url('../../assets/imgs/bg-1.jpg');
            background-size: cover; background-position: center;
            z-index: 1;
            transition: transform 10s ease-in-out;
        }
        
        .overlay-dark {
            position: absolute; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7); z-index: 2;
        }

        .login-container {
            position: relative; z-index: 3;
            height: 100vh; display: flex;
            justify-content: center; align-items: center;
        }

        /* Card Container */
        .login-card-member {
            background: rgba(20, 10, 10, 0.4);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 50px 40px;
            border-radius: 35px;
            border: 1px solid var(--glass-border);
            width: 90%;
            max-width: 420px;
            text-align: center;
            color: white;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
        }

        .login-card-member i.icon-user {
            color: var(--accent);
            margin-bottom: 25px;
            font-size: 4.5rem;
            filter: drop-shadow(0 0 15px rgba(239, 76, 77, 0.3));
        }

        /* Perbaikan Presisi Textbox */
        .form-group-custom {
            width: 100%;
            margin-bottom: 15px;
        }

        .login-input {
            box-sizing: border-box; /* Kunci presisi lebar */
            width: 100%; 
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid var(--glass-border) !important;
            padding: 14px 20px;
            border-radius: 14px;
            color: white !important;
            text-align: center;
            outline: none;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .login-input:focus {
            border-color: var(--accent) !important;
            background: rgba(239, 76, 77, 0.1) !important;
            box-shadow: 0 0 20px rgba(239, 76, 77, 0.2);
        }

        /* Tombol Utama */
        .btn-member-action {
            box-sizing: border-box;
            width: 100%;
            background: var(--accent) !important;
            border: none !important;
            color: white !important;
            font-weight: 700;
            padding: 16px;
            border-radius: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-member-action:hover {
            transform: translateY(-3px);
            background: var(--accent-hover) !important;
            box-shadow: 0 10px 25px rgba(239, 76, 77, 0.4);
        }

        /* Footer & Link */
        .footer-text { font-size: 0.85rem; margin-top: 25px; opacity: 0.8; }
        .footer-link { color: var(--accent); font-weight: bold; text-decoration: none; transition: 0.3s; }
        .footer-link:hover { text-decoration: underline; color: white; }
        
        .back-home {
            display: inline-block;
            margin-top: 20px;
            color: rgba(255,255,255,0.5);
            font-size: 0.85rem;
            text-decoration: none;
            transition: 0.3s;
        }
        .back-home:hover { color: white; }

        /* Custom SweetAlert NaufaRu Style */
        .swal2-popup {
            background: rgba(25, 25, 25, 0.95) !important;
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 25px !important;
            color: white !important;
        }
        .swal2-title, .swal2-html-container { color: white !important; }
        .swal2-confirm { background-color: var(--accent) !important; border-radius: 12px !important; padding: 10px 30px !important; }
        .swal2-timer-progress-bar { background: var(--accent) !important; }
    </style>
</head>
<body>

    <div class="login-bg">
        <div class="overlay-dark"></div>
    </div>

    <div class="login-container">
        <div class="login-card-member animate__animated animate__fadeInDown">
            <i class="fas fa-user-circle icon-user"></i>
            
            <h5 class="mb-2 font-weight-bold scramble-hover" data-value="<?php echo $text['welcome']; ?>">
                <?php echo $text['welcome']; ?>
            </h5>
            <p style="font-size: 0.85rem; opacity: 0.6; margin-bottom: 30px;"><?php echo $text['desc']; ?></p>
            
            <form id="memberLoginForm">
                <div class="form-group-custom">
                    <input type="text" name="username" class="login-input" 
                        placeholder="<?php echo $text['user_placeholder']; ?>" required>
                </div>
                <div class="form-group-custom">
                    <input type="password" name="password" class="login-input" 
                        placeholder="<?php echo $text['pass_placeholder']; ?>" required>
                </div>
                
                <button type="submit" class="btn-member-action">
                    <?php echo $text['login_btn']; ?>
                </button>
            </form>

            <div class="footer-text">
                <?php echo $text['not_member']; ?> 
                <a href="register.php" class="footer-link"><?php echo $text['register']; ?></a>
            </div>
            
            <a href="../../index.php" class="back-home">
                <i class="fas fa-arrow-left me-1"></i> <?php echo $text['back']; ?>
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Efek background zoom pelan
            setTimeout(() => { $('.login-bg').css('transform', 'scale(1.1)'); }, 100);

            // AJAX Login
            $('#memberLoginForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'proses_login_member.php', 
                    data: $(this).serialize(),
                    success: function(response) {
                        if(response.trim() === "success") {
                            Swal.fire({
                                title: 'Berhasil Masuk',
                                html: 'Halaman dialihkan dalam <b>3</b> detik.',
                                icon: 'success',
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: () => { Swal.showLoading(); },
                            }).then(() => { window.location.href = "dashboard.php"; });
                        } else {
                            Swal.fire({ 
                                icon: 'error', 
                                title: 'Gagal Masuk', 
                                text: 'Username atau password salah!',
                                confirmButtonColor: '#EF4C4D'
                            });
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>