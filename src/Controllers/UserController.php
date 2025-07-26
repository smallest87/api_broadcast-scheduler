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
        $stmt = $this->user->read(); // Metode read() di model User harus mengambil semua kolom
        $num = $stmt->rowCount();

        if ($num > 0) {
            $users_arr = [];
            $users_arr['data'] = [];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                // Ekstrak semua kolom dari baris hasil query
                extract($row);

                $user_item = [
                    "ID" => $ID, // Sesuaikan dengan nama kolom 'ID'
                    "user_email" => $user_email, // Sesuaikan dengan nama kolom 'user_email'
                    "user_status" => $user_status, // Sesuaikan dengan nama kolom 'user_status'
                    "user_publicname" => $user_publicname, // Sesuaikan dengan nama kolom 'user_publicname'
                    "user_url" => $user_url // Sesuaikan dengan nama kolom 'user_url'
                    // user_pass tidak disertakan untuk keamanan
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
        $this->user->ID = $id; // Sesuaikan dengan properti ID di model User
        if ($this->user->readOne()) { // Metode readOne() di model User harus mengisi semua properti
            $user_arr = [
                "ID" => $this->user->ID,
                "user_email" => $this->user->user_email,
                "user_status" => $this->user->user_status,
                "user_publicname" => $this->user->user_publicname,
                "user_url" => $this->user->user_url
                // user_pass tidak disertakan untuk keamanan
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

        // Validasi input berdasarkan kolom tabel baru
        if (empty($data->user_email) || empty($data->user_pass) || empty($data->user_publicname)) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to create user. Data is incomplete. Please provide user_email, user_pass, and user_publicname."]);
            return;
        }

        // Isi properti model dengan data dari request
        $this->user->user_email = $data->user_email;
        $this->user->user_pass = password_hash($data->user_pass, PASSWORD_BCRYPT); // Hash password
        $this->user->user_publicname = $data->user_publicname;
        $this->user->user_status = $data->user_status ?? 0; // Beri nilai default jika tidak disediakan
        $this->user->user_url = $data->user_url ?? null; // Beri nilai default jika tidak disediakan

        // Periksa apakah email sudah ada sebelum mencoba membuat
        // Metode userExists() di model User harus memeriksa user_email
        if ($this->user->userExists()) {
            http_response_code(409); // Conflict
            echo json_encode(["message" => "Unable to create user. Email already exists."]);
            return;
        }

        if ($this->user->create()) { // Metode create() di model User harus menyimpan semua properti
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

        // Validasi input: ID harus ada dan setidaknya satu field yang bisa diupdate
        if (empty($id) || (empty($data->user_email) && empty($data->user_pass) && empty($data->user_status) && empty($data->user_publicname) && empty($data->user_url))) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to update user. Provide ID and at least one field (user_email, user_pass, user_status, user_publicname, or user_url)."]);
            return;
        }

        $this->user->ID = $id; // Sesuaikan dengan properti ID di model User

        // Baca data user yang sudah ada untuk mengisi properti yang tidak diupdate
        if (!$this->user->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "User with ID {$id} not found."]);
            return;
        }

        // Update properti model hanya jika data baru disediakan
        $this->user->user_email = !empty($data->user_email) ? $data->user_email : $this->user->user_email;
        $this->user->user_status = isset($data->user_status) ? $data->user_status : $this->user->user_status; // Gunakan isset untuk 0
        $this->user->user_publicname = !empty($data->user_publicname) ? $data->user_publicname : $this->user->user_publicname;
        $this->user->user_url = isset($data->user_url) ? $data->user_url : $this->user->user_url; // Gunakan isset untuk null/empty string

        // Handle password update jika disediakan
        if (!empty($data->user_pass)) {
            $this->user->user_pass = password_hash($data->user_pass, PASSWORD_BCRYPT);
        } else {
            // Penting: Jika password tidak diupdate, properti user_pass di model harus tetap menggunakan hash yang sudah ada
            // atau model update method harus diatur untuk tidak mengupdate field password jika tidak ada perubahan.
            // Di sini, kita tidak mengubah properti user_pass di controller jika tidak ada input baru,
            // mengandalkan model untuk mempertahankan nilai lama.
        }

        if ($this->user->update()) { // Metode update() di model User harus mengupdate properti yang telah diubah
            http_response_code(200);
            echo json_encode(["message" => "User updated."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to update user."]);
        }
    }

    public function destroy($id)
    {
        $this->user->ID = $id; // Sesuaikan dengan properti ID di model User

        if (!$this->user->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "User with ID {$id} not found."]);
            return;
        }

        if ($this->user->delete()) { // Metode delete() di model User harus menghapus berdasarkan ID
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

        if (empty($data->user_email) || empty($data->user_pass)) {
            http_response_code(400);
            echo json_encode(["message" => "Login failed. Email and password are required."]);
            return;
        }

        $this->user->user_email = $data->user_email;

        // Metode userExists() di model User harus mengambil semua data user (termasuk user_pass) berdasarkan email
        if ($this->user->userExists()) {
            // Password yang diambil dari DB sudah dalam bentuk hash (disimpan di $this->user->user_pass)
            if (password_verify($data->user_pass, $this->user->user_pass)) {
                // Hardcode JWT_SECRET_KEY, JWT_ISSUER, JWT_AUDIENCE untuk debugging
                // JANGAN GUNAKAN INI DI PRODUKSI!
                $secret_key = "kunci_rahasia_jwt_yang_sangat_panjang_dan_acak"; // Ganti dengan kunci kuat
                $issuer = "https://api.lumbungdata.com";
                $audience = "https://api.lumbungdata.com";

                $issued_at = time();
                $expiration_time = $issued_at + (3600 * 24); // Token berlaku 24 jam

                $token = array(
                    "iss" => $issuer,
                    "aud" => $audience,
                    "iat" => $issued_at,
                    "exp" => $expiration_time,
                    "data" => array(
                        "ID" => $this->user->ID, // Sesuaikan dengan ID
                        "user_publicname" => $this->user->user_publicname, // Sesuaikan dengan nama publik
                        "user_email" => $this->user->user_email // Sesuaikan dengan email
                    )
                );

                try {
                    $jwt = JWT::encode($token, $secret_key, 'HS256');
                    http_response_code(200);
                    echo json_encode([
                        "message" => "Successful login.",
                        "jwt" => $jwt,
                        "user_email" => $this->user->user_email // Sesuaikan dengan email
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