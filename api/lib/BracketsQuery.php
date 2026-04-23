<?php
namespace HemaScorecard\Api\Lib;

class BracketsQuery {

    public const BRACKET_TYPE_PRIMARY   = 'primary';
    public const BRACKET_TYPE_SECONDARY = 'secondary';

    public const ELIM_TYPE_SINGLE        = 'single';
    public const ELIM_TYPE_CONSOLATION   = 'consolation';
    public const ELIM_TYPE_LOWER_BRACKET = 'lower_bracket';
    public const ELIM_TYPE_TRUE_DOUBLE   = 'true_double';

    /**
     * Flat list of all brackets (eventGroups where groupType='elim') for a
     * tournament, sorted primary-first. groupNumber=1 is primary, =2 is
     * secondary. Columns aliased to the API's shape.
     */
    public static function listForTournament(int $tournamentID): array {
        $tournamentID = (int)$tournamentID;
        $sql = "SELECT
                    groupID AS bracketID,
                    groupNumber,
                    bracketLevels,
                    numFighters,
                    locationID,
                    groupComplete AS isComplete
                FROM eventGroups
                WHERE tournamentID = {$tournamentID}
                AND groupType = 'elim'
                ORDER BY groupNumber ASC, groupID ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Find a bracket scoped event → tournament → bracket. Includes
     * locationName. Returns null on miss or cross-scope.
     */
    public static function findBracketInScope(int $eventID, int $tournamentID, int $bracketID): ?array {
        if ($eventID <= 0 || $tournamentID <= 0 || $bracketID <= 0) {
            return null;
        }
        $sql = "SELECT
                    eG.groupID AS bracketID,
                    eG.groupNumber,
                    eG.bracketLevels,
                    eG.numFighters,
                    eG.locationID,
                    lL.locationName,
                    eG.groupComplete AS isComplete
                FROM eventGroups eG
                INNER JOIN eventTournaments eT ON eT.tournamentID = eG.tournamentID
                LEFT JOIN logisticsLocations lL ON lL.locationID = eG.locationID
                WHERE eG.groupID = {$bracketID}
                AND eG.tournamentID = {$tournamentID}
                AND eT.eventID = {$eventID}
                AND eG.groupType = 'elim'
                LIMIT 1";
        $row = mysqlQuery($sql, SINGLE);
        return $row ?: null;
    }

    /**
     * Fighters in a bracket, sorted by poolPosition (seed). Joins in name
     * and school via the same chain as PoolsQuery::rosterForPool.
     */
    public static function rosterForBracket(int $bracketID): array {
        $bracketID = (int)$bracketID;
        $sql = "SELECT
                    eR.rosterID           AS rosterID,
                    sR.firstName          AS firstName,
                    sR.lastName           AS lastName,
                    eR.schoolID           AS schoolID,
                    sS.schoolFullName     AS schoolName,
                    eGR.poolPosition      AS poolPosition,
                    eGR.participantStatus AS participantStatus,
                    eGR.tournamentTableID AS tournamentTableID
                FROM eventGroupRoster eGR
                INNER JOIN eventRoster eR ON eR.rosterID = eGR.rosterID
                INNER JOIN systemRoster sR ON sR.systemRosterID = eR.systemRosterID
                LEFT JOIN systemSchools sS ON sS.schoolID = eR.schoolID
                WHERE eGR.groupID = {$bracketID}
                ORDER BY eGR.poolPosition ASC, eR.rosterID ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Derive elimType for a tournament.
     *  - 3 matches at bracketLevel=1 in the primary bracket → true_double
     *  - 2 matches there → lower_bracket
     *  - else, if a secondary bracket exists with bracketLevels > 1 → consolation
     *  - else → single
     */
    public static function elimTypeFor(int $tournamentID): string {
        $tournamentID = (int)$tournamentID;

        $sql = "SELECT COUNT(*) AS c
                FROM eventMatches eM
                INNER JOIN eventGroups eG ON eG.groupID = eM.groupID
                WHERE eG.tournamentID = {$tournamentID}
                AND eG.groupType = 'elim'
                AND eG.groupNumber = 1
                AND eM.bracketLevel = 1
                AND eM.isPlaceholder = 0";
        $numFinals = (int)mysqlQuery($sql, SINGLE, 'c');

        if ($numFinals === 3) { return self::ELIM_TYPE_TRUE_DOUBLE; }
        if ($numFinals === 2) { return self::ELIM_TYPE_LOWER_BRACKET; }

        $sql = "SELECT bracketLevels
                FROM eventGroups
                WHERE tournamentID = {$tournamentID}
                AND groupType = 'elim'
                AND groupNumber = 2
                LIMIT 1";
        $secondaryLevels = mysqlQuery($sql, SINGLE, 'bracketLevels');

        if ($secondaryLevels !== null && (int)$secondaryLevels > 1) {
            return self::ELIM_TYPE_CONSOLATION;
        }

        return self::ELIM_TYPE_SINGLE;
    }

    /**
     * Map eventGroups.groupNumber → API bracketType string.
     */
    public static function bracketTypeFromGroupNumber(int $groupNumber): string {
        if ($groupNumber === 1) { return self::BRACKET_TYPE_PRIMARY; }
        if ($groupNumber === 2) { return self::BRACKET_TYPE_SECONDARY; }
        return (string)$groupNumber;
    }
}
