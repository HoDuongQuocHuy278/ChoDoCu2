<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Trả về JSON response không escape dấu /
     */
    protected function jsonResponse($data = null, $status = 200, $headers = [])
    {
        $response = response()->json($data, $status, $headers);
        $response->setEncodingOptions(
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        return $response;
    }
}
