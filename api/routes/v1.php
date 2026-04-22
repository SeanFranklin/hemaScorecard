<?php
use HemaScorecard\Api\Controllers\HealthController;

Flight::route('GET /api/v1/health', [HealthController::class, 'index']);
