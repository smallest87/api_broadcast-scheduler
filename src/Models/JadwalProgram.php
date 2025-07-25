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

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Read all jadwal programs
    public function read()
    {
        $query = "SELECT id, durasi, segmen, jenis,waktu_siar FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create a new jadwal program
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (durasi, segmen, jenis) VALUES (:durasi, :segmen, :jenis)";
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->durasi = htmlspecialchars(strip_tags($this->durasi));
        $this->segmen = htmlspecialchars(strip_tags($this->segmen));
        $this->jenis = htmlspecialchars(strip_tags($this->jenis));

        // Bind values
        $stmt->bindParam(":durasi", $this->durasi);
        $stmt->bindParam(":segmen", $this->segmen);
        $stmt->bindParam(":jenis", $this->jenis);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("JadwalProgram creation error: " . $e->getMessage());
        }
        return false;
    }

    // Read a single jadwal program by ID
    public function readOne()
    {
        $query = "SELECT id, durasi, segmen, jenis FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->durasi = $row['durasi'];
            $this->segmen = $row['segmen'];
            $this->jenis = $row['jenis'];
            return true;
        }
        return false;
    }

    // Update a jadwal program
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET durasi = :durasi, segmen = :segmen, jenis = :jenis WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->durasi = htmlspecialchars(strip_tags($this->durasi));
        $this->segmen = htmlspecialchars(strip_tags($this->segmen));
        $this->jenis = htmlspecialchars(strip_tags($this->jenis));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(':durasi', $this->durasi);
        $stmt->bindParam(':segmen', $this->segmen);
        $stmt->bindParam(':jenis', $this->jenis);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a jadwal program
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind value
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}