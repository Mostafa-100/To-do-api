<?php

namespace App\Controllers;

use App\Models\Database;
use App\Models\Task;
use App\Validator;
use Exception;

class TaskController
{
    public function index()
    {
        $offset = $_GET["offset"] ?? null;
        $limit = $_GET["limit"] ?? null;
        $status_id = $_GET["status_id"] ?? null;

        $user = Authentication::getInfoFromToken();

        try {

            if ($offset !== null && (($msg = Validator::int($offset, "offset")) !== true)) {
                throw new Exception($msg, 401);
            }

            if ($limit !== null && (($msg = Validator::int($limit, "limit")) !== true)) {
                throw new Exception($msg, 401);
            }

            if ($status_id !== null && (($msg = Validator::int($status_id, "status_id")) !== true)) {
                throw new Exception($msg, 401);
            }

            $tasks = Task::getTasks($user->id, $offset, $limit, $status_id);

            foreach ($tasks as &$task) {
                $task["links"] = [
                    "update" => "/api/tasks/{$task['id']}",
                    "delete" => "/api/tasks/{$task['id']}"
                ];
            }

            return json_encode($tasks);
        } catch (Exception $e) {
            error_log_helper($e);
            http_response_code($e->getCode());
            return json_encode([
                "message" => $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        try {

            $user = Authentication::getInfoFromToken();
            $task = json_decode(file_get_contents("php://input"), true);

            $task["name"] = trim(htmlspecialchars($task["name"] ?? ""));
            $task["description"] = trim(htmlspecialchars($task["description"] ?? ""));

            $csrf_token = Authentication::getClientCsrf();

            if (($msg = Validator::csrf($csrf_token)) !== true) {
                throw new Exception($msg, 401);
            }

            if (($msg = Validator::string($task["name"], "name", max: 100)) !== true) {
                throw new Exception($msg, 401);
            }

            if (($msg = Validator::string($task["description"], "description", 0, 200)) !== true) {
                throw new Exception($msg, 401);
            }

            Task::storeTask($user->id, $task);

            return json_encode([
                "message" => "Task successfuly added"
            ]);
        } catch (Exception $e) {
            error_log_helper($e);
            http_response_code($e->getCode());
            return json_encode([
                "message" => $e->getMessage()
            ]);
        }
    }

    public function update($id)
    {

        try {
            $task = json_decode(file_get_contents("php://input"), true);
            $task["name"] = trim(htmlspecialchars($task["name"] ?? ""));
            $task["description"] = trim(htmlspecialchars($task["description"] ?? ""));
            $task["status_id"] = trim($task["status_id"]);

            $user = Authentication::getInfoFromToken();

            $csrf_token = Authentication::getClientCsrf();

            if (($msg = Validator::csrf($csrf_token)) !== true) {
                throw new Exception($msg, 401);
            }

            if (($msg = Validator::string($task["name"], "name", max: 100)) !== true) {
                throw new Exception($msg, 401);
            }

            if (($msg = Validator::string($task["description"], "description", 0, 200)) !== true) {
                throw new Exception($msg, 401);
            }

            if (($msg = Validator::int($task["status_id"], "status id")) !== true) {
                throw new Exception($msg, 401);
            }

            Task::updateTask($id, $task["name"], $task["description"], $task["status_id"], $user->id);

            echo json_encode([
                "message" => "Task successfuly updated"
            ]);
        } catch (Exception $e) {
            error_log_helper($e);
            http_response_code($e->getCode());
            return json_encode([
                "message" => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Authentication::getInfoFromToken();

            $csrf_token = Authentication::getClientCsrf();

            if (($msg = Validator::csrf($csrf_token)) !== true) {
                throw new Exception($msg, 401);
            }

            Task::deleteTask($id, $user->id);

            echo json_encode(["message" => "Task deleted successfuly"]);
        } catch (Exception $e) {
            error_log_helper($e);
            http_response_code($e->getCode());
            return json_encode([
                "message" => $e->getMessage()
            ]);
        }
    }

    public function getStatusList()
    {
        try {
            $statusList = Task::fetchStatusList();
            return json_encode($statusList);
        } catch (Exception $e) {
            error_log_helper($e);
            http_response_code($e->getCode());
            return json_encode([
                "message" => $e->getMessage()
            ]);
        }
    }
}
