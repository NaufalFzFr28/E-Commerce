<?php
/**
 * File: functions.php
 * Deskripsi: Fungsi global untuk translasi, format tanggal, dan logika umum.
 */

/**
 * 1. FORMAT TANGGAL INDONESIA
 * Mengubah timestamp database menjadi format: 19 April 2026
 */
function format_tanggal_indonesia($datetime) {
    if (!$datetime || $datetime == '0000-00-00 00:00:00') return "Belum pernah diperbarui";
    
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $split    = explode(' ', $datetime);
    $tanggal  = explode('-', $split[0]);
    
    return $tanggal[2] . ' ' . $bulan[(int)$tanggal[1]] . ' ' . $tanggal[0];
}

/**
 * 2. SISTEM TRANSLASI (ID, EN, JP)
 * Mengambil teks dari file JSON di folder /languages/
 */
function __($key, $lang = 'id') {
    $path = __DIR__ . "/languages/{$lang}.json";
    if (file_exists($path)) {
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        return $data[$key] ?? $key; // Kembalikan key jika teks tidak ditemukan
    }
    return $key;
}

/**
 * 3. UPDATE TIMESTAMP OTOMATIS
 * Memperbarui waktu 'Last Update' spesifik untuk salah satu bagian web saja.
 * Contoh penggunaan: update_last_modified('last_updated_cv');
 */
function update_last_modified($column) {
    global $conn;
    $allowed_columns = ['last_updated_main', 'last_updated_cv', 'last_updated_event', 'last_updated_invoice'];
    
    if (in_array($column, $allowed_columns)) {
        $stmt = $conn->prepare("UPDATE site_settings SET $column = CURRENT_TIMESTAMP WHERE id = 1");
        return $stmt->execute();
    }
    return false;
}

/**
 * 4. LOGIKA NIGHT MODE
 * Mengecek apakah mode malam aktif berdasarkan database atau session
 */
function is_night_mode() {
    global $conn;
    $query = $conn->query("SELECT night_mode_default FROM site_settings WHERE id = 1");
    $res = $query->fetch_assoc();
    return (bool)$res['night_mode_default'];
}

/**
 * 5. SHORTCUT ALERT (SweetAlert2)
 * Helper untuk memicu alert dari PHP (disimpan di session untuk dibaca JS)
 */
function set_alert($title, $text, $icon) {
    $_SESSION['alert'] = [
        'title' => $title,
        'text'  => $text,
        'icon'  => $icon
    ];
}
?>