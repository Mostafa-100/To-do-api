<?php

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\RouteCollector;
use App\Controllers\Authentication;
use App\Controllers\TaskController;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


$route = new RouteCollector();

$route->get("/api/generate-csrf-token", [Authentication::class, "generateCsrfToken"]);

$route->post("/api/register", [Authentication::class, "register"]);
$route->post("/api/login", [Authentication::class, "login"]);
$route->get("/api/logout", [Authentication::class, "logout"]);

$route->get("/api/tasks", [TaskController::class, "index"]);
$route->post("/api/tasks", [TaskController::class, "store"]);
$route->put("/api/tasks/{id:i}", [TaskController::class, "update"]);
$route->delete("/api/tasks/{id:i}", [TaskController::class, "destroy"]);
$route->get("/api/status", [TaskController::class, "getStatusList"]);


# test
$route->get("/api/test", function () {
    echo session_id();
    echo "<br>";
    echo $_SESSION["csrf_token"];
});


/* Dispatcher */
$dispatcher = new Phroute\Phroute\Dispatcher($route->getData());

try {
    $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    echo $response;
} catch (HttpRouteNotFoundException | HttpMethodNotAllowedException $e) {
    http_response_code(404);
    echo "Page Not found";
}
