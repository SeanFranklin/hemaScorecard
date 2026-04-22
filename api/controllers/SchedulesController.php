<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\EventsQuery;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\ScheduleBlocks;
use HemaScorecard\Api\Lib\SchedulesQuery;

class SchedulesController {

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
        $gate = EventsQuery::findVisibleForGate($eventID);
        if ($gate === null) {
            throw new ApiException('not_found', 404, "Event {$eventID} not found");
        }
        if (!($gate['isArchived'] || $gate['publishSchedule'])) {
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

        // Group by dayNum, omitting empty days.
        $byDay = [];
        foreach ($shaped as $block) {
            $byDay[$block['dayNum']][] = $block;
        }
        ksort($byDay, SORT_NUMERIC);

        $days = [];
        foreach ($byDay as $d => $blocks) {
            $days[] = ['dayNum' => $d, 'blocks' => $blocks];
        }

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
