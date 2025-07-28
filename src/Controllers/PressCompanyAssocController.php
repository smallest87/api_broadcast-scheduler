<?php

namespace App\Controllers;

use App\Models\PressCompanyAssoc;
use App\Database;

class PressCompanyAssocController extends BaseController // Extend BaseController
{
    private $pressCompanyAssoc;

    public function __construct()
    {
        parent::__construct(); // Panggil konstruktor BaseController
        $this->pressCompanyAssoc = new PressCompanyAssoc($this->db);
    }

    // Get all jadwal programs
    public function index()
    {
        $stmt = $this->pressCompanyAssoc->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $pressCompanyAssoc_arr = [];
            $pressCompanyAssoc_arr['data'] = [];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                extract($row);
                $pressCompanyAssoc_item = [
                    "ID" => $ID,
                    "jenis_entitas" => $jenis_entitas,
                    "nama_entitas" => $nama_entitas,
                    "singkatan" => $singkatan
                ];
                array_push($pressCompanyAssoc_arr['data'], $pressCompanyAssoc_item);
            }

            http_response_code(200);
            echo json_encode($pressCompanyAssoc_arr);
        } else {
            http_response_code(200);
            echo json_encode(["message" => "No jadwal programs found.", "data" => []]);
        }
    }
}