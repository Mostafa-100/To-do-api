<?php

namespace App\Controllers;

use App\Models\User;
use App\Validator;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Authentication
{
    public function register()
    {
        $user = json_decode(file_get_contents("php://input"), true);

        $user["first_name"] = trim(htmlspecialchars($user["first_name"] ?? ""));
        $user["last_name"] = trim(htmlspecialchars($user["last_name"] ?? ""));
        $user["email"] = $user["email"] ?? "";
        $user["password"] = $user["password"] ?? "";
        $user["re_password"] = $user["re_password"] ?? "";

        $csrf_token = self::getClientCsrf();

        try {

            if (($msg = Validator::csrf($csrf_token)) !== true) {
                throw new Exception($msg, 401);
            }

            if (($msg = Validator::string($user["first_name"], "firstname", max: 100)) !== true) {
                throw new Exception($msg, 401);
            }

            if (($msg = Validator::string($user["last_name"], "lastname", max: 100)) !== true) {
                throw new Exception($msg, 401);
            }

            if (! Validator::email($user["email"])) {
                throw new Exception("Email not valid.", 401);
            }

            if (User::isEmailToken($user["email"])) {
                throw new Exception("Email already token", 401);
            }

            if (($msg = Validator::password($user["password"], $user["re_password"])) !== true) {
                throw new Exception($msg, 401);
            }

            $user["password"] = password_hash($user["password"], PASSWORD_DEFAULT);

            return User::addUser($user["first_name"], $user["last_name"], $user["email"], $user["password"]);
        } catch (Exception $e) {
            error_log_helper($e);
            http_response_code($e->getCode());
            return json_encode([
                "message" => $e->getMessage()
            ]);
        }
    }

    public function login()
    {
        $user = json_decode(file_get_contents("php://input"), true);
        $email = $user["email"] ?? "";
        $password = $user["password"] ?? "";
        $csrf_token = self::getClientCsrf();

        try {

            if (($msg = Validator::csrf($csrf_token)) !== true) {
                throw new Exception($msg, 401);
            }

            if (empty($email) || empty($password)) {
                throw new Exception("Data is empty", 401);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email not valide", 401);
            }

            $user = User::getUserByEmail($email);

            if (!password_verify($password, $user["password"])) {
                throw new Exception("Information not correct", 401);
            }

            $token = $this->generateToken($user);
            $expires_at = strtotime("+7days");

            setcookie("token", $token, $expires_at);

            session_regenerate_id(true);

            echo json_encode(["token" => $token]);
        } catch (Exception $e) {
            error_log_helper($e);
            http_response_code($e->getCode());
            return json_encode([
                "message" => $e->getMessage()
            ]);
        }
    }

    private function generateToken($user)
    {
        $secret_key = $_ENV["SECRET_KEY"];

        $payload = [
            "iss" => "Todo-app",
            "aud" => "localhost:8000",
            "id" => $user["id"],
            "email" => $user["email"]
        ];

        try {
            $encode = JWT::encode($payload, $secret_key, "HS256");
            return $encode;
        } catch (Exception $e) {
            throw new Exception("Token is invalid.", 401);
        }
    }

    public function logout()
    {
        setcookie("token", "", -1);
        $_SESSION = [];
        session_unset();
        session_destroy();
        return json_encode([
            "message" => "User log out successfully"
        ]);
    }

    public static function getInfoFromToken()
    {
        try {

            $key = $_ENV["SECRET_KEY"];
            $jwt = Authentication::getUserToken();

            if (is_null($jwt)) {
                throw new Exception("Token not provided.", 401);
            }

            $decoded = JWT::decode($jwt, new Key($key, "HS256"));

            return $decoded;
        } catch (Exception $e) {
            error_log_helper($e);
            http_response_code($e->getCode());
            return json_encode([
                "message" => $e->getMessage()
            ]);
        }
    }

    private static function getUserToken()
    {
        $headers = apache_request_headers();

        $authHeader = $headers["Authorization"] ?? "";

        if (preg_match("/Bearer\s(\S+)/", $authHeader, $matches)) {
            $jwt = $matches[1];
            return $jwt;
        } else {
            return null;
        }
    }

    public static function getClientCsrf()
    {
        $headers = apache_request_headers();
        $csrf_token = $headers["X-CSRF-TOKEN"] ?? "";
        return $csrf_token;
    }

    public function generateCsrfToken()
    {
        if (!isset($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        }
        echo json_encode([
            "csrf_token" => $_SESSION["csrf_token"]
        ]);
    }
}
