<?php
/**
 * File: includes/footer.php
 * Deskripsi: Footer global dengan auto-detection path untuk widget AI Helpdesk
 */

// Menghitung kedalaman path file saat ini untuk menentukan relative path ke root folder
$current_script = $_SERVER['SCRIPT_NAME']; // Contoh: /E-commerce/modules/member_area/dashboard.php
if (strpos($current_script, '/modules/') !== false || strpos($current_script, '/member_area/') !== false) {
    // Jika diakses dari dalam folder modul, naik 2 tingkat ke root
    $base_path = "../../";
} else {
    // Jika diakses dari root utama (index.php)
    $base_path = "";
}

$api_url = $base_path . "chatbot_api.php";
?>

<footer class="main-footer py-4 mt-5 border-top">
    <div class="container text-center">
        <div class="footer-brand mb-3">
            <h5 class="font-weight-bold"><?php echo function_exists('__') ? __('app_name') : 'NaufaRu Digital'; ?></h5>
            <p class="small text-muted"><?php echo function_exists('__') ? __('splash_tagline') : 'Platform E-Commerce & Portfolio Desain'; ?></p>
        </div>
        <div class="copyright mt-3 small text-muted">
            &copy; <?php echo date('Y'); ?> NaufaRu Digital. All rights reserved.
        </div>
    </div>
</footer>

<div id="ai-chat-widget">
    <div class="chat-btn shadow-lg" onclick="document.getElementById('ai-chat-box').classList.toggle('hidden')">
        <span style="font-size: 26px; line-height: 1;">💬</span>
    </div>

    <div id="ai-chat-box" class="ai-chat-box card shadow-lg hidden">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="padding: 12px 15px;">
            <span class="font-weight-bold">🤖 Asisten AI Helpdesk</span>
            <button type="button" class="close text-white" style="background: none; border: none; font-size: 22px; cursor: pointer; outline: none;" onclick="document.getElementById('ai-chat-box').classList.add('hidden')">&times;</button>
        </div>
        
        <div class="ai-chat-tabs">
            <button class="chat-tab active" data-target="faq-section">FAQ Member</button>
            <button class="chat-tab" data-target="chat-section">Tanya AI</button>
        </div>
        
        <div class="ai-chat-body">
            <div id="faq-section" class="chat-content active">
                <div class="faq-item">
                    <strong>💡 Memilih Konsep/Desain?</strong>
                    <p>Buka tab "Tanya AI" lalu ketik kebutuhanmu (misal: "rekomendasi warna untuk CV profesional" atau "konsep banner pameran yang elegan").</p>
                </div>
                <div class="faq-item">
                    <strong>🛒 Cara Memesan Layanan?</strong>
                    <p>Pilih menu Katalog di navbar, tentukan jenis produk desain, tambahkan ke keranjang belanja, lalu konfirmasi pembayaran.</p>
                </div>
                <div class="faq-item">
                    <strong>⏳ Estimasi Waktu Pengerjaan?</strong>
                    <p>Pengerjaan standar memakan waktu 2-3 hari kerja. Anda akan mendapatkan update berkala langsung di dashboard ini.</p>
                </div>
            </div>
            
            <div id="chat-section" class="chat-content hidden">
                <div id="chat-messages" class="chat-messages">
                    <div class="msg ai">Halo! Saya asisten AI yang siap membantu kamu menentukan konsep/desain terbaik, merekomendasikan warna, serta memandu navigasi fitur di website e-commerce ini. Ada yang bisa saya bantu?</div>
                </div>
                <div class="chat-input-area">
                    <input type="text" id="chat-input" placeholder="Tanyakan seputar desain atau sistem...">
                    <button id="chat-send" class="btn btn-primary btn-sm">Kirim</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Styling Widget Melayang */
#ai-chat-widget { position: fixed; bottom: 20px; right: 20px; z-index: 99999; font-family: Arial, sans-serif; }
#ai-chat-widget .chat-btn {
    background-color: #0d6efd; color: white; width: 60px; height: 60px; border-radius: 50%;
    display: flex; justify-content: center; align-items: center; cursor: pointer; transition: transform 0.3s;
}
#ai-chat-widget .chat-btn:hover { transform: scale(1.1); }
.ai-chat-box { position: absolute; bottom: 75px; right: 0; width: 330px; border-radius: 12px; overflow: hidden; background: #fff; display: flex; flex-direction: column; border: 1px solid #dee2e6; }
.ai-chat-box.hidden { display: none; }
.ai-chat-tabs { display: flex; border-bottom: 1px solid #dee2e6; background: #f8f9fa; }
.chat-tab { flex: 1; padding: 12px; border: none; background: transparent; cursor: pointer; font-weight: bold; color: #495057; outline: none; font-size: 14px; }
.chat-tab.active { background: #fff; border-bottom: 3px solid #0d6efd; color: #0d6efd; }
.ai-chat-body { height: 330px; display: flex; flex-direction: column; background: #fff; }
.chat-content { display: none; flex: 1; overflow-y: auto; padding: 15px; }
.chat-content.active { display: flex; flex-direction: column; }

/* Komponen FAQ & Room Chat */
.faq-item { margin-bottom: 12px; font-size: 13px; border-bottom: 1px dashed #e9ecef; padding-bottom: 8px; text-align: left; }
.faq-item p { margin: 4px 0 0 0; color: #6c757d; line-height: 1.4; }
.chat-messages { flex: 1; overflow-y: auto; display: flex; flex-direction: column; padding-right: 5px; }
.msg { padding: 8px 14px; margin-bottom: 10px; border-radius: 15px; max-width: 85%; font-size: 13px; line-height: 1.4; text-align: left; }
.msg.ai { background: #e9ecef; align-self: flex-start; color: #212529; border-bottom-left-radius: 0; }
.msg.user { background: #0d6efd; color: white; margin-left: auto; align-self: flex-end; border-bottom-right-radius: 0; }
.chat-input-area { display: flex; border-top: 1px solid #dee2e6; padding: 10px; background: #fff; align-items: center; }
.chat-input-area input { flex: 1; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 20px; outline: none; font-size: 13px; }
.chat-input-area button { margin-left: 8px; border-radius: 20px; padding: 6px 15px; font-size: 13px; font-weight: bold; border: none; background-color: #0d6efd; color: white; cursor: pointer; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll(".chat-tab");
    const contents = document.querySelectorAll(".chat-content");
    const chatInput = document.getElementById("chat-input");
    const sendBtn = document.getElementById("chat-send");
    const chatMessages = document.getElementById("chat-messages");

    // Switcher antara Tab FAQ dan Tanya AI
    if(tabs.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener("click", () => {
                tabs.forEach(t => t.classList.remove("active"));
                contents.forEach(c => c.classList.remove("active"));
                tab.classList.add("active");
                document.getElementById(tab.dataset.target).classList.add("active");
            });
        });
    }

    // Eksekusi Pengiriman Pesan ke Endpoint PHP
    if(sendBtn && chatInput) {
        const sendMessage = async () => {
            const message = chatInput.value.trim();
            if (!message) return;

            // Cetak pesan user ke layar widget
            chatMessages.innerHTML += `<div class="msg user">${message}</div>`;
            chatInput.value = "";
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Membuat ID loading unik
            const loadingId = "loading-" + Date.now();
            chatMessages.innerHTML += `<div id="${loadingId}" class="msg ai">Sedang menganalisis konsep...</div>`;
            chatMessages.scrollTop = chatMessages.scrollHeight;

            try {
                // Mengirim request fetch menggunakan endpoint yang jalurnya sudah dikalkulasi dinamis
                const response = await fetch("<?php echo $api_url; ?>", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ message: message })
                });
                const data = await response.json();

                // Hapus efek loading, tampilkan respon Gemini
                const loadingEl = document.getElementById(loadingId);
                if(loadingEl) loadingEl.remove();
                
                chatMessages.innerHTML += `<div class="msg ai">${data.reply}</div>`;
                chatMessages.scrollTop = chatMessages.scrollHeight;
            } catch (error) {
                const loadingEl = document.getElementById(loadingId);
                if(loadingEl) loadingEl.remove();
                chatMessages.innerHTML += `<div class="msg ai" style="color: red;">Gagal memuat respon AI. Pastikan local server aktif.</div>`;
            }
        };

        sendBtn.addEventListener("click", sendMessage);
        chatInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") sendMessage();
        });
    }
});
</script>