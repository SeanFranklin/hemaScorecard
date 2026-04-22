<?php
namespace HemaScorecard\Api\Controllers;

use HemaScorecard\Api\Lib\ApiException;
use HemaScorecard\Api\Lib\EventsQuery;
use HemaScorecard\Api\Lib\JsonResponse;
use HemaScorecard\Api\Lib\Pagination;

class EventsController {

    public function index(): void {
        $p = Pagination::parse($_GET);
        $rows = EventsQuery::listPublished($p->offset, $p->perPage);
        $total = EventsQuery::countPublished();

        JsonResponse::success(
            array_map([$this, 'shapeListItem'], $rows),
            Pagination::meta($p, $total, count($rows))
        );
    }

    public function today(): void {
        $this->emitList(EventsQuery::today());
    }

    public function upcoming(): void {
        $this->emitList(EventsQuery::upcoming());
    }

    public function recent(): void {
        $this->emitList(EventsQuery::recent());
    }

    public function show(string $id): void {
        $idInt = (int)$id;
        $row = EventsQuery::findById($idInt);
        if ($row === null) {
            throw new ApiException('not_found', 404, "Event {$id} not found");
        }

        $tournamentCount = EventsQuery::countTournaments($idInt);
        $rosterCount     = EventsQuery::countRoster($idInt);

        JsonResponse::success($this->shapeSingle($row, $tournamentCount, $rosterCount));
    }

    /**
     * Convert the findById row into the full single-event response shape,
     * applying the "suppress description if not published and not archived"
     * rule from the design spec.
     */
    private function shapeSingle(array $row, int $tournamentCount, int $rosterCount): array {
        $isArchived = (bool)(int)$row['isArchived'];
        $publishDescription = (bool)(int)$row['publishDescription'];

        // Suppress description text unless it's archived OR publishDescription=1.
        // Archived events are treated as fully published (matching existing web helpers).
        $descriptionVisible = $isArchived || $publishDescription;
        $description = $descriptionVisible ? $row['descriptionRaw'] : null;

        // Publication flags — archived treats all as true (matches isRosterPublished()
        // / isSchedulePublished() / etc. behavior for archived events in the web app).
        $pub = [
            'description' => $isArchived || (bool)(int)$row['publishDescription'],
            'roster'      => $isArchived || (bool)(int)$row['publishRoster'],
            'schedule'    => $isArchived || (bool)(int)$row['publishSchedule'],
            'matches'     => $isArchived || (bool)(int)$row['publishMatches'],
            'rules'       => $isArchived || (bool)(int)$row['publishRules'],
        ];

        return [
            'eventID'         => (int)$row['eventID'],
            'name'            => $row['name'],
            'abbreviation'    => $row['abbreviation'],
            'year'            => $row['year'] !== null ? (int)$row['year'] : null,
            'startDate'       => $row['startDate'],
            'endDate'         => $row['endDate'],
            'city'            => $row['city'],
            'province'        => $row['province'],
            'countryIso2'     => $row['countryIso2'],
            'countryName'     => $row['countryName'],
            'status'          => $row['status'],
            'isMetaEvent'     => (bool)(int)$row['isMetaEvent'],
            'regionCode'      => $row['regionCode'] !== null ? (int)$row['regionCode'] : null,
            'description'     => $description,
            'publication'     => $pub,
            'tournamentCount' => $tournamentCount,
            'rosterCount'     => $rosterCount,
        ];
    }

    /**
     * Shared emit for the three bounded endpoints. No pagination —
     * returns the whole bounded window plus a {count} meta block.
     */
    private function emitList(array $rows): void {
        JsonResponse::success(
            array_map([$this, 'shapeListItem'], $rows),
            ['count' => count($rows)]
        );
    }

    /**
     * Convert a DB row (aliased by EventsQuery::baseSelect) into the
     * API list-item shape. Handles type coercion that SQL doesn't do
     * (booleans come back as "0"/"1" strings from mysqli).
     */
    private function shapeListItem(array $row): array {
        return [
            'eventID'      => (int)$row['eventID'],
            'name'         => $row['name'],
            'abbreviation' => $row['abbreviation'],
            'year'         => $row['year'] !== null ? (int)$row['year'] : null,
            'startDate'    => $row['startDate'],
            'endDate'      => $row['endDate'],
            'city'         => $row['city'],
            'province'     => $row['province'],
            'countryIso2'  => $row['countryIso2'],
            'countryName'  => $row['countryName'],
            'status'       => $row['status'],
            'isMetaEvent'  => (bool)(int)$row['isMetaEvent'],
        ];
    }
}
