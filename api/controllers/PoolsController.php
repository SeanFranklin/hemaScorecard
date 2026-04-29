<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\ChecksEventVisibility;
use HemaScorecard\Api\Lib\GroupsQuery;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\PoolsQuery;
use HemaScorecard\Api\Lib\StandingsQuery;
use HemaScorecard\Api\Lib\TournamentsQuery;

class PoolsController {

    use ChecksEventVisibility;

    public function index(string $eventID, string $tournamentID): void {
        $eid = (int)$eventID;
        $tid = (int)$tournamentID;
        $gate = $this->findVisibleEventOrThrow($eid);

        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            JsonResponse::success([], ['count' => 0]);
            return;
        }

        if (!TournamentsQuery::belongsToEvent($eid, $tid)) {
            throw new ApiException('not_found', 404, "Tournament {$tid} not found");
        }

        $rows = PoolsQuery::listForTournament($tid);
        $shaped = array_map([$this, 'shapeListItem'], $rows);
        JsonResponse::success($shaped, ['count' => count($shaped)]);
    }

    public function show(string $eventID, string $tournamentID, string $poolID): void {
        $eid = (int)$eventID;
        $tid = (int)$tournamentID;
        $pid = (int)$poolID;

        $gate = $this->findVisibleEventOrThrow($eid);
        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            throw new ApiException('not_found', 404, "Pool {$pid} not found");
        }

        $row = PoolsQuery::findPoolInScope($eid, $tid, $pid);
        if ($row === null) {
            throw new ApiException('not_found', 404, "Pool {$pid} not found");
        }

        $counts = GroupsQuery::progressCountsForGroup($pid);
        JsonResponse::success($this->shapeDetail($row, $counts));
    }

    public function roster(string $eventID, string $tournamentID, string $poolID): void {
        $eid = (int)$eventID;
        $tid = (int)$tournamentID;
        $pid = (int)$poolID;

        $gate = $this->findVisibleEventOrThrow($eid);
        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            throw new ApiException('not_found', 404, "Pool {$pid} not found");
        }

        if (PoolsQuery::findPoolInScope($eid, $tid, $pid) === null) {
            throw new ApiException('not_found', 404, "Pool {$pid} not found");
        }

        $rows = PoolsQuery::rosterForPool($pid);
        $shaped = array_map([$this, 'shapeRosterRow'], $rows);
        JsonResponse::success($shaped, ['count' => count($shaped)]);
    }

    public function standings(string $eventID, string $tournamentID, string $poolID): void {
        $eid = (int)$eventID;
        $tid = (int)$tournamentID;
        $pid = (int)$poolID;

        $gate = $this->findVisibleEventOrThrow($eid);
        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            throw new ApiException('not_found', 404, "Pool {$pid} not found");
        }

        if (PoolsQuery::findPoolInScope($eid, $tid, $pid) === null) {
            throw new ApiException('not_found', 404, "Pool {$pid} not found");
        }

        $rows = StandingsQuery::forPool($pid);
        $shaped = array_map([$this, 'shapeStandingsRow'], $rows);
        JsonResponse::success($shaped, ['count' => count($shaped)]);
    }

    private function shapeListItem(array $row): array {
        return [
            'poolID'      => (int)$row['poolID'],
            'poolName'    => $row['poolName'],
            'poolNumber'  => (int)$row['poolNumber'],
            'groupSet'    => (int)$row['groupSet'],
            'numFighters' => (int)$row['numFighters'],
            'locationID'  => $row['locationID'] !== null ? (int)$row['locationID'] : null,
            'isComplete'  => (bool)(int)$row['isComplete'],
        ];
    }

    private function shapeDetail(array $row, array $counts): array {
        return [
            'poolID'          => (int)$row['poolID'],
            'poolName'        => $row['poolName'],
            'poolNumber'      => (int)$row['poolNumber'],
            'groupSet'        => (int)$row['groupSet'],
            'numFighters'     => (int)$row['numFighters'],
            'locationID'      => $row['locationID']   !== null ? (int)$row['locationID']   : null,
            'locationName'    => $row['locationName'] !== null ? $row['locationName']       : null,
            'isComplete'      => (bool)(int)$row['isComplete'],
            'rank'            => $row['rank']        !== null ? (int)$row['rank']        : null,
            'overlapSize'     => $row['overlapSize'] !== null ? (int)$row['overlapSize'] : null,
            'matchesTotal'    => $counts['total'],
            'matchesComplete' => $counts['complete'],
        ];
    }

    private function shapeRosterRow(array $row): array {
        return [
            'rosterID'           => (int)$row['rosterID'],
            'firstName'          => $row['firstName'],
            'lastName'           => $row['lastName'],
            'schoolID'           => $row['schoolID']   !== null ? (int)$row['schoolID']   : null,
            'schoolName'         => $row['schoolName'] !== null ? $row['schoolName']      : null,
            'poolPosition'       => (int)$row['poolPosition'],
            'participantStatus'  => $row['participantStatus'],
            'tournamentTableID'  => $row['tournamentTableID'] !== null ? (int)$row['tournamentTableID'] : null,
        ];
    }

    private function shapeStandingsRow(array $row): array {
        return [
            'rosterID'                 => (int)$row['rosterID'],
            'firstName'                => $row['firstName'],
            'lastName'                 => $row['lastName'],
            'schoolID'                 => $row['schoolID']   !== null ? (int)$row['schoolID']   : null,
            'schoolName'               => $row['schoolName'] !== null ? $row['schoolName']      : null,
            'rank'                     => $row['rank']        !== null ? (int)$row['rank']        : null,
            'overlapSize'              => $row['overlapSize'] !== null ? (int)$row['overlapSize'] : null,
            'score'                    => (float)$row['score'],
            'matches'                  => (float)$row['matches'],
            'wins'                     => (float)$row['wins'],
            'losses'                   => (float)$row['losses'],
            'ties'                     => (float)$row['ties'],
            'pointsFor'                => (float)$row['pointsFor'],
            'pointsAgainst'            => (float)$row['pointsAgainst'],
            'hitsFor'                  => (float)$row['hitsFor'],
            'hitsAgainst'              => (float)$row['hitsAgainst'],
            'afterblowsFor'            => (float)$row['afterblowsFor'],
            'afterblowsAgainst'        => (float)$row['afterblowsAgainst'],
            'doubles'                  => (float)$row['doubles'],
            'noExchanges'              => (float)$row['noExchanges'],
            'absPointsFor'             => (float)$row['absPointsFor'],
            'absPointsAgainst'         => (float)$row['absPointsAgainst'],
            'absPointsAwarded'         => (float)$row['absPointsAwarded'],
            'numPenalties'             => (float)$row['numPenalties'],
            'numYellowCards'           => (int)$row['numYellowCards'],
            'numRedCards'              => (int)$row['numRedCards'],
            'penaltiesAgainstOpponents'=> (float)$row['penaltiesAgainstOpponents'],
            'penaltiesAgainst'         => (float)$row['penaltiesAgainst'],
            'doubleOuts'               => (float)$row['doubleOuts'],
            'numCleanHits'             => (float)$row['numCleanHits'],
            'basePointValue'           => (int)$row['basePointValue'],
            'ignoreForBracket'         => (bool)(int)$row['ignoreForBracket'],
        ];
    }
}
