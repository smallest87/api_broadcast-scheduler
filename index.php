<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/vendor/autoload.php';

use App\Router;
use App\Database;
use App\Controllers\UserController;
use App\Controllers\JadwalProgramController; // <-- NEW
use App\Controllers\UserSettingsController;   // <-- NEW
use App\Models\User;

if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php') {
    $db_connection_status = "Tidak terkoneksi";
    $table_status = "Tidak terdeteksi";

    try {
        $database = new Database();
        $conn = $database->getConnection();

        if ($conn) {
            $db_connection_status = "Berhasil terkoneksi dengan database!";

            // Test if 'users' table exists
            $stmt_users = $conn->query("SHOW TABLES LIKE 'users'");
            $users_table_status = $stmt_users->rowCount() > 0 ? "Tabel 'users' terdeteksi." : "Tabel 'users' TIDAK ditemukan. Mohon buat tabelnya.";

            // Test if 'jadwal_program' table exists
            $stmt_jadwal = $conn->query("SHOW TABLES LIKE 'jadwal_program'");
            $jadwal_table_status = $stmt_jadwal->rowCount() > 0 ? "Tabel 'jadwal_program' terdeteksi." : "Tabel 'jadwal_program' TIDAK ditemukan. Mohon buat tabelnya.";
            
            // Test if 'user_settings' table exists
            $stmt_settings = $conn->query("SHOW TABLES LIKE 'user_settings'");
            $settings_table_status = $stmt_settings->rowCount() > 0 ? "Tabel 'user_settings' terdeteksi." : "Tabel 'user_settings' TIDAK ditemukan. Mohon buat tabelnya.";

        }
    } catch (\Exception $e) {
        $db_connection_status = "Gagal terkoneksi database: " . $e->getMessage();
        $users_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";
        $jadwal_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";
        $settings_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";
    }

    echo json_encode([
        "status" => "API is running",
        "database_connection" => $db_connection_status,
        "tables_status" => [ // Group table statuses
            "users" => $users_table_status,
            "jadwal_program" => $jadwal_table_status,
            "user_settings" => $settings_table_status
        ],
        "message" => "Akses endpoint API lain untuk fungsionalitas CRUD (e.g., /users, /jadwal-program, /user-settings, /login)"
    ]);
    exit();
}


$router = new Router();

// User Routes (existing)
$router->addRoute('GET', '/users', ['UserController', 'index']);
$router->addRoute('GET', '/users/{id}', ['UserController', 'show']);
$router->addRoute('POST', '/users', ['UserController', 'store']);
$router->addRoute('PUT', '/users/{id}', ['UserController', 'update']);
$router->addRoute('DELETE', '/users/{id}', ['UserController', 'destroy']);
$router->addRoute('POST', '/login', ['UserController', 'login']);

// JadwalProgram Routes (NEW)
$router->addRoute('GET', '/jadwal-program', ['JadwalProgramController', 'index']);
$router->addRoute('GET', '/jadwal-program/{id}', ['JadwalProgramController', 'show']);
$router->addRoute('POST', '/jadwal-program', ['JadwalProgramController', 'store']);
$router->addRoute('PUT', '/jadwal-program/{id}', ['JadwalProgramController', 'update']);
$router->addRoute('DELETE', '/jadwal-program/{id}', ['JadwalProgramController', 'destroy']);

// UserSettings Routes (NEW)
$router->addRoute('GET', '/user-settings', ['UserSettingsController', 'index']);
$router->addRoute('GET', '/user-settings/{id}', ['UserSettingsController', 'show']);
$router->addRoute('GET', '/user-settings/key/{key}', ['UserSettingsController', 'showByKey']); // Get by setting_key
$router->addRoute('POST', '/user-settings', ['UserSettingsController', 'store']);
$router->addRoute('PUT', '/user-settings/{id}', ['UserSettingsController', 'update']);
$router->addRoute('PUT', '/user-settings/key/{key}', ['UserSettingsController', 'updateByKey']); // Update by setting_key
$router->addRoute('DELETE', '/user-settings/{id}', ['UserSettingsController', 'destroy']);

$router->dispatch();

?>