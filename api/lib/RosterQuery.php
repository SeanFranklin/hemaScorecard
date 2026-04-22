<?php
namespace HemaScorecard\Api\Lib;

class RosterQuery {

    /**
     * Count all eventRoster rows for an event (used for pagination meta).
     */
    public static function countForEvent(int $eventID): int {
        $eventID = (int)$eventID;
        $sql = "SELECT COUNT(*) AS c FROM eventRoster WHERE eventID = {$eventID}";
        return (int)mysqlQuery($sql, SINGLE, 'c');
    }

    /**
     * Return one page of roster rows for an event. Each row is pre-joined
     * against systemRoster (for individual fields), systemSchools (via
     * the event-override-or-home resolution), and eventTeamRoster (for
     * teamName on team rows).
     *
     * Team members are NOT in this result set — they're resolved via a
     * follow-up fetchTeamMembers() call, see RosterController::index.
     */
    public static function listForEvent(int $eventID, int $offset, int $limit): array {
        $eventID = (int)$eventID;
        $offset  = max(0, $offset);
        $limit   = max(1, $limit);

        $sql = "SELECT
                    eR.rosterID            AS rosterID,
                    eR.systemRosterID      AS systemRosterID,
                    eR.isTeam              AS isTeam,
                    eR.eventCheckIn        AS eventCheckIn,
                    eR.eventWaiver         AS eventWaiver,
                    eR.publicNotes         AS publicNotes,
                    sR.firstName           AS firstName,
                    sR.middleName          AS middleName,
                    sR.lastName            AS lastName,
                    sR.nickname            AS nickname,
                    sR.gender              AS gender,
                    sR.HemaRatingsID       AS hemaRatingsID,
                    sR.rosterCity          AS rosterCity,
                    sR.rosterProvince      AS rosterProvince,
                    sR.rosterCountry       AS rosterCountry,
                    COALESCE(eR.schoolID, sR.schoolID) AS resolvedSchoolID,
                    sS.schoolFullName      AS schoolName,
                    sS.schoolShortName     AS schoolShortName,
                    sS.schoolAbbreviation  AS schoolAbbreviation,
                    tn.memberName          AS teamName
                FROM eventRoster eR
                LEFT JOIN systemRoster sR ON sR.systemRosterID = eR.systemRosterID
                LEFT JOIN systemSchools sS ON sS.schoolID = COALESCE(eR.schoolID, sR.schoolID)
                LEFT JOIN eventTeamRoster tn
                    ON tn.teamID = eR.rosterID AND tn.memberRole = 'teamName'
                WHERE eR.eventID = {$eventID}
                ORDER BY
                    COALESCE(sR.lastName, '') ASC,
                    COALESCE(sR.firstName, '') ASC,
                    eR.rosterID ASC
                LIMIT {$limit} OFFSET {$offset}";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Given a list of team rosterIDs, return a map of:
     *   teamID => [rosterID, rosterID, ...]  (ordered by teamOrder ASC)
     * Missing teams get an empty array. One query total — no N+1.
     */
    public static function fetchTeamMembers(array $teamIDs): array {
        $result = [];
        foreach ($teamIDs as $id) {
            $result[(int)$id] = [];
        }
        if (empty($teamIDs)) {
            return $result;
        }

        $ints = array_map('intval', $teamIDs);
        $inClause = implode(',', $ints);

        $sql = "SELECT teamID, rosterID, teamOrder
                FROM eventTeamRoster
                WHERE teamID IN ({$inClause})
                AND memberRole = 'member'
                ORDER BY teamOrder ASC";
        $rows = mysqlQuery($sql, ASSOC);

        foreach ($rows as $row) {
            $tid = (int)$row['teamID'];
            if (isset($result[$tid])) {
                $result[$tid][] = (int)$row['rosterID'];
            }
        }

        return $result;
    }
}
