<?php

// Nonaktifkan pelaporan error dan display_errors untuk produksi
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Konfigurasi CORS (Sesuaikan di produksi untuk domain spesifik Anda)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/vendor/autoload.php';

use App\Router;
use App\Database;
use App\Controllers\UserController;
use App\Controllers\JadwalProgramController;
use App\Controllers\UserSettingsController;
// use App\Models\User; // Tidak perlu diimpor di sini lagi

// Menangani permintaan OPTIONS (preflight CORS) lebih awal
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Global Exception Handler untuk menangani semua uncaught exceptions
set_exception_handler(function ($exception) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal Server Error. Please try again later.",
        // "details" => $exception->getMessage() // HANYA untuk development
    ]);
    error_log("Unhandled exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    exit();
});

// Endpoint untuk status API dan koneksi database (dipindahkan ke rute)
// Ini akan ditangani oleh router sekarang, bukan kondisi if di root
// Jika Anda ingin endpoint status tetap di root, Anda perlu logic terpisah atau
// membiarkan router mencoba mencocokkan '/'

$router = new Router();

// Rute API untuk Status/Health Check
// Ini menggantikan blok if ($_SERVER['REQUEST_URI'] === '/' ...) yang lama
$router->addRoute('GET', '/', function() {
    $db_connection_status = "Tidak terkoneksi";
    $users_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";
    $jadwal_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";
    $settings_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";

    try {
        $database = new Database();
        $conn = $database->getConnection(); // Potensi melempar PDOException

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
    } catch (\PDOException $e) { // Catch specific PDOException
        $db_connection_status = "Gagal terkoneksi database: " . $e->getMessage();
        $users_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";
        $jadwal_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";
        $settings_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";
    }

    echo json_encode([
        "status" => "API is running",
        "database_connection" => $db_connection_status,
        "tables_status" => [
            "users" => $users_table_status,
            "jadwal_program" => $jadwal_table_status,
            "user_settings" => $settings_table_status
        ],
        "message" => "Akses endpoint API lain untuk fungsionalitas CRUD (e.g., /users, /jadwal-program, /user-settings, /login)"
    ]);
});

// User Routes
$router->addRoute('GET', '/users', ['UserController', 'index']);
$router->addRoute('GET', '/users/{id}', ['UserController', 'show']);
$router->addRoute('POST', '/users', ['UserController', 'store']);
$router->addRoute('PUT', '/users/{id}', ['UserController', 'update']);
$router->addRoute('DELETE', '/users/{id}', ['UserController', 'destroy']);
$router->addRoute('POST', '/login', ['UserController', 'login']);

// JadwalProgram Routes
$router->addRoute('GET', '/jadwal-program', ['JadwalProgramController', 'index']);
$router->addRoute('GET', '/jadwal-program/{id}', ['JadwalProgramController', 'show']);
$router->addRoute('POST', '/jadwal-program', ['JadwalProgramController', 'store']);
$router->addRoute('PUT', '/jadwal-program/{id}', ['JadwalProgramController', 'update']);
$router->addRoute('DELETE', '/jadwal-program/{id}', ['JadwalProgramController', 'destroy']);

// UserSettings Routes
$router->addRoute('GET', '/user-settings', ['UserSettingsController', 'index']);
$router->addRoute('GET', '/user-settings/{id}', ['UserSettingsController', 'show']);
$router->addRoute('GET', '/user-settings/key/{key}', ['UserSettingsController', 'showByKey']);
$router->addRoute('POST', '/user-settings', ['UserSettingsController', 'store']);
$router->addRoute('PUT', '/user-settings/{id}', ['UserSettingsController', 'update']);
$router->addRoute('PUT', '/user-settings/key/{key}', ['UserSettingsController', 'updateByKey']);
$router->addRoute('DELETE', '/user-settings/{id}', ['UserSettingsController', 'destroy']);

$router->dispatch();