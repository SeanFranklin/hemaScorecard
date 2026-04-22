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
}
