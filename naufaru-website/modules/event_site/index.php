<?php
/**
 * File: modules/event_site/index.php
 * Deskripsi: Website Event NaufaRu - Manajemen & Dokumentasi Acara
 */

include '../../admin/config/db.php';
include '../../functions.php';

// Ambil pengaturan site
$settings = $conn->query("SELECT * FROM site_settings WHERE id = 1")->fetch_assoc();

// Ambil data event (Mendatang & Selesai)
$upcoming_events = $conn->query("SELECT * FROM event_records WHERE status = 'mendatang' ORDER BY event_date ASC");
$past_events = $conn->query("SELECT * FROM event_records WHERE status = 'selesai' ORDER BY event_date DESC LIMIT 6");

// Ambil 1 Event Utama untuk Promo/Hero
$featured_event = $conn->query("SELECT * FROM event_records WHERE status = 'mendatang' LIMIT 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaufaRu - My Event Ecosystem</title>
    
    <link rel="stylesheet" href="../../assets/vendors/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="<?php echo $settings['night_mode_default'] ? 'night-mode' : ''; ?>">

    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <section class="event-hero bg-dark text-white py-5 shadow-sm" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../../assets/imgs/event-bg.webp') center/cover;">
        <div class="container text-center py-5 fade-in-element">
            <?php if($featured_event): ?>
                <h6 class="text-primary text-uppercase font-weight-bold">Acara Mendatang</h6>
                <h1 class="display-4 mb-4"><?php echo $featured_event['event_name']; ?></h1>
                
                <div id="countdown" class="d-flex justify-content-center mb-4" data-date="<?php echo $featured_event['event_date']; ?>">
                    <div class="mx-3">
                        <h2 id="days">00</h2>
                        <small>Hari</small>
                    </div>
                    <div class="mx-3">
                        <h2 id="hours">00</h2>
                        <small>Jam</small>
                    </div>
                    <div class="mx-3">
                        <h2 id="minutes">00</h2>
                        <small>Menit</small>
                    </div>
                </div>
                
                <p class="lead mb-4"><i class="fas fa-map-marker-alt mr-2"></i> <?php echo $featured_event['location']; ?></p>
                <a href="#tentang" class="btn btn-primary btn-lg px-5 shadow">Detail Acara</a>
            <?php else: ?>
                <h1>Belum Ada Acara Mendatang</h1>
                <p>Pantau terus untuk pembaruan selanjutnya.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="section py-5" id="acara">
        <div class="container">
            <h2 class="section-title text-center mb-5">Jadwal Acara</h2>
            <div class="row">
                <?php while($ev = $upcoming_events->fetch_assoc()): ?>
                <div class="col-md-6 mb-4 fade-in-element">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden">
                        <div class="row no-gutters">
                            <div class="col-4 bg-primary text-white d-flex align-items-center justify-content-center flex-column p-3">
                                <h3><?php echo date('d', strtotime($ev['event_date'])); ?></h3>
                                <span><?php echo date('M Y', strtotime($ev['event_date'])); ?></span>
                            </div>
                            <div class="col-8">
                                <div class="card-body">
                                    <h5><?php echo $ev['event_name']; ?></h5>
                                    <p class="small text-muted mb-2"><i class="fas fa-clock"></i> <?php echo $ev['location']; ?></p>
                                    <button class="btn btn-sm btn-outline-primary" onclick="Swal.fire('Info', 'Pendaftaran akan segera dibuka!', 'info')">Daftar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <section class="section py-5 bg-light" id="arsip">
        <div class="container">
            <h2 class="section-title text-center mb-5">Dokumentasi Acara</h2>
            <div class="row">
                <?php while($past = $past_events->fetch_assoc()): ?>
                <div class="col-md-4 mb-4 fade-in-element">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="../../assets/imgs/<?php echo $past['image_cover']; ?>" class="card-img-top" alt="Event Image">
                        <div class="card-body">
                            <h6 class="font-weight-bold"><?php echo $past['event_name']; ?></h6>
                            <p class="small text-muted"><?php echo format_tanggal_indonesia($past['event_date']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <?php include '../../includes/footer.php'; ?>

    <script src="../../assets/vendors/jquery/jquery.min.js"></script>
    <script src="../../assets/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendors/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="../../assets/js/main.js"></script>

    <script>
        // Logika Countdown Timer Sederhana
        const countdownDate = new Date($('#countdown').data('date')).getTime();
        
        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = countdownDate - now;

            const d = Math.floor(distance / (1000 * 60 * 60 * 24));
            const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

            $('#days').text(d < 10 ? '0' + d : d);
            $('#hours').text(h < 10 ? '0' + h : h);
            $('#minutes').text(m < 10 ? '0' + m : m);

            if (distance < 0) {
                clearInterval(x);
                $('#countdown').html("<h4>Acara Sedang Berlangsung</h4>");
            }
        }, 1000);
    </script>
</body>
</html>