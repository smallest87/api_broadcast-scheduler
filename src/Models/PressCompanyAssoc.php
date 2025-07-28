<?php

namespace App\Models;

use PDO;
use PDOException;

class PressCompanyAssoc
{
    private $conn;
    private $table_name = "press"; // Nama tabel database sudah disesuaikan

    // Object properties - INI ADALAH NAMA FIELD DATABASE ANDA
    public $ID;
    public $jenis_entitas;
    public $nama_entitas;
    public $singkatan;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metode pembantu untuk sanitasi input
    private function sanitizeProperties() {
        // Sanitasi menggunakan nama properti yang benar
        // Pastikan format waktu sesuai dengan tipe data database
        $this->ID = htmlspecialchars(strip_tags($this->ID)); // Akan dikirim sebagai HH:MM:SS
        $this->jenis_entitas = htmlspecialchars(strip_tags($this->jenis_entitas));
        $this->nama_entitas = htmlspecialchars(strip_tags($this->nama_entitas));
        $this->singkatan = htmlspecialchars(strip_tags($this->singkatan));
    }

    // Read all jadwal programs
    public function read()
    {
        // Sesuaikan nama kolom di SELECT, tambahkan field baru
        $query = "SELECT ID, jenis_entitas, nama_entitas, singkatan FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}