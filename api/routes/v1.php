<?php
use HemaScorecard\Api\Controllers\HealthController;
use HemaScorecard\Api\Controllers\EventsController;

Flight::route('GET /api/v1/health', [HealthController::class, 'index']);

// NOTE: order matters — Flight matches in registration order. The three literal
// subpaths below MUST register before the /events/@id pattern, otherwise
// /events/today would route to show('today').
Flight::route('GET /api/v1/events',          [EventsController::class, 'index']);
Flight::route('GET /api/v1/events/today',    [EventsController::class, 'today']);
Flight::route('GET /api/v1/events/upcoming', [EventsController::class, 'upcoming']);
Flight::route('GET /api/v1/events/recent',   [EventsController::class, 'recent']);
Flight::route('GET /api/v1/events/@id',      [EventsController::class, 'show']);
