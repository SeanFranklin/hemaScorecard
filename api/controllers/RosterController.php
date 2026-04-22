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
            throw new ApiException('not_found', 404, "Event {$eventID} not found");
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
            return $this->shapeItem($row, $membersByTeam);
        }, $rows);

        JsonResponse::success($shaped, Pagination::meta($p, $total, count($shaped)));
    }

    private function shapeItem(array $row, array $membersByTeam): array {
        $isTeam = (int)$row['isTeam'] === 1;
        $rosterID = (int)$row['rosterID'];

        $school = null;
        if ($row['resolvedSchoolID'] !== null) {
            $school = [
                'schoolID'     => (int)$row['resolvedSchoolID'],
                'name'         => $row['schoolName'],
                'shortName'    => $row['schoolShortName'],
                'abbreviation' => $row['schoolAbbreviation'],
            ];
        }

        if ($isTeam) {
            return [
                'rosterID'       => $rosterID,
                'systemRosterID' => $row['systemRosterID'] !== null ? (int)$row['systemRosterID'] : null,
                'isTeam'         => true,
                'firstName'      => null,
                'middleName'     => null,
                'lastName'       => null,
                'nickname'       => null,
                'gender'         => null,
                'hemaRatingsID'  => null,
                'location'       => null,
                'school'         => $school,
                'teamName'       => $row['teamName'],
                'teamMembers'    => $membersByTeam[$rosterID] ?? [],
                'checkedIn'      => (bool)(int)$row['eventCheckIn'],
                'waiverSigned'   => (bool)(int)$row['eventWaiver'],
                'publicNotes'    => $row['publicNotes'],
            ];
        }

        $location = null;
        if ($row['rosterCity'] !== null || $row['rosterProvince'] !== null || $row['rosterCountry'] !== null) {
            $location = [
                'city'     => $row['rosterCity'],
                'province' => $row['rosterProvince'],
                'country'  => $row['rosterCountry'],
            ];
        }

        return [
            'rosterID'       => $rosterID,
            'systemRosterID' => $row['systemRosterID'] !== null ? (int)$row['systemRosterID'] : null,
            'isTeam'         => false,
            'firstName'      => $row['firstName'],
            'middleName'     => $row['middleName'],
            'lastName'       => $row['lastName'],
            'nickname'       => $row['nickname'],
            'gender'         => $row['gender'],
            'hemaRatingsID'  => $row['hemaRatingsID'] !== null ? (int)$row['hemaRatingsID'] : null,
            'location'       => $location,
            'school'         => $school,
            'teamName'       => null,
            'teamMembers'    => null,
            'checkedIn'      => (bool)(int)$row['eventCheckIn'],
            'waiverSigned'   => (bool)(int)$row['eventWaiver'],
            'publicNotes'    => $row['publicNotes'],
        ];
    }
}
