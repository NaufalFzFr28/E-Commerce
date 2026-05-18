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
        'user_placeholder' => 'Username Baru',
        'pass_placeholder' => 'Password Baru',
        'name_placeholder' => 'Nama Lengkap',
        'wa_placeholder' => 'Nomor WhatsApp Aktif', // Field baru
        'address_placeholder' => 'Alamat Tempat Tinggal', // Field baru
        'photo_label' => 'FOTO PROFIL (OPSIONAL)',
        'already_member' => 'Sudah punya akun?'
    ],
    'en' => [
        'title' => 'Register Member',
        'desc' => 'Join us to start ordering the best works.',
        'btn_reg' => 'CREATE MEMBER ACCOUNT',
        'back' => 'Back to Login',
        'user_placeholder' => 'New Username',
        'pass_placeholder' => 'New Password',
        'name_placeholder' => 'Full Name',
        'wa_placeholder' => 'Active WhatsApp Number', // New field
        'address_placeholder' => 'Residential Address', // New field
        'photo_label' => 'PROFILE PHOTO (OPTIONAL)',
        'already_member' => 'Already have an account?'
    ],
    'jp' => [
        'title' => 'メンバー登録',
        'desc' => '最高の作品を注文するために参加してください。',
        'btn_reg' => 'アカウントを作成する',
        'back' => 'ログインに戻る',
        'user_placeholder' => 'ユーザー名',
        'pass_placeholder' => 'パスワード',
        'name_placeholder' => 'フルネーム',
        'wa_placeholder' => '有効なWhatsApp番号', // New field
        'address_placeholder' => '居住住所', // New field
        'photo_label' => 'プロフィール写真（任意）',
        'already_member' => 'すでにアカウントをお持ちですか？'
    ]
];

$text = $translations[$lang];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NaufaRu | Register</title>
    
    <link rel="stylesheet" href="../../assets/vendors/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --accent: #EF4C4D;
            --accent-hover: #d43f40;
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body, html {
            margin: 0; padding: 0; height: 100%;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #000;
            /* MENGHILANGKAN SCROLLBAR */
            overflow: hidden; 
        }

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

        .register-card-member {
            background: rgba(20, 10, 10, 0.4);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 35px 40px; /* Diperkecil sedikit agar pas */
            border-radius: 35px;
            border: 1px solid var(--glass-border);
            width: 90%;
            max-width: 420px;
            text-align: center;
            color: white;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
        }

        .form-group-custom {
            width: 100%;
            margin-bottom: 12px; /* Margin dipersempit */
            text-align: left;
        }

        .form-group-custom label {
            font-size: 0.65rem; /* Lebih kecil agar tidak makan tempat */
            opacity: 0.6;
            margin-left: 5px;
            margin-bottom: 5px;
            display: block;
        }

        .login-input {
            box-sizing: border-box;
            width: 100%; 
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid var(--glass-border) !important;
            padding: 12px 20px;
            border-radius: 14px;
            color: white !important;
            outline: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .login-input:focus {
            border-color: var(--accent) !important;
            background: rgba(239, 76, 77, 0.1) !important;
        }

        /* TOMBOL CHOOSE FILE CUSTOM */
        .login-input[type="file"] {
            padding: 8px 12px;
            cursor: pointer;
        }

        .login-input::file-selector-button {
            background: var(--accent);
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 8px;
            margin-right: 12px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: bold;
            transition: 0.3s;
        }

        .login-input::file-selector-button:hover {
            background: var(--accent-hover);
        }

        .btn-register-action {
            box-sizing: border-box;
            width: 100%;
            background: var(--accent) !important;
            border: none !important;
            color: white !important;
            font-weight: 700;
            padding: 14px;
            border-radius: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 10px;
            font-size: 0.85rem;
        }

        .btn-register-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(239, 76, 77, 0.4);
        }

        .footer-text { font-size: 0.8rem; margin-top: 20px; opacity: 0.8; }
        .footer-link { color: var(--accent); font-weight: bold; text-decoration: none; }

        /* Custom SweetAlert */
        .swal2-popup {
            background: rgba(25, 25, 25, 0.95) !important;
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 25px !important;
            color: white !important;
        }
        .swal2-confirm { background-color: var(--accent) !important; border-radius: 12px !important; }
        .swal2-timer-progress-bar { background: var(--accent) !important; }
    </style>
</head>
<body>

    <div class="login-bg">
        <div class="overlay-dark"></div>
    </div>

    <div class="login-container">
        <div class="register-card-member animate__animated animate__fadeInUp">
            <h3 class="font-weight-bold mb-1" style="font-size: 1.5rem;"><?php echo $text['title']; ?></h3>
            <p style="font-size: 0.8rem; opacity: 0.6; margin-bottom: 25px;"><?php echo $text['desc']; ?></p>
            
            <form id="registerForm" enctype="multipart/form-data">
                <div class="form-group-custom">
                    <input type="text" name="username" class="login-input" 
                        placeholder="<?php echo $text['user_placeholder']; ?>" required>
                </div>
                <div class="form-group-custom">
                    <input type="password" name="password" class="login-input" 
                        placeholder="<?php echo $text['pass_placeholder']; ?>" required>
                </div>
                <div class="form-group-custom">
                    <input type="text" name="nama_lengkap" class="login-input" 
                        placeholder="<?php echo $text['name_placeholder']; ?>" required>
                </div>
                <div class="form-group-custom">
                    <input type="text" 
                        name="no_hp" 
                        class="login-input" 
                        placeholder="<?php echo $text['wa_placeholder']; ?>" 
                        id="inputWA"
                        maxlength="13" 
                        required>
                </div>
                <div class="form-group-custom">
                    <textarea name="alamat" class="login-input" placeholder="<?php echo $text['address_placeholder']; ?>" rows="2" required></textarea>
                </div>
                <div class="form-group-custom">
                    <label><?php echo $text['photo_label']; ?></label>
                    <input type="file" name="foto_profil" class="login-input" accept="image/*">
                </div>
                
                <button type="submit" class="btn-register-action">
                    <?php echo $text['btn_reg']; ?>
                </button>
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
            setTimeout(() => { $('.login-bg').css('transform', 'scale(1.1)'); }, 100);

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

        // Memastikan input nomor telepon hanya angka
        document.getElementById('inputWA').addEventListener('input', function (e) {
            // Menghapus karakter selain angka secara real-time
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>