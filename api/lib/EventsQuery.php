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
                INNER JOIN systemCountries USING(countryIso2)
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
     * Find a single visible event by ID. Returns null if the event doesn't
     * exist OR isn't visible (hidden/unpublished drafts). Deliberately does
     * not distinguish the two — prevents ID enumeration of hidden events.
     *
     * Returned array has all list-item fields PLUS:
     *   regionCode, description, publication (5 booleans), plus the raw
     *   publish flags for the controller to compose the publication block.
     */
    public static function findById(int $id): ?array {
        if ($id <= 0) {
            return null;
        }

        $sql = "SELECT
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
                    regionCode           AS regionCode,
                    isArchived           AS isArchived,
                    COALESCE(publishDescription, 0) AS publishDescription,
                    COALESCE(publishRoster,      0) AS publishRoster,
                    COALESCE(publishSchedule,    0) AS publishSchedule,
                    COALESCE(publishMatches,     0) AS publishMatches,
                    COALESCE(publishRules,       0) AS publishRules,
                    eventDescriptions.description   AS descriptionRaw,
                    " . self::STATUS_EXPR . "       AS status
                FROM systemEvents
                INNER JOIN systemCountries USING(countryIso2)
                LEFT JOIN eventPublication   USING(eventID)
                LEFT JOIN eventDescriptions  ON eventDescriptions.eventID = systemEvents.eventID
                WHERE systemEvents.eventID = {$id}
                AND " . self::VISIBLE_WHERE . "
                LIMIT 1";

        $row = mysqlQuery($sql, SINGLE);
        return $row ?: null;
    }

    public static function countTournaments(int $eventID): int {
        $eventID = (int)$eventID;
        $sql = "SELECT COUNT(*) AS c FROM eventTournaments WHERE eventID = {$eventID}";
        return (int)mysqlQuery($sql, SINGLE, 'c');
    }

    public static function countRoster(int $eventID): int {
        $eventID = (int)$eventID;
        $sql = "SELECT COUNT(*) AS c FROM eventRoster WHERE eventID = {$eventID}";
        return (int)mysqlQuery($sql, SINGLE, 'c');
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
