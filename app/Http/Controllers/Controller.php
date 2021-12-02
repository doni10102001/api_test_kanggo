<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    // ------------------------------------- Return Response ------------------------------ //
    public function response_message($message, $status=200) {
        return response()->json([
            "status"    => $status,
            "message"   => $message
        ], $status);
    }
    
    public function response_data($message, $data, $status=200) {
        return response()->json([
            "status"    => $status,
            "message"   => $message,
            "data"      => $data
        ], $status);
    }
}
