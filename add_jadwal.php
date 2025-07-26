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
        input[type="text"],
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"],
        select { /* Tambahkan select di sini */
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* Penting untuk lebar yang konsisten */
        }
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
        if (is_array($message) || is_object($message)) {
            $message = var_export($message, true);
        }
        $debug_output[] = date('Y-m-d H:i:s') . " - " . $message;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        log_message("POST request received.");

        // Mendapatkan data dari form dengan nama field yang baru
        $schedule_item_duration = $_POST['schedule_item_duration'] ?? '';
        $schedule_item_title = $_POST['schedule_item_title'] ?? '';
        $schedule_item_type = $_POST['schedule_item_type'] ?? '';
        $tgl_siaran = $_POST['tgl_siaran'] ?? '';
        $schedule_onair = $_POST['schedule_onair'] ?? '';
        $schedule_author = $_POST['schedule_author'] ?? ''; // Ini akan menjadi ID dari dropdown

        log_message("Form data collected: Duration='{$schedule_item_duration}', Title='{$schedule_item_title}', Type='{$schedule_item_type}', TglSiaran='{$tgl_siaran}', OnAir='{$schedule_onair}', AuthorID='{$schedule_author}'");

        // Perbaikan format durasi jika hanya HH:MM (dari input type="time")
        if (!empty($schedule_item_duration) && strlen($schedule_item_duration) === 5) {
            $schedule_item_duration .= ':00';
        }

        // Data yang akan dikirim dalam format JSON dengan nama field yang baru
        $data = json_encode([
            "schedule_item_duration" => $schedule_item_duration,
            "schedule_item_title" => $schedule_item_title,
            "schedule_item_type" => $schedule_item_type,
            "tgl_siaran" => $tgl_siaran,
            "schedule_onair" => $schedule_onair,
            "schedule_author" => (int)$schedule_author // Pastikan ini dikirim sebagai integer
        ]);

        log_message("JSON payload prepared: " . $data);

        // URL endpoint API Anda untuk membuat jadwal
        $api_url = 'https://api.newsnoid.com/jadwal-program';
        log_message("API Endpoint URL: " . $api_url);

        // Inisialisasi cURL
        $ch = curl_init($api_url);
        log_message("cURL initialized.");

        // Mengatur opsi cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $curl_errno = curl_errno($ch);

        log_message("cURL execution completed.");
        log_message("HTTP Status Code received: " . $http_code);
        log_message("cURL Error (if any): [{$curl_errno}] " . ($curl_error ? $curl_error : 'No error'));
        log_message("Raw API Response: " . ($response ? $response : 'Empty response'));

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
        <label for="schedule_item_duration">Durasi Program (HH:MM:SS):</label>
        <input type="time" id="schedule_item_duration" name="schedule_item_duration" step="1" required><br>

        <label for="schedule_item_title">Judul Segmen:</label>
        <input type="text" id="schedule_item_title" name="schedule_item_title" placeholder="Misal: Berita Utama" required><br>

        <label for="schedule_item_type">Tipe Program:</label>
        <input type="text" id="schedule_item_type" name="schedule_item_type" placeholder="Misal: Live" required><br>

        <label for="tgl_siaran">Tanggal Siaran:</label>
        <input type="date" id="tgl_siaran" name="tgl_siaran" required><br>

        <label for="schedule_onair">Waktu Tayang (Tanggal & Jam):</label>
        <input type="datetime-local" id="schedule_onair" name="schedule_onair" required><br>

        <label for="schedule_author">Penulis Jadwal:</label>
        <select id="schedule_author" name="schedule_author" required>
            <option value="">Pilih Penulis</option>
            </select><br>

        <button type="submit">Kirim Data</button>
    </form>

    <?php if (!empty($debug_output)): ?>
    <div class="debug-log">
        <h3>Debug Log:</h3>
        <pre><?php echo implode("\n", $debug_output); ?></pre>
    </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const authorSelect = document.getElementById('schedule_author');

            // Asumsi endpoint API untuk mendapatkan daftar pengguna
            const usersApiUrl = 'https://api.newsnoid.com/users'; // Ganti dengan URL API pengguna Anda yang sebenarnya

            fetch(usersApiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Asumsi data pengguna ada di data.data atau langsung di data jika tidak ada pembungkus 'data'
                    const users = data.data || data; // Sesuaikan dengan struktur respons API Anda

                    if (Array.isArray(users)) {
                        users.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.ID; // Asumsi ID pengguna ada di properti 'ID'
                            option.textContent = user.user_publicname; // Asumsi nama publik ada di properti 'user_publicname'
                            authorSelect.appendChild(option);
                        });
                    } else {
                        console.error('Data pengguna bukan array:', users);
                    }
                })
                .catch(error => {
                    console.error('Gagal mengambil daftar penulis:', error);
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Gagal memuat penulis';
                    option.disabled = true;
                    authorSelect.appendChild(option);
                });
        });
    </script>

</body>
</html>