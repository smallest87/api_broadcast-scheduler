<?php

namespace App\Controllers;

use App\Models\JadwalProgram;
use App\Database;

class JadwalProgramController extends BaseController // Extend BaseController
{
    private $jadwalProgram;

    public function __construct()
    {
        parent::__construct(); // Panggil konstruktor BaseController
        $this->jadwalProgram = new JadwalProgram($this->db);
    }

    // Get all jadwal programs
    public function index()
    {
        $stmt = $this->jadwalProgram->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $jadwal_arr = [];
            $jadwal_arr['data'] = [];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                extract($row);
                $jadwal_item = [
                    "id" => $id,
                    "durasi" => $durasi,
                    "segmen" => $segmen,
                    "jenis" => $jenis,
                    "waktu_siar" => $waktu_siar // Pastikan properti ini ada di model
                ];
                array_push($jadwal_arr['data'], $jadwal_item);
            }

            http_response_code(200);
            echo json_encode($jadwal_arr);
        } else {
            // Lebih baik 200 OK dengan array kosong untuk koleksi
            http_response_code(200);
            echo json_encode(["message" => "No jadwal programs found.", "data" => []]);
        }
    }

    // Get a single jadwal program
    public function show($id)
    {
        $this->jadwalProgram->id = $id;
        if ($this->jadwalProgram->readOne()) {
            $jadwal_item = [
                "id" => $this->jadwalProgram->id,
                "durasi" => $this->jadwalProgram->durasi,
                "segmen" => $this->jadwalProgram->segmen,
                "jenis" => $this->jadwalProgram->jenis,
                "waktu_siar" => $this->jadwalProgram->waktu_siar // Pastikan properti ini ada di model
            ];
            http_response_code(200);
            echo json_encode($jadwal_item);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Jadwal program not found."]);
        }
    }

    // Create a jadwal program
    public function store()
    {
        $data = $this->getJsonInput(); // Gunakan metode dari BaseController

        if (empty($data->durasi) || empty($data->segmen) || empty($data->jenis)) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to create jadwal program. Data is incomplete. Please provide durasi, segmen, and jenis."]);
            return;
        }

        $this->jadwalProgram->durasi = $data->durasi;
        $this->jadwalProgram->segmen = $data->segmen;
        $this->jadwalProgram->jenis = $data->jenis;

        if ($this->jadwalProgram->create()) {
            http_response_code(201);
            echo json_encode(["message" => "Jadwal program created."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to create jadwal program."]);
        }
    }

    // Update a jadwal program
    public function update($id)
    {
        $data = $this->getJsonInput(); // Gunakan metode dari BaseController

        if (empty($id) || (empty($data->durasi) && empty($data->segmen) && empty($data->jenis))) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to update jadwal program. Provide ID and at least one field."]);
            return;
        }

        $this->jadwalProgram->id = $id;
        if (!$this->jadwalProgram->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "Jadwal program with ID {$id} not found."]);
            return;
        }

        $this->jadwalProgram->durasi = !empty($data->durasi) ? $data->durasi : $this->jadwalProgram->durasi;
        $this->jadwalProgram->segmen = !empty($data->segmen) ? $data->segmen : $this->jadwalProgram->segmen;
        $this->jadwalProgram->jenis = !empty($data->jenis) ? $data->jenis : $this->jadwalProgram->jenis;

        if ($this->jadwalProgram->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Jadwal program updated."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to update jadwal program."]);
        }
    }

    // Delete a jadwal program
    public function destroy($id)
    {
        $this->jadwalProgram->id = $id;

        if (!$this->jadwalProgram->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "Jadwal program with ID {$id} not found."]);
            return;
        }

        if ($this->jadwalProgram->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Jadwal program deleted."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to delete jadwal program."]);
        }
    }
}