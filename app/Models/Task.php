<?php

namespace App\Models;

use Exception;
use PDOException;

class Task
{

    private static function db()
    {
        return (new Db)->getInstance();
    }

    public static function getTasks($user_id, $offset = null, $limit = null, $status_id = null)
    {
        try {
            $sql = "SELECT tasks.id, tasks.name, tasks.description, status.name as status FROM tasks INNER JOIN status ON status.id = tasks.status_id WHERE user_id = :user_id";

            if (!is_null($status_id)) {
                $sql .= " AND status_id = :status_id";
            }

            if (!is_null($offset) && !is_null($limit)) {
                $sql .= " LIMIT $offset, $limit";
            }

            $statement = self::db()->prepare($sql);

            $statement->bindParam(":user_id", $user_id);

            if (!is_null($status_id)) {
                $statement->bindParam(":status_id", $status_id);
            }

            $statement->execute();

            $tasks = $statement->fetchAll();

            if (empty($tasks)) {
                throw new Exception("User with this id doesnt have any tasks yet.", 404);
            }

            return $tasks;
        } catch (PDOException) {
            throw new PDOException("Failed to get tasks.", 500);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function storeTask($user_id, $task)
    {
        try {
            $statement = self::db()->prepare("INSERT INTO tasks(name, description, user_id, status_id) VALUES(:name, :description, :user_id, 5)");

            $statement->execute([
                ":name" => $task["name"],
                ":description" => $task["description"],
                ":user_id" => $user_id
            ]);
        } catch (PDOException) {
            throw new PDOException("Failed to save this task.", 500);
        }
    }

    public static function updateTask($id, $name, $description = null, $status_id, $user_id)
    {
        try {
            $statement = self::db()->prepare(
                "UPDATE tasks SET name = :name, description = :description, status_id = :status_id, create_at = :current_date WHERE id = :id AND user_id = :user_id"
            );

            $statement->execute([
                ":id" => $id,
                ":name" => $name,
                ":description" => $description,
                ":current_date" => date("Y-m-d"),
                "status_id" => $status_id,
                ":user_id" => $user_id
            ]);

            if ($statement->rowCount() <= 0) {
                throw new Exception("Task not found.", 404);
            }
        } catch (PDOException) {
            throw new PDOException("Task with this id not exists.", 500);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function deleteTask($id, $user_id)
    {
        try {
            $statement = self::db()->prepare(
                "DELETE FROM tasks WHERE id = :id AND user_id = :user_id"
            );

            $statement->execute([":id" => $id, ":user_id" => $user_id]);

            if ($statement->rowCount() == 0) {
                throw new Exception("Task with this id not exists.", 404);
            }
        } catch (PDOException) {
            throw new PDOException("Task with this id not exists.", 500);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function fetchStatusList()
    {
        try {
            $sql = "SELECT * FROM status";
            $statement = self::db()->prepare($sql);

            $statement->execute();

            $statusList = $statement->fetchAll();

            return $statusList;
        } catch (PDOException) {
            throw new PDOException("Task with this id not exists.", 500);
        }
    }
}
