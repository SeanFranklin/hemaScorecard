<?php
namespace HemaScorecard\Api\Lib;

use HemaScorecard\Api\Lib\LogisticsRoles;
use HemaScorecard\Api\Lib\ScheduleBlockTypes;

class SchedulesQuery {

    /**
     * All blocks for an event, optionally filtered to a single day.
     */
    public static function main(int $eventID, ?int $dayNum = null): array {
        $eventID = (int)$eventID;
        $where = "eventID = {$eventID}";
        if ($dayNum !== null) {
            $where .= " AND dayNum = " . (int)$dayNum;
        }
        return ScheduleBlocks::select($where, 'dayNum ASC, startTime ASC, blockID ASC');
    }

    /**
     * Workshop blocks for an event, optionally filtered to a single day.
     */
    public static function workshops(int $eventID, ?int $dayNum = null): array {
        $eventID = (int)$eventID;
        $where = "eventID = {$eventID} AND blockTypeID = " . ScheduleBlockTypes::WORKSHOP;
        if ($dayNum !== null) {
            $where .= " AND dayNum = " . (int)$dayNum;
        }
        return ScheduleBlocks::select($where, 'dayNum ASC, startTime ASC, blockID ASC');
    }

    /**
     * Confirm a school exists globally. 404 gate for /schedules/school/:id.
     */
    public static function schoolExists(int $schoolID): bool {
        $schoolID = (int)$schoolID;
        if ($schoolID <= 0) {
            return false;
        }
        $sql = "SELECT 1 FROM systemSchools WHERE schoolID = {$schoolID} LIMIT 1";
        return (bool)mysqlQuery($sql, SINGLE);
    }

    /**
     * School-filtered schedule (option C semantics):
     *   - Tournament blocks whose tournament contains at least one entrant
     *     from the school (COALESCE(eventRoster.schoolID, systemRoster.schoolID)).
     *   - Workshop blocks with at least one instructor from the school.
     *   - Excludes staffing and misc blocks and tournaments without school entrants.
     *
     * Note: intentionally diverges from the web app's getMatchesBySchool()
     * (DB_read_functions.php), which uses eventRoster.schoolID without
     * COALESCE. That pattern misses fighters whose home school is X but
     * whose event-override is unset. The API's COALESCE version matches
     * the /roster endpoint's "school at this event" semantic.
     *
     * Optional dayNum filter.
     */
    public static function school(int $eventID, int $schoolID, ?int $dayNum = null): array {
        $eventID  = (int)$eventID;
        $schoolID = (int)$schoolID;
        $roleInstructor = LogisticsRoles::INSTRUCTOR;
        $blockTournament = ScheduleBlockTypes::TOURNAMENT;
        $blockWorkshop   = ScheduleBlockTypes::WORKSHOP;

        $dayClause = $dayNum !== null ? " AND lSB.dayNum = " . (int)$dayNum : "";

        $sql = "SELECT DISTINCT
                    lSB.blockID         AS blockID,
                    lSB.eventID         AS eventID,
                    lSB.dayNum          AS dayNum,
                    lSB.startTime       AS startTime,
                    lSB.endTime         AS endTime,
                    lSB.blockTypeID     AS blockTypeID,
                    lSB.tournamentID    AS tournamentID,
                    lSB.blockTitle      AS blockTitle,
                    lSB.blockSubtitle   AS blockSubtitle,
                    lSB.blockDescription AS blockDescription,
                    lSB.blockLink       AS blockLink,
                    lSB.blockLinkDescription AS blockLinkDescription
                FROM logisticsScheduleBlocks lSB
                WHERE lSB.eventID = {$eventID}
                {$dayClause}
                AND (
                    -- Tournament blocks with a school entrant
                    (lSB.blockTypeID = {$blockTournament} AND EXISTS (
                        SELECT 1
                        FROM eventTournamentRoster eTR
                        INNER JOIN eventRoster eR ON eR.rosterID = eTR.rosterID
                        LEFT JOIN systemRoster sR ON sR.systemRosterID = eR.systemRosterID
                        WHERE eTR.tournamentID = lSB.tournamentID
                          AND COALESCE(eR.schoolID, sR.schoolID) = {$schoolID}
                    ))
                    OR
                    -- Workshop blocks with a school instructor
                    (lSB.blockTypeID = {$blockWorkshop} AND EXISTS (
                        SELECT 1
                        FROM logisticsStaffShifts staff
                        INNER JOIN logisticsScheduleShifts shifts ON shifts.shiftID = staff.shiftID
                        INNER JOIN eventRoster eR ON eR.rosterID = staff.rosterID
                        LEFT JOIN systemRoster sR ON sR.systemRosterID = eR.systemRosterID
                        WHERE shifts.blockID = lSB.blockID
                          AND staff.logisticsRoleID = {$roleInstructor}
                          AND COALESCE(eR.schoolID, sR.schoolID) = {$schoolID}
                    ))
                )
                ORDER BY lSB.dayNum ASC, lSB.startTime ASC, lSB.blockID ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Confirm a roster entry exists for (rosterID, eventID) and that the
     * row is NOT a team (isTeam = 1 would mean team opacity — personal
     * schedules only apply to individuals in v1).
     */
    public static function participantBelongsToEvent(int $rosterID, int $eventID): bool {
        $rosterID = (int)$rosterID;
        $eventID  = (int)$eventID;
        if ($rosterID <= 0 || $eventID <= 0) {
            return false;
        }
        $sql = "SELECT 1
                FROM eventRoster
                WHERE rosterID = {$rosterID}
                AND eventID = {$eventID}
                AND COALESCE(isTeam, 0) = 0
                LIMIT 1";
        return (bool)mysqlQuery($sql, SINGLE);
    }

    /**
     * Personal schedule for a fighter at an event:
     *   - Tournament blocks whose tournament contains the fighter as entrant.
     *   - Any block where the fighter has a staff shift.
     *
     * Does NOT emit "unscheduled tournament" notices (spec-deferred).
     * Dedup'd by blockID.
     */
    public static function personal(int $eventID, int $rosterID, ?int $dayNum = null): array {
        $eventID  = (int)$eventID;
        $rosterID = (int)$rosterID;
        $dayClause = $dayNum !== null ? " AND lSB.dayNum = " . (int)$dayNum : "";

        $sql = "SELECT DISTINCT
                    lSB.blockID         AS blockID,
                    lSB.eventID         AS eventID,
                    lSB.dayNum          AS dayNum,
                    lSB.startTime       AS startTime,
                    lSB.endTime         AS endTime,
                    lSB.blockTypeID     AS blockTypeID,
                    lSB.tournamentID    AS tournamentID,
                    lSB.blockTitle      AS blockTitle,
                    lSB.blockSubtitle   AS blockSubtitle,
                    lSB.blockDescription AS blockDescription,
                    lSB.blockLink       AS blockLink,
                    lSB.blockLinkDescription AS blockLinkDescription
                FROM logisticsScheduleBlocks lSB
                WHERE lSB.eventID = {$eventID}
                {$dayClause}
                AND (
                    EXISTS (
                        SELECT 1
                        FROM eventTournamentRoster eTR
                        WHERE eTR.tournamentID = lSB.tournamentID
                          AND eTR.rosterID = {$rosterID}
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM logisticsStaffShifts staff
                        INNER JOIN logisticsScheduleShifts shifts ON shifts.shiftID = staff.shiftID
                        WHERE shifts.blockID = lSB.blockID
                          AND staff.rosterID = {$rosterID}
                    )
                )
                ORDER BY lSB.dayNum ASC, lSB.startTime ASC, lSB.blockID ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Resolve each block's participation entries for a fighter. Returns
     * map: blockID => [ { role, tournamentID?, shiftStartTime?, shiftEndTime? }, ... ].
     *
     * One query for entrant rows (tournamentID per tournament block) and
     * one for shift rows (role + shift-specific times).
     */
    public static function fetchPersonalParticipation(int $rosterID, array $blockIDs): array {
        $result = [];
        foreach ($blockIDs as $id) {
            $result[(int)$id] = [];
        }
        if (empty($blockIDs) || $rosterID <= 0) {
            return $result;
        }

        $ints = array_map('intval', $blockIDs);
        $inClause = implode(',', $ints);

        // Entrant rows (tournament blocks)
        $sql = "SELECT lSB.blockID AS blockID, eTR.tournamentID AS tournamentID
                FROM eventTournamentRoster eTR
                INNER JOIN logisticsScheduleBlocks lSB ON lSB.tournamentID = eTR.tournamentID
                WHERE eTR.rosterID = {$rosterID}
                AND lSB.blockID IN ({$inClause})";
        foreach (mysqlQuery($sql, ASSOC) as $r) {
            $bid = (int)$r['blockID'];
            if (isset($result[$bid])) {
                $result[$bid][] = [
                    'role'         => 'entrant',
                    'tournamentID' => (int)$r['tournamentID'],
                ];
            }
        }

        // Shift rows — join role name
        $sql = "SELECT
                    shifts.blockID       AS blockID,
                    LOWER(role.roleName) AS role,
                    shifts.startTime     AS startTime,
                    shifts.endTime       AS endTime
                FROM logisticsStaffShifts staff
                INNER JOIN logisticsScheduleShifts shifts ON shifts.shiftID = staff.shiftID
                INNER JOIN systemLogisticsRoles role ON role.logisticsRoleID = staff.logisticsRoleID
                WHERE staff.rosterID = {$rosterID}
                AND shifts.blockID IN ({$inClause})";
        foreach (mysqlQuery($sql, ASSOC) as $r) {
            $bid = (int)$r['blockID'];
            if (isset($result[$bid])) {
                $result[$bid][] = [
                    'role'           => $r['role'],
                    'shiftStartTime' => TimeFormat::minutesToHhmm((int)$r['startTime']),
                    'shiftEndTime'   => TimeFormat::minutesToHhmm((int)$r['endTime']),
                ];
            }
        }

        return $result;
    }

    /**
     * Confirm a location belongs to $eventID. 404 gate for
     * /schedules/location/:id.
     */
    public static function locationBelongsToEvent(int $locationID, int $eventID): bool {
        $locationID = (int)$locationID;
        $eventID    = (int)$eventID;
        if ($locationID <= 0 || $eventID <= 0) {
            return false;
        }
        $sql = "SELECT 1
                FROM logisticsLocations
                WHERE locationID = {$locationID}
                AND eventID = {$eventID}
                LIMIT 1";
        return (bool)mysqlQuery($sql, SINGLE);
    }

    /**
     * Schedule filtered to a single location. Returns rows in the same
     * shape as ScheduleBlocks::select so the caller can use
     * enrichWithLocations + shape on them.
     */
    public static function location(int $eventID, int $locationID, ?int $dayNum = null): array {
        $eventID    = (int)$eventID;
        $locationID = (int)$locationID;
        $dayClause  = $dayNum !== null ? " AND lSB.dayNum = " . (int)$dayNum : "";

        $sql = "SELECT DISTINCT
                    lSB.blockID         AS blockID,
                    lSB.eventID         AS eventID,
                    lSB.dayNum          AS dayNum,
                    lSB.startTime       AS startTime,
                    lSB.endTime         AS endTime,
                    lSB.blockTypeID     AS blockTypeID,
                    lSB.tournamentID    AS tournamentID,
                    lSB.blockTitle      AS blockTitle,
                    lSB.blockSubtitle   AS blockSubtitle,
                    lSB.blockDescription AS blockDescription,
                    lSB.blockLink       AS blockLink,
                    lSB.blockLinkDescription AS blockLinkDescription
                FROM logisticsScheduleBlocks lSB
                INNER JOIN logisticsLocationsBlocks lLB ON lLB.blockID = lSB.blockID
                WHERE lSB.eventID = {$eventID}
                AND lLB.locationID = {$locationID}
                {$dayClause}
                ORDER BY lSB.dayNum ASC, lSB.startTime ASC, lSB.blockID ASC";
        return mysqlQuery($sql, ASSOC);
    }
}
