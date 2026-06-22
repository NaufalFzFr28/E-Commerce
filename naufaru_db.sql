-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Jun 2026 pada 14.42
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `naufaru_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cart`
--

INSERT INTO `cart` (`id`, `member_id`, `product_id`, `qty`, `created_at`) VALUES
(25, 7, 10, 1, '2026-05-24 03:46:53'),
(26, 7, 7, 1, '2026-05-24 03:46:56'),
(27, 7, 11, 1, '2026-05-24 03:47:16'),
(28, 7, 8, 1, '2026-05-24 03:47:21');

-- --------------------------------------------------------

--
-- Struktur dari tabel `member_surveys`
--

CREATE TABLE `member_surveys` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `source_answer` varchar(100) NOT NULL,
  `custom_answer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `member_surveys`
--

INSERT INTO `member_surveys` (`id`, `member_id`, `source_answer`, `custom_answer`, `created_at`) VALUES
(1, 3, 'Lainnya', 'gk tau', '2026-05-23 15:06:01'),
(2, 7, 'Teman / Sahabat', '', '2026-05-24 03:25:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `guest_address` text DEFAULT NULL,
  `total_price` decimal(15,2) DEFAULT 0.00,
  `discount` decimal(15,2) DEFAULT 0.00,
  `status` enum('Pending','Process','Finished','Canceled') DEFAULT 'Pending',
  `is_invoice` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `invoice_number`, `invoice_date`, `member_id`, `guest_name`, `guest_address`, `total_price`, `discount`, `status`, `is_invoice`, `created_at`, `updated_at`, `catatan`) VALUES
(1, 'NR-20260510-A976E', 'NR-20260523-8168', '2026-05-23', 3, NULL, NULL, 130000.00, 0.00, 'Finished', 1, '2026-05-10 13:59:43', '2026-05-23 16:46:14', NULL),
(2, 'NR-20260510-0EB43', '0007-20260516-6577', '2026-05-16', 3, NULL, NULL, 30000.00, 0.00, 'Finished', 1, '2026-05-10 14:20:16', '2026-05-16 11:55:50', NULL),
(3, 'NR-20260510-948AE', '0002-2026-05-14', '2026-05-14', 3, NULL, NULL, 173000.00, 0.00, 'Finished', 1, '2026-05-10 14:48:18', '2026-05-14 15:21:08', ''),
(4, 'NR-20260511-22966', '0001-2026-05-14', '2026-05-14', 3, NULL, NULL, 35000.00, 0.00, 'Finished', 1, '2026-05-11 07:28:47', '2026-05-14 07:53:19', NULL),
(5, 'NR-20260511-37D37', NULL, NULL, 3, NULL, NULL, 30000.00, 0.00, 'Finished', 1, '2026-05-11 12:33:52', '2026-05-14 04:30:45', 'tes'),
(6, 'NR-20260514-57EB8', '0003-20260514-1041', '2026-05-14', 7, NULL, NULL, 45000.00, 25000.00, 'Finished', 1, '2026-05-14 15:53:55', '2026-05-14 16:20:04', 'lets go'),
(7, 'ORD-1778937712', '0007-NR-20260516-A490', NULL, 3, NULL, NULL, 18000.00, 0.00, 'Finished', 0, '2026-05-16 13:21:52', '2026-05-16 13:21:52', NULL),
(8, 'ORD-1778937750', '0007-NR-20260516-A490', NULL, 3, NULL, NULL, 18000.00, 0.00, 'Finished', 0, '2026-05-16 13:22:30', '2026-05-16 13:22:30', NULL),
(11, 'ORD-1778940423', '0008-NR-20260516-7306', NULL, NULL, 'Fulan', NULL, 55000.00, 10000.00, 'Finished', 0, '2026-05-16 14:07:03', '2026-05-16 14:07:03', NULL),
(12, 'ORD-1778941561', '0009-NR-20260516-78A9', NULL, NULL, 'Zakaria', NULL, 28000.00, 50000.00, 'Finished', 0, '2026-05-16 14:26:01', '2026-05-16 14:26:01', NULL),
(13, 'ORD-1778949966', '0009-NR-20260516-5C20', NULL, NULL, 'Syab', NULL, 48000.00, 0.00, 'Finished', 0, '2026-05-16 16:46:06', '2026-05-16 16:46:06', NULL),
(14, 'ORD-1779029377', '0010-NR-20260517-8227', NULL, NULL, 'yahaha', 'yntkts', 55000.00, 10000.00, 'Finished', 0, '2026-05-17 14:49:37', '2026-05-17 14:49:37', 'cek dulu'),
(15, 'ORD-1779029428', '0010-NR-20260517-8227', NULL, NULL, 'yahaha', 'yntkts', 55000.00, 10000.00, 'Finished', 0, '2026-05-17 14:50:28', '2026-05-17 14:50:28', 'cek dulu'),
(16, 'ORD-1779029516', '0011-NR-20260517-1688', NULL, NULL, 'budi', 'ciledug', 50000.00, 15000.00, 'Finished', 0, '2026-05-17 14:51:56', '2026-05-17 14:51:56', 'tester'),
(17, 'ORD-1779030050', 'INV-NR-20260517-7CFC', NULL, NULL, 'heru', 'bogor', 35000.00, 25000.00, 'Finished', 0, '2026-05-17 15:00:50', '2026-05-17 15:00:50', 'tes'),
(18, 'ORD-1779030567', '0011-NR-20260517-CCAC', NULL, 3, NULL, NULL, 20000.00, 10000.00, 'Finished', 0, '2026-05-17 15:09:27', '2026-05-17 15:09:27', 'tes'),
(19, 'ORD-1779079659', '0011-NR-20260518-5DE4', NULL, NULL, 'yudi', 'dimana aja', 58000.00, 25000.00, 'Finished', 0, '2026-05-18 04:47:39', '2026-05-18 04:47:39', 'cek n ricek'),
(20, 'ORD-1779445072', '0012-NR-20260522-0390', NULL, NULL, 'yanto', 'papua', 20000.00, 10000.00, 'Finished', 0, '2026-05-22 10:17:52', '2026-05-22 10:17:52', 'mantab'),
(21, 'ORD-1779445129', '0013-NR-20260522-EF42', NULL, 7, NULL, NULL, 25000.00, 5000.00, 'Finished', 0, '2026-05-22 10:18:49', '2026-05-22 10:18:49', 'oke'),
(22, 'ORD-1779592943', '0014-NR-20260524-1BCA', NULL, NULL, 'aldi', 'aldi', 50000.00, 10000.00, 'Finished', 0, '2026-05-24 03:22:23', '2026-05-24 03:22:23', 'tester'),
(23, 'ORD-1779593111', 'INV-NR-20260524-708B', NULL, 7, NULL, NULL, 30000.00, 5000.00, 'Finished', 0, '2026-05-24 03:25:11', '2026-05-24 03:25:11', 'nah'),
(24, 'NR-20260524-7D160', 'NR-20260524-2582', '2026-05-24', 3, NULL, NULL, 160000.00, 20000.00, 'Finished', 1, '2026-05-24 03:58:51', '2026-05-24 04:00:53', 'okehh');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `price_at_order` decimal(15,2) NOT NULL,
  `catatan_item` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `qty`, `price_at_order`, `catatan_item`) VALUES
(1, 1, 7, 2, 30000.00, NULL),
(2, 1, 11, 2, 35000.00, NULL),
(3, 2, 12, 1, 30000.00, NULL),
(4, 3, 10, 1, 30000.00, ''),
(5, 4, 11, 1, 35000.00, NULL),
(6, 5, 10, 1, 30000.00, 'tes'),
(10, 3, 9, 1, 18000.00, ''),
(11, 3, 11, 1, 35000.00, ''),
(12, 3, 12, 1, 30000.00, ''),
(13, 3, 10, 1, 30000.00, ''),
(14, 3, 7, 1, 30000.00, ''),
(15, 6, 11, 2, 35000.00, 'mantab'),
(16, 8, 9, 1, 18000.00, NULL),
(17, 11, 8, 1, 30000.00, NULL),
(18, 11, 11, 1, 35000.00, NULL),
(19, 12, 12, 1, 30000.00, NULL),
(20, 12, 9, 1, 18000.00, NULL),
(21, 12, 10, 1, 30000.00, NULL),
(22, 13, 12, 1, 30000.00, NULL),
(23, 13, 9, 1, 18000.00, NULL),
(24, 15, 11, 1, 35000.00, ''),
(25, 15, 7, 1, 30000.00, ''),
(26, 16, 11, 1, 35000.00, 'cek dulu'),
(27, 16, 10, 1, 30000.00, 'cek dulu'),
(28, 17, 10, 1, 30000.00, ''),
(29, 17, 7, 1, 30000.00, ''),
(30, 18, 8, 1, 30000.00, 'cek dulu'),
(31, 19, 9, 1, 18000.00, 'yahaha'),
(32, 19, 11, 1, 35000.00, 'ntabz'),
(33, 19, 12, 1, 30000.00, 'lets go'),
(34, 20, 12, 1, 30000.00, 'oke'),
(35, 21, 7, 1, 30000.00, ''),
(36, 22, 12, 1, 30000.00, 'tester'),
(37, 22, 8, 1, 30000.00, 'cek'),
(38, 23, 11, 1, 35000.00, 'nah'),
(39, 24, 8, 3, 30000.00, 'okehh'),
(40, 24, 7, 3, 30000.00, 'okehh');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_about`
--

CREATE TABLE `site_about` (
  `id` int(11) NOT NULL,
  `img_front` varchar(255) DEFAULT 'avatar-naufaru-1.jpg',
  `img_back` varchar(255) DEFAULT 'avatar-naufaru-2.jpg',
  `about_title_id` varchar(255) DEFAULT NULL,
  `about_title_en` varchar(255) DEFAULT NULL,
  `about_title_jp` varchar(255) DEFAULT NULL,
  `about_subtitle_id` varchar(255) DEFAULT NULL,
  `about_subtitle_en` varchar(255) DEFAULT NULL,
  `about_subtitle_jp` varchar(255) DEFAULT NULL,
  `p1_id` text DEFAULT NULL,
  `p2_id` text DEFAULT NULL,
  `p3_id` text DEFAULT NULL,
  `p4_id` text DEFAULT NULL,
  `p5_id` text DEFAULT NULL,
  `p1_en` text DEFAULT NULL,
  `p2_en` text DEFAULT NULL,
  `p3_en` text DEFAULT NULL,
  `p4_en` text DEFAULT NULL,
  `p5_en` text DEFAULT NULL,
  `p1_jp` text DEFAULT NULL,
  `p2_jp` text DEFAULT NULL,
  `p3_jp` text DEFAULT NULL,
  `p4_jp` text DEFAULT NULL,
  `p5_jp` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `site_about`
--

INSERT INTO `site_about` (`id`, `img_front`, `img_back`, `about_title_id`, `about_title_en`, `about_title_jp`, `about_subtitle_id`, `about_subtitle_en`, `about_subtitle_jp`, `p1_id`, `p2_id`, `p3_id`, `p4_id`, `p5_id`, `p1_en`, `p2_en`, `p3_en`, `p4_en`, `p5_en`, `p1_jp`, `p2_jp`, `p3_jp`, `p4_jp`, `p5_jp`) VALUES
(1, 'avatar-naufaru-1.jpg', 'avatar-naufaru-2.jpg', 'Naufal FzFr', 'Naufal FzFr', 'ナウファル (能法留)', 'Editor & Fotografer', 'Editor & Photographer', 'エディター、フォトグラファー', 'Assalamu’alaikum. Saya Naufal, atau nama kerennya “Naufal FzFr”. Saat ini, saya sedang menempuh pendidikan di program studi Teknik Informatika. Meskipun memiliki latar belakang di bidang editing seperti foto dan video, saya memutuskan untuk mengejar karier di dunia teknologi informasi yang penuh dengan tantangan pemrograman. Meskipun terbilang sulit, saya berusaha menjadikan pengalaman ini sebagai tambahan keterampilan baru.', 'Selain itu, saya juga memiliki pengalaman dalam pembuatan film pendek, pengeditan foto dan video, desain grafis, seni lukis kaligrafi Arab, dan lain-lain. Terkadang, saya juga mendapatkan panggilan sebagai fotografer atau kameramen dalam berbagai acara. Saya juga memiliki channel YouTube dengan nama “Naufal FzFr”. Meskipun awalnya bercita-cita menjadi seorang masinis, saya justru terjun ke dunia editing, yang kemudian berkembang ke bidang teknologi informasi.', 'Membangun sebuah usaha sendiri bukanlah hal yang mudah. Banyak tantangan yang saya hadapi seiring waktu dalam mengembangkan jasa fotografi. Perjalanan ini dimulai dari menyewa hingga meminjam peralatan, hingga akhirnya, alhamdulillah, sedikit demi sedikit saya mampu membeli perlengkapan yang cukup untuk membangun usaha ini. Setelah memiliki kamera DSLR, saya memberanikan diri untuk mendirikan jasa editor dan fotografi NaufaRu. Pada tanggal 25 Juli 2023, saya resmi menetapkan hari lahirnya jasa ini, yang bertepatan dengan pertama kalinya saya memasang layanan di Google Maps.', 'Mengapa memilih nama NaufaRu (sebelumnya bernama Naufal FzFr)? Sebagian orang merasa sulit melafalkan nama sebelumnya, sehingga saya menyederhanakannya agar lebih mudah diingat. Dalam bahasa Jepang, nama “Naufal” menjadi “Naufaru.” Saya memilih bahasa Jepang karena saya tertarik dengan nuansa dan karakteristik fotografi yang memiliki estetika khas ala Jepang.', 'Meskipun terkadang cukup melelahkan dan penuh tantangan, saya yakin bahwa dengan keikhlasan dan ketekunan, setiap usaha akan membawa berkah. Meskipun hasil karya saya mungkin belum sesuai ekspektasi, saya akan terus berlatih untuk menjadi lebih baik lagi. Saya memohon doa dari teman-teman agar saya dapat meraih kesuksesan dalam karier dan membahagiakan kedua orang tua. Semoga segala impian kita tercapai, termasuk salah satu impian saya untuk berkunjung ke Arab Saudi dan Jepang. Terima kasih. Wassalamu’alaikum.', 'Assalamu\'alaikum. My name is Naufal, or my cool name is \"Naufal FzFr.\" Currently, I am studying Informatics Engineering. Despite having a background in editing such as photo and video, I decided to pursue a career in the field of information technology, which is full of programming challenges. Although it is quite difficult, I am trying to make this experience a new skill.', 'In addition, I also have experience in making short films, editing photos and videos, graphic design, Arabic calligraphy, and others. Sometimes, I also get calls as a photographer or cameraman for various events. I also have a YouTube channel called \"Naufal FzFr.\" Although I initially aspired to be a train driver, I ended up in the world of editing, which then developed into the field of information technology.', 'Building my own business is not easy. I faced many challenges over time in developing photography services. This journey started from renting to borrowing equipment, until finally, alhamdulillah, little by little I was able to buy enough equipment to build this business. After owning a DSLR camera, I dared to establish the editor and photography service NaufaRu. On July 25, 2023, I officially set the birth of this service, which coincided with the first time I set up the service on Google Maps.', 'Why choose the name NaufaRu (previously named \"Naufal FzFr\")? Some people found it difficult to pronounce the previous name, so I simplified it to make it easier to remember. In Japanese, the name \"Naufal\" becomes \"Naufaru.\" I chose Japanese because I am interested in the nuances and characteristics of photography that have a unique Japanese aesthetic.', 'Although it is sometimes tiring and full of challenges, I am sure that with sincerity and perseverance, every effort will bring blessings. Even though my work may not yet meet expectations, I will continue to practice to be even better. I ask for prayers from my friends so that I can achieve success in my career and make my parents happy. May all our dreams come true, including one of my dreams to visit Saudi Arabia and Japan. Thank you. Wassalamu\'alaikum.', 'アッサラーム・アライクム（あなたの上に平安がありますように）。私の名前はナウファル、クールな名前は「Naufal FzFr」です。現在、情報技術工学の学部で学んでいます。 写真やビデオ編集のバックグラウンドがありますが、プログラミングの課題に満ちた情報技術の世界でキャリアを追求することを決めました。 これは難しいことですが、この経験を新しいスキルとして身につけようと努力しています。', 'それ以外にも、私は短編映画制作、写真やビデオ編集、グラフィックデザイン、アラビア語カリグラフィー、その他にも経験があります。 時々、さまざまなイベントでフォトグラファーやカメラマンとして呼ばれることもあります。また、「Naufal FzFr」という名のYouTubeチャンネルも持っています。最初は 機関士になることを夢見ていましたが、編集の世界に入り、その後情報技術の分野へと発展しました。', '自分自身のビジネスを築くことは簡単なことではありませんでした。写真サービスを開発する上で、時間とともに多くの課題に直面しました。この旅は 機材を借りたり、少しずつ買い揃えたりすることから始まり、最終的に、アルハムドゥリッラー（アッラーに感謝）、このビジネスを立ち上げるのに十分な機材を少しずつ購入することができました。一眼レフカメラを手に入れてから、 NaufaRuというエディターおよび写真サービスを立ち上げる勇気を出しました。2023年7月25日に、このサービスをGoogleマップに初めて登録した日に、正式に設立日と定めました。', 'なぜNaufaRu（以前は「Naufal FzFr」）という名前を選んだのですか？一部の人々は以前の名前を発音するのが難しいと感じていたので、 覚えやすいように簡素化しました。日本語では、「Naufal」という名前は「Naufaru」となります。日本語を選んだのは、日本の美学を持つ写真の雰囲気や特徴に興味があるからです。', '時には非常に疲れるし、多くの課題に満ちていますが、誠実さと忍耐があれば、どんな努力も祝福をもたらすと信じています。 私の作品はまだ期待通りではないかもしれませんが、私は常に向上するために練習し続けます。キャリアで成功し、両親を喜ばせることができるように、友人たちの祈りを お願いします。私たちのすべての夢が叶うことを願っています。サウジアラビアと日本を訪れるという私の夢も含まれています。 ありがとうございました。ワッサラーム・アライクム（そしてあなたの上に平安がありますように）。');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_bg_dark`
--

CREATE TABLE `site_bg_dark` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_hero`
--

CREATE TABLE `site_hero` (
  `id` int(11) NOT NULL,
  `main_name` varchar(100) DEFAULT NULL,
  `name_jp` varchar(100) DEFAULT NULL,
  `greeting_id` varchar(100) DEFAULT NULL,
  `greeting_en` varchar(100) DEFAULT NULL,
  `greeting_jp` varchar(100) DEFAULT NULL,
  `desc_id` text DEFAULT NULL,
  `desc_en` text DEFAULT NULL,
  `desc_jp` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_hero`
--

INSERT INTO `site_hero` (`id`, `main_name`, `name_jp`, `greeting_id`, `greeting_en`, `greeting_jp`, `desc_id`, `desc_en`, `desc_jp`) VALUES
(1, 'Naufal FzFr', 'ナウファル', 'Halo, Nama saya', 'Hello, My name', 'こんにちは、私の名前は', 'Seorang Editor, Fotografer, & Kaligrafer', 'An Editor, Photographer, & Calligrapher', 'エディター、フォトグラファー、カリグラファー');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_hero_slides`
--

CREATE TABLE `site_hero_slides` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_hero_slides`
--

INSERT INTO `site_hero_slides` (`id`, `image_path`, `is_default`) VALUES
(1, '1777688751_slide1.png', 0),
(2, '1777688751_slide2.png', 0),
(3, '1777688751_slide3.png', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_portfolio`
--

CREATE TABLE `site_portfolio` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `link_url` text DEFAULT NULL,
  `title_id` varchar(255) DEFAULT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `title_jp` varchar(255) DEFAULT NULL,
  `desc_id` text DEFAULT NULL,
  `desc_en` text DEFAULT NULL,
  `desc_jp` text DEFAULT NULL,
  `price_original` bigint(20) DEFAULT 0,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `price_display` varchar(50) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_portfolio`
--

INSERT INTO `site_portfolio` (`id`, `product_id`, `image_path`, `link_url`, `title_id`, `title_en`, `title_jp`, `desc_id`, `desc_en`, `desc_jp`, `price_original`, `upload_date`, `price_display`, `date_created`) VALUES
(3, 1, '1777433180_Cetak_X_Banner.jpg', 'http://localhost/phpmyadmin/', 'Warung Kopi Vytera', 'Vytera Coffee Shop', 'ワルン・コピ・ヴィテラ (Warung Kopi Vytera)', 'Warnai harimu dengan suasana cozy ala Warkop Vytera! Nikmati secangkir kopi berkualitas sambil merenung dan menemukan ketenangan di sini. Sudahkah Anda merasakan kehangatan yang kami tawarkan?\r\n\r\nButuh sarana promosi serupa? Pesan X-Banner hanya seharga Rp100.000 (biaya desain terpisah).', 'Brighten your day with the cozy vibes of Vytera! Enjoy a cup of premium coffee while reflecting in a peaceful atmosphere. Have you experienced this warmth yet?\r\n\r\nNeed similar promotional media? Order an X-Banner for only Rp100.000 (design fee excluded).', 'ヴィテラの心地よい雰囲気で、一日を彩りましょう！ 上質なコーヒーを飲みながら、ここで静かなひとときを過ごしませんか。この温もりをもう体験しましたか？\r\n\r\nこのようなプロモーションツールが必要ですか？ Xバナー (X-Banner) をわずか10万ルピアで注文できます（デザイン料別）。', 100000, '2026-04-29 04:18:40', NULL, '2026-04-28 15:02:42'),
(4, 1, '1777435931_Takoyaki_Nurhasanah.jpg', '', 'Takoyaki Nurhasanah', 'Nurhasanah Takoyaki', 'タコヤキ・ヌルハサナ (Takoyaki Nurhasanah)', 'Takoyaki lezat ala Hasanah 45! 🐙 Nikmati perpaduan tekstur lembut dan isian premium yang siap membuat lidah Anda bergoyang. Sudahkah Anda mencoba kelezatan autentik dari kami hari ini?\r\n\r\nButuh media promosi seperti ini? Pesan Banner berkualitas hanya Rp18.000/m (biaya desain terpisah).', 'Delicious takoyaki by Hasanah 45! 🐙 Savory, soft, and packed with premium fillings, ready to dance on your tongue. Have you tasted our authentic flavor today?\r\n\r\nNeed promotional media like this? Get your Banner for just Rp18.000/m (design fee excluded).', 'ハサナ45の絶品たこ焼き！🐙 ふんわり食感と贅沢な具材のハーモニーが、あなたの舌を躍らせます。今日の至福の一口はもう体験しましたか？\r\n\r\nこのような宣伝ツールが必要ですか？高品質な バナー (Banner) が1メートルあたりわずか1.8万ルピア（デザイン料別）。', 18000, '2026-04-29 04:18:40', NULL, '2026-04-29 04:12:11'),
(5, 1, '1777437181_Es_Lumut_Nurhasanah.jpg', 'http://localhost/phpmyadmin/', 'Es Lumut Nurhasanah', 'Nurhasanah Moss Ice', 'エス・ルムット・ヌルハサナ', 'Es yang sempurna untuk hari yang sempurna! 🍧❤️ Rasakan kesegaran autentik dengan tekstur lembut yang memanjakan tenggorokan. Pilihan tepat untuk menemani momen spesial Anda agar lebih ceria dan menyegarkan.\r\n\r\nButuh media promosi menarik? Pesan Banner berkualitas hanya seharga Rp100.000 (biaya desain terpisah).', 'The perfect ice for a perfect day! 🍧❤️ Experience authentic freshness with a smooth texture that delights your palate. The ideal choice to make your special moments even more refreshing.\r\n\r\nNeed eye-catching promotional media? Order a high-quality Banner for only Rp100.000 (design fee excluded).', '最高の日のための最高のアイス！🍧❤️ 喉越しを愉しませる滑らかな食感と、本物の爽快感をお届けします。特別なひとときをより鮮やかに彩る、究極の選択です。\r\n\r\n魅力的な宣伝ツールが必要ですか？高品質な バナー (Banner) をわずか10万ルピアで作成いたします（デザイン料別）。', 100000, '2026-04-29 04:33:01', NULL, '2026-04-29 04:33:01'),
(6, 1, '1777437731_Nasi_Liwet_Nurhasanah.jpg', 'http://localhost/phpmyadmin/', 'Nasi Liwet Nurhasanah', 'Nasi Liwet Nurhasanah', 'ナシ・リウェット・ヌルハサナ (Nasi Liwet Nurhasanah)', 'Taklukkan laparmu dengan kelezatan nasi liwet Hasanah 45! 🍛😋 Diracik dengan bumbu rempah autentik dan aroma yang menggugah selera, setiap suapannya menjanjikan kepuasan tiada tara bagi pecinta kuliner tradisional.\r\n\r\nBuat bisnismu lebih menonjol dengan Banner berkualitas hanya Rp18.000/m (biaya desain terpisah).', 'Conquer your hunger with the delicious Nasi Liwet Hasanah 45! 🍛😋 Crafted with authentic spices and a mouth-watering aroma, every bite promises unparalleled satisfaction for traditional food lovers.\r\n\r\nMake your business stand out with a high-quality Banner for just Rp18.000/m (design fee excluded).', 'ハサナ45の絶品ナシ・リウェットで空腹を征服しましょう！🍛😋 本格的なスパイスと食欲をそそる香りで、伝統料理ファンを虜にする至福の味わいをお届けします。\r\n\r\n高品質な バナー (Banner) でビジネスを目立たせませんか？ 1メートルあたりわずか1.8万ルピア（デザイン料別）。', 18000, '2026-04-29 04:42:11', NULL, '2026-04-29 04:42:11'),
(7, 3, '1777438096_Buku_Tahunan_SMP_Nusantara_Plus.jpg', 'http://localhost/phpmyadmin/', 'Buku Tahunan SMP Nusantara Plus', 'SMP Nusantara Plus Yearbook', 'SMPヌサンタラ・プラス 卒業アルバム (Yearbook)', 'Kenangan indah di antara teman-teman terbaik semasa SMP Nusantara Plus! 🎓✨ Kami hadirkan buku tahunan dengan kualitas premium untuk mengabadikan setiap momen berharga masa sekolahmu menjadi abadi dan elegan.\r\n\r\nDapatkan layanan Cetak Buku Tahunan dengan 4 pilihan paket: Pemotretan, Desain, atau kombinasi keduanya hingga Cetak. Konsultasikan harga dan paket untuk proyek besar sekolah Anda sekarang!', 'Beautiful memories among best friends at SMP Nusantara Plus! 🎓✨ We provide premium quality yearbooks to capture every precious school moment, making them timeless and elegant.\r\n\r\nGet our Yearbook Printing service with 4 package options: Photo Session, Design, or a complete bundle including Printing. Contact us for price consultations and large-scale school projects!', 'SMPヌサンタラ・プラスの親友たちとの素敵な思い出！🎓✨ 大切な学校生活の瞬間を、永遠に美しく残るプレミアムな卒業アルバムに収めます。\r\n\r\n卒業アルバム制作 (Yearbook Printing) は、撮影、デザイン、印刷を含む4つのプランから選べます。大規模な学校プロジェクトの価格相談や打ち合わせも承っております！', 150000, '2026-04-29 04:48:16', NULL, '2026-04-29 04:48:16'),
(8, 3, '1777438290_Buku_Tahunan_MI_Ad_Diyanah.jpg', 'http://localhost/phpmyadmin/', 'Buku Tahunan MI Ad-Diyanah', 'MI Ad-Diyanah Yearbook', 'MIアド・ディヤナ 卒業アルバム (Yearbook)', 'Kenangan yang takkan pernah pudar bersama kawan-kawanku, MI Ad-Diyanah 📚✨ Kami mengabadikan setiap senyum dan langkah awal pendidikan Anda dalam sebuah mahakarya visual yang eksklusif, rapi, dan tahan lama.\r\n\r\nDapatkan layanan Cetak Buku Tahunan dengan 4 paket fleksibel: Pemotretan, Desain, hingga Cetak. Konsultasikan harga dan rencana proyek besar sekolah Anda bersama kami!', 'Memories that will never fade with my friends at MI Ad-Diyanah 📚✨ We capture every smile and early step of your education in an exclusive, neat, and durable visual masterpiece.\r\n\r\nOur Yearbook Printing service offers 4 flexible packages: Photography, Design, or full Printing. Contact us to discuss pricing and large-scale school projects!', 'MIアド・ディヤナの友だちとの、決して色あせない思い出 📚✨ 教育の第一歩とすべての笑顔を、美しく耐久性に優れた特別な一冊に収めます。\r\n\r\n卒業アルバム制作 (Yearbook Printing) は、撮影、デザイン、印刷の4プランをご用意。大規模な学校プロジェクトの相談や打ち合わせも随時承っております！', 150000, '2026-04-29 04:51:30', NULL, '2026-04-29 04:51:30'),
(9, 4, '1777438547_Cetak_Foto_A4___Bingkai.jpg', 'http://localhost/phpmyadmin/', 'Cetak Foto A4 + Bingkai', 'A4 Photo Print + Frame', 'A4写真プリント + フレーム', 'Tampilkan kenangan Anda dengan cara yang istimewa. Bingkai foto + cetak kami akan membantu menghidupkan momen-momen terbaik dalam gambar Anda. Ciptakan kenangan yang abadi dengan kualitas cetak tajam dan bingkai elegan (Putih/Hitam) yang mempercantik setiap sudut ruangan!\r\n\r\nLayanan Cetak Foto tersedia mulai Rp35.000 (biaya desain terpisah). Tersedia pilihan ekonomis atau paket Rp50.000 yang tahan air & awet lama. Tambah editing background ala studio hanya Rp30.000!', 'Display your memories in a special way. Our photo frame + print will help bring your best moments to life. Create lasting memories with sharp print quality and elegant frames (White/Black) that beautify any room!\r\n\r\nPhoto Printing service starts from Rp35.000 (design fee excluded). Choose our economic option or the Rp50.000 waterproof & durable package. Add studio-style background editing for just Rp30.000!', 'あなたの思い出を特別な形で飾りましょう。私たちのフォトフレーム＋プリントは、最高の瞬間を鮮やかに蘇らせます。シャープな印刷品質と、お部屋を彩るエレガントなフレーム（白・黒）で、永遠の思い出を！\r\n\r\n写真プリント (Photo Printing) サービスは35,000ルピアから（デザイン料別）。防水・高耐久の5万ルピアパックもご用意。スタジオ風の背景編集もプラス3万ルピアで承ります！', 35000, '2026-04-29 04:55:47', NULL, '2026-04-29 04:55:47'),
(10, 4, '1777442713_Kolase_Foto_A4___Bingkai.jpg', 'http://localhost/phpmyadmin/', 'Kolase Foto A4 + Bingkai', 'A4 Photo Collage + Frame', 'A4フォトコラージュ + フレーム', 'Sebongkah seni dalam setiap momen. Jadikan kenangan Anda lebih indah dengan kreasi unik ini di mana setiap foto dipotong manual sesuai pola dan disusun artistik. Ciptakan sejarah visual Anda sendiri dengan menambahkan elemen personal seperti emas atau cincin di dalam bingkai yang estetik!\r\n\r\nDapatkan layanan Cetak Foto kolase ini hanya seharga Rp70.000, sudah termasuk hiasan pop-up dan bingkai eksklusif.', 'A piece of art in every moment. Make your memories more beautiful with this unique creation where photos are hand-cut and artistically arranged. Create your own visual history by adding personal items like gold or rings inside this aesthetic frame!\r\n\r\nGet this Photo Printing collage service for only Rp70.000, including pop-up decorations and an exclusive frame.', 'すべての瞬間にひとしずくのアートを。手作業でカットされた写真が織りなすユニークな作品で、思い出をより美しく彩りましょう。金や指輪などの大切な品を添えて、あなただけのビジュアルヒストリーを！\r\n\r\nこの高品質な 写真プリント (Photo Printing) コラージュは、ポップアップ装飾とフレーム付きでわずか7万ルピアです。', 70000, '2026-04-29 06:05:13', NULL, '2026-04-29 06:05:13'),
(11, 5, '1777443300_Poster_Paket_Khitan_dr__Suzie_BAS.jpg', 'http://localhost/phpmyadmin/', 'Poster Paket Khitan dr. Suzie BAS', 'dr. Suzie BAS Circumcision Package Poster', 'ドクター・スージーBAS 割礼パッケージポスター (Poster)', 'Raih kesempurnaan dengan Paket Khitan Anak dan Dewasa! 🌟 Kunjungi Klinik 24 Jam dan Rumah Bersalin dr. Suzie BAS untuk layanan profesional dengan hasil terbaik dan terpercaya. Pastikan Anda memilih paket yang tersedia untuk kenyamanan buah hati dan keluarga! 💫\r\n\r\nButuh media cetak informatif? Pesan Poster bahan artpaper (120g/150g) hanya Rp15.000 (biaya desain terpisah). Kami menerima jasa desain kustom dengan metode Amati-Tiru-Modifikasi!', 'Achieve perfection with our Child and Adult Circumcision Packages! 🌟 Visit dr. Suzie BAS 24-Hour Clinic and Maternity House for professional services with trusted results. Make sure to choose the available packages for your family\'s comfort! 💫\r\n\r\nNeed informative print media? Order an artpaper Poster (120g/150g) for only Rp15.000 (design fee excluded). We accept custom designs—send us your reference and we will refine it for you!', '子供と大人のための割礼パッケージで完璧なケアを！🌟 24時間診療のドクター・スージーBASクリニック＆産院では、信頼のおけるプロの技術を提供しています。ご家族の安心のために、最適なパッケージをお選びください！💫\r\n\r\n情報発信に最適な ポスター (Poster) はいかがですか？アートペーパー使用でわずか1.5万ルピア（デザイン料別）。カスタムデザインも承ります。お手持ちの参考資料をベースにした制作も可能です！', 15000, '2026-04-29 06:15:00', NULL, '2026-04-29 06:15:00'),
(12, 1, '1777444307_Paket_Khitan_dr__Suzie_BAS.jpg', 'http://localhost/phpmyadmin/', 'Paket Khitan dr. Suzie BAS', 'dr. Suzie BAS Circumcision Package', 'ドクター・スージーBAS 割礼パッケージ (Circumcision Package)', 'Paket Khitan dr. Suzie BAS, solusi komprehensif untuk kebaikan anak Anda! 🌟✂️ Kami menghadirkan layanan medis profesional dengan metode modern yang aman dan nyaman, memastikan proses pemulihan si kecil berjalan optimal dan penuh perhatian.\r\n\r\nDapatkan informasi lengkap dengan X-Banner menarik kami hanya seharga Rp100.000 (biaya desain terpisah). Solusi promosi praktis untuk klinik Anda!', 'dr. Suzie BAS Circumcision Package, a comprehensive solution for your child\'s well-being! 🌟✂️ We provide professional medical services with safe and comfortable modern methods, ensuring an optimal and attentive recovery process for your little one.\r\n\r\nGet all the details with our eye-catching X-Banner for only Rp100.000 (design fee excluded). A practical promotional solution for your clinic!', 'ドクター・スージーBASの割礼パッケージは、お子様の健康のための総合的なソリューションです！🌟✂️ 安全で快適な最新手法を用いた専門的な医療サービスを提供し、お子様の回復プロセスを丁寧かつ最適にサポートします。\r\n\r\n詳細情報は魅力的な Xバナー (X-Banner) でご確認いただけます。わずか10万ルピアで作成可能です（デザイン料別）。クリニックの宣伝にぜひ！', 100000, '2026-04-29 06:31:47', NULL, '2026-04-29 06:31:47'),
(13, 7, '1777648975_Paket_Khitan_dr__Suzie_BAS.jpg', 'http://localhost/phpmyadmin/', 'Paket Khitan dr. Suzie BAS', 'dr. Suzie BAS Circumcision Package', 'ドクター・スージーBAS 割礼パッケージ (Circumcision Package)', 'Dapatkan Sertifikat Khitan yang berkesan di Klinik dr. Suzie BAS! 🎉✨ Jadikan momen khitan lebih istimewa dengan desain elegan sebagai bukti keberanian si kecil yang bisa dikenang selamanya. Kualitas cetak premium kami memastikan setiap detail tampak istimewa.\r\n\r\nPesan Sertifikat kustom ini dengan biaya desain hanya Rp30.000 (belum termasuk biaya finishing). Hubungi kami hari ini! 📜😊', 'Get a memorable Circumcision Certificate at dr. Suzie BAS Clinic! 🎉✨ Make this milestone extra special with an elegant design as a lasting tribute to your little one\'s bravery. Our premium quality ensures every detail looks stunning.\r\n\r\nOrder this custom Certificate with a design fee of only Rp30.000 (finishing fees excluded). Contact us today! 📜😊', 'Berikut adalah draf caption untuk promosi Anda:\r\n\r\nPaket Khitan dr. Suzie BAS\r\nDapatkan Sertifikat Khitan yang berkesan di Klinik dr. Suzie BAS! 🎉✨ Jadikan momen khitan lebih istimewa dengan desain elegan sebagai bukti keberanian si kecil yang bisa dikenang selamanya. Kualitas cetak premium kami memastikan setiap detail tampak istimewa.\r\n\r\nPesan Sertifikat kustom ini dengan biaya desain hanya Rp30.000 (belum termasuk biaya finishing). Hubungi kami hari ini! 📜😊\r\n\r\ndr. Suzie BAS Circumcision Package\r\nGet a memorable Circumcision Certificate at dr. Suzie BAS Clinic! 🎉✨ Make this milestone extra special with an elegant design as a lasting tribute to your little one\'s bravery. Our premium quality ensures every detail looks stunning.\r\n\r\nOrder this custom Certificate with a design fee of only Rp30.000 (finishing fees excluded). Contact us today! 📜😊\r\n\r\nドクター・スージーBAS 割礼パッケージ (Circumcision Package)\r\nドクター・スージーBASクリニックで、心に残る割礼証書を！🎉✨ お子様の勇気の証として、エレガントなデザインで特別な瞬間を永遠に残しましょう。高品質な仕上がりで、大切な思い出を彩ります。\r\n\r\nこのカスタム 証書 (Certificate) のデザイン料はわずか3万ルピアです（仕上げ費用別）。詳細はお気軽にお問い合わせください！📜😊', 30000, '2026-05-01 15:22:55', NULL, '2026-05-01 15:22:55'),
(14, 5, '1777649867_Singkongeuy_Promo.jpg', 'http://localhost/phpmyadmin/', 'Singkongeuy Flyer Promo', 'Singkongeuy Flyer Promo', 'シンコンウイ・フライヤープロモ (Singkongeuy Flyer Promo)', 'Jajanan unik Singkongeuy siap bikin ngiler! 🤤✨ Nagih euy dengan cita rasa singkong yang renyah dan bumbu melimpah. Nikmati kelezatan camilan kekinian yang diracik khusus untuk memanjakan lidah Anda di setiap gigitan. 🎨🔥\r\n\r\nSampaikan pesan bisnismu lewat Flyer menarik dengan biaya desain hanya Rp30.000 (belum termasuk biaya finishing).', 'The unique Singkongeuy snack is ready to make your mouth water! 🤤✨ It\'s addictive with its crispy texture and rich seasonings. Enjoy the deliciousness of this modern snack, specially crafted to pamper your taste buds in every bite. 🎨🔥\r\n\r\nSpread your business message with an eye-catching Flyer for a design fee of only Rp30.000 (finishing fees excluded).', 'ユニークなスナック、シンコンウイが食欲をそそります！🤤✨ カリカリの食感と豊かな味付けで、一口ごとに至福の味わいをお届け。現代風にアレンジされたキャッサバの美味しさをぜひ体験してください。🎨🔥', 30000, '2026-05-01 15:37:32', NULL, '2026-05-01 15:37:32'),
(16, 5, '1777650452_Tasyakuran___Aqiqah_Info.jpg', 'http://localhost/phpmyadmin/', 'Tasyakuran & Aqiqah Info', 'Tasyakuran & Aqiqah Info', '感謝祭＆アキカ情報 (Tasyakuran & Aqiqah Info)', 'Ciptakan momen bahagia yang tak terlupakan dengan flyer spesial untuk tasyakuran dan aqiqah! 🎉✨ Kami menghadirkan desain eksklusif di atas kertas art paper berkualitas setebal karton untuk mengabadikan rasa syukur Anda dengan tampilan yang elegan dan premium. 🌈👶\r\n\r\nBagikan kebahagiaan dengan Flyer satu lembar yang praktis. Biaya desain hanya Rp30.000 (belum termasuk biaya finishing). Pesan sekarang!', 'Create unforgettable happy moments with our special flyers for Tasyakuran and Aqiqah! 🎉✨ We provide exclusive designs on premium, cardstock-like art paper to commemorate your gratitude with an elegant and high-quality look. 🌈👶\r\n\r\nShare your joy with a practical, single-sheet Flyer. Design fee is only Rp30.000 (finishing fees excluded). Order now!', '感謝祭（タシャクラン）やアキカのための特別なフライヤーで、忘れられない幸せな瞬間を！🎉✨ 高品質なアートペーパーを使用し、あなたの感謝の気持ちをエレガントでプレミアムなデザインに仕上げます。🌈👶\r\n\r\n実用的な1枚刷りの フライヤー (Flyer) で喜びを分かち合いましょう。デザイン料はわずか3万ルピアです（仕上げ費用別）。今すぐご注文を！', 30000, '2026-05-01 15:47:32', NULL, '2026-05-01 15:47:32'),
(17, 8, '1777650731_Label_Makanan___Superfood.jpg', 'http://localhost/phpmyadmin/', 'Label Makanan - Superfood', 'Superfood Food Label', 'スーパーフード 食品ラベル (Food Label)', 'Berikan makananmu sentuhan super dengan label berkualitas tinggi! 🌿✨ Label makanan superfood kami membantu produkmu bersinar di pasaran dengan material premium yang tahan lama dan desain yang memikat. Jadikan identitas brand Anda terlihat lebih profesional dan terpercaya di mata pelanggan! 🏷️💚\r\n\r\nTingkatkan nilai jual produk dengan Label kustom. Biaya desain hanya Rp30.000 (belum termasuk biaya finishing).', 'Give your food a super touch with high-quality labels! 🌿✨ Our superfood food labels help your products shine in the market with premium, durable materials and captivating designs. Make your brand identity look more professional and trusted by customers! 🏷️💚\r\n\r\nEnhance your product\'s value with custom Labels. Design fee is only Rp30.000 (finishing fees excluded).', '高品質なラベルで、あなたの食品に特別な輝きを！🌿✨ 私たちのスーパーフード食品ラベルは、耐久性に優れたプレミアム素材と魅力的なデザインで、製品の市場価値を高めます。プロフェッショナルで信頼されるブランドアイデンティティを築きましょう！🏷️💚\r\n\r\nカスタム ラベル (Label) で商品の魅力をアップ。デザイン料はわずか3万ルピアです（仕上げ費用別）。', 30000, '2026-05-01 15:52:11', NULL, '2026-05-01 15:52:11'),
(18, 5, '1777651017_Promosi_Ala_Ala_Nyaleg.jpg', 'http://localhost/phpmyadmin/', 'Promosi Ala-Ala Nyaleg', 'Election-Style Promotion', '選挙風プロモーション (Election-Style Promotion)', 'Berikan sentuhan nyaleg pada bisnis Anda! 🚀✨ Dapatkan jasa desain ala-ala nyaleg yang memukau untuk membangun citra yang kuat dan berwibawa di mata pelanggan. Dengan gaya unik yang ikonik, bisnis Anda pasti mencuri perhatian dan menjadi pusat pembicaraan! 💼🎨\r\n\r\nSampaikan visi bisnismu melalui Flyer satu lembar berbahan art paper tebal. Biaya desain hanya Rp30.000 (belum termasuk biaya finishing).', 'Give your business an election-style touch! 🚀✨ Get stunning election-themed designs to build a strong and authoritative image. With an iconic and unique style, your business will definitely grab attention and become the talk of the town! 💼🎨\r\n\r\nSpread your vision with a premium, single-sheet Flyer made of thick art paper. Design fee is only Rp30.000 (finishing fees excluded).', 'あなたのビジネスに選挙風のインパクトを！🚀✨ 強力で権威あるイメージを築く、魅力的な選挙風デザインを提供します。アイコニックでユニークなスタイルで、注目を集めること間違いなし！💼🎨\r\n\r\n厚手のアートペーパーを使用した1枚刷りの フライヤー (Flyer) でビジョンを伝えましょう。デザイン料はわずか3万ルピアです（仕上げ費用別）。', 30000, '2026-05-01 15:56:57', NULL, '2026-05-01 15:56:57'),
(19, 8, '1777651252_PT__Hobi_Wisata_Tour___Travel.jpg', 'http://localhost/phpmyadmin/', 'PT. Hobi Wisata Tour & Travel', 'PT. Hobi Wisata Tour & Travel', 'PT. ホビ・ウィサタ・ツアー＆トラベル (PT. Hobi Wisata Tour & Travel)', 'Jelajahi dunia bersama PT. Hobi Wisata! 🌍✈️ Kami menghadirkan petualangan seru dengan pelayanan terbaik dan rute eksklusif yang dirancang untuk kenyamanan perjalanan Anda. Wujudkan impian liburan mewah dan berkesan bersama mitra perjalanan terpercaya! 🗺️🚀\r\n\r\nInformasi lengkap ada di Brosur kami (kertas artpaper 120g/150g). Biaya desain hanya Rp30.000 (belum termasuk biaya finishing).', 'Explore the world with PT. Hobi Wisata! 🌍✈️ We offer exciting adventures with top-tier service and exclusive routes designed for your comfort. Turn your dream vacation into a memorable reality with a trusted travel partner! 🗺️🚀\r\n\r\nDetailed info is available in our Brochure (120g/150g artpaper). Design fee is only Rp30.000 (finishing fees excluded).', 'PT. ホビ・ウィサタと一緒に世界を旅しましょう！🌍✈️ 最高のサービスと快適な専用ルートで、刺激的な冒険をお届けします。信頼できるパートナーと共に、夢のバカンスを現実に！🗺️🚀\r\n\r\n詳細は パンフレット (Brochure) をご覧ください（アートペーパー120g/150g使用）。デザイン料はわずか3万ルピアです（仕上げ費用別）。', 30000, '2026-05-01 16:00:52', NULL, '2026-05-01 16:00:52'),
(20, 1, '1777651476_Summer_Trip_SMK_Yadika_5_x_PT__Hobi_Wisata.jpg', 'http://localhost/phpmyadmin/', 'Summer Trip SMK Yadika 5 x PT. Hobi Wisata', 'Summer Trip SMK Yadika 5 x PT. Hobi Wisata', 'SMKヤディカ5 サマートリップ x PT. ホビ・ウィサタ (Summer Trip)', 'Sukseskan Summer Trip SMK Yadika 5 dengan banner eksklusif dari PT Hobi Wisata! 🌴🚌 Kami menghadirkan media informasi berkualitas tinggi yang tidak cepat pudar untuk mengabadikan momen perjalanan sekolah Anda agar lebih berkesan, profesional, dan tak terlupakan. 🌞🎓\r\n\r\nPesan Banner kustom ukuran bebas dengan biaya desain hanya Rp30.000 (biaya finishing terpisah). Kami menerima segala desain dengan metode Amati-Tiru-Modifikasi!', 'Make the SMK Yadika 5 Summer Trip a success with an exclusive banner from PT Hobi Wisata! 🌴🚌 We provide high-quality, fade-resistant information media to make your school trip more memorable, professional, and unforgettable. 🌞🎓\r\n\r\nOrder a custom-sized Banner for a design fee of only Rp30.000 (finishing fees excluded). We accept all designs using the Observe-Imitate-Modify method!', 'PT. ホビ・ウィサタの専用バナーで、SMKヤディカ5のサマートリップを成功させましょう！🌴🚌 色あせしにくい高品質な素材で、学校の旅行をより印象深く、プロフェッショナルで忘れられない思い出にします。🌞🎓\r\n\r\nサイズオーダー可能な バナー (Banner) のデザイン料はわずか3万ルピアです（仕上げ費用別）。お客様の参考資料を元にした制作も承っております！', 30000, '2026-05-01 16:04:36', NULL, '2026-05-01 16:04:36'),
(21, 1, '1777651580_Summer_Trip_PT_Bina_Flora_x_PT_Hobi_Wisata.jpg', 'http://localhost/phpmyadmin/', 'Summer Trip PT Bina Flora x PT Hobi Wisata', 'PT Bina Flora x PT Hobi Wisata Summer Trip', 'PTビナ・フローラ x PTホビ・ウィサタ サマートリップ (Summer Trip)', 'Jadikan liburanmu berkesan dengan banner keren dari saya! 🌺🚌 Kolaborasi seru PT Bina Flora & PT Hobi Wisata ini menghadirkan perjalanan tak terlupakan dengan kualitas cetak premium yang tajam, berbahan baik, dan tidak cepat pudar. 🌟🏖️\r\n\r\nPesan Banner ukuran kustom dengan biaya desain hanya Rp30.000 (biaya finishing terpisah). Kami menerima desain kustom dengan metode Amati-Tiru-Modifikasi!', 'Make your holiday memorable with my cool banner! 🌺🚌 This exciting collaboration between PT Bina Flora & PT Hobi Wisata offers an unforgettable journey with premium, sharp print quality that is durable and fade-resistant. 🌟🏖️\r\n\r\nOrder a custom-sized Banner for a design fee of only Rp30.000 (finishing fees excluded). We accept all designs using the Observe-Imitate-Modify method!', '素敵なバナーで、最高の思い出を！🌺🚌 PTビナ・フローラとPTホビ・ウィサタのコラボレーションにより、色あせしにくい高品質な素材で忘れられない旅を彩ります。🌟🏖️\r\n\r\nサイズオーダー可能な バナー (Banner) のデザイン料はわずか3万ルピアです（仕上げ費用別）。お客様の参考資料を元にした制作も承っております！', 30000, '2026-05-01 16:06:20', NULL, '2026-05-01 16:06:20'),
(22, 3, '1777651941_Cetak_Buku_Custom_Cerpen_Novel.jpg', 'http://localhost/phpmyadmin/', 'Cetak Buku Custom Cerpen/Novel', 'Custom Short Story/Novel Book Printing', '短編小説・小説のカスタムブック制作 (Custom Book Printing)', 'Birukan impianmu dengan cerpenmu sendiri! 📘🌈 Kami hadir untuk mewujudkan kisah unik atau tugas Anda menjadi buku fisik yang elegan dengan pilihan kertas HVS atau book paper berkualitas. Abadikan cerita kehidupan Anda dalam cetakan yang rapi, profesional, dan berkelas!\r\n\r\nDapatkan layanan Cetak Buku custom ukuran bebas dengan harga mulai Rp50.000 (tergantung ketebalan halaman). Kami juga menerima jasa desain buku untuk Anda.', 'Bring your dreams to life with your own short stories! 📘🌈 We are here to turn your unique tales or assignments into elegant physical books with quality HVS or book paper options. Immortalize your life stories in a neat, professional, and classy print!\r\n\r\nGet our custom-sized Book Printing service starting from Rp50.000 (depending on page thickness). We also offer professional book design services.', 'あなた自身の短編小説で夢を形にしましょう！📘🌈 独自の物語や課題を、高品質なHVSまたはブックペーパーを使用したエレガントな実体本に仕上げます。 あなたの人生の物語を、整ったプロフェッショナルで高級感のある印刷で永遠に残しましょう！\r\n\r\n自由なサイズの 本制作 (Book Printing) サービスは5万ルピアから（ページの厚さによる）。 本のデザイン制作も承っております！', 50000, '2026-05-01 16:12:21', NULL, '2026-05-01 16:12:21'),
(23, 6, '1777652083_Kalender_Duduk_2024___Naufal_FzFr_Limited_Edition.jpg', 'http://localhost/phpmyadmin/', 'Kalender Duduk 2024 - Naufal FzFr Limited Edition', '2024 Desk Calendar - Naufal FzFr Limited Edition', '2024年 卓上カレンダー - Naufal FzFr 限定版', 'Selamat tinggal 2023, sambut tahun baru dengan kalender 2024 yang keren dan informatif! 📅✨ Edisi terbatas ini hadir dengan desain visioner-perfeksionis pada kertas artpaper 120g berkualitas tinggi. Setiap lembarnya penuh inspirasi untuk menemani produktivitas Anda sepanjang tahun dengan tampilan yang elegan dan eksklusif. 🌟🎉\r\n\r\nDapatkan Kalender Duduk dalam berbagai ukuran dengan pilihan laminasi glossy atau doff mulai dari Rp30.000/kalender.', 'Goodbye 2023, welcome the new year with a cool and informative 2024 calendar! 📅✨ This limited edition features a visionary-perfectionist design on high-quality 120g artpaper. Every page is full of inspiration to accompany your productivity throughout the year with an elegant and exclusive look. 🌟🎉\r\n\r\nGet this Desk Calendar in various sizes with glossy or doff lamination starting from Rp30.000/calendar.', 'さよなら2023年、クールで情報満載の2024年カレンダーで新年を迎えましょう！📅✨ この限定版は、高品質な120gアートペーパーに完璧主義的なデザイン を施しています。エレガントで独創的な外観で、一年中あなたの創造性を刺激し続けます。🌟🎉\r\n\r\n様々なサイズとグロス・マット加工が選べる 卓上カレンダー (Desk Calendar) は、1冊3万ルピアから。', 50000, '2026-05-01 16:14:43', NULL, '2026-05-01 16:14:43'),
(24, 1, '1779118248_Singkongeuy_X_Banner_Promo.jpg', 'http://localhost/naufaru-website/', 'Singkongeuy X-Banner Promo', 'Singkongeuy X-Banner Promo', 'シンコンウイ Xバナープロモ (Singkongeuy X-Banner Promo)', 'Tampil beda, jualan makin laris! X-banner Singkongeuy memberikan sentuhan magis pada promosi dengan visual produk yang menggoda selera, bikin nagih euy! Jadikan gerai Anda pusat perhatian dengan desain yang mencolok dan profesional.\r\n\r\nTingkatkan daya tarik tokomu dengan X-Banner berkualitas. Biaya desain hanya Rp30.000 (belum termasuk biaya finishing).\r\n', 'Stand out and boost your sales! Singkongeuy X-banner adds a magical touch to your promotion with mouth-watering visuals that keep customers coming back!  Make your stall the center of attention with a bold and professional look.\r\n\r\nBoost your store\'s appeal with a high-quality X-Banner. Design fee is only Rp30.000 (finishing fees excluded).\r\n', '他と差をつけて、売り上げを伸ばしましょう！ シンコンウイのXバナーは、食欲をそそるビジュアルでプロモーションに魔法をかけ、リピーター続出間違いなし！ 大胆でプロフェッショナルなデザインで、お店を注目の的に。\r\n\r\n高品質な Xバナー (X-Banner) で集客力をアップ！デザイン料はわずか10万ルピアです（仕上げ費用別）。', 100000, '2026-05-18 15:30:48', NULL, '2026-05-18 15:30:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_portfolio_alerts`
--

CREATE TABLE `site_portfolio_alerts` (
  `id` int(11) NOT NULL,
  `text_id` text NOT NULL,
  `text_en` text DEFAULT NULL,
  `text_jp` text DEFAULT NULL,
  `link_text_id` varchar(100) DEFAULT NULL,
  `link_text_en` varchar(100) DEFAULT NULL,
  `link_text_jp` varchar(100) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_portfolio_alerts`
--

INSERT INTO `site_portfolio_alerts` (`id`, `text_id`, `text_en`, `text_jp`, `link_text_id`, `link_text_en`, `link_text_jp`, `link_url`, `is_active`, `created_at`) VALUES
(1, 'Punya rencana desain yang ingin dibuat?', 'Have a design plan you want to create?', '作成したいデザインプランはありますか？', 'Chat sekarang!', 'Chat now!', '今すぐチャット！', 'https://wa.me/62895330141019', 1, '2026-05-02 00:42:14'),
(2, 'Menerima jasa finishing untuk desain Anda.', 'Accepting finishing services for your designs.', 'デザインの仕上げサービスを受け付けています。', NULL, NULL, NULL, NULL, 1, '2026-05-02 00:42:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_products`
--

CREATE TABLE `site_products` (
  `id` int(11) NOT NULL,
  `product_slug` varchar(50) NOT NULL,
  `product_name_id` varchar(100) DEFAULT NULL,
  `product_name_en` varchar(100) DEFAULT NULL,
  `product_name_jp` varchar(100) DEFAULT NULL,
  `base_price` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_products`
--

INSERT INTO `site_products` (`id`, `product_slug`, `product_name_id`, `product_name_en`, `product_name_jp`, `base_price`, `is_active`) VALUES
(1, 'banner', 'Banner/X-Banner', 'Banner/X-Banner', 'バナー/Xバナー', 0.00, 1),
(2, 'stiker', 'Stiker', 'Sticker', 'ステッカー', 0.00, 1),
(3, 'buku', 'Cetak Buku', 'Book Printing', '書籍印刷', 0.00, 1),
(4, 'foto', 'Cetak Foto', 'Photo Printing', '写真印刷', 0.00, 1),
(5, 'flyer', 'Flyer & Poster', 'Flyer & Poster', 'チラシとポスター', 0.00, 1),
(6, 'kalender', 'Kalender & Lembar Balik', 'Calendar & Flipchart', 'カレンダーとフリップチャート', 0.00, 1),
(7, 'sertifikat', 'Sertifikat/Piagam', 'Certificate', '証明書', 0.00, 1),
(8, 'lainnya', 'Lainnya', 'Others', 'その他', 0.00, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_products_promo`
--

CREATE TABLE `site_products_promo` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_en` varchar(255) DEFAULT NULL,
  `product_jp` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `deskripsi_en` text DEFAULT NULL,
  `deskripsi_jp` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `kategori_en` varchar(100) DEFAULT NULL,
  `kategori_jp` varchar(100) DEFAULT NULL,
  `gambar_produk` varchar(255) DEFAULT 'placeholder.png',
  `stok` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_products_promo`
--

INSERT INTO `site_products_promo` (`id`, `product_name`, `product_en`, `product_jp`, `deskripsi`, `deskripsi_en`, `deskripsi_jp`, `price`, `kategori`, `kategori_en`, `kategori_jp`, `gambar_produk`, `stok`, `is_active`, `created_at`) VALUES
(7, 'Desain Sertifikat Custom', 'Custom Certificate Design', 'カスタム表彰状・証書デザイン', 'Berikan penghargaan yang elegan dan berkelas dengan desain sertifikat kustom yang eksklusif! Kami menghadirkan sentuhan visual yang profesional untuk mengapresiasi setiap prestasi, memastikan setiap penerimanya merasa bangga dengan piagam yang memiliki nilai estetika tinggi dan desain yang autentik.\r\n\r\nDapatkan Sertifikat dengan kualitas desain premium hanya seharga Rp30.000. Anda cukup mengirimkan referensi gaya dan data isi di dalamnya, lalu biarkan kami yang menyusunnya dengan rapi dan memukau sesuai keinginan Anda!', 'Give an elegant and classy recognition with an exclusive custom certificate design! We provide a professional visual touch to appreciate every achievement, ensuring every recipient feels proud with a certificate that boasts high aesthetic value and authentic design.\r\n\r\nGet a premium quality certificate design for only IDR 30,000. Simply send us your style references and content details, and let us arrange them neatly and stunningly according to your wishes!', '高級感あふれる特別なカスタムデザインで、心に残る表彰を。私たちは、あらゆる実績を称えるためにプロフェッショナルな視覚的演出を施し、受け取った方が誇りに思えるような、高い美意識と独自性を兼ね備えた証書をお届けします。\r\n\r\nプレミアム品質の証書デザインがわずか30,000ルピア。ご希望のスタイル（参考資料）と記載内容をお送りいただくだけで、プロの手で美しく、魅力的なデザインに仕上げます！', 30000.00, 'Sertifikat/Piagam', '', '', 'cat_1778028900_69fa91641f35b.jpg', 0, 1, '2026-05-06 00:55:00'),
(8, 'Desain Banner Custom', 'Custom Banner Design', 'カスタムバナーデザイン', 'Wujudkan citra visual yang kuat dengan desain banner custom yang eksklusif dan profesional! Kami menghadirkan solusi kreatif untuk menonjolkan pesan Anda dengan tampilan yang menarik, estetik, dan sesuai dengan identitas brand Anda agar lebih berkesan bagi audiens.\r\n\r\nDapatkan Banner dengan berbagai ukuran custom sesuai kebutuhan Anda mulai dari Rp30.000 dengan kualitas standar 280gr. Cukup kirimkan referensi desain Anda dan kami akan mewujudkannya!', 'Establish a powerful visual image with our exclusive and professional custom banner designs! We provide creative solutions to highlight your message with an attractive, aesthetic look that aligns perfectly with your brand identity to leave a lasting impression on your audience.\r\n\r\nGet custom-sized banners tailored to your needs starting from just IDR 30,000 for standard 280gsm quality. Simply send us your design references, and we will bring them to life!', '独占的でプロフェッショナルなカスタムバナーデザインで、強力なビジュアルイメージを実現しましょう！お客様のブランドアイデンティティに合わせ、メッセージをより魅力的に、より美しく引き立てるクリエイティブなソリューションを提供し、ターゲットの心に残るデザインを制作します。\r\n\r\n標準280g品質のカスタムサイズバナーが、用途に合わせて30,000ルピアから。デザインの参考資料をお送りいただくだけで、私たちが理想の形に仕上げます！', 30000.00, 'Banner/X-Banner', '', '', 'cat_1778028978_69fa91b215000.jpg', 0, 1, '2026-05-06 00:56:18'),
(9, 'Cetak Banner Standar 280gr', 'Standard Banner Printing (280gsm)', '標準バナー印刷（280g）', 'Hadirkan pesan bisnis Anda dengan visual yang tajam dan profesional melalui layanan cetak banner kustom kami. Kami menjamin hasil cetakan berkualitas tinggi yang presisi, estetik, dan dirancang khusus untuk memperkuat identitas brand Anda agar tampil lebih menonjol serta berkesan di mata setiap audiens.\r\n\r\nDapatkan Banner dengan berbagai ukuran sesuai kebutuhan Anda hanya seharga Rp18.000/m untuk kualitas standar 280gr. Cukup kirimkan referensi desain Anda dan biarkan kami mewujudkan media promosi terbaik untuk Anda!', 'Deliver your business message with sharp and professional visuals through our custom banner printing services. We guarantee high-quality, precise, and aesthetic prints specifically designed to strengthen your brand identity, ensuring it stands out and leaves a lasting impression on every audience.\r\n\r\nGet banners in various sizes according to your needs for only IDR 18,000/m for our standard 280gsm quality. Simply send your design references and let us create the best promotional media for you!', 'カスタムバナー印刷サービスを通じて、あなたのビジネスメッセージを鮮明かつプロフェッショナルなビジュアルで伝えましょう。ブランドアイデンティティを強化し、ターゲットの目に留まり、記憶に残るような、高精度で審美性の高い高品質なプリントをお約束します。\r\n\r\n標準的な280g品質のバナーが、用途に合わせたサイズ展開で1メートルあたりわずか18,000ルピア。デザインの参考資料をお送りいただくだけで、最高のプロモーションメディアを形にします！', 18000.00, 'Banner/X-Banner', '', '', 'catalog_upd_1778047124_69fad894e4e3a.jpg', 0, 1, '2026-05-06 00:57:12'),
(10, 'Desain Poster Custom', 'Custom Poster Design', 'カスタムポスター・チラシデザイン', 'Ciptakan dampak visual yang kuat dan memukau dengan desain poster atau flyer kustom yang profesional! Kami menghadirkan solusi kreatif dengan presisi tinggi untuk menonjolkan pesan Anda, memastikan setiap detail tampil estetik, informatif, dan mampu mencuri perhatian audiens dalam sekejap.\r\n\r\nDapatkan desain berkualitas hanya seharga Rp30.000. Anda cukup mengirimkan referensi gaya serta data isi desain, dan kami akan mewujudkannya dengan hasil terbaik! Pesan banyak desain sekarang untuk mendapatkan harga yang jauh lebih murah.', 'Create a powerful and stunning visual impact with a professional custom poster or flyer design! We provide high-precision creative solutions to highlight your message, ensuring every detail is aesthetic, informative, and capable of grabbing your audience\'s attention instantly.\r\n\r\nGet a high-quality design for only IDR 30,000. Simply send your style references and design content, and we will bring them to life with the best results! Order multiple designs now to get an even lower price.', 'プロフェッショナルなカスタムデザインで、見る人を一瞬で惹きつける強力なビジュアルインパクトを！細部にまでこだわった高い精度とクリエイティブな解決策で、あなたのメッセージをより魅力的に、より分かりやすく伝えます。\r\n\r\n高品質なデザイン制作がわずか30,000ルピア。ご希望のスタイル（参考資料）と掲載内容をお送りいただくだけで、最高の仕上がりを実現します。まとめてのご注文なら、さらにさらにお得な価格で提供いたします！', 30000.00, 'Flyer & Poster', '', '', 'cat_1778031531_69fa9babaeea2.jpg', 0, 1, '2026-05-06 01:38:51'),
(11, 'Cetak Foto A4 + Bingkai', 'A4 Photo Print + Frame', 'A4写真プリント ＋ フレーム付き', 'Abadikan momen berharga Anda dengan kualitas cetak tajam yang elegan dan berkelas! Kami menghadirkan perpaduan sempurna antara kertas foto standar berkualitas dengan bingkai eksklusif yang dirancang untuk mempercantik dekorasi ruangan Anda sekaligus menjaga kenangan tetap hidup selamanya.\r\n\r\nDapatkan paket Cetak Foto praktis dan ekonomis ini hanya seharga Rp35.000. Bingkai tersedia dalam pilihan warna hitam dan putih yang minimalis. Pesan sekarang untuk mengabadikan momen terbaik Anda!', 'Preserve your precious moments with sharp, elegant, and classy print quality! We offer the perfect blend of high-quality standard photo paper and exclusive frames designed to enhance your room decor while keeping your memories alive forever.\r\n\r\nGet this practical and affordable Photo Print package for only IDR 35,000. Frames are available in minimalist Black or White. Order now to capture your best moments!', '大切な瞬間を、鮮明で気品ある高品質なプリントで残しませんか？ 高品質なフォトペーパーと、お部屋のインテリアを彩る限定フレームをセットでお届けします。あなたの思い出をいつまでも美しく、鮮やかに。\r\n\r\nこの実用的でお得な写真プリントセットは、わずか35,000ルピア。フレームはミニマルなブラックとホワイトの2色からお選びいただけます。今すぐご注文して、最高の瞬間を形にしましょう！', 35000.00, 'Cetak Foto', '', '', 'cat_1778031953_69fa9d513a130.jpg', 0, 1, '2026-05-06 01:45:53'),
(12, 'Cetak Kalender Duduk', 'Print Desk Calendars', '卓上カレンダー印刷', 'Jadikan setiap meja lebih bermakna dengan kalender duduk eksklusif yang memadukan fungsionalitas dan estetika tinggi! Kami menghadirkan media pengingat waktu berkualitas premium yang dirancang untuk mempercantik ruang kerja Anda sekaligus menjaga jadwal tetap terorganisir dengan tampilan yang profesional dan elegan.\r\n\r\nDapatkan Kalender Duduk custom dengan harga mulai dari Rp30.000/pcs. Pesan dalam jumlah banyak untuk mendapatkan penawaran harga yang lebih murah (harga belum termasuk jasa desain).', 'Make every desk more meaningful with an exclusive desk calendar that blends high functionality and aesthetics! We provide premium-quality timekeepers designed to beautify your workspace while keeping your schedule organized with a professional and elegant look.\r\n\r\nGet your custom Desk Calendar starting from IDR 30,000/pcs. Order in bulk to get a lower price (price excludes design services).', '機能性と高いデザイン性を兼ね備えた独占的な卓上カレンダーで、すべてのデスクをより特別なものにしましょう！私たちは、プロフェッショナルでエレガントな外観でスケジュール管理をサポートしながら、ワークスペースを彩るプレミアム品質のカレンダーをお届けします。\r\n\r\nカスタム卓上カレンダーは1個30,000ルピアから。まとめ買いでさらに割引いたします（デザイン料金は含まれておりません）。', 30000.00, 'Kalender & Lembar Balik', '', '', 'cat_1778050589_69fae61d483ca.jpg', 0, 1, '2026-05-06 06:56:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_products_sale`
--

CREATE TABLE `site_products_sale` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `kategori` enum('barang','jasa') NOT NULL,
  `gambar_produk` varchar(255) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_promotion`
--

CREATE TABLE `site_promotion` (
  `id` int(11) NOT NULL,
  `img_primary` varchar(255) DEFAULT NULL,
  `img_secondary` varchar(255) DEFAULT NULL,
  `btn_url` text DEFAULT NULL,
  `title_id` varchar(255) DEFAULT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `title_jp` varchar(255) DEFAULT NULL,
  `caption_id` text DEFAULT NULL,
  `caption_en` text DEFAULT NULL,
  `caption_jp` text DEFAULT NULL,
  `btn_text_id` varchar(100) DEFAULT NULL,
  `btn_text_en` varchar(100) DEFAULT NULL,
  `btn_text_jp` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `site_promotion`
--

INSERT INTO `site_promotion` (`id`, `img_primary`, `img_secondary`, `btn_url`, `title_id`, `title_en`, `title_jp`, `caption_id`, `caption_en`, `caption_jp`, `btn_text_id`, `btn_text_en`, `btn_text_jp`) VALUES
(1, '', '', 'https://www.instagram.com/reel/DHOQBxKTPCr/', 'Apa yang baru?', 'What\'s New?', '新着情報', 'Mari saksikan video sinematik reels yang menarik dari kegiatan Bukber dan Makrab tim Hello Multimedia. ', 'Watch interesting cinematic reels from the Bukber and Makrab events of the Hello Multimedia team. ', 'Hello Multimediaチームの「Bukber」と「Makrab」イベントの魅力的なシネマティックリールをご覧ください。', 'Lihat Selengkapnya ', 'View More ', 'もっと見る ');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `site_name` varchar(100) DEFAULT 'NaufaRu',
  `last_updated_main` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_updated_cv` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_updated_event` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_updated_invoice` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `night_mode_default` tinyint(1) DEFAULT 0,
  `portfolio_grid_desktop` int(1) DEFAULT 3,
  `team_hover_color_1` varchar(20) DEFAULT '#EF4C4D',
  `team_hover_color_2` varchar(20) DEFAULT '#f39c12'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_settings`
--

INSERT INTO `site_settings` (`id`, `site_name`, `last_updated_main`, `last_updated_cv`, `last_updated_event`, `last_updated_invoice`, `night_mode_default`, `portfolio_grid_desktop`, `team_hover_color_1`, `team_hover_color_2`) VALUES
(1, 'NaufaRu', '2026-05-22 15:36:50', '2026-05-22 15:36:50', '2026-05-22 15:36:50', '2026-05-22 15:36:50', 0, 3, '#ef4c4d', '#f39c12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_skills`
--

CREATE TABLE `site_skills` (
  `id` int(11) NOT NULL,
  `skill_name_id` varchar(255) DEFAULT NULL,
  `skill_name_en` varchar(255) DEFAULT NULL,
  `skill_name_jp` varchar(255) DEFAULT NULL,
  `percentage` int(3) DEFAULT 0,
  `order_index` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `site_skills`
--

INSERT INTO `site_skills` (`id`, `skill_name_id`, `skill_name_en`, `skill_name_jp`, `percentage`, `order_index`) VALUES
(1, 'Editing Foto', 'Photo Editing', '写真編集', 95, 1),
(2, 'Editing Video', 'Video Editing', '動画編集', 92, 2),
(3, 'Editor', 'Editor', 'エディター', 94, 3),
(4, 'Fotografer', 'Photographer', 'フォトグラファー', 93, 4),
(5, 'Videografer', 'Videographer', 'ビデオグラファー', 90, 5),
(6, 'Kaligrafer', 'Calligrapher', 'カリグラファー', 90, 6);

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_stats`
--

CREATE TABLE `site_stats` (
  `id` int(11) NOT NULL,
  `subscribers` varchar(50) DEFAULT NULL,
  `followers` varchar(50) DEFAULT NULL,
  `orders` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_stats`
--

INSERT INTO `site_stats` (`id`, `subscribers`, `followers`, `orders`) VALUES
(1, '675', '796', '150+');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_team`
--

CREATE TABLE `site_team` (
  `id` int(11) NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `name_id` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ja` varchar(100) NOT NULL,
  `role_id` varchar(100) NOT NULL,
  `role_en` varchar(100) NOT NULL,
  `role_ja` varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_team`
--

INSERT INTO `site_team` (`id`, `photo_path`, `name_id`, `name_en`, `name_ja`, `role_id`, `role_en`, `role_ja`, `sort_order`, `is_active`, `created_at`) VALUES
(4, 'team_e9fe8f3a1c.png', 'Naufal F. Firdaus', 'Naufal F. Firdaus', 'ナウファル・F・フィルダウス', 'Owner of NaufaRu & Lead of Hello Multimedia', 'Owner of NaufaRu & Lead of Hello Multimedia', '「NaufaRu」代表 兼「Hello Multimedia」リードディレクター', 1, 1, '2026-05-23 04:19:34'),
(5, 'team_2cc7aa500b.png', 'Hanafi N. S. Wahid', 'Hanafi N. S. Wahid', 'ハナフィ・N・S・ワヒド', 'Social Media Specialist at Hello Multimedia', 'Social Media Specialist at Hello Multimedia', '「Hello Multimedia」コンテンツクリエイター', 2, 1, '2026-05-23 06:28:04'),
(6, 'team_b36ba69301.png', 'Andri Saputra', 'Andri Saputra', 'アンドリ・サプトラ', 'Associate Photographer at Hello Multimedia', 'Associate Photographer at Hello Multimedia', '「Hello Multimedia」専属フォトグラファー', 3, 1, '2026-05-23 06:37:13'),
(7, 'team_fcd9cd3856.png', 'Bahrudin Alfian', 'Bahrudin Alfian', 'バハルディン・アルフィアン', 'Aerial Videographer at Hello Multimedia', 'Aerial Videographer at Hello Multimedia', '「Hello Multimedia」空撮ビデオグラファー', 4, 1, '2026-05-23 06:39:46'),
(8, 'team_7ba936a48f.png', 'Gilang D. Prasetya', 'Gilang D. Prasetya', 'ギラン・D・プラセティヤ', 'Videographer at Hello Multimedia', 'Videographer at Hello Multimedia', '「Hello Multimedia」ビデオグラファー', 5, 1, '2026-05-23 06:44:05'),
(9, 'team_d9ef1c4f97.png', 'Dwiki Saputra', 'Dwiki Saputra', 'ドゥウィキ・サプトラ', '3D Artist at Hello Multimedia', '3D Artist at Hello Multimedia', '「Hello Multimedia」3Dアーティスト', 6, 1, '2026-05-23 06:45:38'),
(10, 'team_c6b8b713ce.png', 'Hadid Abdillah AM.', 'Hadid Abdillah AM.', 'ハディッド・アブディラー・AM', 'Associate Photographer at Hello Multimedia', 'Associate Photographer at Hello Multimedia', '「Hello Multimedia」専属フォトグラファー', 7, 1, '2026-05-23 06:47:43'),
(11, 'team_72eff9042f.png', 'Afriyan T. Prasetyo', 'Afriyan T. Prasetyo', 'アフリヤン・T・プラセティヤ', 'Production Assistant at Hello Multimedia', 'Production Assistant at Hello Multimedia', '「Hello Multimedia」プロダクションアシスタント', 8, 1, '2026-05-23 06:49:15'),
(12, 'team_d40c1bc291.png', 'M. Fahri Rahmana', 'M. Fahri Rahmana', 'M・ファフリ・ラマナ', 'Multimedia Crew at Hello Multimedia', 'Multimedia Crew at Hello Multimedia', '「Hello Multimedia」照明＆特機技術スタッフ', 9, 1, '2026-05-23 06:55:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_testimonials`
--

CREATE TABLE `site_testimonials` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `manual_name` varchar(255) DEFAULT NULL,
  `manual_photo` varchar(255) DEFAULT NULL,
  `pekerjaan` varchar(100) NOT NULL,
  `review_text` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_testimonials`
--

INSERT INTO `site_testimonials` (`id`, `order_id`, `member_id`, `manual_name`, `manual_photo`, `pekerjaan`, `review_text`, `is_active`, `created_at`) VALUES
(5, 24, 3, '', NULL, 'Pengusaha Ternak Lele', 'Desainnya bisa request, keren banget. Jangan ragu pesan disini ya.', 1, '2026-05-25 10:21:47'),
(6, 6, 7, '', NULL, 'Guru Sekolah', 'Sangat berkualitas editing foto videonya. Semoga lancar usahanya :)', 1, '2026-05-25 10:23:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_testi_alerts`
--

CREATE TABLE `site_testi_alerts` (
  `id` int(11) NOT NULL,
  `text_id` text NOT NULL,
  `text_en` text DEFAULT NULL,
  `text_jp` text DEFAULT NULL,
  `link_text_id` varchar(100) DEFAULT NULL,
  `link_text_en` varchar(100) DEFAULT NULL,
  `link_text_jp` varchar(100) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_testi_alerts`
--

INSERT INTO `site_testi_alerts` (`id`, `text_id`, `text_en`, `text_jp`, `link_text_id`, `link_text_en`, `link_text_jp`, `link_url`, `is_active`) VALUES
(2, 'Berikut review jujur para pelanggan saya.', 'Here are honest reviews from my customers.', 'これらは私の顧客からの正直なレビューです。', '', NULL, NULL, '', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_users`
--

CREATE TABLE `site_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_video_alerts`
--

CREATE TABLE `site_video_alerts` (
  `id` int(11) NOT NULL,
  `text_id` text NOT NULL,
  `text_en` text DEFAULT NULL,
  `text_jp` text DEFAULT NULL,
  `link_text_id` varchar(255) DEFAULT NULL,
  `link_text_en` varchar(255) DEFAULT NULL,
  `link_text_jp` varchar(255) DEFAULT NULL,
  `link_url` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_video_alerts`
--

INSERT INTO `site_video_alerts` (`id`, `text_id`, `text_en`, `text_jp`, `link_text_id`, `link_text_en`, `link_text_jp`, `link_url`, `is_active`, `created_at`) VALUES
(1, 'Ada banyak konten menarik di akun YouTube saya.', 'There\'s a lot of cool content on my YouTube channel.', '私のYouTubeチャンネル、面白いコンテンツがたくさんあるよ。', 'Cek sekarang!', 'Check it out now!', '今すぐチェックしてみてね！', 'https://www.youtube.com/@NaufalFzFr/videos', 1, '2026-05-19 14:46:21'),
(2, 'Jangan lupa untuk klik subscribe channel saya.', 'Make sure to hit that subscribe button!', 'チャンネル登録も忘れずにね。', 'Visit sekarang!', 'Head over to my channel now!', '今すぐ見に来て！', 'https://www.youtube.com/channel/UCpxYQZBR9XxDMRLRr3nhR4w', 1, '2026-05-19 14:53:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_video_portfolio`
--

CREATE TABLE `site_video_portfolio` (
  `id` int(11) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `title_id` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `title_jp` varchar(255) DEFAULT NULL,
  `desc_id` text NOT NULL,
  `desc_en` text DEFAULT NULL,
  `desc_jp` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_video_portfolio`
--

INSERT INTO `site_video_portfolio` (`id`, `video_url`, `title_id`, `title_en`, `title_jp`, `desc_id`, `desc_en`, `desc_jp`, `is_active`, `created_at`) VALUES
(1, 'https://youtu.be/4gPA1-J_4l8?si=strZ7qOjj3bURODe', 'Birthday Party - Gendhis Nayyara Sigi || Cinematic Documentation Video', 'Birthday Party - Gendhis Nayyara Sigi || Cinematic Documentation Video', 'バースデーパーティー - ゲンディス・ナイヤラ・シギ || シネマティック・ドキュメンテーション・ビデオ', 'Saksikan sebuah kegiatan lucu dan berkesan dari kegiatan ulang tahun/milad adik Gendhis Nayyara Sigi. Semoga diulang tahunnya ini menjadi anak yang shalihah, cerdas, dan berbakti kepada kedua orangtuanya, aamiin. Bagaimanakah kegiatannya, saksikan sekarang!', 'Witness the adorable and memorable moments from the birthday/milad celebration of little sister Gendhis Nayyara Sigi. May she grow up to be a pious (shalihah), smart, and devoted daughter to her parents, Aamiin. How did the celebration go? Watch it now!', 'ゲンディス・ナイヤラ・シギちゃんの、可愛くて思い出に残るバースデー（誕生祭）の様子をぜひご覧ください。この素晴らしい1年が、彼女にとって賢く、信仰深く（シャリハ）、そして両親を大切にする優しい子へと成長する歩みとなりますように。アミーン。\r\nどんな素敵なパーティーになったのでしょうか？今すぐご覧ください！', 1, '2026-05-19 15:34:39'),
(2, 'https://youtu.be/ByfndHvp9Fg?si=HocES7ClliLWXO_L', 'Wedding - Dinda & Tegar || Cinematic Documentation Video', 'Wedding - Dinda & Tegar || Cinematic Documentation Video', 'ウェディング - ディンダ ＆ テガール || シネマティック・ドキュメンテーション・ビデオ', 'Sebuah dokumentasi pernikahan yang berkesan, dikemas dalam bentuk video dokumentasi sinematik yang belum pernah anda lihat sebelumnya. Video ini berisikan prosesi akad nikah hingga resepsi. Bagaimanakah kegiatannya, saksikan sekarang!', 'A truly memorable wedding documentation, beautifully captured in a cinematic video like you\'ve never seen before. This video covers the entire journey, from the solemn wedding vow (Akad Nikah) to the beautiful reception. How did the celebration unfold? Watch it now!', 'これまでに見たことのないような、美しいシネマティック映像で綴る感動的な結婚式の記録です。厳かな挙式（アカ・ニカ）から華やかな披露宴までの特別な瞬間をたっぷりと収録しています。お二人の素晴らしい門出の様子を、ぜひ今すぐご覧ください！', 1, '2026-05-19 15:52:03'),
(3, 'https://youtu.be/VzZ4DVH7akU?si=0gpztiHjU8GS6vq1', 'Aqiqah Thanksgiving - Hilman Jamil Prasya || Cinematic Documentation Video', 'Aqiqah Thanksgiving - Hilman Jamil Prasya || Cinematic Documentation Video', 'アキカ生誕祝賀会 - ヒルマン・ジャミル・プラシャ || シネマティック・ドキュメンテーション・ビデオ', 'Saksikan sebuah dokumentasi berharga dari acara syukuran aqiqah adik Hilman Jamil Prasya. Semoga menjadi adik yang cerdas, pintar, dan shalih, aamiin. Bagaimanakah kegiatan aqiqahnya, saksikan sekarang!', 'Witness a precious documentation of the Aqiqah thanksgiving celebration for baby brother Hilman Jamil Prasya. May he grow up to be a smart, clever, and pious (shalih) boy, Aamiin. How did the Aqiqah celebration go? Watch it now!', 'ヒルマン・ジャミル・プラシャくんの、大切なアキカ（生誕記念・感謝祭）の記録をぜひご覧ください。これからの成長の中で、賢く、聡明で、信仰深い（サリフ）男の子になりますように。アミーン。\r\nどのような温かいお祝いになったのでしょうか？今すぐご覧ください！', 1, '2026-05-19 15:53:46'),
(4, 'https://youtu.be/9ZP8_F6H37k?si=FFWhF-kgnEYi8L2x', 'Cinematic PKM - Universitas Pamulang x SMKN 1 Tangerang Selatan || Cinematic Documentation Video', 'Cinematic PKM - Universitas Pamulang x SMKN 1 Tangerang Selatan || Cinematic Documentation Video', 'シネマティック PKM - パムラン大学 × タンゲラン・スラタン第1職業高校 || シネマティック・ドキュメンテーション・ビデオ', 'Berikut adalah sebuah video dokumentasi sinematik sebuah kegiatan PKM atau Program Kreativitas Mahasiswa dari mahasiswa Universitas Pamulang (Unpam) prodi Teknik Informatika semester 5 yang dilaksanakan di SMKN 1 Tangerang Selatan (NESTAN). Keseruan dan kreativitas menjadi nilai plus dalam kegiatan kali ini, dipenuhi oleh momen-momen yang inspiratif yang diharapkan menjadi kenangan dimasa yang akan datang.', 'Here is a cinematic documentation video of the PKM (Student Creativity Program) activity, presented by the 5th-semester Informatics Engineering students from Universitas Pamulang (Unpam), held at SMKN 1 Tangerang Selatan (NESTAN). Excitement and creativity truly highlighted this event, filled with inspiring moments that will hopefully become cherished memories for the future.', 'パムラン大学（Unpam）情報工学科の5学期生が、タンゲラン・スラタン第1職業高校（NESTAN）で実施した「PKM（学生創意工夫プログラム）」のシネマティック・ドキュメンテーション映像です。今回の活動は、楽しさとクリエイティビティが最大の魅力であり、将来の大切な思い出となるような、インスピレーションに満ちた瞬間がたくさん詰まっています。ぜひご覧ください！', 1, '2026-05-21 04:41:44'),
(5, 'https://youtu.be/SYjhhGC2GDY?si=2koVp7X5ux4-I764', 'Behind The Scenes & Result - Soft Selling & Hard Selling Video Promo || STIKes Pertamedika', 'Behind The Scenes & Result - Soft Selling & Hard Selling Video Promo || STIKes Pertamedika', 'メイキング＆完成映像 - ソフトセリング＆ハードセリング プロモーションビデオ || STIKesペルタメディカ', 'Berikut adalah dokumentasi dibuang sayang, behind the scenes, dan result dari sebuah video promo (soft selling & hard selling) yang dilaksanakan di STIKes Pertamedika. Yuk intip keseruannya.', 'Here is the \"too good to throw away\" documentation, behind-the-scenes footage, and the final results of a promotional video project (featuring both soft-selling & hard-selling concepts) filmed at STIKes Pertamedika. Let’s take a sneak peek at all the excitement and fun!', 'STIKesペルタメディカで実施されたプロモーションビデオ（ソフトセリング＆ハードセリング）の、未公開の蔵出し映像、メイキング（舞台裏）、そして完成した本編映像の記録です。撮影現場の楽し気で熱気あふれる様子を、ぜひ覗いてみてください！', 1, '2026-05-21 04:54:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_wallpaper`
--

CREATE TABLE `site_wallpaper` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `theme_mode` varchar(10) NOT NULL DEFAULT 'all',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_wallpaper`
--

INSERT INTO `site_wallpaper` (`id`, `image_path`, `theme_mode`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'splash_bg_1_8b3b65eb.jpg', 'all', 1, 1, '2026-04-19 10:54:01'),
(2, 'splash_bg_2_0db7c935.jpg', 'all', 2, 1, '2026-04-19 10:54:01'),
(3, 'splash_bg_3_72517c26.jpg', 'all', 3, 1, '2026-04-19 10:54:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin123', 'admin123');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users_member`
--

CREATE TABLE `users_member` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT 'default-member.png',
  `no_hp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users_member`
--

INSERT INTO `users_member` (`id`, `username`, `password`, `nama_lengkap`, `foto_profil`, `no_hp`, `alamat`, `pekerjaan`, `created_at`) VALUES
(3, 'Naufal', 'Naufal', 'Naufal FzFr', 'member_1777986554.jpg', '0895330141019', 'Ciputat', NULL, '2026-05-05 13:09:14'),
(7, 'Syabila09', 'Syabila09', 'Syabila', 'member_1778773941.jpg', '081234567890', 'Sukabumi', NULL, '2026-05-14 15:52:21'),
(18, 'tes', 'tes', 'Fulan', 'member_1779623228.png', '081234567890', 'Antartika', NULL, '2026-05-24 11:47:08');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `member_surveys`
--
ALTER TABLE `member_surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `orders_ibfk_1` (`member_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `site_about`
--
ALTER TABLE `site_about`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_bg_dark`
--
ALTER TABLE `site_bg_dark`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_hero`
--
ALTER TABLE `site_hero`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_hero_slides`
--
ALTER TABLE `site_hero_slides`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_portfolio`
--
ALTER TABLE `site_portfolio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `site_portfolio_alerts`
--
ALTER TABLE `site_portfolio_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_products`
--
ALTER TABLE `site_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_slug` (`product_slug`);

--
-- Indeks untuk tabel `site_products_promo`
--
ALTER TABLE `site_products_promo`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_products_sale`
--
ALTER TABLE `site_products_sale`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_promotion`
--
ALTER TABLE `site_promotion`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_skills`
--
ALTER TABLE `site_skills`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_stats`
--
ALTER TABLE `site_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_team`
--
ALTER TABLE `site_team`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_testimonials`
--
ALTER TABLE `site_testimonials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `site_testi_alerts`
--
ALTER TABLE `site_testi_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_users`
--
ALTER TABLE `site_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `site_video_alerts`
--
ALTER TABLE `site_video_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_video_portfolio`
--
ALTER TABLE `site_video_portfolio`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `site_wallpaper`
--
ALTER TABLE `site_wallpaper`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users_member`
--
ALTER TABLE `users_member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `member_surveys`
--
ALTER TABLE `member_surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT untuk tabel `site_about`
--
ALTER TABLE `site_about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `site_bg_dark`
--
ALTER TABLE `site_bg_dark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `site_hero`
--
ALTER TABLE `site_hero`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `site_hero_slides`
--
ALTER TABLE `site_hero_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `site_portfolio`
--
ALTER TABLE `site_portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `site_portfolio_alerts`
--
ALTER TABLE `site_portfolio_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `site_products`
--
ALTER TABLE `site_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `site_products_promo`
--
ALTER TABLE `site_products_promo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `site_products_sale`
--
ALTER TABLE `site_products_sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `site_promotion`
--
ALTER TABLE `site_promotion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `site_skills`
--
ALTER TABLE `site_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `site_stats`
--
ALTER TABLE `site_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `site_team`
--
ALTER TABLE `site_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `site_testimonials`
--
ALTER TABLE `site_testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `site_testi_alerts`
--
ALTER TABLE `site_testi_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `site_users`
--
ALTER TABLE `site_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `site_video_alerts`
--
ALTER TABLE `site_video_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `site_video_portfolio`
--
ALTER TABLE `site_video_portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `site_wallpaper`
--
ALTER TABLE `site_wallpaper`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users_member`
--
ALTER TABLE `users_member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `users_member` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `site_products_promo` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `member_surveys`
--
ALTER TABLE `member_surveys`
  ADD CONSTRAINT `member_surveys_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `users_member` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `users_member` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `site_products_promo` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `site_portfolio`
--
ALTER TABLE `site_portfolio`
  ADD CONSTRAINT `site_portfolio_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `site_products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
