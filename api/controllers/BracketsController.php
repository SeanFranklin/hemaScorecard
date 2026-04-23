<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\BracketsQuery;
use HemaScorecard\Api\Lib\ChecksEventVisibility;
use HemaScorecard\Api\Lib\GroupsQuery;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\PlacingsQuery;
use HemaScorecard\Api\Lib\TournamentsQuery;

class BracketsController {

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

        $rows = BracketsQuery::listForTournament($tid);
        $elimType = $rows ? BracketsQuery::elimTypeFor($tid) : BracketsQuery::ELIM_TYPE_SINGLE;
        $shaped = array_map(function(array $row) use ($elimType) {
            return $this->shapeListItem($row, $elimType);
        }, $rows);
        JsonResponse::success($shaped, ['count' => count($shaped)]);
    }

    public function show(string $eventID, string $tournamentID, string $bracketID): void {
        $eid = (int)$eventID;
        $tid = (int)$tournamentID;
        $bid = (int)$bracketID;

        $gate = $this->findVisibleEventOrThrow($eid);
        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            throw new ApiException('not_found', 404, "Bracket {$bid} not found");
        }

        $row = BracketsQuery::findBracketInScope($eid, $tid, $bid);
        if ($row === null) {
            throw new ApiException('not_found', 404, "Bracket {$bid} not found");
        }

        $elimType = BracketsQuery::elimTypeFor($tid);
        $counts   = GroupsQuery::progressCountsForGroup($bid);
        $placings = array_map([PlacingsController::class, 'shape'], PlacingsQuery::forTournament($tid));

        JsonResponse::success($this->shapeDetail($row, $elimType, $counts, $placings));
    }

    public function roster(string $eventID, string $tournamentID, string $bracketID): void {
        $eid = (int)$eventID;
        $tid = (int)$tournamentID;
        $bid = (int)$bracketID;

        $gate = $this->findVisibleEventOrThrow($eid);
        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            throw new ApiException('not_found', 404, "Bracket {$bid} not found");
        }

        if (BracketsQuery::findBracketInScope($eid, $tid, $bid) === null) {
            throw new ApiException('not_found', 404, "Bracket {$bid} not found");
        }

        $rows = BracketsQuery::rosterForBracket($bid);
        $shaped = array_map([$this, 'shapeRosterRow'], $rows);
        JsonResponse::success($shaped, ['count' => count($shaped)]);
    }

    private function shapeListItem(array $row, string $elimType): array {
        return [
            'bracketID'     => (int)$row['bracketID'],
            'bracketType'   => BracketsQuery::bracketTypeFromGroupNumber((int)$row['groupNumber']),
            'bracketLevels' => (int)$row['bracketLevels'],
            'numFighters'   => (int)$row['numFighters'],
            'elimType'      => $elimType,
            'isComplete'    => (bool)(int)$row['isComplete'],
            'locationID'    => $row['locationID'] !== null ? (int)$row['locationID'] : null,
        ];
    }

    private function shapeDetail(array $row, string $elimType, array $counts, array $placings): array {
        return [
            'bracketID'       => (int)$row['bracketID'],
            'bracketType'     => BracketsQuery::bracketTypeFromGroupNumber((int)$row['groupNumber']),
            'bracketLevels'   => (int)$row['bracketLevels'],
            'numFighters'     => (int)$row['numFighters'],
            'elimType'        => $elimType,
            'isComplete'      => (bool)(int)$row['isComplete'],
            'locationID'      => $row['locationID']   !== null ? (int)$row['locationID']   : null,
            'locationName'    => $row['locationName'] !== null ? $row['locationName']      : null,
            'matchesTotal'    => $counts['total'],
            'matchesComplete' => $counts['complete'],
            'placings'        => $placings,
        ];
    }

    private function shapeRosterRow(array $row): array {
        return [
            'rosterID'          => (int)$row['rosterID'],
            'firstName'         => $row['firstName'],
            'lastName'          => $row['lastName'],
            'schoolID'          => $row['schoolID']   !== null ? (int)$row['schoolID']   : null,
            'schoolName'        => $row['schoolName'] !== null ? $row['schoolName']      : null,
            'poolPosition'      => (int)$row['poolPosition'],
            'participantStatus' => $row['participantStatus'],
            'tournamentTableID' => $row['tournamentTableID'] !== null ? (int)$row['tournamentTableID'] : null,
        ];
    }
}
