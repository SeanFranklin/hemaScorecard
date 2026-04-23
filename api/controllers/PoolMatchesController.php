<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\ChecksEventVisibility;
use HemaScorecard\Api\Lib\ExchangesQuery;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\PoolMatchesQuery;
use HemaScorecard\Api\Lib\PoolsQuery;

class PoolMatchesController {

    use ChecksEventVisibility;

    public function index(string $eventID, string $tournamentID, string $poolID): void {
        $eid = (int)$eventID;
        $tid = (int)$tournamentID;
        $pid = (int)$poolID;

        $gate = $this->findVisibleEventOrThrow($eid);
        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            JsonResponse::success([], ['count' => 0]);
            return;
        }

        if (PoolsQuery::findPoolInScope($eid, $tid, $pid) === null) {
            throw new ApiException('not_found', 404, "Pool {$pid} not found");
        }

        $rows = PoolMatchesQuery::listForPool($pid);
        $shaped = array_map([$this, 'shapeListItem'], $rows);
        JsonResponse::success($shaped, ['count' => count($shaped)]);
    }

    public function show(string $eventID, string $tournamentID, string $poolID, string $matchID): void {
        $eid = (int)$eventID;
        $tid = (int)$tournamentID;
        $pid = (int)$poolID;
        $mid = (int)$matchID;

        $gate = $this->findVisibleEventOrThrow($eid);
        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            throw new ApiException('not_found', 404, "Match {$mid} not found");
        }

        $row = PoolMatchesQuery::findMatchInScope($eid, $tid, $pid, $mid);
        if ($row === null) {
            throw new ApiException('not_found', 404, "Match {$mid} not found");
        }

        $exchanges = ExchangesQuery::forMatch($mid);
        $options   = array_map(function(array $o): array {
            return [
                'optionID'    => (int)$o['optionID'],
                'optionValue' => (int)$o['optionValue'],
            ];
        }, PoolMatchesQuery::optionsForMatch($mid));

        JsonResponse::success($this->shapeDetail($row, $exchanges, $options));
    }

    protected function shapeListItem(array $row): array {
        return [
            'matchID'        => (int)$row['matchID'],
            'matchNumber'    => (int)$row['matchNumber'],
            'groupID'        => (int)$row['groupID'],
            'fighter1'       => $this->shapeFighter($row, 1),
            'fighter2'       => $this->shapeFighter($row, 2),
            'fighter1Score'  => $row['fighter1Score'] !== null ? (float)$row['fighter1Score'] : null,
            'fighter2Score'  => $row['fighter2Score'] !== null ? (float)$row['fighter2Score'] : null,
            'winnerRosterID' => $row['winnerID']  !== null && (int)$row['winnerID']  > 0 ? (int)$row['winnerID']  : null,
            'isComplete'     => (bool)(int)$row['isComplete'],
            'isIgnored'      => (bool)(int)$row['isIgnored'],
            'locationID'     => $row['locationID']   !== null ? (int)$row['locationID']   : null,
            'locationName'   => $row['locationName'] !== null ? $row['locationName']      : null,
        ];
    }

    /**
     * Build a {rosterID, firstName, lastName} object for fighter N (1 or 2).
     * Returns null when the fighter slot is unassigned (NULL fighterNID —
     * valid for bye placeholders left in real data).
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

    private function shapeDetail(array $row, array $exchanges, array $options): array {
        $base = $this->shapeListItem($row);
        $base['matchTime'] = $row['matchTime'] !== null ? (int)$row['matchTime'] : null;
        $base['signOff1']  = (bool)(int)$row['signOff1'];
        $base['signOff2']  = (bool)(int)$row['signOff2'];
        $base['exchanges']    = $exchanges;
        $base['matchOptions'] = $options;
        return $base;
    }
}
