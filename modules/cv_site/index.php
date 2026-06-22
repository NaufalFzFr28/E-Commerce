<?php
/**
 * File: modules/cv_site/index.php
 * Deskripsi: Adaptasi Website CV NaufaRu dengan sistem dinamis PHP
 */

include '../../admin/config/db.php';
include '../../functions.php';

// Ambil data pengaturan dan profil CV dari database
$settings = $conn->query("SELECT * FROM site_settings WHERE id = 1")->fetch_assoc();

// Query untuk mengambil data CV berdasarkan kategori (Pendidikan, Pengalaman, dll)
$education_data = $conn->query("SELECT * FROM cv_profile WHERE section_name = 'pendidikan' ORDER BY sort_order ASC");
$experience_data = $conn->query("SELECT * FROM cv_profile WHERE section_name = 'pengalaman' ORDER BY sort_order ASC");
$skills_data = $conn->query("SELECT * FROM cv_profile WHERE section_name = 'keterampilan' ORDER BY sort_order ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaufaRu - My Curriculum Vitae</title>
    
    <link rel="stylesheet" href="../../assets/vendors/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="<?php echo $settings['night_mode_default'] ? 'night-mode' : ''; ?>" data-spy="scroll" data-target=".navbar" data-offset="40" id="home">

    <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="cv-nav">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <img src="../../assets/imgs/naufaru-logo.png" alt="Logo" width="30">
            </a>
            <button class="navbar-toggler" type="button" id="burger-btn">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="#profil">Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pendidikan">Pendidikan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pengalaman">Pengalaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="#keterampilan">Keterampilan</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="header d-flex align-items-center" id="profil">
        <div class="container text-center fade-in-element">
            <h6 class="subtitle">Halo, Nama saya</h6>
            <h1 class="title">Naufal Fauzi Firdaus</h1>
            <p class="lead">Undergraduate IT Student & Creative Professional</p>
            <div class="mt-4">
                <button class="btn btn-primary" onclick="window.print()">Download CV (PDF)</button>
            </div>
        </div>
    </header>

    <section class="section" id="pendidikan">
        <div class="container">
            <h2 class="section-title text-center mb-5">Pendidikan</h2>
            <div class="row">
                <?php while($edu = $education_data->fetch_assoc()): ?>
                <div class="col-md-6 mb-4 fade-in-element">
                    <div class="card p-4 h-100 shadow-sm">
                        <h5 class="text-primary"><?php echo $edu['title']; ?></h5>
                        <p class="text-muted mb-1"><?php echo $edu['period']; ?></p>
                        <p><?php echo $edu['description']; ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/footer.php'; ?>

    <script src="../../assets/vendors/jquery/jquery.min.js"></script>
    <script src="../../assets/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendors/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>