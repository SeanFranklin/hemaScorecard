<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\ChecksEventVisibility;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\PlacingsQuery;
use HemaScorecard\Api\Lib\TournamentsQuery;

class PlacingsController {

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

        $rows = PlacingsQuery::forTournament($tid);
        $shaped = array_map([self::class, 'shape'], $rows);
        JsonResponse::success($shaped, ['count' => count($shaped)]);
    }

    /**
     * Shape a single eventPlacings row. Static so it can be reused by
     * BracketsController::show for the embedded placings array.
     */
    public static function shape(array $row): array {
        return [
            'rosterID'   => (int)$row['rosterID'],
            'firstName'  => $row['firstName'],
            'lastName'   => $row['lastName'],
            'schoolID'   => $row['schoolID']   !== null ? (int)$row['schoolID']   : null,
            'schoolName' => $row['schoolName'] !== null ? $row['schoolName']      : null,
            'placing'    => (int)$row['placing'],
            'placeType'  => $row['placeType'],
            'highBound'  => $row['highBound'] !== null ? (int)$row['highBound'] : null,
            'lowBound'   => $row['lowBound']  !== null ? (int)$row['lowBound']  : null,
        ];
    }
}
