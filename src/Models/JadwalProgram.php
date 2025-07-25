<?php

namespace App\Models;

use PDO;
use PDOException;

class JadwalProgram
{
    private $conn;
    private $table_name = "jadwal_program";

    // Object properties
    public $id;
    public $durasi;
    public $segmen;
    public $jenis;
    public $waktu_siar; [cite_start]// <-- Ditambahkan [cite: 2]

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metode pembantu untuk sanitasi input
    private function sanitizeProperties() {
        $this->durasi = htmlspecialchars(strip_tags($this->durasi));
        $this->segmen = htmlspecialchars(strip_tags($this->segmen));
        $this->jenis = htmlspecialchars(strip_tags($this->jenis));
        // waktu_siar umumnya dari DB atau fungsi waktu, tidak perlu di-sanitize seperti input teks
    }

    // Read all jadwal programs
    public function read()
    {
        $query = "SELECT id, durasi, segmen, jenis, waktu_siar FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create a new jadwal program
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (durasi, segmen, jenis) VALUES (:durasi, :segmen, :jenis)";
        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties(); // Panggil metode sanitasi

        // Bind values
        $stmt->bindParam(":durasi", $this->durasi);
        $stmt->bindParam(":segmen", $this->segmen);
        $stmt->bindParam(":jenis", $this->jenis);

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
        $query = "SELECT id, durasi, segmen, jenis, waktu_siar FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT); // Bind as integer
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->durasi = $row['durasi'];
            $this->segmen = $row['segmen'];
            $this->jenis = $row['jenis'];
            $this->waktu_siar = $row['waktu_siar']; // Set properti ini
            return true;
        }
        return false;
    }

    // Update a jadwal program
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET durasi = :durasi, segmen = :segmen, jenis = :jenis WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties(); // Panggil metode sanitasi

        // Bind values
        $stmt->bindParam(':durasi', $this->durasi);
        $stmt->bindParam(':segmen', $this->segmen);
        $stmt->bindParam(':jenis', $this->jenis);
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