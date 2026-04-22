<?php
namespace HemaScorecard\Api\Controllers;

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
