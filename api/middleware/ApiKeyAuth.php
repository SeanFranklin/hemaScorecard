<?php
namespace HemaScorecard\Api\Middleware;

use HemaScorecard\Api\Lib\JsonResponse;

class ApiKeyAuth {

    private const EXEMPT_PATHS = ['/api/v1/health'];

    public static function check(): void {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
        if (in_array($path, self::EXEMPT_PATHS, true)) {
            return;
        }

        $presented = self::readKeyFromHeaders();
        if ($presented === null) {
            JsonResponse::error('unauthorized', 401, 'API key required');
        }

        if (!self::isValidKey($presented)) {
            JsonResponse::error('unauthorized', 401, 'Invalid API key');
        }
    }

    private static function readKeyFromHeaders(): ?string {
        if (!empty($_SERVER['HTTP_X_API_KEY'])) {
            return $_SERVER['HTTP_X_API_KEY'];
        }
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])
            && preg_match('/^Bearer\s+(.+)$/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private static function isValidKey(string $presented): bool {
        $file = __DIR__ . '/../../data/api_keys.json';
        if (!file_exists($file)) {
            return false;
        }
        $raw = @file_get_contents($file);
        if ($raw === false) {
            return false;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['keys']) || !is_array($data['keys'])) {
            return false;
        }
        foreach ($data['keys'] as $entry) {
            if (empty($entry['key']) || !empty($entry['revoked'])) {
                continue;
            }
            if (hash_equals((string)$entry['key'], $presented)) {
                return true;
            }
        }
        return false;
    }
}
