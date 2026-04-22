<?php
namespace HemaScorecard\Api\Lib;

class ScheduleBlocks {

    private const BLOCK_TYPE_LABELS = [
        1 => 'tournament',
        2 => 'workshop',
        3 => 'staffing',
        4 => 'misc',
    ];

    /**
     * Run a raw SELECT over logisticsScheduleBlocks with the caller's
     * WHERE fragment and ORDER BY clause. Integer IDs must already be
     * cast-interpolated into $whereFragment by the caller.
     */
    public static function select(string $whereFragment, string $orderBy): array {
        $sql = "SELECT
                    blockID, eventID, dayNum, startTime, endTime,
                    blockTypeID, tournamentID,
                    blockTitle, blockSubtitle, blockDescription,
                    blockLink, blockLinkDescription
                FROM logisticsScheduleBlocks
                WHERE {$whereFragment}
                ORDER BY {$orderBy}";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Batch-fetch locations for a list of block rows and attach each
     * row's locations array under the 'locations' key.
     *
     * Uses one query regardless of row count. Rows with no locations
     * get 'locations' => [].
     */
    public static function enrichWithLocations(array $rows): array {
        if (empty($rows)) {
            return $rows;
        }

        $blockIDs = array_map(function($r) { return (int)$r['blockID']; }, $rows);
        $inClause = implode(',', $blockIDs);

        $sql = "SELECT
                    lLB.blockID     AS blockID,
                    lL.locationID   AS locationID,
                    lL.locationName AS name,
                    lL.locationNameShort AS shortName,
                    lL.locationOrder AS locationOrder
                FROM logisticsLocationsBlocks lLB
                INNER JOIN logisticsLocations lL ON lL.locationID = lLB.locationID
                WHERE lLB.blockID IN ({$inClause})
                ORDER BY lL.locationOrder ASC, lL.locationName ASC";
        $locRows = mysqlQuery($sql, ASSOC);

        $byBlock = [];
        foreach ($rows as $r) {
            $byBlock[(int)$r['blockID']] = [];
        }
        foreach ($locRows as $lr) {
            $byBlock[(int)$lr['blockID']][] = [
                'locationID' => (int)$lr['locationID'],
                'name'       => $lr['name'],
                'shortName'  => $lr['shortName'],
            ];
        }

        foreach ($rows as &$r) {
            $r['locations'] = $byBlock[(int)$r['blockID']] ?? [];
        }
        unset($r);

        return $rows;
    }

    /**
     * Convert an enriched block row (post-enrichWithLocations) to the
     * core API response shape. Does not emit the 'participation' field —
     * callers layer that on for personal schedules.
     */
    public static function shape(array $row): array {
        return [
            'blockID'         => (int)$row['blockID'],
            'blockType'       => self::blockTypeLabel((int)$row['blockTypeID']),
            'dayNum'          => (int)$row['dayNum'],
            'startTime'       => TimeFormat::minutesToHhmm((int)$row['startTime']),
            'endTime'         => TimeFormat::minutesToHhmm((int)$row['endTime']),
            'title'           => $row['blockTitle'],
            'subtitle'        => $row['blockSubtitle'],
            'description'     => $row['blockDescription'],
            'link'            => $row['blockLink'],
            'linkDescription' => $row['blockLinkDescription'],
            'tournamentID'    => $row['tournamentID'] !== null ? (int)$row['tournamentID'] : null,
            'locations'       => $row['locations'] ?? [],
        ];
    }

    public static function blockTypeLabel(int $blockTypeID): string {
        return self::BLOCK_TYPE_LABELS[$blockTypeID] ?? 'misc';
    }
}
