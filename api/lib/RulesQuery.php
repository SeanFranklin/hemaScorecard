<?php
namespace HemaScorecard\Api\Lib;

class RulesQuery {

    /**
     * All rulesets for an event. Ordered by rulesOrder then rulesName.
     */
    public static function listForEvent(int $eventID): array {
        $eventID = (int)$eventID;
        $sql = "SELECT rulesID, rulesName, rulesOrder
                FROM eventRules
                WHERE eventID = {$eventID}
                ORDER BY rulesOrder ASC, rulesName ASC";
        return mysqlQuery($sql, ASSOC);
    }

    /**
     * Find a single ruleset, but only if it belongs to $eventID. Returns
     * null if missing OR belongs to a different event.
     */
    public static function findForEvent(int $eventID, int $rulesID): ?array {
        $eventID = (int)$eventID;
        $rulesID = (int)$rulesID;
        if ($eventID <= 0 || $rulesID <= 0) {
            return null;
        }
        $sql = "SELECT rulesID, rulesName, rulesOrder, rulesText
                FROM eventRules
                WHERE rulesID = {$rulesID}
                AND eventID = {$eventID}
                LIMIT 1";
        $row = mysqlQuery($sql, SINGLE);
        return $row ?: null;
    }

    /**
     * Tournaments a ruleset is attached to, with composed names.
     *
     * Name composition follows the "prefix" style from the web app's
     * getTournamentName(): <prefix> <gender> <material> <weapon> <suffix>,
     * null/empty parts omitted. When the tournaments endpoint group lands
     * this may be extracted into a shared helper.
     */
    public static function listLinkedTournaments(int $rulesID): array {
        $rulesID = (int)$rulesID;
        $nameExpr = TournamentNames::composedNameExpr();
        $joinChain = TournamentNames::joinClauses('et');

        $sql = "SELECT
                    et.tournamentID AS tournamentID,
                    {$nameExpr}     AS name
                FROM eventRulesLinks erl
                INNER JOIN eventTournaments et ON et.tournamentID = erl.tournamentID
                {$joinChain}
                WHERE erl.rulesID = {$rulesID}
                ORDER BY et.tournamentID ASC";
        return mysqlQuery($sql, ASSOC);
    }
}
