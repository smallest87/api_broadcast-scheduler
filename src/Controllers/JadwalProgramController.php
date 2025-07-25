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
                    "schedule_item_duration" => $schedule_item_duration,
                    "schedule_item_title" => $schedule_item_title,
                    "schedule_item_type" => $schedule_item_type,
                    "tgl_siaran" => $tgl_siaran,       // <-- BARU: Ditambahkan
                    "schedule_onair" => $schedule_onair,
                    "schedule_author" => $schedule_author // <-- BARU: Ditambahkan
                ];
                array_push($jadwal_arr['data'], $jadwal_item);
            }

            http_response_code(200);
            echo json_encode($jadwal_arr);
        } else {
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
                "schedule_item_duration" => $this->jadwalProgram->schedule_item_duration,
                "schedule_item_title" => $this->jadwalProgram->schedule_item_title,
                "schedule_item_type" => $this->jadwalProgram->schedule_item_type,
                "tgl_siaran" => $this->jadwalProgram->tgl_siaran, // <-- BARU: Ditambahkan
                "schedule_onair" => $this->jadwalProgram->schedule_onair,
                "schedule_author" => $this->jadwalProgram->schedule_author // <-- BARU: Ditambahkan
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

        // Validasi input, sekarang termasuk field baru
        if (empty($data->schedule_item_duration) || empty($data->schedule_item_title) || empty($data->schedule_item_type) || empty($data->tgl_siaran) || empty($data->schedule_onair) || empty($data->schedule_author)) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to create jadwal program. Data is incomplete. Please provide schedule_item_duration, schedule_item_title, schedule_item_type, tgl_siaran, schedule_onair, and schedule_author."]);
            return;
        }

        // Properti model disesuaikan dengan nama field baru dari input JSON
        $this->jadwalProgram->schedule_item_duration = $data->schedule_item_duration; // Harapkan HH:MM:SS
        $this->jadwalProgram->schedule_item_title = $data->schedule_item_title;
        $this->jadwalProgram->schedule_item_type = $data->schedule_item_type;
        $this->jadwalProgram->tgl_siaran = $data->tgl_siaran;                       // Harapkan YYYY-MM-DD
        $this->jadwalProgram->schedule_onair = $data->schedule_onair;               // Harapkan YYYY-MM-DD HH:MM:SS
        $this->jadwalProgram->schedule_author = $data->schedule_author;             // Harapkan string

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

        // Validasi untuk update: ID dan setidaknya satu field
        if (empty($id) || (empty($data->schedule_item_duration) && empty($data->schedule_item_title) && empty($data->schedule_item_type) && empty($data->tgl_siaran) && empty($data->schedule_onair) && empty($data->schedule_author))) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to update jadwal program. Provide ID and at least one field (duration, title, type, date, onair, or author)."]);
            return;
        }

        $this->jadwalProgram->id = $id;
        if (!$this->jadwalProgram->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "Jadwal program with ID {$id} not found."]);
            return;
        }

        // Hanya update field yang disediakan dalam request
        $this->jadwalProgram->schedule_item_duration = !empty($data->schedule_item_duration) ? $data->schedule_item_duration : $this->jadwalProgram->schedule_item_duration;
        $this->jadwalProgram->schedule_item_title = !empty($data->schedule_item_title) ? $data->schedule_item_title : $this->jadwalProgram->schedule_item_title;
        $this->jadwalProgram->schedule_item_type = !empty($data->schedule_item_type) ? $data->schedule_item_type : $this->jadwalProgram->schedule_item_type;
        $this->jadwalProgram->tgl_siaran = !empty($data->tgl_siaran) ? $data->tgl_siaran : $this->jadwalProgram->tgl_siaran;
        $this->jadwalProgram->schedule_onair = !empty($data->schedule_onair) ? $data->schedule_onair : $this->jadwalProgram->schedule_onair;
        $this->jadwalProgram->schedule_author = !empty($data->schedule_author) ? $data->schedule_author : $this->jadwalProgram->schedule_author;


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