<?php

namespace App\Controllers;

use App\Database;
use PDO;
use PDOException;

abstract class BaseController
{
    protected $db;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch (PDOException $e) {
            // Log the error for debugging purposes
            error_log("Database connection failed in BaseController: " . $e->getMessage());
            // Return a consistent JSON error response to the client
            http_response_code(500);
            echo json_encode([
                "message" => "Internal Server Error: Database connection unavailable.",
                // "details" => $e->getMessage() // HANYA untuk development, jangan tampilkan di produksi
            ]);
            exit(); // Stop execution as database is critical
        }
    }

    /**
     * Helper method to get JSON input from request body.
     * @return object|null Decoded JSON object, or null if invalid.
     */
    protected function getJsonInput(): ?object
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid JSON input."]);
            exit();
        }
        return $data;
    }
}