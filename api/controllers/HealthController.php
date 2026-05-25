<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\JsonResponse;

class HealthController {

    public function index(): void {
        JsonResponse::success([
            'status' => 'ok',
            'version' => 'v1',
        ]);
    }
}
