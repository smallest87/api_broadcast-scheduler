<?php

namespace App\Controllers;

use App\Models\Setting; // Pastikan menggunakan model Setting yang sudah disesuaikan
use App\Database;
use PDOException;

class SettingController extends BaseController // Extend BaseController
{
    private $setting;

    public function __construct()
    {
        parent::__construct(); // Panggil konstruktor BaseController
        $this->setting = new Setting($this->db); // $this->db diinisialisasi di BaseController
    }

    public function index()
    {
        $stmt = $this->setting->read(); // Metode read() di model Setting harus mengambil semua kolom
        $num = $stmt->rowCount();

        if ($num > 0) {
            $settings_arr = [];
            $settings_arr['data'] = [];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                // Ekstrak semua kolom dari baris hasil query
                // Variabel yang diekstrak akan menjadi $ID, $posisi, $judul
                extract($row);

                $setting_item = [
                    "ID" => $ID,
                    "posisi" => $posisi, // Langsung gunakan variabel $posisi
                    "judul" => $judul    // Langsung gunakan variabel $judul
                ];
                array_push($settings_arr['data'], $setting_item);
            }

            http_response_code(200);
            echo json_encode($settings_arr);
        } else {
            http_response_code(200);
            echo json_encode(["message" => "No menu items found.", "data" => []]);
        }
    }

    public function show($id)
    {
        $this->setting->ID = $id; // Sesuaikan dengan properti ID di model Setting
        if ($this->setting->readOne()) { // Metode readOne() di model Setting harus mengisi semua properti
            $setting_arr = [
                "ID" => $this->setting->ID,
                "posisi" => $this->setting->posisi,
                "judul" => $this->setting->judul
            ];
            http_response_code(200);
            echo json_encode($setting_arr);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Menu item not found."]);
        }
    }

    public function store()
    {
        $data = $this->getJsonInput(); // Gunakan metode dari BaseController

        // Validasi input berdasarkan kolom tabel baru (posisi, judul)
        if (empty($data->posisi) || empty($data->judul)) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to create menu item. Data is incomplete. Please provide posisi and judul."]);
            return;
        }

        // Isi properti model dengan data dari request
        $this->setting->posisi = $data->posisi;
        $this->setting->judul = $data->judul;

        // Periksa apakah posisi sudah ada sebelum mencoba membuat
        if ($this->setting->menuPosisiExists()) { // Sesuaikan nama metode di model
            http_response_code(409); // Conflict
            echo json_encode(["message" => "Unable to create menu item. Position already exists."]);
            return;
        }

        if ($this->setting->create()) { // Metode create() di model Setting harus menyimpan semua properti
            http_response_code(201);
            echo json_encode(["message" => "Menu item created."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to create menu item. Service unavailable."]);
        }
    }

    public function update($id)
    {
        $data = $this->getJsonInput(); // Gunakan metode dari BaseController

        // Validasi input: ID harus ada dan setidaknya satu field yang bisa diupdate
        if (empty($id) || (empty($data->posisi) && empty($data->judul))) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to update menu item. Provide ID and at least one field (posisi or judul)."]);
            return;
        }

        $this->setting->ID = $id; // Sesuaikan dengan properti ID di model Setting

        // Baca data setting yang sudah ada untuk mengisi properti yang tidak diupdate
        if (!$this->setting->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "Menu item with ID {$id} not found."]);
            return;
        }

        // Update properti model hanya jika data baru disediakan
        $this->setting->posisi = !empty($data->posisi) ? $data->posisi : $this->setting->posisi;
        $this->setting->judul = !empty($data->judul) ? $data->judul : $this->setting->judul;

        if ($this->setting->update()) { // Metode update() di model Setting harus mengupdate properti yang telah diubah
            http_response_code(200);
            echo json_encode(["message" => "Menu item updated."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to update menu item."]);
        }
    }

    public function destroy($id)
    {
        $this->setting->ID = $id; // Sesuaikan dengan properti ID di model Setting

        if (!$this->setting->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "Menu item with ID {$id} not found."]);
            return;
        }

        if ($this->setting->delete()) { // Metode delete() di model Setting harus menghapus berdasarkan ID
            http_response_code(200);
            echo json_encode(["message" => "Menu item deleted."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to delete menu item."]);
        }
    }
}