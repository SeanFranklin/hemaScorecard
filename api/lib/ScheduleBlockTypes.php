<?php
namespace HemaScorecard\Api\Lib;

/**
 * Values for logisticsScheduleBlocks.blockTypeID.
 * Verified against includes/config.php:129-132 (SCHEDULE_BLOCK_* constants).
 */
class ScheduleBlockTypes {
    public const TOURNAMENT = 1;
    public const WORKSHOP   = 2;
    public const STAFFING   = 3;
    public const MISC       = 4;
}
