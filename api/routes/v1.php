<?php
use HemaScorecard\Api\Controllers\HealthController;
use HemaScorecard\Api\Controllers\EventsController;

Flight::route('GET /api/v1/health', [HealthController::class, 'index']);

// NOTE: order matters — Flight matches in registration order. When adding the
// /events/today, /events/upcoming, /events/recent, and /events/@id routes in
// later tasks, the three literal subpaths must register BEFORE the @id pattern.
Flight::route('GET /api/v1/events', [EventsController::class, 'index']);
