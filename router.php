<?php
/*******************************************************************************
	Router script for PHP's built-in dev server.

	Routes /api/* paths to the Flight front controller at api/index.php.
	For any other path, returns false so the built-in server serves the
	file (or falls through to a 404) exactly as before.

*******************************************************************************/

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';

if (strpos($path, '/api/') === 0) {
    require __DIR__ . '/api/index.php';
    return true;
}

return false;
