<?php

namespace App\Models;

use PDO;
use PDOException;

class UserSettings
{
    private $conn;
    private $table_name = "user_settings";

    // Object properties
    public $id;
    public $setting_key;
    public $theme;
    public $start_time;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Read all user settings
    public function read()
    {
        $query = "SELECT id, setting_key, theme, start_time, created_at, updated_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create a new user setting
    public function create()
    {
        // created_at and updated_at are handled by MySQL's DEFAULT CURRENT_TIMESTAMP
        $query = "INSERT INTO " . $this->table_name . " (setting_key, theme, start_time) VALUES (:setting_key, :theme, :start_time)";
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->setting_key = htmlspecialchars(strip_tags($this->setting_key));
        $this->theme = htmlspecialchars(strip_tags($this->theme));
        $this->start_time = htmlspecialchars(strip_tags($this->start_time));

        // Bind values
        $stmt->bindParam(":setting_key", $this->setting_key);
        $stmt->bindParam(":theme", $this->theme);
        $stmt->bindParam(":start_time", $this->start_time);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("UserSettings creation error: " . $e->getMessage());
        }
        return false;
    }

    // Read a single user setting by ID
    public function readOne()
    {
        $query = "SELECT id, setting_key, theme, start_time, created_at, updated_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->setting_key = $row['setting_key'];
            $this->theme = $row['theme'];
            $this->start_time = $row['start_time'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }
    
    // Read a single user setting by setting_key (e.g. for unique settings)
    public function readByKey()
    {
        $query = "SELECT id, setting_key, theme, start_time, created_at, updated_at FROM " . $this->table_name . " WHERE setting_key = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->setting_key);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id']; // Make sure to set the ID too
            $this->setting_key = $row['setting_key'];
            $this->theme = $row['theme'];
            $this->start_time = $row['start_time'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Update a user setting
    public function update()
    {
        // updated_at is handled by MySQL's ON UPDATE CURRENT_TIMESTAMP
        $query = "UPDATE " . $this->table_name . " SET theme = :theme, start_time = :start_time WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->theme = htmlspecialchars(strip_tags($this->theme));
        $this->start_time = htmlspecialchars(strip_tags($this->start_time));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(':theme', $this->theme);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update a user setting by setting_key
    public function updateByKey()
    {
        $query = "UPDATE " . $this->table_name . " SET theme = :theme, start_time = :start_time WHERE setting_key = :setting_key";
        $stmt = $this->conn->prepare($query);

        $this->theme = htmlspecialchars(strip_tags($this->theme));
        $this->start_time = htmlspecialchars(strip_tags($this->start_time));
        $this->setting_key = htmlspecialchars(strip_tags($this->setting_key));

        $stmt->bindParam(':theme', $this->theme);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':setting_key', $this->setting_key);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a user setting
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