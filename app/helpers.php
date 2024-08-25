<?php

if (!function_exists("exception_handler")) {
    function exception_handler($e)
    {
        $exception_info = [
            "file" => $e->getFile(),
            "line" => $e->getLine(),
            "message" => $e->getMessage()
        ];
        echo json_encode($exception_info);
    }
}

if (!function_exists("dbd")) {
    function dbd($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}

if (!function_exists("error_log_helper")) {
    function error_log_helper(Exception $e)
    {
        $message = "ERROR in file {$e->getFile()} line {$e->getLine()}: {$e->getMessage()}";
        error_log($message . PHP_EOL, 3, "../error_log.txt");
    }
}
