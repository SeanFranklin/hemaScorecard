<?php
use HemaScorecard\Api\Controllers\HealthController;
use HemaScorecard\Api\Controllers\EventsController;
use HemaScorecard\Api\Controllers\AnnouncementsController;
use HemaScorecard\Api\Controllers\RulesController;

Flight::route('GET /api/v1/health', [HealthController::class, 'index']);

// NOTE: order matters — Flight matches in registration order. Literal subpaths
// and all /events/@eventID/... nested routes MUST register before the
// /events/@id pattern, otherwise /events/today (or /events/9001/rules) would
// route to show('today') or show('9001').
Flight::route('GET /api/v1/events',                         [EventsController::class, 'index']);
Flight::route('GET /api/v1/events/today',                   [EventsController::class, 'today']);
Flight::route('GET /api/v1/events/upcoming',                [EventsController::class, 'upcoming']);
Flight::route('GET /api/v1/events/recent',                  [EventsController::class, 'recent']);
Flight::route('GET /api/v1/events/@eventID/announcements',  [AnnouncementsController::class, 'index']);
Flight::route('GET /api/v1/events/@eventID/rules',          [RulesController::class, 'index']);
Flight::route('GET /api/v1/events/@eventID/rules/@rulesID', [RulesController::class, 'show']);
Flight::route('GET /api/v1/events/@id',                     [EventsController::class, 'show']);
