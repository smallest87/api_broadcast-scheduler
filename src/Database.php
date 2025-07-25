<?php

namespace App;

use PDO;
use PDOException;
// use Dotenv\Dotenv; // Hapus import Dotenv

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        // Gunakan kredensial langsung (tidak disarankan untuk produksi)
        $this->host = '127.0.0.1'; // Ganti dengan host database Anda
        $this->db_name = 'restapidb'; // Ganti dengan nama database Anda
        $this->username = 'admin-api'; // Ganti dengan username database Anda
        $this->password = 'HitamKan23@#'; // Ganti dengan password database Anda
    }

    public function getConnection(): ?PDO
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Log error koneksi database
            error_log("[Database Error] Connection failed: " . $exception->getMessage());
            // Lemparkan kembali exception agar ditangani oleh global exception handler atau controller
            throw $exception;
        }

        return $this->conn;
    }
}