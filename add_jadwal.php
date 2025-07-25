<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal Program</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { background: #f4f4f4; padding: 20px; border-radius: 8px; max-width: 400px; margin: auto; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input[type="text"] { width: calc(100% - 20px); padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #45a049; }
        .message { margin-top: 20px; padding: 10px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .debug-log { background-color: #e0e0e0; padding: 15px; border-radius: 8px; margin-top: 30px; border: 1px solid #ccc; max-width: 600px; margin-left: auto; margin-right: auto; }
        .debug-log h3 { margin-top: 0; color: #333; }
        .debug-log pre { background-color: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>

    <h2>Tambah Jadwal Program Baru</h2>

    <?php
    // Inisialisasi array log
    $debug_output = [];
    function log_message($message) {
        global $debug_output;
        $debug_output[] = date('Y-m-d H:i:s') . " - " . $message;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        log_message("POST request received.");

        // Mendapatkan data dari form dengan nama field yang baru
        $schedule_item_duration = $_POST['schedule_item_duration'] ?? '';
        $schedule_item_title = $_POST['schedule_item_title'] ?? '';
        $schedule_item_type = $_POST['schedule_item_type'] ?? '';

        log_message("Form data collected: Duration='{$schedule_item_duration}', Title='{$schedule_item_title}', Type='{$schedule_item_type}'");

        // Data yang akan dikirim dalam format JSON dengan nama field yang baru
        $data = json_encode([
            "schedule_item_duration" => $schedule_item_duration,
            "schedule_item_title" => $schedule_item_title,
            "schedule_item_type" => $schedule_item_type
        ]);

        log_message("JSON payload prepared: " . $data);

        // URL endpoint API Anda
        // Sesuaikan dengan URL API Anda yang sebenarnya
        $api_url = 'http://api.newsnoid.com/index.php/jadwal-program'; // Contoh: jika API Anda diakses via domain api.newsnoid.com
        log_message("API Endpoint URL: " . $api_url);

        // Inisialisasi cURL
        $ch = curl_init($api_url);
        log_message("cURL initialized.");

        // Mengatur opsi cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Mengembalikan respons sebagai string
        curl_setopt($ch, CURLOPT_POST, true);           // Mengatur metode permintaan ke POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);    // Mengatur data yang akan dikirim (JSON)
        curl_setopt($ch, CURLOPT_HTTPHEADER, [          // Mengatur header
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        // MENAMBAHKAN OPSI UNTUK MENGIKUTI REDIRECT
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // cURL akan mengikuti redirect 3xx
        // Jika Anda memiliki masalah dengan verifikasi SSL, Anda mungkin perlu ini (TIDAK DISARANKAN UNTUK PRODUKSI)
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        log_message("cURL options set.");

        // Eksekusi cURL dan mendapatkan respons
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Mendapatkan kode status HTTP
        $curl_error = curl_error($ch); // Mendapatkan pesan error cURL
        $curl_errno = curl_errno($ch); // Mendapatkan nomor error cURL

        log_message("cURL execution completed.");
        log_message("HTTP Status Code received: " . $http_code);
        log_message("cURL Error (if any): [{$curl_errno}] " . ($curl_error ? $curl_error : 'No error'));
        log_message("Raw API Response: " . ($response ? $response : 'Empty response'));

        // Menutup sesi cURL
        curl_close($ch);
        log_message("cURL session closed.");

        // Menampilkan hasil
        echo "<div class='message ";
        if ($curl_error) {
            echo "error'>Kesalahan cURL: " . htmlspecialchars($curl_error);
        } else {
            $response_data = json_decode($response);
            if ($http_code == 201) {
                echo "success'>Berhasil! " . htmlspecialchars($response_data->message ?? 'Jadwal program berhasil dibuat.');
            } else {
                echo "error'>Gagal membuat jadwal program. Status HTTP: " . $http_code . ". Pesan: " . htmlspecialchars($response_data->message ?? 'Terjadi kesalahan tidak dikenal.');
                if ($response_data && property_exists($response_data, 'details')) {
                     echo "<br>Details: " . htmlspecialchars($response_data->details);
                }
            }
        }
        echo "</div>";
    }
    ?>

    <form action="" method="POST">
        <label for="schedule_item_duration">Durasi Program:</label>
        <input type="text" id="schedule_item_duration" name="schedule_item_duration" placeholder="Misal: 30 menit" required><br>

        <label for="schedule_item_title">Judul Segmen:</label>
        <input type="text" id="schedule_item_title" name="schedule_item_title" placeholder="Misal: Berita Utama" required><br>

        <label for="schedule_item_type">Tipe Program:</label>
        <input type="text" id="schedule_item_type" name="schedule_item_type" placeholder="Misal: Live" required><br>

        <button type="submit">Kirim Data</button>
    </form>

    <?php if (!empty($debug_output)): ?>
    <div class="debug-log">
        <h3>Debug Log:</h3>
        <pre><?php echo implode("\n", $debug_output); ?></pre>
    </div>
    <?php endif; ?>

</body>
</html>