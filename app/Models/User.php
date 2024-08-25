<?php

namespace App\Models;

use Exception;
use PDOException;

class User
{

    private static function db()
    {
        return (new Db)->getInstance();
    }

    public static function addUser($first_name, $last_name, $email, $password)
    {
        try {
            $statement = self::db()->prepare("INSERT INTO users VALUES(NULL, :first_name, :last_name, :email, :password)");

            $statement->execute([
                ":first_name" => $first_name,
                ":last_name" => $last_name,
                ":email" => $email,
                ":password" => $password
            ]);

            return json_encode([
                "message" => "User is sign successfully"
            ]);
        } catch (PDOException) {
            throw new PDOException("Failed to add user.");
        }
    }

    public static function isEmailToken($email)
    {
        try {
            $statement = self::db()->prepare("SELECT * FROM users WHERE email = :email");

            $statement->execute([":email" => $email]);

            return (bool) $statement->fetch();
        } catch (PDOException) {
            throw new PDOException("Failed to check email.", 500);
        }
    }

    public static function getUserByEmail($email)
    {
        try {
            $statement = self::db()->prepare("SELECT * FROM users WHERE email = :email");

            $statement->execute([":email" => $email]);

            $user = $statement->fetch();

            if (empty($user)) {
                throw new Exception("User not exist", 404);
            }

            return $user;
        } catch (PDOException) {
            throw new PDOException("Failed to get user.", 500);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
