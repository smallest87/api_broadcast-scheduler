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
                    "schedule_onair" => $schedule_onair // Pastikan properti ini ada di model
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
                "schedule_item_duration" => $this->jadwalProgram->schedule_item_duration,
                "schedule_item_title" => $this->jadwalProgram->schedule_item_title,
                "schedule_item_type" => $this->jadwalProgram->schedule_item_type,
                "schedule_onair" => $this->jadwalProgram->schedule_onair // Pastikan properti ini ada di model
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

        if (empty($data->schedule_item_duration) || empty($data->schedule_item_title) || empty($data->schedule_item_type)) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to create jadwal program. Data is incomplete. Please provide durasi, segmen, and jenis."]);
            return;
        }

        $this->jadwalProgram->schedule_item_duration = $data->durasi;
        $this->jadwalProgram->schedule_item_title = $data->schedule_item_title;
        $this->jadwalProgram->schedule_item_type = $data->schedule_item_type;

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

        if (empty($id) || (empty($data->schedule_item_duration) && empty($data->schedule_item_title) && empty($data->schedule_item_type))) {
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

        $this->jadwalProgram->durschedule_item_durationasi = !empty($data->schedule_item_duration) ? $data->schedule_item_duration : $this->jadwalProgram->schedule_item_duration;
        $this->jadwalProgram->schedule_item_title = !empty($data->schedule_item_title) ? $data->schedule_item_title : $this->jadwalProgram->schedule_item_title;
        $this->jadwalProgram->schedule_item_type = !empty($data->schedule_item_type) ? $data->schedule_item_type : $this->jadwalProgram->schedule_item_type;

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