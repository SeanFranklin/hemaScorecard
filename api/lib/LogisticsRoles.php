<?php
namespace HemaScorecard\Api\Lib;

/**
 * Values for logisticsStaffShifts.logisticsRoleID + systemLogisticsRoles.logisticsRoleID.
 * Verified against includes/config.php:140-146 (LOGISTICS_ROLE_* constants).
 * Note: UNKNOWN corrects a typo in the web app (LOGISTICS_ROLE_UNKONWN);
 * the numeric value is unchanged so SQL comparisons match the same rows.
 */
class LogisticsRoles {
    public const DIRECTOR    = 1;
    public const JUDGE       = 2;
    public const TABLE       = 3;
    public const UNKNOWN     = 4;
    public const INSTRUCTOR  = 5;
    public const GENERAL     = 6;
    public const PARTICIPANT = 7;
}
