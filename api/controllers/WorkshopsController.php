<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\EventsQuery;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\ScheduleBlocks;
use HemaScorecard\Api\Lib\WorkshopsQuery;

class WorkshopsController {

    public function index(string $eventID): void {
        $id = (int)$eventID;
        $gate = EventsQuery::findVisibleForGate($id);
        if ($gate === null) {
            throw new ApiException('not_found', 404, "Event {$id} not found");
        }

        if (!($gate['isArchived'] || $gate['publishSchedule'])) {
            JsonResponse::success([], ['count' => 0]);
            return;
        }

        $rows = WorkshopsQuery::listForEvent($id);
        $rows = ScheduleBlocks::enrichWithLocations($rows);

        $blockIDs = array_map(function($r) { return (int)$r['blockID']; }, $rows);
        $attrs = WorkshopsQuery::fetchAttributes($blockIDs);
        $instructors = WorkshopsQuery::fetchInstructors($blockIDs);

        $shaped = array_map(function($row) use ($attrs, $instructors) {
            return $this->shapeSingle($row, $attrs, $instructors);
        }, $rows);

        JsonResponse::success($shaped, ['count' => count($shaped)]);
    }

    public function show(string $eventID, string $blockID): void {
        $eid = (int)$eventID;
        $bid = (int)$blockID;

        $gate = EventsQuery::findVisibleForGate($eid);
        if ($gate === null) {
            throw new ApiException('not_found', 404, "Event {$eid} not found");
        }

        if (!($gate['isArchived'] || $gate['publishSchedule'])) {
            throw new ApiException('not_found', 404, "Workshop {$bid} not found");
        }

        $row = WorkshopsQuery::findForEvent($eid, $bid);
        if ($row === null) {
            throw new ApiException('not_found', 404, "Workshop {$bid} not found");
        }

        $rows = ScheduleBlocks::enrichWithLocations([$row]);
        $attrs = WorkshopsQuery::fetchAttributes([$bid]);
        $instructors = WorkshopsQuery::fetchInstructors([$bid]);

        JsonResponse::success($this->shapeSingle($rows[0], $attrs, $instructors));
    }

    private function shapeSingle(array $row, array $attrs, array $instructors): array {
        $core = ScheduleBlocks::shape($row);
        $bid = (int)$row['blockID'];
        $core['experience']  = $attrs[$bid]['experience']  ?? null;
        $core['equipment']   = $attrs[$bid]['equipment']   ?? null;
        $core['instructors'] = $instructors[$bid] ?? [];
        return $core;
    }
}
