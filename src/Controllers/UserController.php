<?php

namespace App\Controllers;

use App\Models\User;
use App\Database;
use PDOException; // Import PDOException
use Firebase\JWT\JWT;

class UserController extends BaseController // Extend BaseController
{
    private $user;

    public function __construct()
    {
        parent::__construct(); // Panggil konstruktor BaseController
        $this->user = new User($this->db); // $this->db diinisialisasi di BaseController
    }

    public function index()
    {
        $stmt = $this->user->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $users_arr = [];
            $users_arr['data'] = [];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                extract($row);
                $user_item = [
                    "id" => $id,
                    "name" => $name,
                    "email" => $email,
                    "created_at" => $created_at
                ];
                array_push($users_arr['data'], $user_item);
            }

            http_response_code(200);
            echo json_encode($users_arr);
        } else {
            // Lebih baik 200 OK dengan array kosong untuk koleksi
            http_response_code(200);
            echo json_encode(["message" => "No users found.", "data" => []]);
        }
    }

    public function show($id)
    {
        $this->user->id = $id;
        if ($this->user->readOne()) {
            $user_arr = [
                "id" => $this->user->id,
                "name" => $this->user->name,
                "email" => $this->user->email,
                "created_at" => $this->user->created_at
            ];
            http_response_code(200);
            echo json_encode($user_arr);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found."]);
        }
    }

    public function store()
    {
        $data = $this->getJsonInput(); // Gunakan metode dari BaseController

        if (empty($data->name) || empty($data->email) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to create user. Data is incomplete. Please provide name, email, and password."]);
            return;
        }

        $this->user->name = $data->name;
        $this->user->email = $data->email;
        $this->user->password = password_hash($data->password, PASSWORD_BCRYPT);

        // Periksa apakah email sudah ada sebelum mencoba membuat
        if ($this->user->userExists()) {
            http_response_code(409); // Conflict
            echo json_encode(["message" => "Unable to create user. Email already exists."]);
            return;
        }

        if ($this->user->create()) {
            http_response_code(201);
            echo json_encode(["message" => "User created."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to create user. Service unavailable."]);
        }
    }

    public function update($id)
    {
        $data = $this->getJsonInput(); // Gunakan metode dari BaseController

        if (empty($id) || (empty($data->name) && empty($data->email) && empty($data->password))) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to update user. Provide ID and at least one field (name, email, or password)."]);
            return;
        }

        $this->user->id = $id;

        if (!$this->user->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "User with ID {$id} not found."]);
            return;
        }

        $this->user->name = !empty($data->name) ? $data->name : $this->user->name;
        $this->user->email = !empty($data->email) ? $data->email : $this->user->email;
        // Handle password update if provided
        if (!empty($data->password)) {
            $this->user->password = password_hash($data->password, PASSWORD_BCRYPT);
        } else {
            // Penting: Jika password tidak diupdate, pastikan properti password di model tidak diubah
            // (atau tidak disertakan dalam query update jika tidak ada perubahan)
            // Model update method harus diperbarui untuk ini
            $this->user->password = null; // Set to null or original hash to signal no change
        }

        if ($this->user->update()) {
            http_response_code(200);
            echo json_encode(["message" => "User updated."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to update user."]);
        }
    }

    public function destroy($id)
    {
        $this->user->id = $id;

        if (!$this->user->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "User with ID {$id} not found."]);
            return;
        }

        if ($this->user->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "User deleted."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to delete user."]);
        }
    }

    public function login()
    {
        $data = $this->getJsonInput(); // Gunakan metode dari BaseController

        if (empty($data->email) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Login failed. Email and password are required."]);
            return;
        }

        $this->user->email = $data->email;

        if ($this->user->userExists()) {
            // Password yang diambil dari DB sudah dalam bentuk hash
            if (password_verify($data->password, $this->user->password)) {
                $secret_key = $_ENV['JWT_SECRET_KEY']; [cite_start]// Ambil dari .env [cite: 1]
                $issuer = $_ENV['JWT_ISSUER'] ?? "https://api.newsnoid.com"; // Bisa juga dari .env
                $audience = $_ENV['JWT_AUDIENCE'] ?? "https://api.newsnoid.com"; // Bisa juga dari .env
                $issued_at = time();
                $expiration_time = $issued_at + (3600 * 24); // Token berlaku 24 jam

                $token = array(
                    "iss" => $issuer,
                    "aud" => $audience,
                    "iat" => $issued_at,
                    "exp" => $expiration_time,
                    "data" => array(
                        "id" => $this->user->id,
                        "name" => $this->user->name,
                        "email" => $this->user->email
                    )
                );

                try {
                    $jwt = JWT::encode($token, $secret_key, 'HS256');
                    http_response_code(200);
                    echo json_encode([
                        "message" => "Successful login.",
                        "jwt" => $jwt,
                        "email" => $this->user->email
                    ]);
                } catch (\Exception $e) {
                    http_response_code(500);
                    echo json_encode(["message" => "Failed to generate token.", "error" => $e->getMessage()]);
                    error_log("JWT generation error: " . $e->getMessage());
                }
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Login failed. Incorrect password."]);
            }
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Login failed. User not found."]);
        }
    }
}