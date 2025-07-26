<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { text-align: center; }
        .container { max-width: 900px; margin: auto; background: #f4f4f4; padding: 20px; border-radius: 8px; }
        .add-button-container { text-align: right; margin-bottom: 20px; }
        .add-button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 16px; }
        .add-button:hover { background-color: #0056b3; }
        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; white-space: nowrap; }
        th { background-color: #e2e2e2; }
        .action-buttons button { background-color: #ffc107; color: #333; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin-right: 5px; }
        .action-buttons button:hover { background-color: #e0a800; }
        .message { margin-top: 20px; padding: 10px; border-radius: 4px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .debug-log { background-color: #e0e0e0; padding: 15px; border-radius: 8px; margin-top: 30px; border: 1px solid #ccc; max-width: 900px; margin-left: auto; margin-right: auto; }
        .debug-log h3 { margin-top: 0; color: #333; }
        .debug-log pre { background-color: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; }

        /* --- Modal Styles --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none; /* Hidden by default */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        .modal-close-button {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
            border: none;
            background: none;
        }
        .modal-close-button:hover {
            color: #333;
        }
        .modal-content h3 {
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        /* --- Perubahan Gaya Form dalam Modal --- */
        .form-group {
            margin-bottom: 15px;
        }
        .modal-content form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .modal-content form input[type="text"],
        .modal-content form input[type="email"],
        .modal-content form input[type="url"], /* Added URL type */
        .modal-content form input[type="number"] { /* Added number type for status */
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #fcfcfc;
            font-size: 16px;
        }
        .modal-content form input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.1rem rgba(0, 123, 255, 0.25);
        }
        /* --- Akhir Perubahan Gaya Form dalam Modal --- */

        .modal-buttons {
            text-align: right;
            margin-top: 20px;
        }
        .modal-buttons button {
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        .modal-buttons .save-button {
            background-color: #28a745;
            color: white;
            border: none;
        }
        .modal-buttons .save-button:hover {
            background-color: #218838;
        }
        .modal-buttons .cancel-button {
            background-color: #6c757d;
            color: white;
            border: none;
        }
        .modal-buttons .cancel-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

    <h2>Daftar Pengguna</h2>

    <div class="container">
        <div class="add-button-container">
            <a href="add_user.php" class="add-button">Tambah Pengguna Baru</a>
        </div>

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

        $api_url = 'https://api.newsnoid.com/users'; // URL API GET ALL for users
        log_message("Mengambil data dari API: " . $api_url);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Optional: If you face SSL issues during development (NOT RECOMMENDED FOR PRODUCTION):
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $curl_errno = curl_errno($ch);
        curl_close($ch);

        log_message("cURL execution completed.");
        log_message("HTTP Status Code received: " . $http_code);
        log_message("cURL Error (if any): [{$curl_errno}] " . ($curl_error ? $curl_error : 'No error'));
        log_message("Raw API Response: " . ($response ? $response : 'Empty response'));

        $users = [];
        $message = "";
        $message_type = "";

        if ($response === false) {
            $message = "Kesalahan Koneksi cURL: " . htmlspecialchars($curl_error) . " (Kode: {$curl_errno})";
            $message_type = "error";
            log_message("cURL execution FAILED: [{$curl_errno}] " . $curl_error);
        } else {
            $response_data = json_decode($response);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $message = "Gagal memproses respons API: Data bukan JSON valid. Pesan: " . json_last_error_msg();
                $message_type = "error";
                log_message("JSON decode FAILED: " . json_last_error_msg());
            } else {
                log_message("Decoded API Response (Object):");
                log_message($response_data);

                if ($http_code == 200) {
                    if (isset($response_data->data) && is_array($response_data->data)) {
                        $users = $response_data->data;
                        $message = $response_data->message ?? 'Data pengguna berhasil diambil.';
                        $message_type = "success";
                    } else if (isset($response_data->message)) {
                        $message = $response_data->message;
                        $message_type = "success"; // If API returns 200 but data is empty, still success
                    }
                } else {
                    $message = "Gagal mengambil data. Status HTTP: " . $http_code . ". Pesan: " . htmlspecialchars($response_data->message ?? 'Terjadi kesalahan tidak dikenal.');
                    $message_type = "error";
                }
            }
        }

        if (!empty($message)) {
            echo "<div class='message {$message_type}'>" . $message . "</div>";
        }

        if (!empty($users)):
        ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email Pengguna</th>
                        <th>Status Pengguna</th>
                        <th>Nama Publik</th>
                        <th>URL Profil</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user->ID ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($user->user_email ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($user->user_status ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($user->user_publicname ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($user->user_url ?? ''); ?></td>
                        <td class="action-buttons">
                            <button onclick="openEditModal(
                                '<?php echo htmlspecialchars($user->ID ?? ''); ?>',
                                '<?php echo htmlspecialchars($user->user_email ?? ''); ?>',
                                '<?php echo htmlspecialchars($user->user_status ?? ''); ?>',
                                '<?php echo htmlspecialchars($user->user_publicname ?? ''); ?>',
                                '<?php echo htmlspecialchars($user->user_url ?? ''); ?>'
                            )">Sunting</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <?php if (empty($message) || $message_type == "success"): ?>
                <div class='message success'>Tidak ada data pengguna untuk ditampilkan.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($debug_output)): ?>
    <div class="debug-log">
        <h3>Debug Log:</h3>
        <pre><?php echo implode("\n", $debug_output); ?></pre>
    </div>
    <?php endif; ?>

    <div id="editModal" class="modal-overlay">
        <div class="modal-content">
            <button class="modal-close-button" onclick="closeEditModal()">&times;</button>
            <h3>Sunting Pengguna</h3>
            <form id="editForm">
                <input type="hidden" id="edit-id" name="ID">

                <div class="form-group">
                    <label for="edit-user_email">Email Pengguna:</label>
                    <input type="email" id="edit-user_email" name="user_email" required>
                </div>

                <div class="form-group">
                    <label for="edit-user_status">Status Pengguna:</label>
                    <input type="number" id="edit-user_status" name="user_status" required>
                </div>

                <div class="form-group">
                    <label for="edit-user_publicname">Nama Publik:</label>
                    <input type="text" id="edit-user_publicname" name="user_publicname" required>
                </div>

                <div class="form-group">
                    <label for="edit-user_url">URL Profil:</label>
                    <input type="url" id="edit-user_url" name="user_url">
                </div>

                <div class="modal-buttons">
                    <button type="button" class="cancel-button" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="save-button">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Get modal references
        const editModal = document.getElementById('editModal');
        const editForm = document.getElementById('editForm');

        function openEditModal(id, userEmail, userStatus, userPublicname, userUrl) {
            // Populate form fields
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-user_email').value = userEmail;
            document.getElementById('edit-user_status').value = userStatus;
            document.getElementById('edit-user_publicname').value = userPublicname;
            document.getElementById('edit-user_url').value = userUrl;

            // Show modal
            editModal.style.display = 'flex';
        }

        function closeEditModal() {
            // Hide modal
            editModal.style.display = 'none';
        }

        // Close modal when clicking outside content area
        editModal.addEventListener('click', (event) => {
            if (event.target === editModal) {
                closeEditModal();
            }
        });

        // --- Handle Form Submission in Modal (AJAX/Fetch API) ---
        editForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission (page reload)

            const formData = new FormData(editForm);
            const jsonData = {};
            // Convert FormData to JSON object
            formData.forEach((value, key) => {
                jsonData[key] = value;
            });

            const recordId = jsonData.ID; // Get ID from hidden field
            delete jsonData.ID; // Remove ID from payload (as ID is in URL for PUT)

            console.log("Data to update (JSON):", jsonData);
            console.log("Updating record with ID:", recordId);

            // --- Perform PUT API Call Here ---
            fetch(`https://api.newsnoid.com/users/${recordId}`, { // Assuming PUT endpoint for users
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => { throw new Error(errData.message || `HTTP error! status: ${response.status}`); });
                }
                return response.json();
            })
            .then(data => {
                console.log('Update Success:', data);
                alert('Pengguna berhasil diperbarui!');
                closeEditModal();
                location.reload(); // Reload page to see changes
            })
            .catch((error) => {
                console.error('Update Error:', error);
                alert('Gagal memperbarui pengguna: ' + error.message);
            });

            // --- End PUT API Call ---
        });
    </script>

</body>
</html>