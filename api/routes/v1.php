<?php
use HemaScorecard\Api\Controllers\HealthController;
use HemaScorecard\Api\Controllers\EventsController;
use HemaScorecard\Api\Controllers\AnnouncementsController;
use HemaScorecard\Api\Controllers\RosterController;
use HemaScorecard\Api\Controllers\RulesController;
use HemaScorecard\Api\Controllers\PoolsController;
use HemaScorecard\Api\Controllers\PoolMatchesController;
use HemaScorecard\Api\Controllers\PlacingsController;
use HemaScorecard\Api\Controllers\BracketsController;
use HemaScorecard\Api\Controllers\TournamentsController;
use HemaScorecard\Api\Controllers\WorkshopsController;
use HemaScorecard\Api\Controllers\SchedulesController;

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
Flight::route('GET /api/v1/events/@eventID/roster',         [RosterController::class, 'index']);
Flight::route('GET /api/v1/events/@eventID/rules',          [RulesController::class, 'index']);
Flight::route('GET /api/v1/events/@eventID/rules/@rulesID', [RulesController::class, 'show']);
Flight::route('GET /api/v1/events/@eventID/tournaments',                 [TournamentsController::class, 'index']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID',   [TournamentsController::class, 'show']);
Flight::route('GET /api/v1/events/@eventID/workshops',            [WorkshopsController::class, 'index']);
Flight::route('GET /api/v1/events/@eventID/workshops/@blockID',   [WorkshopsController::class, 'show']);
Flight::route('GET /api/v1/events/@eventID/schedules/main',                  [SchedulesController::class, 'mainAll']);
Flight::route('GET /api/v1/events/@eventID/schedules/main/day/@dayNum',      [SchedulesController::class, 'mainDay']);
Flight::route('GET /api/v1/events/@eventID/schedules/workshops',             [SchedulesController::class, 'workshopsAll']);
Flight::route('GET /api/v1/events/@eventID/schedules/workshops/day/@dayNum', [SchedulesController::class, 'workshopsDay']);
Flight::route('GET /api/v1/events/@eventID/schedules/school/@schoolID',             [SchedulesController::class, 'schoolAll']);
Flight::route('GET /api/v1/events/@eventID/schedules/school/@schoolID/day/@dayNum', [SchedulesController::class, 'schoolDay']);
Flight::route('GET /api/v1/events/@eventID/schedules/personal/@rosterID',             [SchedulesController::class, 'personalAll']);
Flight::route('GET /api/v1/events/@eventID/schedules/personal/@rosterID/day/@dayNum', [SchedulesController::class, 'personalDay']);
Flight::route('GET /api/v1/events/@eventID/schedules/location/@locationID',             [SchedulesController::class, 'locationAll']);
Flight::route('GET /api/v1/events/@eventID/schedules/location/@locationID/day/@dayNum', [SchedulesController::class, 'locationDay']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID/pools',                        [PoolsController::class, 'index']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID/pools/@poolID',                [PoolsController::class, 'show']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID/pools/@poolID/roster',         [PoolsController::class, 'roster']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID/pools/@poolID/standings',      [PoolsController::class, 'standings']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID/pools/@poolID/matches',        [PoolMatchesController::class, 'index']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID/pools/@poolID/matches/@matchID', [PoolMatchesController::class, 'show']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID/placings', [PlacingsController::class, 'index']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID/brackets',                         [BracketsController::class, 'index']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID/brackets/@bracketID',              [BracketsController::class, 'show']);
Flight::route('GET /api/v1/events/@eventID/tournaments/@tournamentID/brackets/@bracketID/roster',       [BracketsController::class, 'roster']);
Flight::route('GET /api/v1/events/@id',                     [EventsController::class, 'show']);
