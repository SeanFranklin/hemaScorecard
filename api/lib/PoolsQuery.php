<?php
namespace HemaScorecard\Api\Lib;

class PoolsQuery {

    /**
     * Flat list of all pools for a tournament, across every groupSet.
     * Returns [{poolID, poolName, poolNumber, groupSet, numFighters,
     * locationID, isComplete}, ...]. Sort: groupSet ASC, poolNumber ASC.
     */
    public static function listForTournament(int $tournamentID): array {
        $tournamentID = (int)$tournamentID;
        $sql = "SELECT
                    groupID       AS poolID,
                    groupName     AS poolName,
                    groupNumber   AS poolNumber,
                    groupSet      AS groupSet,
                    numFighters   AS numFighters,
                    locationID    AS locationID,
                    groupComplete AS isComplete
                FROM eventGroups
                WHERE tournamentID = {$tournamentID}
                AND groupType = 'pool'
                ORDER BY groupSet ASC, groupNumber ASC, groupID ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Fetch a single pool's row + location name + rank info, scoped to a
     * specific tournament (and by extension an event via the caller's gate).
     * Returns null for missing or cross-tournament pool.
     */
    public static function findPoolInScope(int $eventID, int $tournamentID, int $poolID): ?array {
        if ($eventID <= 0 || $tournamentID <= 0 || $poolID <= 0) {
            return null;
        }
        $sql = "SELECT
                    eG.groupID       AS poolID,
                    eG.groupName     AS poolName,
                    eG.groupNumber   AS poolNumber,
                    eG.groupSet      AS groupSet,
                    eG.numFighters   AS numFighters,
                    eG.locationID    AS locationID,
                    lL.locationName  AS locationName,
                    eG.groupComplete AS isComplete,
                    eGR.groupRank    AS `rank`,
                    eGR.overlapSize  AS overlapSize
                FROM eventGroups eG
                INNER JOIN eventTournaments eT ON eT.tournamentID = eG.tournamentID
                LEFT JOIN logisticsLocations lL ON lL.locationID = eG.locationID
                LEFT JOIN eventGroupRankings eGR ON eGR.groupID = eG.groupID
                WHERE eG.groupID = {$poolID}
                AND eG.tournamentID = {$tournamentID}
                AND eT.eventID = {$eventID}
                AND eG.groupType = 'pool'
                LIMIT 1";
        $row = mysqlQuery($sql, SINGLE);
        return $row ?: null;
    }

    /**
     * Count of matches in a pool (excluding placeholders) plus subset that
     * are marked complete AND not ignored. Used by the pool-detail endpoint.
     */
    public static function progressCounts(int $poolID): array {
        $poolID = (int)$poolID;
        $sql = "SELECT
                    SUM(CASE WHEN isPlaceholder = 0 THEN 1 ELSE 0 END) AS total,
                    SUM(CASE WHEN isPlaceholder = 0 AND matchComplete = 1 AND ignoreMatch = 0 THEN 1 ELSE 0 END) AS complete
                FROM eventMatches
                WHERE groupID = {$poolID}";
        $row = mysqlQuery($sql, SINGLE);
        return [
            'total'    => (int)($row['total']    ?? 0),
            'complete' => (int)($row['complete'] ?? 0),
        ];
    }

    /**
     * Fighters in a pool, sorted by poolPosition. Includes name + school.
     */
    public static function rosterForPool(int $poolID): array {
        $poolID = (int)$poolID;
        $sql = "SELECT
                    eR.rosterID          AS rosterID,
                    sR.firstName         AS firstName,
                    sR.lastName          AS lastName,
                    eR.schoolID          AS schoolID,
                    sS.schoolFullName    AS schoolName,
                    eGR.poolPosition     AS poolPosition,
                    eGR.participantStatus AS participantStatus,
                    eGR.tournamentTableID AS tournamentTableID
                FROM eventGroupRoster eGR
                INNER JOIN eventRoster eR ON eR.rosterID = eGR.rosterID
                INNER JOIN systemRoster sR ON sR.systemRosterID = eR.systemRosterID
                LEFT JOIN systemSchools sS ON sS.schoolID = eR.schoolID
                WHERE eGR.groupID = {$poolID}
                ORDER BY eGR.poolPosition ASC, eR.rosterID ASC";
        return mysqlQuery($sql, ASSOC);
    }
}
