<?php

namespace App\Controllers;

use App\Models\UserSettings;
use App\Database;

class UserSettingsController
{
    private $db;
    private $userSettings;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userSettings = new UserSettings($this->db);
    }

    // Get all user settings
    public function index()
    {
        $stmt = $this->userSettings->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $settings_arr = [];
            $settings_arr['data'] = [];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                extract($row);
                $settings_item = [
                    "id" => $id,
                    "setting_key" => $setting_key,
                    "theme" => $theme,
                    "start_time" => $start_time,
                    "created_at" => $created_at,
                    "updated_at" => $updated_at
                ];
                array_push($settings_arr['data'], $settings_item);
            }

            http_response_code(200);
            echo json_encode($settings_arr);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "No user settings found."]);
        }
    }

    // Get a single user setting by ID
    public function show($id)
    {
        $this->userSettings->id = $id;
        if ($this->userSettings->readOne()) {
            $settings_item = [
                "id" => $this->userSettings->id,
                "setting_key" => $this->userSettings->setting_key,
                "theme" => $this->userSettings->theme,
                "start_time" => $this->userSettings->start_time,
                "created_at" => $this->userSettings->created_at,
                "updated_at" => $this->userSettings->updated_at
            ];
            http_response_code(200);
            echo json_encode($settings_item);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User setting not found."]);
        }
    }
    
    // Get a single user setting by setting_key
    public function showByKey($key) // New method to get by key
    {
        $this->userSettings->setting_key = $key;
        if ($this->userSettings->readByKey()) {
            $settings_item = [
                "id" => $this->userSettings->id,
                "setting_key" => $this->userSettings->setting_key,
                "theme" => $this->userSettings->theme,
                "start_time" => $this->userSettings->start_time,
                "created_at" => $this->userSettings->created_at,
                "updated_at" => $this->userSettings->updated_at
            ];
            http_response_code(200);
            echo json_encode($settings_item);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User setting with key '{$key}' not found."]);
        }
    }

    // Create a user setting
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->setting_key) || empty($data->theme) || empty($data->start_time)) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to create user setting. Data is incomplete. Please provide setting_key, theme, and start_time."]);
            return;
        }

        $this->userSettings->setting_key = $data->setting_key;
        $this->userSettings->theme = $data->theme;
        $this->userSettings->start_time = $data->start_time;

        if ($this->userSettings->create()) {
            http_response_code(201);
            echo json_encode(["message" => "User setting created."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to create user setting. Setting key might already exist."]);
        }
    }

    // Update a user setting
    public function update($id)
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($id) || (empty($data->theme) && empty($data->start_time))) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to update user setting. Provide ID and at least theme or start_time."]);
            return;
        }

        $this->userSettings->id = $id;
        if (!$this->userSettings->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "User setting with ID {$id} not found."]);
            return;
        }

        $this->userSettings->theme = !empty($data->theme) ? $data->theme : $this->userSettings->theme;
        $this->userSettings->start_time = !empty($data->start_time) ? $data->start_time : $this->userSettings->start_time;

        if ($this->userSettings->update()) {
            http_response_code(200);
            echo json_encode(["message" => "User setting updated."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to update user setting."]);
        }
    }

    // Update a user setting by setting_key
    public function updateByKey($key) // New method to update by key
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty($key) || (empty($data->theme) && empty($data->start_time))) {
            http_response_code(400);
            echo json_encode(["message" => "Unable to update user setting. Provide setting_key and at least theme or start_time."]);
            return;
        }

        $this->userSettings->setting_key = $key;
        if (!$this->userSettings->readByKey()) { // Use readByKey to check existence
            http_response_code(404);
            echo json_encode(["message" => "User setting with key '{$key}' not found."]);
            return;
        }

        $this->userSettings->theme = !empty($data->theme) ? $data->theme : $this->userSettings->theme;
        $this->userSettings->start_time = !empty($data->start_time) ? $data->start_time : $this->userSettings->start_time;

        if ($this->userSettings->updateByKey()) { // Call updateByKey
            http_response_code(200);
            echo json_encode(["message" => "User setting updated."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to update user setting."]);
        }
    }

    // Delete a user setting
    public function destroy($id)
    {
        $this->userSettings->id = $id;

        if (!$this->userSettings->readOne()) {
            http_response_code(404);
            echo json_encode(["message" => "User setting with ID {$id} not found."]);
            return;
        }

        if ($this->userSettings->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "User setting deleted."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to delete user setting."]);
        }
    }
}