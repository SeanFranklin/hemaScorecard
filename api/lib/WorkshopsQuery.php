<?php
namespace HemaScorecard\Api\Lib;

class WorkshopsQuery {

    // Matches LOGISTICS_ROLE_INSTRUCTOR (=5) from includes/config.php.
    // Hardcoded because the API bootstrap doesn't load config.php; a
    // shared-constants extraction is backlogged (see spec).
    private const ROLE_INSTRUCTOR = 5;

    private const BLOCK_TYPE_WORKSHOP = 2;

    /**
     * Workshop blocks for an event, sorted by day/time.
     */
    public static function listForEvent(int $eventID): array {
        $eventID = (int)$eventID;
        $where = "eventID = {$eventID} AND blockTypeID = " . self::BLOCK_TYPE_WORKSHOP;
        return ScheduleBlocks::select($where, 'dayNum ASC, startTime ASC, blockID ASC');
    }

    /**
     * Single workshop block, but only if it belongs to $eventID AND is
     * actually a workshop (blockTypeID = 2). Returns null otherwise.
     */
    public static function findForEvent(int $eventID, int $blockID): ?array {
        $eventID = (int)$eventID;
        $blockID = (int)$blockID;
        if ($eventID <= 0 || $blockID <= 0) {
            return null;
        }
        $where = "blockID = {$blockID} AND eventID = {$eventID} AND blockTypeID = " . self::BLOCK_TYPE_WORKSHOP;
        $rows = ScheduleBlocks::select($where, 'blockID ASC');
        return $rows ? $rows[0] : null;
    }

    /**
     * Batch-fetch experience and equipment for a list of blockIDs.
     * Returns map: blockID => ['experience' => ?, 'equipment' => ?].
     */
    public static function fetchAttributes(array $blockIDs): array {
        $result = [];
        foreach ($blockIDs as $id) {
            $result[(int)$id] = ['experience' => null, 'equipment' => null];
        }
        if (empty($blockIDs)) {
            return $result;
        }

        $ints = array_map('intval', $blockIDs);
        $inClause = implode(',', $ints);

        $sql = "SELECT blockID, blockAttributeType, blockAttributeText
                FROM logisticsBlockAttributes
                WHERE blockID IN ({$inClause})
                AND blockAttributeType IN ('experience', 'equipment')";
        $rows = mysqlQuery($sql, ASSOC);

        foreach ($rows as $r) {
            $bid = (int)$r['blockID'];
            $type = $r['blockAttributeType'];
            if (isset($result[$bid]) && in_array($type, ['experience', 'equipment'], true)) {
                $result[$bid][$type] = $r['blockAttributeText'];
            }
        }
        return $result;
    }

    /**
     * Batch-fetch instructor info for a list of blockIDs.
     * Returns map: blockID => [ {rosterID, systemRosterID, firstName, lastName, bio}, ... ]
     * Instructor rows are identified via logisticsStaffShifts.logisticsRoleID = ROLE_INSTRUCTOR.
     */
    public static function fetchInstructors(array $blockIDs): array {
        $result = [];
        foreach ($blockIDs as $id) {
            $result[(int)$id] = [];
        }
        if (empty($blockIDs)) {
            return $result;
        }

        $ints = array_map('intval', $blockIDs);
        $inClause = implode(',', $ints);
        $roleInstructor = self::ROLE_INSTRUCTOR;

        $sql = "SELECT DISTINCT
                    lSS.blockID         AS blockID,
                    eR.rosterID         AS rosterID,
                    eR.systemRosterID   AS systemRosterID,
                    sR.firstName        AS firstName,
                    sR.lastName         AS lastName,
                    lI.instructorBio    AS bio
                FROM logisticsStaffShifts staff
                INNER JOIN logisticsScheduleShifts lSS ON lSS.shiftID = staff.shiftID
                INNER JOIN eventRoster eR ON eR.rosterID = staff.rosterID
                LEFT JOIN systemRoster sR ON sR.systemRosterID = eR.systemRosterID
                LEFT JOIN logisticsInstructors lI ON lI.rosterID = eR.rosterID
                WHERE lSS.blockID IN ({$inClause})
                AND staff.logisticsRoleID = {$roleInstructor}
                ORDER BY lSS.blockID ASC, sR.lastName ASC, sR.firstName ASC";
        $rows = mysqlQuery($sql, ASSOC);

        foreach ($rows as $r) {
            $bid = (int)$r['blockID'];
            if (!isset($result[$bid])) {
                continue;
            }
            $result[$bid][] = [
                'rosterID'       => (int)$r['rosterID'],
                'systemRosterID' => $r['systemRosterID'] !== null ? (int)$r['systemRosterID'] : null,
                'firstName'      => $r['firstName'],
                'lastName'       => $r['lastName'],
                'bio'            => $r['bio'],
            ];
        }
        return $result;
    }
}
