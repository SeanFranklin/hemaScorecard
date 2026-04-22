<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\EventsQuery;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\RulesQuery;

class RulesController {

    public function index(string $eventID): void {
        $id = (int)$eventID;
        $gate = EventsQuery::findVisibleForGate($id);
        if ($gate === null) {
            throw new ApiException('not_found', 404, "Event {$id} not found");
        }

        // If rules are not published and event is not archived, return empty.
        if (!($gate['isArchived'] || $gate['publishRules'])) {
            JsonResponse::success([], ['count' => 0]);
            return;
        }

        $rows = RulesQuery::listForEvent($id);
        JsonResponse::success(
            array_map([$this, 'shapeListItem'], $rows),
            ['count' => count($rows)]
        );
    }

    public function show(string $eventID, string $rulesID): void {
        $eid = (int)$eventID;
        $rid = (int)$rulesID;

        $gate = EventsQuery::findVisibleForGate($eid);
        if ($gate === null) {
            throw new ApiException('not_found', 404, "Event {$eid} not found");
        }

        // Named-resource case: 404 (not empty) when rules aren't published.
        if (!($gate['isArchived'] || $gate['publishRules'])) {
            throw new ApiException('not_found', 404, "Ruleset {$rid} not found");
        }

        $row = RulesQuery::findForEvent($eid, $rid);
        if ($row === null) {
            throw new ApiException('not_found', 404, "Ruleset {$rid} not found");
        }

        $tournaments = RulesQuery::listLinkedTournaments($rid);

        JsonResponse::success([
            'rulesID'     => (int)$row['rulesID'],
            'rulesName'   => $row['rulesName'],
            'rulesOrder'  => (int)$row['rulesOrder'],
            'rulesText'   => $row['rulesText'],
            'tournaments' => array_map(function($t) {
                return [
                    'tournamentID' => (int)$t['tournamentID'],
                    'name'         => $t['name'],
                ];
            }, $tournaments),
        ]);
    }

    private function shapeListItem(array $row): array {
        return [
            'rulesID'    => (int)$row['rulesID'],
            'rulesName'  => $row['rulesName'],
            'rulesOrder' => (int)$row['rulesOrder'],
        ];
    }
}
