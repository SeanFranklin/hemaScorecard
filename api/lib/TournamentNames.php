<?php
namespace HemaScorecard\Api\Lib;

class TournamentNames {

    /**
     * SQL fragment that builds the composed "prefix" style tournament name
     * from a 5-way joined systemTournaments alias set. Callers must use
     * the aliases `prefix`, `gender`, `material`, `weapon`, `suffix` in
     * their JOIN chain (see joinClauses below).
     *
     * Format: "<prefix> <gender> <material> <weapon> <suffix>", with null
     * and empty-string components omitted.
     */
    public static function composedNameExpr(): string {
        return "TRIM(CONCAT_WS(' ',
            NULLIF(prefix.tournamentType,   ''),
            NULLIF(gender.tournamentType,   ''),
            NULLIF(material.tournamentType, ''),
            weapon.tournamentType,
            NULLIF(suffix.tournamentType,   '')
        ))";
    }

    /**
     * The 5-way JOIN chain against systemTournaments for a given
     * eventTournaments table alias. Weapon is INNER (required — weapon
     * is NOT NULL in the schema); the other four are LEFT (nullable).
     */
    public static function joinClauses(string $tournamentTableAlias): string {
        $t = $tournamentTableAlias;
        return "
            INNER JOIN systemTournaments weapon   ON {$t}.tournamentWeaponID   = weapon.tournamentTypeID
            LEFT JOIN  systemTournaments prefix   ON {$t}.tournamentPrefixID   = prefix.tournamentTypeID
            LEFT JOIN  systemTournaments gender   ON {$t}.tournamentGenderID   = gender.tournamentTypeID
            LEFT JOIN  systemTournaments material ON {$t}.tournamentMaterialID = material.tournamentTypeID
            LEFT JOIN  systemTournaments suffix   ON {$t}.tournamentSuffixID   = suffix.tournamentTypeID
        ";
    }
}
