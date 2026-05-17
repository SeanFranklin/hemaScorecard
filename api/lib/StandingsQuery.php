<?php
namespace HemaScorecard\Api\Lib;

class StandingsQuery {

    /**
     * Return one row per fighter in the pool with full stats joined from
     * eventStandings + eventGroupRankings (pool-level rank appears on the
     * eventGroupRankings row, not per-fighter — we only keep overlapSize
     * from it here; per-fighter rank comes from eventStandings.rank).
     * Empty array when the pool has no standings rows yet.
     *
     * AbsPointsFor / AbsPointsAgainst / AbsPointsAwarded columns are aliased
     * to camelCase so the shape code can read them with consistent keys.
     * The reserved-word columns `rank` and `groupRank` are backtick-quoted.
     */
    public static function forPool(int $poolID): array {
        $poolID = (int)$poolID;
        $sql = "SELECT
                    eS.rosterID                   AS rosterID,
                    sR.firstName                  AS firstName,
                    sR.lastName                   AS lastName,
                    eR.schoolID                   AS schoolID,
                    sS.schoolFullName             AS schoolName,
                    eS.`rank`                     AS `rank`,
                    eGR.overlapSize               AS overlapSize,
                    eS.score                      AS score,
                    eS.matches                    AS matches,
                    eS.wins                       AS wins,
                    eS.losses                     AS losses,
                    eS.ties                       AS ties,
                    eS.pointsFor                  AS pointsFor,
                    eS.pointsAgainst              AS pointsAgainst,
                    eS.hitsFor                    AS hitsFor,
                    eS.hitsAgainst                AS hitsAgainst,
                    eS.afterblowsFor              AS afterblowsFor,
                    eS.afterblowsAgainst          AS afterblowsAgainst,
                    eS.doubles                    AS doubles,
                    eS.noExchanges                AS noExchanges,
                    eS.AbsPointsFor               AS absPointsFor,
                    eS.AbsPointsAgainst           AS absPointsAgainst,
                    eS.AbsPointsAwarded           AS absPointsAwarded,
                    eS.numPenalties               AS numPenalties,
                    eS.numYellowCards             AS numYellowCards,
                    eS.numRedCards                AS numRedCards,
                    eS.penaltiesAgainstOpponents  AS penaltiesAgainstOpponents,
                    eS.penaltiesAgainst           AS penaltiesAgainst,
                    eS.doubleOuts                 AS doubleOuts,
                    eS.numCleanHits               AS numCleanHits,
                    eS.basePointValue             AS basePointValue,
                    eS.ignoreForBracket           AS ignoreForBracket
                FROM eventStandings eS
                INNER JOIN eventRoster eR ON eR.rosterID = eS.rosterID
                INNER JOIN systemRoster sR ON sR.systemRosterID = eR.systemRosterID
                LEFT JOIN systemSchools sS ON sS.schoolID = eR.schoolID
                LEFT JOIN eventGroupRankings eGR ON eGR.groupID = eS.groupID
                WHERE eS.groupID = {$poolID}
                ORDER BY eS.`rank` ASC, eS.rosterID ASC";
        return mysqlQuery($sql, ASSOC);
    }
}
