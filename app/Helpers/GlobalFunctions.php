<?php



if (!function_exists('debugHalt')) {
    function debugHalt($message)
    {

        $response = response()->json([
            'error' => 'Error',
            'message' => $message
        ], 409);

        $response->send();

        exit;
    }
}
