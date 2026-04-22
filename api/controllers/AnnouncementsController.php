<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\AnnouncementsQuery;
use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\EventsQuery;
use HemaScorecard\Api\Lib\JsonResponse;

class AnnouncementsController {

    public function index(string $eventID): void {
        $id = (int)$eventID;
        $gate = EventsQuery::findVisibleForGate($id);
        if ($gate === null) {
            throw new ApiException('not_found', 404, "Event {$eventID} not found");
        }

        $rows = AnnouncementsQuery::listForEvent($id);

        JsonResponse::success(
            array_map([$this, 'shapeItem'], $rows),
            ['count' => count($rows)]
        );
    }

    private function shapeItem(array $row): array {
        return [
            'announcementID' => (int)$row['announcementID'],
            'message'        => $row['message'],
            'displayUntil'   => gmdate('Y-m-d\TH:i:s\Z', (int)$row['displayUntil']),
            'isGlobal'       => $row['eventID'] === null,
        ];
    }
}
