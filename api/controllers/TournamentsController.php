<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\ChecksEventVisibility;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\TournamentsQuery;

class TournamentsController {

    use ChecksEventVisibility;

    public function index(string $eventID): void {
        $id = (int)$eventID;
        $gate = $this->findVisibleEventOrThrow($id);

        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            JsonResponse::success([], ['count' => 0]);
            return;
        }

        $rows = TournamentsQuery::listForEvent($id);
        $shaped = array_map([$this, 'shapeListItem'], $rows);
        JsonResponse::success($shaped, ['count' => count($shaped)]);
    }

    public function show(string $eventID, string $tournamentID): void {
        $eid = (int)$eventID;
        $tid = (int)$tournamentID;

        $gate = $this->findVisibleEventOrThrow($eid);

        if (!$this->isResourceVisible($gate, 'publishMatches')) {
            throw new ApiException('not_found', 404, "Tournament {$tid} not found");
        }

        $row = TournamentsQuery::findForEvent($eid, $tid);
        if ($row === null) {
            throw new ApiException('not_found', 404, "Tournament {$tid} not found");
        }

        $rulesets = TournamentsQuery::listRulesets($tid);
        JsonResponse::success($this->shapeSingle($row, $rulesets));
    }

    private function shapeListItem(array $row): array {
        return [
            'tournamentID'    => (int)$row['tournamentID'],
            'eventID'         => (int)$row['eventID'],
            'name'            => $row['name'],
            'isTeams'         => (bool)(int)$row['isTeams'],
            'isFinalized'     => (bool)(int)$row['isFinalized'],
            'format'          => [
                'formatID' => (int)$row['formatID'],
                'name'     => $row['formatName'],
            ],
            'numParticipants' => (int)$row['numParticipants'],
        ];
    }

    private function shapeSingle(array $row, array $rulesets): array {
        // weapon is INNER-JOINed + NOT NULL in schema, so weaponID is always
        // set for a row returned by findForEvent. Null-checked here for
        // defensive consistency with the other (truly nullable) attribute slots.
        $attributes = [
            'weapon' => $row['weaponID'] !== null
                ? ['id' => (int)$row['weaponID'], 'name' => $row['weaponName']]
                : null,
            'prefix' => $row['prefixID'] !== null
                ? ['id' => (int)$row['prefixID'], 'name' => $row['prefixName']]
                : null,
            'gender' => $row['genderID'] !== null
                ? ['id' => (int)$row['genderID'], 'name' => $row['genderName']]
                : null,
            'material' => $row['materialID'] !== null
                ? ['id' => (int)$row['materialID'], 'name' => $row['materialName']]
                : null,
            'suffix' => $row['suffixID'] !== null
                ? ['id' => (int)$row['suffixID'], 'name' => $row['suffixName']]
                : null,
        ];

        $ranking = $row['rankingID'] !== null
            ? ['id' => (int)$row['rankingID'], 'name' => $row['rankingName']]
            : null;

        $doubleType = $row['doubleTypeID'] !== null
            ? ['id' => (int)$row['doubleTypeID'], 'name' => $row['doubleTypeName']]
            : null;

        $scoring = [
            'maxPoolSize'      => (int)$row['maxPoolSize'],
            'maxDoubleHits'    => (int)$row['maxDoubleHits'],
            'maximumExchanges' => $row['maximumExchanges'] !== null ? (int)$row['maximumExchanges'] : null,
            'maximumPoints'    => $row['maximumPoints']    !== null ? (int)$row['maximumPoints']    : null,
            'maxPointSpread'   => (int)$row['maxPointSpread'],
            'basePointValue'   => (int)$row['basePointValue'],
            'allowTies'        => (bool)(int)$row['allowTies'],
            'timerCountdown'   => (bool)(int)$row['timerCountdown'],
            'timeLimit'        => (int)$row['timeLimit'],
            'isNotNetScore'    => (bool)(int)$row['isNotNetScore'],
            'isReverseScore'   => (bool)(int)$row['isReverseScore'],
            'doubleType'       => $doubleType,
        ];

        return [
            'tournamentID'    => (int)$row['tournamentID'],
            'eventID'         => (int)$row['eventID'],
            'name'            => $row['name'],
            'isTeams'         => (bool)(int)$row['isTeams'],
            'isFinalized'     => (bool)(int)$row['isFinalized'],
            'format'          => [
                'formatID' => (int)$row['formatID'],
                'name'     => $row['formatName'],
            ],
            'numParticipants' => (int)$row['numParticipants'],
            'attributes'      => $attributes,
            'ranking'         => $ranking,
            'isPrivate'       => (bool)(int)$row['isPrivate'],
            'hideFinalResults'=> (bool)(int)$row['hideFinalResults'],
            'rulesets'        => array_map(function($r) {
                return [
                    'rulesID'   => (int)$r['rulesID'],
                    'rulesName' => $r['rulesName'],
                ];
            }, $rulesets),
            'scoring'         => $scoring,
        ];
    }
}
