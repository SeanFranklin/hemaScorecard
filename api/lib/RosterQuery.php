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
     * Shape a roster row (as returned by listForEvent / TournamentsQuery::
     * rosterForTournament) into the public RosterEntry payload. Column
     * names match the SELECTs in those queries.
     */
    public static function shapeEntry(array $row, array $membersByTeam): array {
        $isTeam = (int)$row['isTeam'] === 1;
        $rosterID = (int)$row['rosterID'];

        $school = null;
        if ($row['resolvedSchoolID'] !== null) {
            $school = [
                'schoolID'     => (int)$row['resolvedSchoolID'],
                'name'         => $row['schoolName'],
                'shortName'    => $row['schoolShortName'],
                'abbreviation' => $row['schoolAbbreviation'],
            ];
        }

        if ($isTeam) {
            return [
                'rosterID'       => $rosterID,
                'systemRosterID' => $row['systemRosterID'] !== null ? (int)$row['systemRosterID'] : null,
                'isTeam'         => true,
                'firstName'      => null,
                'middleName'     => null,
                'lastName'       => null,
                'nickname'       => null,
                'gender'         => null,
                'hemaRatingsID'  => null,
                'location'       => null,
                'school'         => $school,
                'teamName'       => $row['teamName'],
                'teamMembers'    => $membersByTeam[$rosterID] ?? [],
                'checkedIn'      => (bool)(int)$row['eventCheckIn'],
                'waiverSigned'   => (bool)(int)$row['eventWaiver'],
                'publicNotes'    => $row['publicNotes'],
            ];
        }

        $location = null;
        if ($row['rosterCity'] !== null || $row['rosterProvince'] !== null || $row['rosterCountry'] !== null) {
            $location = [
                'city'     => $row['rosterCity'],
                'province' => $row['rosterProvince'],
                'country'  => $row['rosterCountry'],
            ];
        }

        return [
            'rosterID'       => $rosterID,
            'systemRosterID' => $row['systemRosterID'] !== null ? (int)$row['systemRosterID'] : null,
            'isTeam'         => false,
            'firstName'      => $row['firstName'],
            'middleName'     => $row['middleName'],
            'lastName'       => $row['lastName'],
            'nickname'       => $row['nickname'],
            'gender'         => $row['gender'],
            'hemaRatingsID'  => $row['hemaRatingsID'] !== null ? (int)$row['hemaRatingsID'] : null,
            'location'       => $location,
            'school'         => $school,
            'teamName'       => null,
            'teamMembers'    => null,
            'checkedIn'      => (bool)(int)$row['eventCheckIn'],
            'waiverSigned'   => (bool)(int)$row['eventWaiver'],
            'publicNotes'    => $row['publicNotes'],
        ];
    }

    /**
     * Given a list of team rosterIDs, return a map of:
     *   teamID => [rosterID, rosterID, ...]  (ordered by teamOrder ASC)
     * Missing teams get an empty array. One query total — no N+1.
     */
    public static function fetchTeamMembers(array $teamIDs): array {
        if (empty($teamIDs)) {
            return [];
        }
        $result = [];
        foreach ($teamIDs as $id) {
            $result[(int)$id] = [];
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
