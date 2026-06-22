<?php
// chatbot_api.php
header('Content-Type: application/json');

// Menerima input dari fetch API JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$userMessage = isset($data['message']) ? $data['message'] : '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Pesan tidak boleh kosong.']);
    exit;
}

// ⚠️ Masukkan API Key Gemini di sini
$apiKey = 'AQ.Ab8RN6JR-xRo2HCGTsdUevMALWrdQe5nfBLm4eKNKsMvTbzH6Q'; 
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

// Beri AI identitas (Prompt Engineering dasar)
$systemInstruction = "Kamu adalah asisten AI Helpdesk yang cerdas dan ramah untuk e-commerce dan portfolio desain bernama NaufaRu. Tugasmu adalah membantu pengunjung website mencari layanan desain, memberikan informasi portofolio, dan memberikan rekomendasi yang sopan. Jawablah dengan bahasa Indonesia yang jelas, profesional, dan ringkas.";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Instruksi Sistem: " . $systemInstruction . "\n\nPertanyaan User: " . $userMessage]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// Jika kalian melakukan testing di localhost XAMPP dan mengalami error SSL, hapus tanda // pada baris di bawah ini:
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);

// Proses jawaban yang didapat dari Google Gemini
if(isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $aiText = $responseData['candidates'][0]['content']['parts'][0]['text'];
    
    // Ubah format teks mentah ke format HTML agar rapi saat dibaca di widget
    $aiText = nl2br(htmlspecialchars($aiText)); 
    echo json_encode(['reply' => $aiText]);
} else {
    echo json_encode(['reply' => 'Maaf, sistem AI sedang sibuk. Silakan coba beberapa saat lagi.']);
}