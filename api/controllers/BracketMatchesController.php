<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\BracketMatchesQuery;
use HemaScorecard\Api\Lib\BracketsQuery;
use HemaScorecard\Api\Lib\ChecksEventVisibility;
use HemaScorecard\Api\Lib\JsonResponse;

class BracketMatchesController {

    use ChecksEventVisibility;

    public function index(string $eventID, string $tournamentID, string $bracketID): void {
        $eid = (int)$eventID;
        $tid = (int)$tournamentID;
        $bid = (int)$bracketID;

        $gate = $this->findVisibleEventOrThrow($eid);
        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            JsonResponse::success([], ['count' => 0]);
            return;
        }

        if (BracketsQuery::findBracketInScope($eid, $tid, $bid) === null) {
            throw new ApiException('not_found', 404, "Bracket {$bid} not found");
        }

        $rows = BracketMatchesQuery::listForBracket($bid);
        $shaped = array_map([$this, 'shapeListItem'], $rows);
        JsonResponse::success($shaped, ['count' => count($shaped)]);
    }

    protected function shapeListItem(array $row): array {
        return [
            'matchID'         => (int)$row['matchID'],
            'matchNumber'     => (int)$row['matchNumber'],
            'groupID'         => (int)$row['groupID'],
            'bracketLevel'    => (int)$row['bracketLevel'],
            'bracketPosition' => (int)$row['bracketPosition'],
            'fighter1'        => $this->shapeFighter($row, 1),
            'fighter2'        => $this->shapeFighter($row, 2),
            'fighter1Score'   => $row['fighter1Score'] !== null ? (float)$row['fighter1Score'] : null,
            'fighter2Score'   => $row['fighter2Score'] !== null ? (float)$row['fighter2Score'] : null,
            'winnerRosterID'  => $row['winnerID']  !== null && (int)$row['winnerID']  > 0 ? (int)$row['winnerID']  : null,
            'isComplete'      => (bool)(int)$row['isComplete'],
            'isIgnored'       => (bool)(int)$row['isIgnored'],
            'locationID'      => $row['locationID']   !== null ? (int)$row['locationID']   : null,
            'locationName'    => $row['locationName'] !== null ? $row['locationName']      : null,
        ];
    }

    /**
     * Build a {rosterID, firstName, lastName} object for fighter N (1 or 2).
     * Returns null when the fighter slot is unassigned.
     */
    protected function shapeFighter(array $row, int $slot): ?array {
        $idKey    = "fighter{$slot}ID";
        $firstKey = "fighter{$slot}FirstName";
        $lastKey  = "fighter{$slot}LastName";
        if ($row[$idKey] === null) {
            return null;
        }
        return [
            'rosterID'  => (int)$row[$idKey],
            'firstName' => $row[$firstKey],
            'lastName'  => $row[$lastKey],
        ];
    }
}
