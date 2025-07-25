<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        // KREDENSIAL DATABASE DI-HARDCODE AGAR KONEKSI BERHASIL
        // Ini adalah solusi sementara jika .env tidak terbaca di server Anda.
        // HANYA UNTUK PENGUJIAN. Jangan gunakan di produksi.
        $this->host = '127.0.0.1';
        $this->db_name = 'restapidb'; // PASTIKAN NAMA DATABASE ANDA BENAR DI SINI!
        $this->username = 'admin-api';
        $this->password = 'HitamKan23@#';
    }

    public function getConnection()
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
            echo "Connection error: " . $exception->getMessage();
            exit(); // Terminate if connection fails
        }

        return $this->conn;
    }
}