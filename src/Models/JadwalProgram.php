<?php

namespace App\Models;

use PDO;
use PDOException;

class JadwalProgram
{
    private $conn;
    private $table_name = "bs_schedule"; // Nama tabel database sudah disesuaikan

    // Object properties - INI ADALAH NAMA FIELD DATABASE ANDA
    public $id;
    public $schedule_item_duration; // Variabel properti disesuaikan, akan menjadi string HH:MM:SS
    public $schedule_item_title;    // Variabel properti disesuaikan
    public $schedule_item_type;     // Variabel properti disesuaikan
    public $tgl_siaran;             // <-- BARU: Ditambahkan properti tgl_siaran
    public $schedule_onair;         // Variabel properti disesuaikan, akan menjadi string YYYY-MM-DD HH:MM:SS
    public $schedule_author;        // <-- BARU: Ditambahkan properti schedule_author

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metode pembantu untuk sanitasi input
    private function sanitizeProperties() {
        // Sanitasi menggunakan nama properti yang benar
        // Pastikan format waktu sesuai dengan tipe data database
        $this->schedule_item_duration = htmlspecialchars(strip_tags($this->schedule_item_duration)); // Akan dikirim sebagai HH:MM:SS
        $this->schedule_item_title = htmlspecialchars(strip_tags($this->schedule_item_title));
        $this->schedule_item_type = htmlspecialchars(strip_tags($this->schedule_item_type));
        $this->schedule_author = htmlspecialchars(strip_tags($this->schedule_author)); // Sanitasi field baru
        // tgl_siaran dan schedule_onair tidak disanitasi karena diharapkan dalam format yang ketat
    }

    // Read all jadwal programs
    public function read()
    {
        // Sesuaikan nama kolom di SELECT, tambahkan field baru
        $query = "SELECT id, schedule_item_duration, schedule_item_title, schedule_item_type, tgl_siaran, schedule_onair, schedule_author FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create a new jadwal program
    public function create()
    {
        // Sesuaikan nama kolom di INSERT INTO, tambahkan field baru
        $query = "INSERT INTO " . $this->table_name . " (schedule_item_duration, schedule_item_title, schedule_item_type, tgl_siaran, schedule_onair, schedule_author) VALUES (:schedule_item_duration, :schedule_item_title, :schedule_item_type, :tgl_siaran, :schedule_onair, :schedule_author)";
        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties(); // Panggil metode sanitasi

        // Bind values menggunakan nama properti yang benar
        $stmt->bindParam(":schedule_item_duration", $this->schedule_item_duration);
        $stmt->bindParam(":schedule_item_title", $this->schedule_item_title);
        $stmt->bindParam(":schedule_item_type", $this->schedule_item_type);
        $stmt->bindParam(":tgl_siaran", $this->tgl_siaran); // Bind field baru
        $stmt->bindParam(":schedule_onair", $this->schedule_onair); // Bind field baru
        $stmt->bindParam(":schedule_author", $this->schedule_author); // Bind field baru

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
        // Pastikan semua kolom diambil di sini juga, tambahkan field baru
        $query = "SELECT id, schedule_item_duration, schedule_item_title, schedule_item_type, tgl_siaran, schedule_onair, schedule_author FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT); // Bind as integer
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Set properti objek dengan nilai dari kolom database yang benar
            $this->schedule_item_duration = $row['schedule_item_duration'];
            $this->schedule_item_title = $row['schedule_item_title'];
            $this->schedule_item_type = $row['schedule_item_type'];
            $this->tgl_siaran = $row['tgl_siaran']; // Set field baru
            $this->schedule_onair = $row['schedule_onair'];
            $this->schedule_author = $row['schedule_author']; // Set field baru
            return true;
        }
        return false;
    }

    // Update a jadwal program
    public function update()
    {
        // Sesuaikan nama kolom di UPDATE SET, tambahkan field baru
        $query = "UPDATE " . $this->table_name . " SET schedule_item_duration = :schedule_item_duration, schedule_item_title = :schedule_item_title, schedule_item_type = :schedule_item_type, tgl_siaran = :tgl_siaran, schedule_onair = :schedule_onair, schedule_author = :schedule_author WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties(); // Panggil metode sanitasi

        // Bind values menggunakan nama properti yang benar
        $stmt->bindParam(':schedule_item_duration', $this->schedule_item_duration);
        $stmt->bindParam(':schedule_item_title', $this->schedule_item_title);
        $stmt->bindParam(':schedule_item_type', $this->schedule_item_type);
        $stmt->bindParam(':tgl_siaran', $this->tgl_siaran); // Bind field baru
        $stmt->bindParam(':schedule_onair', $this->schedule_onair); // Bind field baru
        $stmt->bindParam(':schedule_author', $this->schedule_author); // Bind field baru
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