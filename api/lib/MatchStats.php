<?php
namespace HemaScorecard\Api\Lib;

class MatchStats {

    // Card severity codes — stable systemAttacks.attackCode values for
    // attackIDs 34/35/38 (PENALTY_CARD_YELLOW/RED/BLACK in includes/config.php).
    // Matching on code rather than numeric ID because ExchangesQuery wraps
    // refType through AttacksVocabulary before it reaches here.
    private const CODE_YELLOW = 'yellowCard';
    private const CODE_RED    = 'redCard';

    // Exchange types that count as "valid scoring exchanges" — parity with
    // the set used by updateStandings() / stats_functions.php.
    private const VALID_EXCHANGE_TYPES = ['clean', 'afterblow', 'double', 'noExchange'];

    /**
     * Compute per-match aggregate stats from an already-loaded exchange list
     * (as produced by ExchangesQuery::forMatch). Pure function — no DB.
     *
     * Penalties are keyed by scoringRosterID (the penalized fighter). Both
     * fighter1ID and fighter2ID always appear as entries, seeded with zero
     * counts, so clients can render a "0 cards" row without null checks.
     * For team matches where sub-match fighters get carded, their rosterIDs
     * appear as additional entries beyond the two top-level fighters.
     */
    public static function computeFromExchanges(array $exchanges, ?int $fighter1ID, ?int $fighter2ID): array {
        $exchangeCount = 0;
        $doublesCount  = 0;

        $penalties = [];
        $seedZero = function(?int $rosterID) use (&$penalties): void {
            if ($rosterID === null) { return; }
            if (!isset($penalties[$rosterID])) {
                $penalties[$rosterID] = ['rosterID' => $rosterID, 'total' => 0, 'yellow' => 0, 'red' => 0];
            }
        };
        $seedZero($fighter1ID);
        $seedZero($fighter2ID);

        foreach ($exchanges as $ex) {
            $type = $ex['exchangeType'];

            if (in_array($type, self::VALID_EXCHANGE_TYPES, true)) {
                $exchangeCount++;
                if ($type === 'double') {
                    $doublesCount++;
                }
                continue;
            }

            if ($type !== 'penalty') {
                continue;
            }

            $rosterID = $ex['scoringRosterID'];
            if ($rosterID === null) {
                continue;
            }
            $seedZero($rosterID);
            $penalties[$rosterID]['total']++;

            $code = $ex['attack']['type']['code'] ?? null;
            if ($code === self::CODE_YELLOW) {
                $penalties[$rosterID]['yellow']++;
            } elseif ($code === self::CODE_RED) {
                $penalties[$rosterID]['red']++;
            }
        }

        // Sort: fighter1 first, fighter2 second, remainder by rosterID ASC.
        $ordered = [];
        foreach ([$fighter1ID, $fighter2ID] as $id) {
            if ($id !== null && isset($penalties[$id])) {
                $ordered[] = $penalties[$id];
                unset($penalties[$id]);
            }
        }
        ksort($penalties);
        foreach ($penalties as $row) {
            $ordered[] = $row;
        }

        return [
            'exchangeCount' => $exchangeCount,
            'doublesCount'  => $doublesCount,
            'penalties'     => $ordered,
        ];
    }
}
