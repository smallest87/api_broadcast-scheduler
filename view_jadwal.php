<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Jadwal Program</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { text-align: center; }
        .container { max-width: 900px; margin: auto; background: #f4f4f4; padding: 20px; border-radius: 8px; }
        .add-button-container { text-align: right; margin-bottom: 20px; }
        .add-button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 16px; }
        .add-button:hover { background-color: #0056b3; }
        /* Pembungkus tabel untuk scroll horizontal */
        .table-responsive {
            overflow-x: auto; /* Aktifkan scroll horizontal jika konten melebihi lebar */
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px; /* Tambahkan min-width agar scroll muncul jika lebar tabel kurang dari ini */
        }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; white-space: nowrap; /* Mencegah teks wrapping dalam sel */ }
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
        .form-group { /* Wrapper untuk setiap label + input */
            margin-bottom: 15px; /* Spasi antar baris form */
        }
        .modal-content form label {
            display: block; /* Pastikan label di baris baru */
            margin-bottom: 5px; /* Spasi antara label dan input */
            font-weight: bold; /* Mirip dengan header tabel */
            color: #555;
        }
        .modal-content form input[type="text"],
        .modal-content form input[type="date"],
        .modal-content form input[type="datetime-local"],
        .modal-content form input[type="time"] {
            width: 100%; /* Lebar penuh dalam container form-group */
            padding: 10px; /* Padding mirip sel tabel */
            border: 1px solid #ccc; /* Border sedikit lebih terang dari tabel */
            border-radius: 4px;
            box-sizing: border-box; /* Pastikan padding dan border tidak menambah lebar */
            background-color: #fcfcfc; /* Warna latar belakang field */
            font-size: 16px;
        }
        .modal-content form input:focus {
            border-color: #007bff; /* Warna border saat fokus */
            outline: none; /* Hapus outline default browser */
            box-shadow: 0 0 0 0.1rem rgba(0, 123, 255, 0.25); /* Efek bayangan saat fokus */
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

    <h2>Daftar Jadwal Program</h2>

    <div class="container">
        <div class="add-button-container">
            <a href="add_jadwal.php" class="add-button">Tambah Jadwal Baru</a>
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

        $api_url = 'https://api.newsnoid.com/jadwal-program'; // URL API GET ALL
        log_message("Mengambil data dari API: " . $api_url);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Jika Anda masih mengalami masalah SSL/HTTPS (TIDAK DISARANKAN UNTUK PRODUKSI):
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

        $jadwal_programs = [];
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
                        $jadwal_programs = $response_data->data;
                        $message = $response_data->message ?? 'Data jadwal program berhasil diambil.';
                        $message_type = "success";
                    } else if (isset($response_data->message)) {
                        $message = $response_data->message;
                        $message_type = "success"; // Jika API mengembalikan 200 tapi data kosong, tetap sukses
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

        if (!empty($jadwal_programs)):
        ?>
        <div class="table-responsive"> <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Durasi</th>
                        <th>Judul Segmen</th>
                        <th>Tipe</th>
                        <th>Tanggal Siaran</th>
                        <th>Waktu Tayang</th>
                        <th>Penulis</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jadwal_programs as $jadwal):
                        // Format waktu untuk input HTML5 type="time" (HH:MM)
                        $display_duration = '';
                        if (!empty($jadwal->schedule_item_duration)) {
                            $time = new DateTime($jadwal->schedule_item_duration);
                            $display_duration = $time->format('H:i');
                        }

                        // Format waktu untuk input HTML5 type="datetime-local" (YYYY-MM-DDTHH:MM)
                        $display_onair = '';
                        if (!empty($jadwal->schedule_onair)) {
                            $datetime = new DateTime($jadwal->schedule_onair);
                            $display_onair = $datetime->format('Y-m-d\TH:i'); // \T for literal 'T'
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($jadwal->id ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($jadwal->schedule_item_duration ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($jadwal->schedule_item_title ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($jadwal->schedule_item_type ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($jadwal->tgl_siaran ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($jadwal->schedule_onair ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($jadwal->schedule_author ?? ''); ?></td>
                        <td class="action-buttons">
                            <button onclick="openEditModal(
                                '<?php echo htmlspecialchars($jadwal->id ?? ''); ?>',
                                '<?php echo htmlspecialchars($display_duration); ?>',
                                '<?php echo htmlspecialchars($jadwal->schedule_item_title ?? ''); ?>',
                                '<?php echo htmlspecialchars($jadwal->schedule_item_type ?? ''); ?>',
                                '<?php echo htmlspecialchars($jadwal->tgl_siaran ?? ''); ?>',
                                '<?php echo htmlspecialchars($display_onair); ?>',
                                '<?php echo htmlspecialchars($jadwal->schedule_author ?? ''); ?>'
                            )">Sunting</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div> <?php else: ?>
            <?php if (empty($message) || $message_type == "success"): ?>
                <div class='message success'>Tidak ada data jadwal program untuk ditampilkan.</div>
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
            <h3>Sunting Jadwal Program</h3>
            <form id="editForm">
                <input type="hidden" id="edit-id" name="id">

                <div class="form-group"> <label for="edit-schedule_item_duration">Durasi Program (HH:MM):</label>
                    <input type="time" id="edit-schedule_item_duration" name="schedule_item_duration" step="1" required>
                </div>

                <div class="form-group">
                    <label for="edit-schedule_item_title">Judul Segmen:</label>
                    <input type="text" id="edit-schedule_item_title" name="schedule_item_title" required>
                </div>

                <div class="form-group">
                    <label for="edit-schedule_item_type">Tipe Program:</label>
                    <input type="text" id="edit-schedule_item_type" name="schedule_item_type" required>
                </div>

                <div class="form-group">
                    <label for="edit-tgl_siaran">Tanggal Siaran:</label>
                    <input type="date" id="edit-tgl_siaran" name="tgl_siaran" required>
                </div>

                <div class="form-group">
                    <label for="edit-schedule_onair">Waktu Siaran (Tanggal & Jam):</label>
                    <input type="datetime-local" id="edit-schedule_onair" name="schedule_onair" required>
                </div>

                <div class="form-group">
                    <label for="edit-schedule_author">Penulis Jadwal:</label>
                    <input type="text" id="edit-schedule_author" name="schedule_author" required>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="cancel-button" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="save-button">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mendapatkan referensi ke modal
        const editModal = document.getElementById('editModal');
        const editForm = document.getElementById('editForm');

        function openEditModal(id, duration, title, type, date, onair, author) {
            // Mengisi form modal dengan data yang diterima
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-schedule_item_duration').value = duration; // HH:MM
            document.getElementById('edit-schedule_item_title').value = title;
            document.getElementById('edit-schedule_item_type').value = type;
            document.getElementById('edit-tgl_siaran').value = date; // YYYY-MM-DD
            document.getElementById('edit-schedule_onair').value = onair; // YYYY-MM-DDTHH:MM
            document.getElementById('edit-schedule_author').value = author;

            // Menampilkan modal
            editModal.style.display = 'flex';
        }

        function closeEditModal() {
            // Menyembunyikan modal
            editModal.style.display = 'none';
        }

        // Menutup modal jika mengklik di luar area konten modal
        editModal.addEventListener('click', (event) => {
            if (event.target === editModal) {
                closeEditModal();
            }
        });

        // --- Handle Form Submission in Modal (AJAX/Fetch API) ---
        editForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah form dari submit biasa (page reload)

            const formData = new FormData(editForm);
            const jsonData = {};
            // Ubah FormData menjadi objek JSON
            formData.forEach((value, key) => {
                jsonData[key] = value;
            });

            // Perbaikan format durasi jika hanya HH:MM
            if (jsonData.schedule_item_duration && jsonData.schedule_item_duration.length === 5) {
                jsonData.schedule_item_duration += ':00';
            }

            const recordId = jsonData.id; // Ambil ID dari hidden field
            delete jsonData.id; // Hapus ID dari payload yang akan dikirim (karena ID ada di URL untuk PUT)

            console.log("Data to update (JSON):", jsonData);
            console.log("Updating record with ID:", recordId);

            // --- Lakukan Panggilan API PUT di SINI ---
            fetch(`https://api.newsnoid.com/jadwal-program/${recordId}`, {
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
                alert('Jadwal berhasil diperbarui!');
                closeEditModal();
                location.reload(); // Muat ulang halaman untuk melihat perubahan
            })
            .catch((error) => {
                console.error('Update Error:', error);
                alert('Gagal memperbarui jadwal: ' + error.message);
            });

            // --- Akhir Panggilan API PUT ---
        });
    </script>

</body>
</html>