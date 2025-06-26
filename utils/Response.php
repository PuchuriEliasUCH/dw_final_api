<?php
class Response {
    public static function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function error($message, $status = 400) {
        self::json([
            'success' => false,
            'message' => $message
        ], $status);
    }

    public static function success($data, $message = null) {
        $response = [
            'success' => true,
            'data' => $data
        ];
        
        if($message) {
            $response['message'] = $message;
        }
        
        self::json($response);
    }
}
?>