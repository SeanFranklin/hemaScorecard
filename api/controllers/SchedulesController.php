<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\ChecksEventVisibility;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\ScheduleBlocks;
use HemaScorecard\Api\Lib\SchedulesQuery;

class SchedulesController {

    use ChecksEventVisibility;

    // ----- Public action methods (route targets) -----

    public function mainAll(string $eventID): void {
        $this->mainCore($eventID, null);
    }

    public function mainDay(string $eventID, string $dayNum): void {
        $this->mainCore($eventID, (int)$dayNum);
    }

    public function workshopsAll(string $eventID): void {
        $this->workshopsCore($eventID, null);
    }

    public function workshopsDay(string $eventID, string $dayNum): void {
        $this->workshopsCore($eventID, (int)$dayNum);
    }

    public function schoolAll(string $eventID, string $schoolID): void {
        $this->schoolCore($eventID, $schoolID, null);
    }

    public function schoolDay(string $eventID, string $schoolID, string $dayNum): void {
        $this->schoolCore($eventID, $schoolID, (int)$dayNum);
    }

    public function personalAll(string $eventID, string $rosterID): void {
        $this->personalCore($eventID, $rosterID, null);
    }

    public function personalDay(string $eventID, string $rosterID, string $dayNum): void {
        $this->personalCore($eventID, $rosterID, (int)$dayNum);
    }

    public function locationAll(string $eventID, string $locationID): void {
        $this->locationCore($eventID, $locationID, null);
    }

    public function locationDay(string $eventID, string $locationID, string $dayNum): void {
        $this->locationCore($eventID, $locationID, (int)$dayNum);
    }

    // ----- Private cores -----

    private function mainCore(string $eventID, ?int $dayNum): void {
        $id = (int)$eventID;
        $gate = $this->gateOrThrow($id, $dayNum);
        if ($gate === null) { return; }  // already emitted empty

        $rows = SchedulesQuery::main($id, $dayNum);
        $this->emitBlocks($rows, $dayNum);
    }

    private function workshopsCore(string $eventID, ?int $dayNum): void {
        $id = (int)$eventID;
        $gate = $this->gateOrThrow($id, $dayNum);
        if ($gate === null) { return; }

        $rows = SchedulesQuery::workshops($id, $dayNum);
        $this->emitBlocks($rows, $dayNum);
    }

    private function schoolCore(string $eventID, string $schoolID, ?int $dayNum): void {
        $eid = (int)$eventID;
        $sid = (int)$schoolID;

        $gate = $this->gateOrThrow($eid, $dayNum);
        if ($gate === null) { return; }

        if (!SchedulesQuery::schoolExists($sid)) {
            throw new ApiException('not_found', 404, "School {$sid} not found");
        }

        $rows = SchedulesQuery::school($eid, $sid, $dayNum);
        $this->emitBlocks($rows, $dayNum);
    }

    private function personalCore(string $eventID, string $rosterID, ?int $dayNum): void {
        $eid = (int)$eventID;
        $rid = (int)$rosterID;

        $gate = $this->gateOrThrow($eid, $dayNum);
        if ($gate === null) { return; }

        if (!SchedulesQuery::participantBelongsToEvent($rid, $eid)) {
            throw new ApiException('not_found', 404, "Participant {$rid} not found");
        }

        $rows = SchedulesQuery::personal($eid, $rid, $dayNum);
        $rows = ScheduleBlocks::enrichWithLocations($rows);

        $blockIDs = array_map(function($r) { return (int)$r['blockID']; }, $rows);
        $participation = SchedulesQuery::fetchPersonalParticipation($rid, $blockIDs);

        $shaped = array_map(function($row) use ($participation) {
            $block = ScheduleBlocks::shape($row);
            $block['participation'] = $participation[(int)$row['blockID']] ?? [];
            return $block;
        }, $rows);

        // Personal needs participation merged into each block's shape, so
        // emitBlocks (which uses ScheduleBlocks::shape alone) isn't usable.
        // We shape individually above, then delegate day-grouping to the
        // same helper emitBlocks uses.
        if ($dayNum !== null) {
            JsonResponse::success($shaped, ['dayNum' => $dayNum, 'count' => count($shaped)]);
            return;
        }

        $days = ScheduleBlocks::groupByDay($shaped);
        JsonResponse::success($days, [
            'dayCount'   => count($days),
            'blockCount' => count($shaped),
        ]);
    }

    private function locationCore(string $eventID, string $locationID, ?int $dayNum): void {
        $eid = (int)$eventID;
        $lid = (int)$locationID;

        $gate = $this->gateOrThrow($eid, $dayNum);
        if ($gate === null) { return; }

        if (!SchedulesQuery::locationBelongsToEvent($lid, $eid)) {
            throw new ApiException('not_found', 404, "Location {$lid} not found");
        }

        $rows = SchedulesQuery::location($eid, $lid, $dayNum);
        $this->emitBlocks($rows, $dayNum);
    }

    // ----- Shared helpers -----

    /**
     * Runs the event-visibility + publishSchedule gate. Returns the gate
     * struct on success. On failure:
     *   - hidden/missing event → throws 404 ApiException
     *   - visible event but publishSchedule=0 and not archived →
     *     emits an empty response (day-grouped or single-day based on
     *     whether the caller passed a $dayNum); caller returns null.
     *
     * Returns null only when empty response was emitted.
     */
    protected function gateOrThrow(int $eventID, ?int $dayNum = null): ?array {
        $gate = $this->findVisibleEventOrThrow($eventID);
        if (!$this->isResourceVisible($gate, 'publishSchedule')) {
            $this->emitEmpty($dayNum);
            return null;
        }
        return $gate;
    }

    /**
     * Enriches + shapes + emits block rows. If $dayNum is null the
     * response is day-grouped; otherwise flat for that day.
     */
    protected function emitBlocks(array $rows, ?int $dayNum): void {
        $rows = ScheduleBlocks::enrichWithLocations($rows);
        $shaped = array_map([ScheduleBlocks::class, 'shape'], $rows);

        if ($dayNum !== null) {
            JsonResponse::success($shaped, ['dayNum' => $dayNum, 'count' => count($shaped)]);
            return;
        }

        $days = ScheduleBlocks::groupByDay($shaped);
        JsonResponse::success($days, [
            'dayCount'   => count($days),
            'blockCount' => count($shaped),
        ]);
    }

    protected function emitEmpty(?int $dayNum): void {
        if ($dayNum !== null) {
            JsonResponse::success([], ['dayNum' => $dayNum, 'count' => 0]);
        } else {
            JsonResponse::success([], ['dayCount' => 0, 'blockCount' => 0]);
        }
    }
}
