<?php
namespace HemaScorecard\Api\Lib;

class Pagination {

    public const DEFAULT_PAGE = 1;
    public const DEFAULT_PER_PAGE = 25;
    public const MAX_PER_PAGE = 100;

    public int $page;
    public int $perPage;
    public int $offset;

    private function __construct(int $page, int $perPage) {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->offset = ($page - 1) * $perPage;
    }

    /**
     * Parse pagination params out of a query array (typically $_GET).
     * Throws ApiException(400) on invalid input.
     */
    public static function parse(array $query): self {
        $page = self::parseIntParam($query, 'page', self::DEFAULT_PAGE, 1, PHP_INT_MAX);
        $perPage = self::parseIntParam($query, 'per_page', self::DEFAULT_PER_PAGE, 1, self::MAX_PER_PAGE);
        return new self($page, $perPage);
    }

    /**
     * Build the meta block for a paginated response.
     */
    public static function meta(self $p, int $total, int $count): array {
        $totalPages = $p->perPage > 0 ? (int)ceil($total / $p->perPage) : 0;
        return [
            'count' => $count,
            'page' => $p->page,
            'per_page' => $p->perPage,
            'total' => $total,
            'total_pages' => $totalPages,
        ];
    }

    private static function parseIntParam(array $query, string $name, int $default, int $min, int $max): int {
        if (!isset($query[$name]) || $query[$name] === '') {
            return $default;
        }
        $raw = $query[$name];
        if (!is_string($raw) && !is_int($raw)) {
            throw new ApiException('bad_request', 400, "Invalid {$name}: must be an integer");
        }
        if (is_string($raw) && !preg_match('/^-?\d+$/', $raw)) {
            throw new ApiException('bad_request', 400, "Invalid {$name}: must be an integer");
        }
        $value = (int)$raw;
        if ($value < $min || $value > $max) {
            throw new ApiException('bad_request', 400, "Invalid {$name}: must be between {$min} and {$max}");
        }
        return $value;
    }
}
