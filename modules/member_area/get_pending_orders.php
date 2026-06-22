<?php
session_start();
include '../../config.php';

// 1. Ambil parameter bahasa dari URL (dikirim oleh AJAX dashboard)
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'id';
$json_file = "languages/member_{$lang}.json";

if (file_exists($json_file)) {
    $text = json_decode(file_get_contents($json_file), true);
} else {
    $text = json_decode(file_get_contents("languages/member_id.json"), true);
}

if (!isset($_SESSION['member_id'])) {
    exit('<p class="text-center py-5">Unauthorized Access</p>');
}

$member_id = $_SESSION['member_id'];

// 2. Pemilihan Nama Kolom Berdasarkan Variabel Bahasa Aktif
$col_name = ($lang == 'en') ? 'product_en' : (($lang == 'jp') ? 'product_jp' : 'product_name');

/**
 * QUERY: Mengambil rincian produk adaptif menggunakan GROUP_CONCAT 
 * Berdasarkan kolom lokalisasi database terpilih ($col_name)
 */
$query = "SELECT 
            o.order_number, 
            o.status, 
            o.created_at,
            o.total_price,
            GROUP_CONCAT(p.$col_name SEPARATOR ', ') as product_names, 
            p.gambar_produk 
          FROM orders o
          INNER JOIN order_items oi ON o.id = oi.order_id
          INNER JOIN site_products_promo p ON oi.product_id = p.id
          WHERE o.member_id = '$member_id' 
          AND o.status = 'Pending' 
          GROUP BY o.id 
          ORDER BY o.created_at DESC";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $path_img = "../../../assets/imgs/img-catalog/" . (!empty($row['gambar_produk']) ? $row['gambar_produk'] : 'default.png');
        ?>
        
        <div class="naufaru-order-card animate__animated animate__fadeInUp">
            <div class="card-grid-container">
                
                <div class="grid-left-visual">
                    <div class="image-box">
                        <img src="<?php echo $path_img; ?>" 
                             alt="Product Preview" 
                             onerror="this.src='../../../assets/imgs/placeholder.png';">
                        <div class="pulse-indicator"></div>
                    </div>
                </div>

                <div class="grid-right-info">
                    <div class="info-header">
                        <div class="status-badge-shape">
                            <i class="fas fa-spinner fa-spin"></i> <span><?php echo $text['status_processing']; ?></span>
                        </div>
                        <span class="order-id">REF: #<?php echo $row['order_number']; ?></span>
                    </div>

                    <h5 class="product-title-scramble scramble-text" data-value="<?php echo htmlspecialchars($row['product_names']); ?>">
                        <?php 
                        $names = htmlspecialchars($row['product_names']);
                        echo (strlen($names) > 45) ? substr($names, 0, 45) . "..." : $names; 
                        ?>
                    </h5>

                    <div class="info-footer-shapes">
                        <div class="info-shape shape-price">
                            <div class="shape-icon"><i class="fas fa-wallet"></i></div>
                            <div class="shape-content">
                                <label><?php echo $text['label_total_pay']; ?></label>
                                <strong>Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?></strong>
                            </div>
                        </div>

                        <div class="info-shape shape-time">
                            <div class="shape-icon"><i class="fas fa-calendar-check"></i></div>
                            <div class="shape-content">
                                <label><?php echo $text['label_order_time']; ?></label>
                                <strong><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php
    }
} else {
    echo '
    <div class="empty-state-container text-center py-5">
        <i class="fas fa-receipt fa-3x mb-3 opacity-20"></i>
        <p class="opacity-50">' . $text['pending_empty'] . '</p>
    </div>';
}
?>

<script>
/**
 * NAUFARU TEXT SCRAMBLE ENGINE
 * Dioptimalkan untuk elemen yang dimuat via AJAX
 */
function runNaufaruScramble() {
    const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const scrambleElements = document.querySelectorAll(".scramble-text");

    scrambleElements.forEach(el => {
        // Mencegah running ganda jika interval sudah ada
        if (el.dataset.scrambling === "true") return;
        el.dataset.scrambling = "true";

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
                el.dataset.scrambling = "false";
            }
            iteration += 1 / 3;
        }, 30);
    });
}

// Jalankan saat file dimuat pertama kali
runNaufaruScramble();
</script>

<style>
/* --- NAUFARU PREMIUM ADAPTIVE STYLE --- */
:root {
    --naufaru-red: #EF4C4D;
    --naufaru-gold: #ffc107;
    --naufaru-green: #4cd137;
    --naufaru-blue: #74b9ff;
}

.naufaru-order-card {
    background: rgba(var(--card-bg-rgb, 255, 255, 255), 0.05);
    border: 1px solid rgba(150, 150, 150, 0.15);
    border-radius: 24px;
    margin-bottom: 20px;
    padding: 22px;
    backdrop-filter: blur(15px);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Mode Terang (Light Mode) Adjustment */
body:not(.dark-mode) .naufaru-order-card {
    background: #ffffff;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04);
    border-color: rgba(0, 0, 0, 0.05);
}

.naufaru-order-card:hover {
    border-color: var(--naufaru-red);
    transform: translateY(-5px) scale(1.01);
}

.card-grid-container {
    display: flex;
    align-items: center;
    gap: 30px;
}

/* Visual Kiri */
.grid-left-visual {
    flex: 0 0 110px;
}

.image-box {
    width: 110px;
    height: 110px;
    border-radius: 20px;
    overflow: hidden;
    border: 3px solid rgba(var(--text-main-rgb, 0, 0, 0), 0.1);
    position: relative;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.image-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.pulse-indicator {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 12px;
    height: 12px;
    background: var(--naufaru-gold);
    border-radius: 50%;
    box-shadow: 0 0 15px var(--naufaru-gold);
    animation: naufaru-pulse 2s infinite;
}

/* Info Kanan */
.grid-right-info {
    flex: 1;
}

.product-title-scramble {
    font-size: 1.25rem;
    font-weight: 900;
    margin: 8px 0 18px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-main-member, #222);
}

body.dark-mode .product-title-scramble {
    color: #ffffff;
}

.status-badge-shape {
    background: var(--naufaru-gold);
    color: #000;
    padding: 5px 15px;
    border-radius: 8px 20px 8px 20px;
    font-size: 0.65rem;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
}

.order-id {
    color: #888;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1px;
    float: right;
}

/* Info Shapes Badge */
.info-footer-shapes {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.info-shape {
    display: flex;
    align-items: center;
    padding: 10px 18px;
    border-radius: 15px;
    gap: 12px;
    min-width: 190px;
    border: 1px solid transparent;
}

.shape-icon { font-size: 1.1rem; }

.shape-content label {
    display: block;
    font-size: 0.6rem;
    text-transform: uppercase;
    margin: 0;
    opacity: 0.6;
    font-weight: 800;
    letter-spacing: 0.5px;
}

.shape-content strong {
    font-size: 0.95rem;
    display: block;
    font-weight: 800;
}

/* Color Palette Shapes */
.shape-price {
    background: rgba(39, 174, 96, 0.1);
    border-color: rgba(39, 174, 96, 0.2);
    color: #219150;
}

body.dark-mode .shape-price {
    background: rgba(76, 209, 55, 0.1);
    border-color: rgba(76, 209, 55, 0.2);
    color: var(--naufaru-green);
}

.shape-time {
    background: rgba(52, 152, 219, 0.1);
    border-color: rgba(52, 152, 219, 0.2);
    color: #2980b9;
}

body.dark-mode .shape-time {
    background: rgba(116, 185, 255, 0.1);
    border-color: rgba(116, 185, 255, 0.2);
    color: var(--naufaru-blue);
}

@keyframes naufaru-pulse {
    0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

/* Responsivitas Mobile */
@media (max-width: 768px) {
    .card-grid-container { flex-direction: column; text-align: center; gap: 20px; }
    .grid-left-visual { flex: 0 0 auto; }
    .info-header { justify-content: center; flex-direction: column; gap: 10px; }
    .order-id { float: none; }
    .info-footer-shapes { justify-content: center; }
    .info-shape { min-width: 100%; }
}
</style>