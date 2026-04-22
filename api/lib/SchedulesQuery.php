<?php
namespace HemaScorecard\Api\Lib;

class SchedulesQuery {

    // Matches LOGISTICS_ROLE_INSTRUCTOR (=5) from includes/config.php.
    // Same hardcoded pattern as WorkshopsQuery.
    private const ROLE_INSTRUCTOR = 5;

    private const BLOCK_TYPE_TOURNAMENT = 1;
    private const BLOCK_TYPE_WORKSHOP   = 2;

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
        $where = "eventID = {$eventID} AND blockTypeID = " . self::BLOCK_TYPE_WORKSHOP;
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
     * Optional dayNum filter.
     */
    public static function school(int $eventID, int $schoolID, ?int $dayNum = null): array {
        $eventID  = (int)$eventID;
        $schoolID = (int)$schoolID;
        $roleInstructor = self::ROLE_INSTRUCTOR;
        $blockTournament = self::BLOCK_TYPE_TOURNAMENT;
        $blockWorkshop   = self::BLOCK_TYPE_WORKSHOP;

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
}
