<?php
namespace HemaScorecard\Api\Lib;

class PlacingsQuery {

    /**
     * Final placings for a tournament, joined with roster + school.
     * Sorted by placing ASC then rosterID ASC. Empty array when no
     * eventPlacings rows exist for the tournament.
     */
    public static function forTournament(int $tournamentID): array {
        $tournamentID = (int)$tournamentID;
        $sql = "SELECT
                    eP.rosterID       AS rosterID,
                    sR.firstName      AS firstName,
                    sR.lastName       AS lastName,
                    eR.schoolID       AS schoolID,
                    sS.schoolFullName AS schoolName,
                    eP.placing        AS placing,
                    eP.placeType      AS placeType,
                    eP.highBound      AS highBound,
                    eP.lowBound       AS lowBound
                FROM eventPlacings eP
                INNER JOIN eventRoster eR ON eR.rosterID = eP.rosterID
                INNER JOIN systemRoster sR ON sR.systemRosterID = eR.systemRosterID
                LEFT JOIN systemSchools sS ON sS.schoolID = eR.schoolID
                WHERE eP.tournamentID = {$tournamentID}
                ORDER BY eP.placing ASC, eP.rosterID ASC";
        return mysqlQuery($sql, ASSOC);
    }
}
