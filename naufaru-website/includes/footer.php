<?php
/**
 * File: includes/footer.php
 * Deskripsi: Footer global dengan fitur Chat Admin dan Dynamic Last Update
 */

// Menentukan kolom update mana yang diambil berdasarkan lokasi file saat ini
$current_page = $_SERVER['REQUEST_URI'];
$update_time = $settings['last_updated_main']; // Default

if (strpos($current_page, 'cv_site') !== false) {
    $update_time = $settings['last_updated_cv'];
} elseif (strpos($current_page, 'event_site') !== false) {
    $update_time = $settings['last_updated_event'];
} elseif (strpos($current_page, 'invoice_site') !== false) {
    $update_time = $settings['last_updated_invoice'];
}
?>

<footer class="main-footer py-4 mt-5 border-top">
    <div class="container text-center">
        <div class="footer-brand mb-3">
            <h5 class="font-weight-bold"><?php echo __('app_name'); ?></h5>
            <p class="small text-muted"><?php echo __('splash_tagline'); ?></p>
        </div>

        <div class="social-links mb-3">
            <a href="#" class="text-secondary mx-2"><i class="fab fa-instagram fa-lg"></i></a>
            <a href="#" class="text-secondary mx-2"><i class="fab fa-linkedin fa-lg"></i></a>
            <a href="#" class="text-secondary mx-2"><i class="fab fa-github fa-lg"></i></a>
            <a href="#" class="text-secondary mx-2"><i class="fab fa-whatsapp fa-lg"></i></a>
        </div>

        <div class="last-update text-muted small">
            <p><?php echo __('last_update'); ?>: <strong><?php echo format_tanggal_indonesia($update_time); ?></strong></p>
        </div>

        <div class="copyright mt-3 small text-muted">
            &copy; <?php echo date('Y'); ?> NaufaRu Digital. All rights reserved.
        </div>
    </div>
</footer>

<div class="chat-widget">
    <div class="chat-btn" onclick="$('.chat-box').fadeToggle()">
        <i class="fas fa-comment-dots fa-2x"></i>
    </div>

    <div class="chat-box card shadow-lg" style="display: none; position: fixed; bottom: 90px; right: 20px; width: 300px; border-radius: 15px;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><?php echo __('chat_admin_title'); ?></span>
            <button type="button" class="close text-white" onclick="$('.chat-box').fadeOut()">&times;</button>
        </div>
        <div class="card-body p-3">
            <form id="chat-form">
                <div class="form-group mb-2">
                    <input type="text" name="sender_name" class="form-control form-control-sm" placeholder="<?php echo __('chat_placeholder_name'); ?>" required>
                </div>
                <div class="form-group mb-2">
                    <textarea name="message" class="form-control form-control-sm" rows="3" placeholder="<?php echo __('chat_placeholder_msg'); ?>" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm btn-block">Kirim Pesan</button>
            </form>
        </div>
    </div>
</div>

<style>
/* Style tambahan untuk Footer & Chat Box */
.main-footer {
    background-color: var(--footer-bg);
}
.chat-box {
    z-index: 1060;
}
.chat-box .card-header {
    border-radius: 15px 15px 0 0;
}
.chat-btn:hover {
    transform: scale(1.1);
    background: var(--accent-color);
}
</style>