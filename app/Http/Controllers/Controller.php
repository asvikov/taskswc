<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function apiErrorResponse(string $message = '', $error = null, $code = 500)
    {
        $data_response = [
            'success' => false,
        ];

        if ($message) {
            $data_response['message'] = $message;
        }
        
        if ($error) {
            $data_response['error'] = $error->getMessage();
        }

        return response()->json($data_response, $code);
    }

    public function apiSuccessResponse(string $message = '', $data = null, $code = 200)
    {
        $data_response = [
            'success' => true,
        ];

        if ($message) {
            $data_response['message'] = $message;
        }

        if ($data) {
            $data_response['data'] = $data;
        }

        return response()->json($data_response, $code);
    }
}
