<?php

if (!function_exists('payeezy_return_success')){
    function payeezy_return_success(string $message, array $data = []) : array{
        return [
            'status'  => 'success',
            'code'    => 200,
            'message' => $message,
            'data'    => $data
        ];
    }
}

if (!function_exists('payeezy_return_error')){
    function payeezy_return_error(string $message, array $data = []) : array{
        return [
            'status'  => 'error',
            'code'    => 500,
            'message' => $message,
            'data'    => $data
        ];
    }
}