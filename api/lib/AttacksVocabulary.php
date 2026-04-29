<?php
namespace HemaScorecard\Api\Lib;

class AttacksVocabulary {

    /** @var array<int, array{code: string, text: string}>|null */
    private static ?array $cache = null;

    /**
     * Return {code, text} for a given systemAttacks.attackID, or null if
     * the ID doesn't exist or is null. Loads the full table once per
     * request on first call.
     */
    public static function lookup(?int $attackID): ?array {
        if ($attackID === null || $attackID <= 0) {
            return null;
        }
        if (self::$cache === null) {
            self::load();
        }
        return self::$cache[$attackID] ?? null;
    }

    private static function load(): void {
        $rows = mysqlQuery("SELECT attackID, attackCode, attackText FROM systemAttacks", ASSOC);
        $cache = [];
        foreach ($rows as $row) {
            $cache[(int)$row['attackID']] = [
                'code' => $row['attackCode'],
                'text' => $row['attackText'],
            ];
        }
        self::$cache = $cache;
    }

    /** For tests — clears the per-request cache. */
    public static function resetCache(): void {
        self::$cache = null;
    }
}
