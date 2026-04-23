<?php
namespace HemaScorecard\Api\Lib;

class ExchangesQuery {

    /**
     * Return one entry per eventExchanges row for $matchID, ordered by
     * exchangeNumber ASC, exchangeID ASC. Expands refPrefix / refType /
     * refTarget via AttacksVocabulary so callers get {code, text} objects
     * (or null) without an extra join.
     *
     * Shape per entry:
     *   exchangeID, exchangeNumber, exchangeType,
     *   scoringRosterID, receivingRosterID,
     *   scoreValue, scoreDeduction, exchangeTime,
     *   attack: { prefix: {code,text}|null, type: ..., target: ... }
     */
    public static function forMatch(int $matchID): array {
        $matchID = (int)$matchID;
        $sql = "SELECT
                    exchangeID       AS exchangeID,
                    exchangeNumber   AS exchangeNumber,
                    exchangeType     AS exchangeType,
                    scoringID        AS scoringRosterID,
                    receivingID      AS receivingRosterID,
                    scoreValue       AS scoreValue,
                    scoreDeduction   AS scoreDeduction,
                    exchangeTime     AS exchangeTime,
                    refPrefix        AS refPrefix,
                    refType          AS refType,
                    refTarget        AS refTarget
                FROM eventExchanges
                WHERE matchID = {$matchID}
                ORDER BY exchangeNumber ASC, exchangeID ASC";
        $rows = mysqlQuery($sql, ASSOC);

        return array_map(function(array $row): array {
            return [
                'exchangeID'        => (int)$row['exchangeID'],
                'exchangeNumber'    => (int)$row['exchangeNumber'],
                'exchangeType'      => $row['exchangeType'],
                'scoringRosterID'   => $row['scoringRosterID']   !== null ? (int)$row['scoringRosterID']   : null,
                'receivingRosterID' => $row['receivingRosterID'] !== null ? (int)$row['receivingRosterID'] : null,
                'scoreValue'        => $row['scoreValue']     !== null ? (float)$row['scoreValue']     : null,
                'scoreDeduction'    => $row['scoreDeduction'] !== null ? (float)$row['scoreDeduction'] : 0.0,
                'exchangeTime'      => $row['exchangeTime']   !== null ? (int)$row['exchangeTime']     : null,
                'attack' => [
                    'prefix' => AttacksVocabulary::lookup($row['refPrefix'] !== null ? (int)$row['refPrefix'] : null),
                    'type'   => AttacksVocabulary::lookup($row['refType']   !== null ? (int)$row['refType']   : null),
                    'target' => AttacksVocabulary::lookup($row['refTarget'] !== null ? (int)$row['refTarget'] : null),
                ],
            ];
        }, $rows);
    }
}
