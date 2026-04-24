<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\EventsQuery;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\Pagination;
use HemaScorecard\Api\Lib\RosterQuery;

class RosterController {

    private const PER_PAGE_DEFAULT = 50;
    private const PER_PAGE_MAX = 200;

    public function index(string $eventID): void {
        $id = (int)$eventID;
        $gate = EventsQuery::findVisibleForGate($id);
        if ($gate === null) {
            throw new ApiException('not_found', 404, "Event {$id} not found");
        }

        $p = Pagination::parse($_GET, self::PER_PAGE_DEFAULT, self::PER_PAGE_MAX);

        $rosterVisible = $gate['isArchived'] || $gate['publishRoster'];
        if (!$rosterVisible) {
            JsonResponse::success([], Pagination::meta($p, 0, 0));
            return;
        }

        $rows = RosterQuery::listForEvent($id, $p->offset, $p->perPage);
        $total = RosterQuery::countForEvent($id);

        // Collect team rosterIDs from this page to fetch member lists in one query.
        $teamIDs = [];
        foreach ($rows as $row) {
            if ((int)$row['isTeam'] === 1) {
                $teamIDs[] = (int)$row['rosterID'];
            }
        }
        $membersByTeam = RosterQuery::fetchTeamMembers($teamIDs);

        $shaped = array_map(function($row) use ($membersByTeam) {
            return RosterQuery::shapeEntry($row, $membersByTeam);
        }, $rows);

        JsonResponse::success($shaped, Pagination::meta($p, $total, count($shaped)));
    }
}
