<?php

namespace App\Models;

use App\Database;
use PDO;
use PDOException; // Import PDOException

class User
{
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Metode pembantu untuk sanitasi input
    private function sanitizeProperties() {
        // ID tidak perlu di-sanitize dengan strip_tags/htmlspecialchars saat diatur dari URL
        // Hanya properti yang datang dari input pengguna (POST/PUT body)
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        // Password sudah di-hash, jadi tidak perlu strip_tags/htmlspecialchars di sini
        // $this->password = htmlspecialchars(strip_tags($this->password));
    }

    public function read()
    {
        $query = "SELECT id, name, email, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties(); // Panggil metode sanitasi

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password); // $this->password sudah berupa hash dari controller

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User creation error: " . $e->getMessage());
            // Jika ada Unique Constraint Violation (misal email duplikat)
            if ($e->getCode() === '23000') { // SQLSTATE for Integrity Constraint Violation
                // Kita akan menangani ini di controller dengan userExists() sebelumnya
                // Jadi di sini cukup false untuk generic failure
            }
            return false;
        }
    }

    public function readOne()
    {
        $query = "SELECT id, name, email, created_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT); // Bind as integer for ID
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function update()
    {
        // Query disesuaikan untuk update password opsional
        $query = "UPDATE " . $this->table_name . " SET name = :name, email = :email";
        if ($this->password !== null) { // Jika password disediakan untuk update
            $query .= ", password = :password";
        }
        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties(); // Panggil metode sanitasi

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        if ($this->password !== null) {
            $stmt->bindParam(':password', $this->password); // Password sudah di-hash
        }
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT); // Bind as integer

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id, PDO::PARAM_INT); // Bind as integer

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function userExists() {
        $query = "SELECT id, name, email, password FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            return true;
        }
        return false;
    }
}