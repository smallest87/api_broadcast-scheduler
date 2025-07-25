<?php

namespace App\Controllers;

use App\Models\User;
use App\Database;

use Firebase\JWT\JWT;

class UserController
{
    private $db;
    private $user;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
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
            http_response_code(404); // Atau 200 OK dengan array kosong jika tidak ada user
            echo json_encode(["message" => "No users found."]);
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
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->name) || empty($data->email) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to create user. Data is incomplete. Please provide name, email, and password."]);
            return;
        }

        $this->user->name = $data->name;
        $this->user->email = $data->email;
        $this->user->password = password_hash($data->password, PASSWORD_BCRYPT);

        if ($this->user->create()) {
            http_response_code(201);
            echo json_encode(["message" => "User created."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to create user. Email might already exist."]);
        }
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($id) || (empty($data->name) && empty($data->email))) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to update user. Provide ID and at least name or email."]);
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

    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->email) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Login failed. Email and password are required."]);
            return;
        }

        $this->user->email = $data->email;

        if ($this->user->userExists()) {
            if (password_verify($data->password, $this->user->password)) {
                $secret_key = 'test_nyobaksaya'; // Hardcoded JWT Secret Key
                $issuer = "https://api.newsnoid.com";
                $audience = "https://api.newsnoid.com";
                $issued_at = time();
                $expiration_time = $issued_at + (3600 * 24);

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
                }
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Login failed. Incorrect password."]);
            }
        }
        else {
            http_response_code(401);
            echo json_encode(["message" => "Login failed. User not found."]);
        }
    }
}