<?php
/*******************************************************************************
	API front controller.

	Dispatches /api/v1/* requests via Flight. Emits a consistent JSON
	error envelope for all error states, including 404 and 405.

	Output buffering is active for the entire request so any stray
	output from legacy code paths (e.g. mysql_lib.php's checkMySQL()
	die('Error: ...') on query failure) can be discarded before we
	emit the proper JSON error response.

*******************************************************************************/

// Start output buffering FIRST — before any include that could emit output.
ob_start();

// When PHP's built-in server routes via router.php, SCRIPT_NAME is set to
// /api/index.php (relative to the docroot). Flight uses dirname(SCRIPT_NAME)
// as the base path and strips it from REQUEST_URI before route matching, which
// would strip "/api" and break every /api/v1/* route. Override SCRIPT_NAME to
// a root-relative path so dirname resolves to "/" and no stripping occurs.
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Middleware\ApiKeyAuth;

// Reject non-GET methods before dispatch. This fires before the auth
// middleware (added in Task 9) so bogus writes get a clean 405, not a 401.
Flight::before('start', function() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        JsonResponse::error('method_not_allowed', 405, 'Only GET is supported');
    }
});

// Auth middleware — runs after the 405 check so bogus writes never
// expose whether a key is valid.
Flight::before('start', function() {
    ApiKeyAuth::check();
});

// Global error handler: expected ApiException → structured JSON;
// anything else → logged 500 without leaking exception details.
// Discard any buffered output (e.g. die()s from legacy DB helpers) first.
Flight::map('error', function($e) {
    if (ob_get_length() !== false) {
        ob_end_clean();
    }
    if ($e instanceof ApiException) {
        JsonResponse::error($e->getErrorCode(), $e->getHttpStatus(), $e->getMessage());
    }
    error_log('[API] Uncaught exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    JsonResponse::error('internal_error', 500, 'An unexpected error occurred');
});

// Override Flight's default HTML 404 page.
Flight::map('notFound', function() {
    if (ob_get_length() !== false) {
        ob_end_clean();
    }
    JsonResponse::error('not_found', 404, 'Not found');
});

require __DIR__ . '/routes/v1.php';

Flight::start();
