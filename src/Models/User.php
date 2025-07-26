<?php

namespace App\Models;

use App\Database;
use PDO;
use PDOException; // Import PDOException

class User
{
    private $conn;
    private $table_name = "bs_user"; // Pastikan ini adalah nama tabel yang benar

    // Properti model yang sesuai dengan kolom tabel Anda
    public $ID; // ID (Primary Key)
    public $user_email;
    public $user_pass; // Akan menyimpan hash password
    public $user_status;
    public $user_publicname;
    public $user_url;
    // Jika ada kolom created_at/updated_at di DB yang otomatis diisi, Anda bisa menambahkannya di sini
    // public $created_at;
    // public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metode pembantu untuk sanitasi input
    private function sanitizeProperties() {
        // Hanya properti yang datang dari input pengguna (POST/PUT body) yang perlu di-sanitize
        // ID tidak perlu di-sanitize
        $this->user_email = htmlspecialchars(strip_tags($this->user_email));
        // user_pass sudah di-hash di controller, tidak perlu sanitasi lagi di sini
        // $this->user_pass = htmlspecialchars(strip_tags($this->user_pass));
        $this->user_publicname = htmlspecialchars(strip_tags($this->user_publicname));
        $this->user_url = htmlspecialchars(strip_tags($this->user_url));
        // user_status biasanya integer, mungkin tidak perlu strip_tags/htmlspecialchars, tapi bisa divalidasi sebagai int
        // $this->user_status = (int)$this->user_status;
    }

    public function read()
    {
        // Sesuaikan kolom yang diambil
        $query = "SELECT ID, user_email, user_status, user_publicname, user_url FROM " . $this->table_name . " ORDER BY ID DESC"; // Mengurutkan berdasarkan ID
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create()
    {
        // Sesuaikan kolom dan placeholder
        $query = "INSERT INTO " . $this->table_name . " (user_email, user_pass, user_status, user_publicname, user_url) VALUES (:user_email, :user_pass, :user_status, :user_publicname, :user_url)";
        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties(); // Panggil metode sanitasi

        // Bind parameter yang sesuai dengan properti model baru
        $stmt->bindParam(":user_email", $this->user_email);
        $stmt->bindParam(":user_pass", $this->user_pass); // $this->user_pass sudah berupa hash dari controller
        $stmt->bindParam(":user_status", $this->user_status, PDO::PARAM_INT); // Asumsi user_status adalah integer
        $stmt->bindParam(":user_publicname", $this->user_publicname);
        $stmt->bindParam(":user_url", $this->user_url);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User creation error: " . $e->getMessage());
            // Jika ada Unique Constraint Violation (misal user_email duplikat)
            if ($e->getCode() === '23000') { // SQLSTATE for Integrity Constraint Violation
                // Ini akan ditangani di controller dengan userExists() sebelumnya
            }
            return false;
        }
    }

    public function readOne()
    {
        // Sesuaikan kolom yang diambil dan kondisi WHERE
        $query = "SELECT ID, user_email, user_pass, user_status, user_publicname, user_url FROM " . $this->table_name . " WHERE ID = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->ID, PDO::PARAM_INT); // Bind ID sebagai integer
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Isi properti model dengan data dari database
            $this->ID = $row['ID'];
            $this->user_email = $row['user_email'];
            $this->user_pass = $row['user_pass']; // Ambil hash password untuk verifikasi login
            $this->user_status = $row['user_status'];
            $this->user_publicname = $row['user_publicname'];
            $this->user_url = $row['user_url'];
            return true;
        }
        return false;
    }

    public function update()
    {
        // Bangun query UPDATE secara dinamis
        $set_parts = [];
        $params = [];

        // HANYA tambahkan ke SET jika properti tidak kosong atau tidak null (kecuali password)
        if (!empty($this->user_email)) {
            $set_parts[] = "user_email = :user_email";
            $params[':user_email'] = $this->user_email;
        }
        if ($this->user_pass !== null) { // user_pass akan diisi hash baru jika ada perubahan
            $set_parts[] = "user_pass = :user_pass";
            $params[':user_pass'] = $this->user_pass;
        }
        // Untuk status dan url, gunakan isset() karena nilai 0 atau string kosong valid
        if (isset($this->user_status)) {
            $set_parts[] = "user_status = :user_status";
            $params[':user_status'] = $this->user_status;
        }
        if (isset($this->user_publicname) && $this->user_publicname !== '') {
            $set_parts[] = "user_publicname = :user_publicname";
            $params[':user_publicname'] = $this->user_publicname;
        }
        if (isset($this->user_url) && $this->user_url !== '') {
            $set_parts[] = "user_url = :user_url";
            $params[':user_url'] = $this->user_url;
        }

        if (empty($set_parts)) {
            // Tidak ada yang perlu diupdate
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET " . implode(", ", $set_parts) . " WHERE ID = :ID";

        $stmt = $this->conn->prepare($query);

        // Bind parameter
        foreach ($params as $key => &$val) {
            // Tentukan tipe PDO yang tepat
            if ($key === ':user_status') {
                $stmt->bindParam($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindParam($key, $val);
            }
        }
        $stmt->bindParam(':ID', $this->ID, PDO::PARAM_INT); // Bind ID sebagai integer

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User update error: " . $e->getMessage());
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
            error_log("User deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function userExists() {
        // Sesuaikan kolom yang diambil dan kondisi WHERE (berdasarkan user_email)
        $query = "SELECT ID, user_email, user_pass, user_status, user_publicname, user_url FROM " . $this->table_name . " WHERE user_email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Isi properti model dengan data dari database
            $this->ID = $row['ID'];
            $this->user_email = $row['user_email'];
            $this->user_pass = $row['user_pass']; // Penting: Ambil hash password untuk verifikasi login
            $this->user_status = $row['user_status'];
            $this->user_publicname = $row['user_publicname'];
            $this->user_url = $row['user_url'];
            return true;
        }
        return false;
    }
}