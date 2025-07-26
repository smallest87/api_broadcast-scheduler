<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna Baru</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="number"],
        .form-group input[type="url"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .form-group input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.1rem rgba(0, 123, 255, 0.25);
        }
        .buttons-container {
            text-align: right;
            margin-top: 20px;
        }
        .buttons-container button,
        .buttons-container a {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none; /* For the link button */
            display: inline-block; /* For the link button */
            margin-left: 10px;
        }
        .buttons-container .save-button {
            background-color: #28a745;
            color: white;
        }
        .buttons-container .save-button:hover {
            background-color: #218838;
        }
        .buttons-container .back-button {
            background-color: #6c757d;
            color: white;
        }
        .buttons-container .back-button:hover {
            background-color: #5a6268;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Tambah Pengguna Baru</h2>

        <?php
        $message = "";
        $message_type = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Mengambil data dari form
            $user_email = $_POST['user_email'] ?? '';
            $user_pass = $_POST['user_pass'] ?? '';
            $user_status = $_POST['user_status'] ?? '0'; // Default status 0
            $user_publicname = $_POST['user_publicname'] ?? '';
            $user_url = $_POST['user_url'] ?? '';

            // Validasi sederhana di sisi klien/server (sesuaikan kebutuhan)
            if (empty($user_email) || empty($user_pass) || empty($user_publicname)) {
                $message = "Email, Password, dan Nama Publik wajib diisi.";
                $message_type = "error";
            } else {
                // Data yang akan dikirim ke API
                $data = [
                    'user_email' => $user_email,
                    'user_pass' => $user_pass, // Password akan di-hash oleh API
                    'user_status' => (int)$user_status, // Pastikan integer
                    'user_publicname' => $user_publicname,
                    'user_url' => $user_url
                ];

                // $api_url = 'https://api.newsnoid.com/users'; // Endpoint API untuk menambahkan user
				$api_url = 'https://api.newsnoid.com/register'; // Ubah endpoint API ini

                $ch = curl_init($api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curl_error = curl_error($ch);
                curl_close($ch);

                if ($response === false) {
                    $message = "Kesalahan koneksi API: " . htmlspecialchars($curl_error);
                    $message_type = "error";
                } else {
                    $response_data = json_decode($response);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $message = "Gagal memproses respons API: Data bukan JSON valid. " . json_last_error_msg();
                        $message_type = "error";
                    } else {
                        if ($http_code == 201) { // 201 Created untuk sukses
                            $message = $response_data->message ?? 'Pengguna berhasil ditambahkan.';
                            $message_type = "success";
                            // Opsional: Kosongkan form setelah sukses
                            $_POST = array();
                        } else {
                            $message = "Gagal menambahkan pengguna. Status HTTP: " . $http_code . ". Pesan: " . htmlspecialchars($response_data->message ?? 'Terjadi kesalahan tidak dikenal.');
                            $message_type = "error";
                        }
                    }
                }
            }
        }

        if (!empty($message)) {
            echo "<div class='message {$message_type}'>" . $message . "</div>";
        }
        ?>

        <form action="add_user.php" method="POST">
            <div class="form-group">
                <label for="user_email">Email Pengguna:</label>
                <input type="email" id="user_email" name="user_email" value="<?php echo htmlspecialchars($_POST['user_email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="user_pass">Password:</label>
                <input type="password" id="user_pass" name="user_pass" required>
            </div>

            <div class="form-group">
                <label for="user_status">Status Pengguna (Angka):</label>
                <input type="number" id="user_status" name="user_status" value="<?php echo htmlspecialchars($_POST['user_status'] ?? '0'); ?>">
            </div>

            <div class="form-group">
                <label for="user_publicname">Nama Publik:</label>
                <input type="text" id="user_publicname" name="user_publicname" value="<?php echo htmlspecialchars($_POST['user_publicname'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="user_url">URL Profil (Opsional):</label>
                <input type="url" id="user_url" name="user_url" value="<?php echo htmlspecialchars($_POST['user_url'] ?? ''); ?>">
            </div>

            <div class="buttons-container">
                <a href="view_user.php" class="back-button">Kembali ke Daftar</a>
                <button type="submit" class="save-button">Tambah Pengguna</button>
            </div>
        </form>
    </div>

</body>
</html>