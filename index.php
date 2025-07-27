<?php

// Aktifkan pelaporan error dan display_errors untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Menangani permintaan OPTIONS (preflight CORS) lebih awal
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Global Exception Handler untuk menangani semua uncaught exceptions
set_exception_handler(function ($exception) {
    http_response_code(500);
    $errorMessage = "Internal Server Error. Please try again later.";
    // Tampilkan detail error hanya di lingkungan development
    if (ini_get('display_errors') == 1) {
        $errorMessage .= " Details: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    }
    echo json_encode([
        "status" => "error",
        "message" => $errorMessage,
    ]);
    error_log("[Global Error Handler] Unhandled exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    exit();
});

// Inisialisasi Router
$router = new Router();

// Rute API untuk Status/Health Check dengan logging detail
$router->addRoute('GET', '/', function() {
    error_log("[Status Check] Starting API status check.");

    $db_connection_status = "Tidak terkoneksi";
    $users_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";
    $jadwal_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";
    $settings_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal.";

    try {
        error_log("[Status Check] Attempting to get database connection.");
        $database = new Database();
        $conn = $database->getConnection(); // Potensi melempar PDOException
        error_log("[Status Check] Database connection attempt completed.");

        if ($conn) {
            $db_connection_status = "Berhasil terkoneksi dengan database!";
            error_log("[Status Check] Database connection successful.");

            // Test if 'users' table exists
            error_log("[Status Check] Checking 'users' table existence.");
            $stmt_users = $conn->query("SHOW TABLES LIKE 'users'");
            $users_table_status = $stmt_users->rowCount() > 0 ? "Tabel 'users' terdeteksi." : "Tabel 'users' TIDAK ditemukan. Mohon buat tabelnya.";
            error_log("[Status Check] 'users' table status: " . $users_table_status);

            // Test if 'bs_schedule' table exists (KOREKSI DI SINI)
            error_log("[Status Check] Checking 'bs_schedule' table existence.");
            $stmt_jadwal = $conn->query("SHOW TABLES LIKE 'bs_schedule'"); // <--- DIKOREKSI DARI 'jadwal_program'
            $jadwal_table_status = $stmt_jadwal->rowCount() > 0 ? "Tabel 'bs_schedule' terdeteksi." : "Tabel 'bs_schedule' TIDAK ditemukan. Mohon buat tabelnya.";
            error_log("[Status Check] 'bs_schedule' table status: " . $jadwal_table_status);

            // Test if 'user_settings' table exists
            error_log("[Status Check] Checking 'user_settings' table existence.");
            $stmt_settings = $conn->query("SHOW TABLES LIKE 'user_settings'");
            $settings_table_status = $stmt_settings->rowCount() > 0 ? "Tabel 'user_settings' terdeteksi." : "Tabel 'user_settings' TIDAK ditemukan. Mohon buat tabelnya.";
            error_log("[Status Check] 'user_settings' table status: " . $settings_table_status);

        } else {
             error_log("[Status Check] Database connection object is null despite no exception.");
             $db_connection_status = "Gagal terkoneksi database: Objek koneksi null.";
        }
    } catch (\PDOException $e) { // Catch specific PDOException
        error_log("[Status Check] PDOException caught during DB connection/table check: " . $e->getMessage());
        $db_connection_status = "Gagal terkoneksi database: " . $e->getMessage();
        $users_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal: " . $e->getMessage();
        $jadwal_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal: " . $e->getMessage();
        $settings_table_status = "Tidak dapat memeriksa tabel karena koneksi gagal: " . $e->getMessage();
    } catch (\Throwable $e) { // Catch any other throwable errors
        error_log("[Status Check] General error caught during DB connection/table check: " . $e->getMessage());
        $db_connection_status = "Terjadi kesalahan tak terduga: " . $e->getMessage();
        $users_table_status = $db_connection_status;
        $jadwal_table_status = $db_connection_status;
        $settings_table_status = $db_connection_status;
    }

    error_log("[Status Check] API status check completed. Responding to client.");
    echo json_encode([
        "status" => "API is running",
        "database_connection" => $db_connection_status,
        "tables_status" => [
            "users" => $users_table_status,
            "jadwal_program" => $jadwal_table_status, // Nama di respons tetap 'jadwal_program' untuk konsistensi API
            "user_settings" => $settings_table_status
        ],
        "message" => "Akses endpoint API lain untuk fungsionalitas CRUD (e.g., /users, /jadwal-program, /user-settings, /login)"
    ]);
});

// User Routes
$router->addRoute('GET', '/users', ['UserController', 'index']);
$router->addRoute('GET', '/users/{id}', ['UserController', 'show']);
$router->addRoute('POST', '/users', ['UserController', 'store']);

// Rute khusus untuk registrasi pengguna
$router->addRoute('POST', '/register', ['UserController', 'store']); // Ini akan menggunakan logika yang sama dengan 'store'
// Rute yang sudah ada untuk login
$router->addRoute('POST', '/login', ['UserController', 'login']);

$router->addRoute('PUT', '/users/{id}', ['UserController', 'update']);
$router->addRoute('DELETE', '/users/{id}', ['UserController', 'destroy']);

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

// Contoh dalam file konfigurasi router Anda (misalnya, index.php atau routes.php)

// Rute RESTful lainnya untuk manajemen pengguna (opsional, tergantung kebutuhan)
// $router->addRoute('GET', '/users', ['App\Controllers\UserController', 'index']);
// $router->addRoute('GET', '/users/{id}', ['App\Controllers\UserController', 'show']);
// $router->addRoute('PUT', '/users/{id}', ['App\Controllers\UserController', 'update']);
// $router->addRoute('DELETE', '/users/{id}', ['App\Controllers\UserController', 'destroy']);

$router->dispatch();