<?php

ini_set("session.use_strict_mode", 1);

session_start();

// ini_set("session.cookie_secure", 1);

header("Content-Type: application/json; charset=UTF-8");

require("../vendor/autoload.php");
require("../app/helpers.php");
require("../routes/router.php");

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
