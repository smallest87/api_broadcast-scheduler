<?php

namespace App\Models;

use App\Database;
use PDO;
use PDOException; // Import PDOException

class Setting
{
    private $conn;
    private $table_name = "bs_menu"; // Sesuaikan dengan nama tabel bs_menu

    // Properti model sesuai dengan kolom tabel Anda: ID, posisi, judul
    public $ID; // ID (Primary Key)
    public $posisi; // Kolom 'posisi'
    public $judul;  // Kolom 'judul'

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metode pembantu untuk sanitasi input
    private function sanitizeProperties() {
        // Hanya properti yang datang dari input pengguna (POST/PUT body) yang perlu di-sanitize
        // ID tidak perlu di-sanitize
        $this->posisi = htmlspecialchars(strip_tags($this->posisi));
        $this->judul = htmlspecialchars(strip_tags($this->judul));
    }

    public function read()
    {
        // Sesuaikan kolom yang diambil
        $query = "SELECT ID, posisi, judul FROM " . $this->table_name . " ORDER BY ID DESC"; // Mengurutkan berdasarkan ID
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create()
    {
        // Sesuaikan kolom dan placeholder
        $query = "INSERT INTO " . $this->table_name . " (posisi, judul) VALUES (:posisi, :judul)";
        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties(); // Panggil metode sanitasi

        // Bind parameter yang sesuai dengan properti model baru
        $stmt->bindParam(":posisi", $this->posisi);
        $stmt->bindParam(":judul", $this->judul);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Menu creation error: " . $e->getMessage());
            // Jika ada Unique Constraint Violation (misal posisi duplikat)
            if ($e->getCode() === '23000') { // SQLSTATE for Integrity Constraint Violation
                // Ini akan ditangani di controller dengan menuPosisiExists() sebelumnya
            }
            return false;
        }
    }

    public function readOne()
    {
        // Sesuaikan kolom yang diambil dan kondisi WHERE
        $query = "SELECT ID, posisi, judul FROM " . $this->table_name . " WHERE ID = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->ID, PDO::PARAM_INT); // Bind ID sebagai integer
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Isi properti model dengan data dari database
            $this->ID = $row['ID'];
            $this->posisi = $row['posisi'];
            $this->judul = $row['judul'];
            return true;
        }
        return false;
    }

    public function update()
    {
        // Bangun query UPDATE secara dinamis
        $set_parts = [];
        $params = [];

        // HANYA tambahkan ke SET jika properti tidak kosong atau tidak null
        if (!empty($this->posisi)) {
            $set_parts[] = "posisi = :posisi";
            $params[':posisi'] = $this->posisi;
        }
        if (!empty($this->judul)) {
            $set_parts[] = "judul = :judul";
            $params[':judul'] = $this->judul;
        }

        if (empty($set_parts)) {
            // Tidak ada yang perlu diupdate
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET " . implode(", ", $set_parts) . " WHERE ID = :ID";

        $stmt = $this->conn->prepare($query);

        // Bind parameter
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindParam(':ID', $this->ID, PDO::PARAM_INT); // Bind ID sebagai integer

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Menu update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE ID = ?"; // Sesuaikan nama kolom ID
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->ID, PDO::PARAM_INT); // Bind ID sebagai integer

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Menu deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function menuPosisiExists() {
        // Sesuaikan kolom yang diambil dan kondisi WHERE (berdasarkan posisi)
        $query = "SELECT ID, posisi, judul FROM " . $this->table_name . " WHERE posisi = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->posisi);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Isi properti model dengan data dari database
            $this->ID = $row['ID'];
            $this->posisi = $row['posisi'];
            $this->judul = $row['judul'];
            return true;
        }
        return false;
    }
}