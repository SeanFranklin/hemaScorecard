<?php
namespace HemaScorecard\Api\Lib;

class EventsQuery {

    /**
     * Shared "publicly visible" WHERE fragment. An event is public if it's
     * archived OR has any publication flag set. Matches the existing web
     * app's getEventListByPublication() behavior.
     */
    private const VISIBLE_WHERE = "(
        isArchived = 1
        OR publishDescription = 1
        OR publishRoster = 1
        OR publishSchedule = 1
        OR publishMatches = 1
        OR publishRules = 1
    )";

    /**
     * SQL expression that derives a string status for a list-item row.
     * Values: complete | active | upcoming | hidden. With VISIBLE_WHERE
     * applied, 'hidden' never appears in results (dead branch kept for
     * parity with the web app's expression).
     */
    private const STATUS_EXPR = "
        IF(isArchived = 1, 'complete',
            IF(publishMatches = 1, 'active',
                IF(publishDescription = 1
                    OR publishRoster = 1
                    OR publishSchedule = 1
                    OR publishRules = 1, 'upcoming', 'hidden')))
    ";

    /**
     * Return one page of visible events.
     */
    public static function listPublished(int $offset, int $limit): array {
        $offset = max(0, $offset);
        $limit  = max(1, $limit);

        $sql = self::baseSelect() . "
                WHERE " . self::VISIBLE_WHERE . "
                ORDER BY eventStartDate DESC, eventID DESC
                LIMIT {$limit} OFFSET {$offset}";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Count all visible events.
     */
    public static function countPublished(): int {
        $sql = "SELECT COUNT(*) AS c
                FROM systemEvents
                LEFT JOIN eventPublication USING(eventID)
                WHERE " . self::VISIBLE_WHERE;
        return (int)mysqlQuery($sql, SINGLE, 'c');
    }

    /**
     * Events whose date range includes CURDATE() (server time is UTC).
     */
    public static function today(): array {
        $sql = self::baseSelect() . "
                WHERE " . self::VISIBLE_WHERE . "
                AND eventStartDate <= CURDATE()
                AND eventEndDate   >= CURDATE()
                ORDER BY eventStartDate ASC, eventID ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Events starting in the next 7 days (exclusive of today).
     */
    public static function upcoming(): array {
        $sql = self::baseSelect() . "
                WHERE " . self::VISIBLE_WHERE . "
                AND eventStartDate >  CURDATE()
                AND eventStartDate <= CURDATE() + INTERVAL 7 DAY
                ORDER BY eventStartDate ASC, eventID ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Events whose end date falls in the past 7 days (exclusive of today).
     */
    public static function recent(): array {
        $sql = self::baseSelect() . "
                WHERE " . self::VISIBLE_WHERE . "
                AND eventEndDate <  CURDATE()
                AND eventEndDate >= CURDATE() - INTERVAL 7 DAY
                ORDER BY eventEndDate DESC, eventID DESC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * SELECT + joins + columns common to all list queries. Does not end
     * with a WHERE — callers compose their own visibility + date filters.
     */
    private static function baseSelect(): string {
        return "
            SELECT
                systemEvents.eventID AS eventID,
                eventName            AS name,
                eventAbbreviation    AS abbreviation,
                eventYear            AS year,
                eventStartDate       AS startDate,
                eventEndDate         AS endDate,
                eventCity            AS city,
                eventProvince        AS province,
                systemEvents.countryIso2 AS countryIso2,
                countryName          AS countryName,
                isMetaEvent          AS isMetaEvent,
                " . self::STATUS_EXPR . " AS status
            FROM systemEvents
            INNER JOIN systemCountries USING(countryIso2)
            LEFT JOIN eventPublication USING(eventID)
        ";
    }
}
