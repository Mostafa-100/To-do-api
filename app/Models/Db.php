<?php

namespace App\Models;

use PDO;
use PDOException;

class Db
{
    private $instance = null;

    public function __construct()
    {
        if (!$this->instance) {
            $config = [
                "host" => $_ENV["HOST_NAME"],
                "dbname" => $_ENV["DATABASE_NAME"],
                "username" => $_ENV["USERNAME"],
                "password" => $_ENV["PASSWORD"]
            ];

            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};port=3306;charset=utf8";

            $username = $config["username"];
            $password = $config["password"];

            try {
                $this->instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
            } catch (PDOException $e) {
                throw new PDOException("Failed to connecting to database", 500);
            }
        }
    }

    public function getInstance()
    {
        return $this->instance;
    }
}
