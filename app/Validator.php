<?php

namespace App;

class Validator
{
    public static function string($value, $key, $min = 1, $max = INF)
    {
        $value = trim($value);

        if (empty($value)) {
            return "$key is required";
        }

        if (!(strlen($value) >= $min)) {
            return "$key should be at least $min characters";
        }

        if (!(strlen($value) <= $max)) {
            return "$key should be at maximum $max characters";
        }

        return true;
    }

    public static function email($value, $checkToken = false)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public static function csrf($value)
    {
        if (empty($value)) {
            return "CSRF not provided.";
        }

        if ($value !== $_SESSION["csrf_token"]) {
            return "CSRF not match.";
        }
        return true;
    }

    public static function password($password, $re_password, $min = 5, $max = 20)
    {
        $password = trim($password);
        $re_password = trim($re_password);
        if (empty($password)) {
            return "password is required.";
        }

        if (empty($re_password)) {
            return "re enter password.";
        }

        if (!(strlen($password) >= $min && strlen($password) <= $max)) {
            return "Password must to be between $min and $max";
        }

        if (!preg_match("/[0-9]/", $password)) {
            return "Password must contain at least one number";
        }

        if (!preg_match("/[a-z]/", $password)) {
            return "Password must contain at least one uppercase letter";
        }

        if (!preg_match("/[$@_\?%]/", $password)) {
            return "Password must contain at least one special letter ($@_-?%)";
        }

        if ($password !== $re_password) {
            return "Passwords doesnt matches.";
        }

        return true;
    }

    public static function int($value, $key)
    {
        if (!filter_var($value, FILTER_VALIDATE_INT) && $value != 0) {
            return "Invalid $key value";
        }

        return true;
    }
}
