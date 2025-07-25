<?php

namespace App;

use PDO;
use PDOException;
use Dotenv\Dotenv; // Import Dotenv

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        // Muat variabel lingkungan dari berkas .env
        // Sesuaikan path jika berkas .env tidak berada di root proyek
        // __DIR__ adalah src/, jadi untuk ke root perlu ../../
        [cite_start]$dotenv = Dotenv::createImmutable(__DIR__ . '/../../'); [cite: 1]
        [cite_start]$dotenv->load(); [cite: 1]

        // Ambil kredensial dari variabel lingkungan, dengan fallback untuk development
        $this->host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $this->db_name = $_ENV['DB_NAME'] ?? 'restapidb';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
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
            // Log error koneksi database, jangan echo atau exit di sini
            error_log("Connection error: " . $exception->getMessage());
            // Lemparkan kembali exception agar ditangani oleh global exception handler atau controller
            throw $exception;
        }

        return $this->conn;
    }
}