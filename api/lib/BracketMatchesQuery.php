<?php
namespace HemaScorecard\Api\Lib;

class BracketMatchesQuery {

    /**
     * List non-placeholder matches in a bracket, sorted by bracketLevel
     * DESC (first rounds first) then bracketPosition ASC. Joins fighter
     * names + match location. Ignored matches are kept (controller flags
     * them with isIgnored=true).
     */
    public static function listForBracket(int $bracketID): array {
        $bracketID = (int)$bracketID;
        $sql = "SELECT
                    eM.matchID         AS matchID,
                    eM.matchNumber     AS matchNumber,
                    eM.groupID         AS groupID,
                    eM.bracketLevel    AS bracketLevel,
                    eM.bracketPosition AS bracketPosition,
                    eM.fighter1ID      AS fighter1ID,
                    sR1.firstName      AS fighter1FirstName,
                    sR1.lastName       AS fighter1LastName,
                    eM.fighter2ID      AS fighter2ID,
                    sR2.firstName      AS fighter2FirstName,
                    sR2.lastName       AS fighter2LastName,
                    eM.fighter1Score   AS fighter1Score,
                    eM.fighter2Score   AS fighter2Score,
                    eM.winnerID        AS winnerID,
                    eM.matchComplete   AS isComplete,
                    eM.ignoreMatch     AS isIgnored,
                    lLM.locationID     AS locationID,
                    lL.locationName    AS locationName
                FROM eventMatches eM
                LEFT JOIN eventRoster eR1 ON eR1.rosterID = eM.fighter1ID
                LEFT JOIN systemRoster sR1 ON sR1.systemRosterID = eR1.systemRosterID
                LEFT JOIN eventRoster eR2 ON eR2.rosterID = eM.fighter2ID
                LEFT JOIN systemRoster sR2 ON sR2.systemRosterID = eR2.systemRosterID
                LEFT JOIN logisticsLocationsMatches lLM ON lLM.matchID = eM.matchID
                LEFT JOIN logisticsLocations lL ON lL.locationID = lLM.locationID
                WHERE eM.groupID = {$bracketID}
                AND eM.isPlaceholder = 0
                ORDER BY eM.bracketLevel DESC, eM.bracketPosition ASC, eM.matchID ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Find a single match scoped event → tournament → bracket → match,
     * rejecting placeholders and non-elim groups. Returns listForBracket
     * columns PLUS matchTime, signOff1, signOff2.
     */
    public static function findMatchInScope(int $eventID, int $tournamentID, int $bracketID, int $matchID): ?array {
        if ($eventID <= 0 || $tournamentID <= 0 || $bracketID <= 0 || $matchID <= 0) {
            return null;
        }
        $sql = "SELECT
                    eM.matchID         AS matchID,
                    eM.matchNumber     AS matchNumber,
                    eM.groupID         AS groupID,
                    eM.bracketLevel    AS bracketLevel,
                    eM.bracketPosition AS bracketPosition,
                    eM.fighter1ID      AS fighter1ID,
                    sR1.firstName      AS fighter1FirstName,
                    sR1.lastName       AS fighter1LastName,
                    eM.fighter2ID      AS fighter2ID,
                    sR2.firstName      AS fighter2FirstName,
                    sR2.lastName       AS fighter2LastName,
                    eM.fighter1Score   AS fighter1Score,
                    eM.fighter2Score   AS fighter2Score,
                    eM.winnerID        AS winnerID,
                    eM.matchComplete   AS isComplete,
                    eM.ignoreMatch     AS isIgnored,
                    eM.matchTime       AS matchTime,
                    eM.signOff1        AS signOff1,
                    eM.signOff2        AS signOff2,
                    lLM.locationID     AS locationID,
                    lL.locationName    AS locationName
                FROM eventMatches eM
                INNER JOIN eventGroups eG ON eG.groupID = eM.groupID
                INNER JOIN eventTournaments eT ON eT.tournamentID = eG.tournamentID
                LEFT JOIN eventRoster eR1 ON eR1.rosterID = eM.fighter1ID
                LEFT JOIN systemRoster sR1 ON sR1.systemRosterID = eR1.systemRosterID
                LEFT JOIN eventRoster eR2 ON eR2.rosterID = eM.fighter2ID
                LEFT JOIN systemRoster sR2 ON sR2.systemRosterID = eR2.systemRosterID
                LEFT JOIN logisticsLocationsMatches lLM ON lLM.matchID = eM.matchID
                LEFT JOIN logisticsLocations lL ON lL.locationID = lLM.locationID
                WHERE eM.matchID = {$matchID}
                AND eM.groupID = {$bracketID}
                AND eG.tournamentID = {$tournamentID}
                AND eT.eventID = {$eventID}
                AND eG.groupType = 'elim'
                AND eM.isPlaceholder = 0
                LIMIT 1";
        $row = mysqlQuery($sql, SINGLE);
        return $row ?: null;
    }

    /** Per-match option overrides. */
    public static function optionsForMatch(int $matchID): array {
        $matchID = (int)$matchID;
        $sql = "SELECT optionID, optionValue
                FROM eventMatchOptions
                WHERE matchID = {$matchID}
                ORDER BY optionID ASC";
        return mysqlQuery($sql, ASSOC);
    }
}
