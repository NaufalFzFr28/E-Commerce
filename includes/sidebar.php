<?php
/**
 * File: includes/sidebar.php
 * Deskripsi: Menu Sidebar Navigasi Antar Website & Pengaturan
 */
?>

<div class="burger-menu-trigger" id="burger-btn">
    <span></span>
    <span></span>
    <span></span>
</div>

<div class="burger-menu-content">
    <div class="sidebar-header text-center mb-4">
        <img src="<?php echo $base_url; ?>assets/imgs/naufaru-logo.png" alt="Logo" class="img-fluid" style="max-width: 80px;">
        <h5 class="mt-2"><?php echo __('app_name'); ?></h5>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_url; ?>index.php">
                <i class="fas fa-home mr-2"></i> <?php echo __('nav_home'); ?> (Splash)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_url; ?>modules/main_site/">
                <i class="fas fa-layer-group mr-2"></i> <?php echo __('btn_main_site'); ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_url; ?>modules/cv_site/">
                <i class="fas fa-file-alt mr-2"></i> <?php echo __('btn_cv'); ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_url; ?>modules/event_site/">
                <i class="fas fa-calendar-check mr-2"></i> <?php echo __('btn_event'); ?>
            </a>
        </li>

        <hr class="border-secondary">

        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" onclick="toggleNightMode()">
                <i class="fas fa-moon mr-2"></i> <?php echo __('night_mode'); ?>
            </a>
        </li>
        
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown">
                <i class="fas fa-language mr-2"></i> <?php echo __('nav_contact'); ?> (Language)
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="?lang=id">Bahasa Indonesia</a>
                <a class="dropdown-item" href="?lang=en">English</a>
                <a class="dropdown-item" href="?lang=jp">日本語</a>
            </div>
        </li>

        <li class="nav-item mt-3">
            <a class="nav-link btn btn-outline-primary text-primary" href="<?php echo $base_url; ?>admin/">
                <i class="fas fa-sign-in-alt mr-2"></i> Login Admin
            </a>
        </li>
    </ul>

    <div class="sidebar-footer mt-5 text-center text-muted">
        <small>© 2026 NaufaRu Digital</small>
    </div>
</div>

<style>
/* Style tambahan khusus Sidebar di luar style.css utama */
.burger-menu-trigger {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1100;
    cursor: pointer;
    background: var(--accent-color);
    padding: 10px;
    border-radius: 5px;
}
.burger-menu-trigger span {
    display: block;
    width: 25px;
    height: 3px;
    background: white;
    margin: 5px 0;
    transition: 0.3s;
}
.nav-link {
    color: var(--text-color);
    padding: 12px 15px;
    transition: 0.3s;
}
.nav-link:hover {
    background: var(--accent-color);
    color: white !important;
    border-radius: 8px;
}
</style>