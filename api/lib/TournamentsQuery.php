<?php
namespace HemaScorecard\Api\Lib;

class TournamentsQuery {

    /**
     * List all tournaments for an event with minimal per-row fields.
     * Sort: organizer-defined sortOrder (from eventTournamentOrder) first,
     * then alphabetical by composed name, then tournamentID as tiebreaker.
     */
    public static function listForEvent(int $eventID): array {
        $eventID = (int)$eventID;
        $nameExpr  = TournamentNames::composedNameExpr();
        $joinChain = TournamentNames::joinClauses('eT');

        $sql = "SELECT
                    eT.tournamentID    AS tournamentID,
                    eT.eventID         AS eventID,
                    eT.isTeams         AS isTeams,
                    eT.isFinalized     AS isFinalized,
                    eT.formatID        AS formatID,
                    sF.formatName      AS formatName,
                    eT.numParticipants AS numParticipants,
                    {$nameExpr}        AS name
                FROM eventTournaments eT
                INNER JOIN systemFormats sF ON sF.formatID = eT.formatID
                {$joinChain}
                LEFT JOIN eventTournamentOrder eTO ON eTO.tournamentID = eT.tournamentID
                WHERE eT.eventID = {$eventID}
                ORDER BY COALESCE(eTO.sortOrder, 999999) ASC, name ASC, eT.tournamentID ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Fetch a single tournament's detail row, but only if it belongs to
     * $eventID. Returns null for missing or cross-event tournaments.
     *
     * Joins in: format (INNER), weapon/prefix/gender/material/suffix
     * (via TournamentNames::joinClauses), systemRankings (LEFT),
     * systemDoubleTypes (LEFT).
     */
    public static function findForEvent(int $eventID, int $tournamentID): ?array {
        $eventID = (int)$eventID;
        $tournamentID = (int)$tournamentID;
        if ($eventID <= 0 || $tournamentID <= 0) {
            return null;
        }

        $nameExpr  = TournamentNames::composedNameExpr();
        $joinChain = TournamentNames::joinClauses('eT');

        $sql = "SELECT
                    eT.tournamentID    AS tournamentID,
                    eT.eventID         AS eventID,
                    eT.isTeams         AS isTeams,
                    eT.isFinalized     AS isFinalized,
                    eT.isPrivate       AS isPrivate,
                    eT.hideFinalResults AS hideFinalResults,
                    eT.formatID        AS formatID,
                    sF.formatName      AS formatName,
                    eT.numParticipants AS numParticipants,
                    eT.tournamentRankingID AS rankingID,
                    sR.name            AS rankingName,
                    eT.doubleTypeID    AS doubleTypeID,
                    sDT.doubleTypeName AS doubleTypeName,
                    eT.maxPoolSize      AS maxPoolSize,
                    eT.maxDoubleHits    AS maxDoubleHits,
                    eT.maximumExchanges AS maximumExchanges,
                    eT.maximumPoints    AS maximumPoints,
                    eT.maxPointSpread   AS maxPointSpread,
                    eT.basePointValue   AS basePointValue,
                    eT.allowTies        AS allowTies,
                    eT.timerCountdown   AS timerCountdown,
                    eT.timeLimit        AS timeLimit,
                    eT.isNotNetScore    AS isNotNetScore,
                    eT.isReverseScore   AS isReverseScore,
                    weapon.tournamentTypeID AS weaponID,
                    weapon.tournamentType   AS weaponName,
                    prefix.tournamentTypeID AS prefixID,
                    prefix.tournamentType   AS prefixName,
                    gender.tournamentTypeID AS genderID,
                    gender.tournamentType   AS genderName,
                    material.tournamentTypeID AS materialID,
                    material.tournamentType   AS materialName,
                    suffix.tournamentTypeID AS suffixID,
                    suffix.tournamentType   AS suffixName,
                    {$nameExpr} AS name
                FROM eventTournaments eT
                INNER JOIN systemFormats sF ON sF.formatID = eT.formatID
                {$joinChain}
                LEFT JOIN systemRankings sR ON sR.tournamentRankingID = eT.tournamentRankingID
                LEFT JOIN systemDoubleTypes sDT ON sDT.doubleTypeID = eT.doubleTypeID
                WHERE eT.tournamentID = {$tournamentID}
                AND eT.eventID = {$eventID}
                LIMIT 1";
        $row = mysqlQuery($sql, SINGLE);
        return $row ?: null;
    }

    /**
     * Rulesets attached to a tournament (inverse of RulesQuery's linked-
     * tournaments). Returns [{rulesID, rulesName}, ...].
     */
    public static function listRulesets(int $tournamentID): array {
        $tournamentID = (int)$tournamentID;
        $sql = "SELECT eR.rulesID, eR.rulesName
                FROM eventRulesLinks erl
                INNER JOIN eventRules eR ON eR.rulesID = erl.rulesID
                WHERE erl.tournamentID = {$tournamentID}
                ORDER BY eR.rulesOrder ASC, eR.rulesName ASC, eR.rulesID ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Confirm tournament $tid belongs to event $eid. Used by controllers
     * before list-style tournament-scoped queries.
     */
    public static function belongsToEvent(int $eventID, int $tournamentID): bool {
        if ($eventID <= 0 || $tournamentID <= 0) {
            return false;
        }
        $sql = "SELECT 1 FROM eventTournaments
                WHERE tournamentID = {$tournamentID} AND eventID = {$eventID}
                LIMIT 1";
        return (bool)mysqlQuery($sql, SINGLE);
    }
}
