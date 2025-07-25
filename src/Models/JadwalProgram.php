<?php

namespace App\Models;

use PDO;
use PDOException;

class JadwalProgram
{
    private $conn;
    private $table_name = "bs_schedule";

    // Object properties
    public $id;
    public $durasi;
    public $segmen;
    public $jenis;
    public $waktu_siar; // Properti ditambahkan

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metode pembantu untuk sanitasi input
    private function sanitizeProperties() {
        $this->schedule_item_duration = htmlspecialchars(strip_tags($this->durasi));
        $this->schedule_item_title = htmlspecialchars(strip_tags($this->segmen));
        $this->schedule_item_type = htmlspecialchars(strip_tags($this->jenis));
        // waktu_siar umumnya dari DB atau fungsi waktu, tidak perlu di-sanitize seperti input teks
    }

    // Read all jadwal programs
    public function read()
    {
        $query = "SELECT id, schedule_item_duration, schedule_item_title, schedule_item_type, schedule_onair FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create a new jadwal program
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (schedule_item_duration, schedule_item_title, schedule_item_type) VALUES (:schedule_item_duration, :schedule_item_title, :schedule_item_type)";
        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties(); // Panggil metode sanitasi

        // Bind values
        $stmt->bindParam(":schedule_item_duration", $this->schedule_item_duration);
        $stmt->bindParam(":schedule_item_title", $this->schedule_item_title);
        $stmt->bindParam(":schedule_item_type", $this->schedule_item_type);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("JadwalProgram creation error: " . $e->getMessage());
            return false;
        }
    }

    // Read a single jadwal program by ID
    public function readOne()
    {
        // Pastikan waktu_siar diambil di sini juga
        $query = "SELECT id, schedule_item_duration, schedule_item_title, schedule_item_type, schedule_onair FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT); // Bind as integer
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->durasi = $row['schedule_item_duration'];
            $this->segmen = $row['schedule_item_title'];
            $this->jenis = $row['schedule_item_type'];
            $this->waktu_siar = $row['schedule_onair']; // Set properti ini
            return true;
        }
        return false;
    }

    // Update a jadwal program
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET schedule_item_duration = :schedule_item_duration, schedule_item_title = :schedule_item_title, schedule_item_type = :schedule_item_type WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties(); // Panggil metode sanitasi

        // Bind values
        $stmt->bindParam(':schedule_item_duration', $this->durasi);
        $stmt->bindParam(':schedule_item_title', $this->segmen);
        $stmt->bindParam(':schedule_item_type', $this->jenis);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT); // Bind as integer

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("JadwalProgram update error: " . $e->getMessage());
            return false;
        }
    }

    // Delete a jadwal program
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id, PDO::PARAM_INT); // Bind as integer

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("JadwalProgram deletion error: " . $e->getMessage());
            return false;
        }
    }
}