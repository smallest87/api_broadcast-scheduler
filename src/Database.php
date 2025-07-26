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
        // Lokasi file .env
        // Asumsikan file .env berada satu direktori di atas file Database.php
        $envFilePath = __DIR__ . '/../.env';

        // Muat variabel lingkungan dari file .env secara manual
        $env_vars = $this->loadEnv($envFilePath);

        // Pastikan variabel database ada sebelum digunakan
        if (
            isset($env_vars['DB_HOST']) &&
            isset($env_vars['DB_NAME']) &&
            isset($env_vars['DB_USER']) &&
            isset($env_vars['DB_PASS'])
        ) {
            $this->host = $env_vars['DB_HOST'];
            $this->db_name = $env_vars['DB_NAME'];
            $this->username = $env_vars['DB_USER'];
            $this->password = $env_vars['DB_PASS'];
        } else {
            // Handle error jika variabel tidak lengkap atau file .env tidak ditemukan/terbaca
            error_log("[Database Error] Missing database environment variables or .env file not loaded.");
            // Anda bisa melempar pengecualian atau menangani ini sesuai kebutuhan aplikasi Anda
            throw new \Exception("Database environment variables are not properly set.");
        }
    }

    /**
     * Fungsi untuk memuat file .env secara manual.
     * Mirip dengan fungsi loadEnv yang Anda berikan sebelumnya.
     */
    private function loadEnv($path): array
    {
        $env_vars = [];

        if (!file_exists($path)) {
            error_log("[Database Error] .env file not found at: " . $path);
            return $env_vars; // Mengembalikan array kosong jika file tidak ditemukan
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Abaikan baris komentar dan baris kosong
            $line = trim($line);
            if (strpos($line, '#') === 0 || empty($line)) {
                continue;
            }

            // Pisahkan kunci dan nilai
            // Pastikan ada '=' dalam baris
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $env_vars[trim($key)] = trim($value);
            }
        }
        return $env_vars;
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
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Menambahkan ini agar default fetch mode adalah associative array
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Mematikan emulasi prepare untuk keamanan dan performa

        } catch (PDOException $exception) {
            // Log kesalahan koneksi database
            error_log("[Database Error] Connection failed: " . $exception->getMessage());
            // Lempar kembali pengecualian untuk penanganan lebih lanjut di tingkat aplikasi
            throw $exception;
        }

        return $this->conn;
    }
}