-- Teardown: clear all smoke-managed rows in reverse-FK order before reseeding.
-- Each group's SQL repeats its own DELETEs, but those fail on re-run when upstream
-- groups try to wipe eventTournaments while downstream groups (placings, standings,
-- matches, exchanges) still reference them. This file drains all children first so
-- subsequent group-*.sql files can run their own DELETE+INSERT from a blank slate.

-- Group 6 child rows (bracket matches, exchanges, placings, bracket groups)
DELETE FROM eventExchanges           WHERE matchID            BETWEEN 12100 AND 12199;
DELETE FROM eventMatchOptions        WHERE matchID            BETWEEN 12100 AND 12199;
DELETE FROM logisticsLocationsMatches WHERE matchID           BETWEEN 12100 AND 12199;
DELETE FROM eventMatches             WHERE matchID            BETWEEN 12100 AND 12199;
DELETE FROM eventGroupRoster         WHERE groupID            BETWEEN 9700 AND 9799;
DELETE FROM eventGroups              WHERE groupID            BETWEEN 9700 AND 9799;
DELETE FROM eventPlacings            WHERE tournamentID       IN (9101, 9104);

-- Group 5 child rows (pool matches, exchanges, standings, group rankings, pool groups)
DELETE FROM eventExchanges           WHERE matchID            BETWEEN 9601 AND 9699;
DELETE FROM eventMatchOptions        WHERE matchID            BETWEEN 9601 AND 9699;
DELETE FROM logisticsLocationsMatches WHERE matchID           BETWEEN 9601 AND 9699;
DELETE FROM eventMatches             WHERE matchID            BETWEEN 9601 AND 9699;
DELETE FROM eventStandings           WHERE groupID            BETWEEN 9501 AND 9599;
DELETE FROM eventGroupRankings       WHERE groupID            BETWEEN 9501 AND 9599;
DELETE FROM eventGroupRoster         WHERE groupID            BETWEEN 9501 AND 9599;
DELETE FROM eventGroups              WHERE groupID            BETWEEN 9501 AND 9599;

-- Group 5 + 6 tournament-roster and extra event-roster rows
DELETE FROM eventTournamentRoster    WHERE tournamentRosterID BETWEEN 9270 AND 9299;
DELETE FROM eventTournamentRoster    WHERE tournamentID       IN (9103, 9104, 9105, 9106);
DELETE FROM eventRoster              WHERE rosterID           BETWEEN 9270 AND 9299;

-- Group 5 + 6 extra tournaments (9103, 9104, 9105, 9106)
DELETE FROM eventTournaments         WHERE tournamentID       IN (9103, 9104, 9105, 9106);

-- Group 3 child rows (logistics, announcements, rules, descriptions, base rosters, base tournaments)
DELETE FROM logisticsStaffShifts      WHERE shiftID      BETWEEN 4500 AND 4599;
DELETE FROM logisticsScheduleShifts   WHERE shiftID      BETWEEN 4500 AND 4599;
DELETE FROM logisticsInstructors      WHERE instructorID BETWEEN 4600 AND 4699;
DELETE FROM logisticsBlockAttributes  WHERE blockAttributeID BETWEEN 4700 AND 4799;
DELETE FROM logisticsLocationsBlocks  WHERE blockLocationID  BETWEEN 4800 AND 4899;
DELETE FROM logisticsScheduleBlocks   WHERE blockID      BETWEEN 4400 AND 4499;
DELETE FROM logisticsLocations        WHERE locationID   BETWEEN 6001 AND 6099;
DELETE FROM eventTournamentRoster    WHERE rosterID     BETWEEN 9200 AND 9299;
DELETE FROM eventTeamRoster           WHERE tableID      BETWEEN 9500 AND 9599;
DELETE FROM logisticsAnnouncements    WHERE announcementID BETWEEN 9400 AND 9499;
DELETE FROM eventRulesLinks           WHERE rulesID      BETWEEN 9300 AND 9399;
DELETE FROM eventRules                WHERE rulesID      BETWEEN 9300 AND 9399;
DELETE FROM eventDescriptions         WHERE eventID      BETWEEN 9001 AND 9099;
DELETE FROM eventRoster               WHERE eventID      BETWEEN 9001 AND 9099;
DELETE FROM eventTournaments          WHERE eventID      BETWEEN 9001 AND 9099;
DELETE FROM eventPublication          WHERE eventID      BETWEEN 9001 AND 9099;
DELETE FROM systemEvents              WHERE eventID      BETWEEN 9001 AND 9099;
DELETE FROM systemRoster              WHERE systemRosterID BETWEEN 8001 AND 8099;
DELETE FROM systemSchools             WHERE schoolID     BETWEEN 7001 AND 7099;
DELETE FROM systemTournaments         WHERE tournamentTypeID BETWEEN 8701 AND 8799;
