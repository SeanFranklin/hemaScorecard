<?php
/*******************************************************************************
	API bootstrap.

	Initializes the MySQL connection and the mysqlQuery() helper without
	pulling in includes/config.php. That file starts a PHP session,
	builds the ALLOW permission constant, and dispatches POST data —
	all inappropriate for a stateless, key-authenticated JSON API.

*******************************************************************************/

require_once __DIR__ . '/../includes/deployment_constants.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/db_constants.php';
require_once __DIR__ . '/../includes/functions/mysql_lib.php';

// API requests leave no session trail.
ini_set('session.use_cookies', '0');

// Open the mysqli connection that mysqlQuery() expects.
$GLOBALS["___mysqli_ston"] = mysqli_connect(
    DATABASE_HOST,
    DATABASE_USER,
    DATABASE_PASSWORD,
    PRIMARY_DATABASE
);

if (mysqli_connect_errno()) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Api-Version: v1');
    echo json_encode([
        'error' => [
            'code' => 'internal_error',
            'message' => 'Database connection failed',
        ],
    ]);
    error_log('[API] mysqli_connect failed: ' . mysqli_connect_error());
    exit;
}
