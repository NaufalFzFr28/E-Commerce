<?php
/**
 * File: includes/header.php
 * Deskripsi: Navbar Header Global dengan Menu Burger & Dropdown Animasi
 */

// Tentukan bahasa aktif (default: id)
$current_lang = $_GET['lang'] ?? 'id';
?>

<nav class="navbar navbar-expand-lg fixed-top navbar-custom">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_url; ?>index.php">
            <img src="<?php echo $base_url; ?>assets/imgs/naufaru-logo.png" alt="Logo" height="40" class="mr-2">
            <span class="brand-text font-weight-bold"><?php echo __('app_name'); ?></span>
        </a>

        <div class="nav-burger-container ml-auto" id="burger-btn">
            <div class="burger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</nav>

<div class="burger-dropdown" id="burger-dropdown-content">
    <div class="dropdown-inner container">
        <div class="row w-100">
            <div class="col-md-6 mb-4">
                <h6 class="text-muted small text-uppercase mb-3"><?php echo __('nav_home'); ?> Navigation</h6>
                <ul class="list-unstyled dropdown-nav-links">
                    <?php if (strpos($_SERVER['REQUEST_URI'], 'cv_site') !== false): ?>
                        <li><a href="#profil"><i class="fas fa-user-circle mr-2"></i> Profil</a></li>
                        <li><a href="#pendidikan"><i class="fas fa-graduation-cap mr-2"></i> Pendidikan</a></li>
                        <li><a href="#pengalaman"><i class="fas fa-briefcase mr-2"></i> Pengalaman</a></li>
                    <?php elseif (strpos($_SERVER['REQUEST_URI'], 'event_site') !== false): ?>
                        <li><a href="#tentang"><i class="fas fa-info-circle mr-2"></i> Tentang Acara</a></li>
                        <li><a href="#acara"><i class="fas fa-calendar-alt mr-2"></i> Jadwal</a></li>
                    <?php else: ?>
                        <li><a href="#tentang"><i class="fas fa-info-circle mr-2"></i> Tentang</a></li>
                        <li><a href="#layanan"><i class="fas fa-concierge-bell mr-2"></i> Layanan</a></li>
                        <li><a href="#galeri"><i class="fas fa-images mr-2"></i> Galeri</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="col-md-6">
                <h6 class="text-muted small text-uppercase mb-3">Settings & Switch</h6>
                <div class="d-flex flex-column">
                    <button class="btn btn-outline-secondary mb-2 text-left" onclick="toggleNightMode()">
                        <i class="fas fa-moon mr-2"></i> <?php echo __('night_mode'); ?>
                    </button>
                    <div class="btn-group mb-3">
                        <a href="?lang=id" class="btn btn-sm btn-light border <?php echo $current_lang == 'id' ? 'active' : ''; ?>">ID</a>
                        <a href="?lang=en" class="btn btn-sm btn-light border <?php echo $current_lang == 'en' ? 'active' : ''; ?>">EN</a>
                        <a href="?lang=jp" class="btn btn-sm btn-light border <?php echo $current_lang == 'jp' ? 'active' : ''; ?>">JP</a>
                    </div>
                    <a href="<?php echo $base_url; ?>admin/" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-shield mr-2"></i> Login Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Khusus Header Navbar */
.navbar-custom {
    background: rgba(var(--bg-color-rgb), 0.85);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    padding: 15px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.nav-burger-container {
    cursor: pointer;
    padding: 10px;
    transition: 0.3s;
}

.burger-icon span {
    display: block;
    width: 25px;
    height: 2px;
    background: var(--text-color);
    margin: 6px 0;
    transition: 0.4s;
}

/* Animasi Burger jadi X */
#burger-btn.open .burger-icon span:nth-child(1) { transform: rotate(-45deg) translate(-5px, 6px); }
#burger-btn.open .burger-icon span:nth-child(2) { opacity: 0; }
#burger-btn.open .burger-icon span:nth-child(3) { transform: rotate(45deg) translate(-5px, -6px); }

/* Style Dropdown */
.burger-dropdown {
    display: none;
    position: fixed;
    top: 70px;
    left: 0;
    width: 100%;
    background: var(--bg-color);
    z-index: 1040;
    padding: 40px 0;
    border-bottom: 2px solid var(--accent-color);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.dropdown-nav-links li { margin-bottom: 15px; }
.dropdown-nav-links a {
    font-size: 1.2rem;
    color: var(--text-color);
    font-weight: 500;
}
.dropdown-nav-links a:hover { color: var(--accent-color); padding-left: 10px; }
</style>