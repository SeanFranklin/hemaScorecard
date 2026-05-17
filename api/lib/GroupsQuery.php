<?php
namespace HemaScorecard\Api\Lib;

class GroupsQuery {

    /**
     * Count of matches in a group (pool OR bracket — anything in
     * eventGroups) excluding placeholders, plus subset that are complete
     * AND not ignored. Used for progress counts on pool + bracket detail
     * endpoints.
     */
    public static function progressCountsForGroup(int $groupID): array {
        $groupID = (int)$groupID;
        $sql = "SELECT
                    SUM(CASE WHEN isPlaceholder = 0 THEN 1 ELSE 0 END) AS total,
                    SUM(CASE WHEN isPlaceholder = 0 AND matchComplete = 1 AND ignoreMatch = 0 THEN 1 ELSE 0 END) AS complete
                FROM eventMatches
                WHERE groupID = {$groupID}";
        $row = mysqlQuery($sql, SINGLE);
        return [
            'total'    => (int)($row['total']    ?? 0),
            'complete' => (int)($row['complete'] ?? 0),
        ];
    }
}
