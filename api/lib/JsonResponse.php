<?php
namespace HemaScorecard\Api\Lib;

class JsonResponse {

    public static function success(array $data, ?array $meta = null): void {
        $body = ['data' => $data];
        if ($meta !== null) {
            $body['meta'] = $meta;
        }
        self::send(200, $body);
    }

    public static function error(string $code, int $httpStatus, string $message): void {
        self::send($httpStatus, [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ]);
    }

    private static function send(int $status, array $body): void {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Api-Version: v1');
        echo json_encode($body);
        exit;
    }
}
