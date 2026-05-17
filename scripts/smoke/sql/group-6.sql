-- Group 6 seed fixtures — extracted from docs/superpowers/plans/2026-04-22-tournament-brackets.md
-- Idempotent: run as many times as needed; DELETE blocks clear prior state before re-inserting.

-- Clear prior seed in all managed ranges owned by this group
DELETE FROM eventExchanges           WHERE matchID            BETWEEN 12100 AND 12199;
DELETE FROM eventMatchOptions        WHERE matchID            BETWEEN 12100 AND 12199;
DELETE FROM logisticsLocationsMatches WHERE matchID           BETWEEN 12100 AND 12199;
DELETE FROM eventMatches             WHERE matchID            BETWEEN 12100 AND 12199;
DELETE FROM eventGroupRoster         WHERE groupID            BETWEEN 9700 AND 9799;
DELETE FROM eventGroups              WHERE groupID            BETWEEN 9700 AND 9799;
DELETE FROM eventPlacings            WHERE tournamentID       IN (9101, 9104);
-- NOTE: narrow IN-list, NOT a BETWEEN range — Group 5's seed owns
-- tournamentRosterIDs 9280-9282 and 9290-9292 (tournament 9103/9104 rosters).
-- A BETWEEN 9273..9299 would delete those and break Group 5 fixtures.
DELETE FROM eventTournamentRoster    WHERE tournamentRosterID IN (9273, 9274, 9283, 9293);
DELETE FROM eventTournamentRoster    WHERE tournamentID       IN (9105, 9106);
DELETE FROM eventRoster              WHERE rosterID           IN (9274, 9275, 9283, 9293);
DELETE FROM eventTournaments         WHERE tournamentID       IN (9105, 9106);

-- Extra fighters
-- Event 9001: 9274, 9275 to get 8 total fighters on event 9001 (existing 9201-9203, 9271-9273 = 6).
-- Event 9008: 9283 to get 4 fighters total (existing 9280-9282 = 3).
-- Event 9004: 9293 to get 4 fighters total (existing 9290-9292 = 3).
INSERT INTO eventRoster (rosterID, systemRosterID, eventID, schoolID, publicNotes, privateNotes, isTeam, eventCheckIn, eventWaiver) VALUES
  (9274, 8001, 9001, 7002, NULL, NULL, 0, 1, 1),
  (9275, 8002, 9001, NULL, NULL, NULL, 0, 1, 1),
  (9283, 8001, 9008, NULL, NULL, NULL, 0, 1, 1),
  (9293, 8001, 9004, NULL, NULL, NULL, 0, 1, 1);

-- New tournaments on event 9001 for bracket-edge-case coverage
INSERT INTO eventTournaments (tournamentID, eventID, tournamentWeaponID, formatID, numParticipants, maxPoolSize) VALUES
  (9105, 9001, 8701, 2, 3, 5),  -- 3-fighter single-bracket case
  (9106, 9001, 8701, 2, 4, 5);  -- true_double case

-- Tournament roster rows for the new brackets.
-- 9274, 9275 added to tournament 9101 so bracket 9701 (8 fighters) has matching tournament-roster entries.
-- 9283 added to tournament 9103 (4 fighters). 9293 added to tournament 9104 (4 fighters).
-- 9105 gets 3 fighters from existing event-9001 roster (9201, 9202, 9203).
-- 9106 gets 4 fighters (9201, 9202, 9203, 9274).
-- For 9101/9103/9104 we specify explicit PKs (deleted via BETWEEN cleanup above).
-- For 9105/9106 we let AUTO_INCREMENT assign PKs (deleted via tournamentID IN (...) cleanup above).
INSERT INTO eventTournamentRoster (tournamentRosterID, tournamentID, rosterID) VALUES
  (9273, 9101, 9274),
  (9274, 9101, 9275),
  (9283, 9103, 9283),
  (9293, 9104, 9293);

INSERT INTO eventTournamentRoster (tournamentID, rosterID) VALUES
  (9105, 9201), (9105, 9202), (9105, 9203),
  (9106, 9201), (9106, 9202), (9106, 9203), (9106, 9274);

-- Brackets (eventGroups, groupType='elim')
-- 9701 = tournament 9101 primary (3 levels, 8 fighters, fully scored)
-- 9702 = tournament 9101 secondary (1 level, 2 fighters, 1 match)
-- 9703 = tournament 9105 primary only (2 levels, 3 fighters)
-- 9704 = tournament 9106 primary (1 level but 3 matches at level 1 → true_double)
-- 9705 = tournament 9106 secondary (1 level, 2 fighters)
-- 9710 = tournament 9103 (event 9008 publishMatches=0) primary (2 levels, 4 fighters)
-- 9711 = tournament 9104 (event 9004 archived) primary (2 levels, 4 fighters)
INSERT INTO eventGroups (groupID, tournamentID, groupType, groupNumber, groupName, groupSet, bracketLevels, numFighters, groupStatus, groupComplete, locationID) VALUES
  (9701, 9101, 'elim', 1, 'Primary',   1, 3, 8, 'complete', 1, 6001),
  (9702, 9101, 'elim', 2, 'Secondary', 1, 1, 2, 'complete', 1, NULL),
  (9703, 9105, 'elim', 1, 'Primary',   1, 2, 3, 'complete', 1, NULL),
  (9704, 9106, 'elim', 1, 'Primary',   1, 1, 4, 'active',   0, NULL),
  (9705, 9106, 'elim', 2, 'Secondary', 1, 1, 2, 'active',   0, NULL),
  (9710, 9103, 'elim', 1, 'Primary',   1, 2, 4, 'complete', 1, NULL),
  (9711, 9104, 'elim', 1, 'Primary',   1, 2, 4, 'complete', 1, NULL);

-- Bracket rosters (eventGroupRoster, same table as pool roster).
-- Seed positions are 1..N in each bracket.
INSERT INTO eventGroupRoster (groupID, rosterID, poolPosition, participantStatus, tournamentTableID, groupCheckIn, groupGearCheck) VALUES
  -- Primary 9701 (tournament 9101): 8 fighters, seeds 1..8
  (9701, 9201, 1, 'normal', 9201, 1, 1),
  (9701, 9202, 2, 'normal', 9202, 1, 1),
  (9701, 9203, 3, 'normal', NULL, 1, 1),
  (9701, 9271, 4, 'normal', 9270, 1, 1),
  (9701, 9272, 5, 'normal', 9271, 1, 1),
  (9701, 9273, 6, 'normal', 9272, 1, 1),
  (9701, 9274, 7, 'normal', 9273, 1, 1),
  (9701, 9275, 8, 'normal', 9274, 1, 1),
  -- Secondary 9702 (tournament 9101): 2 fighters
  (9702, 9202, 1, 'normal', 9202, 1, 1),
  (9702, 9203, 2, 'normal', NULL, 1, 1),
  -- Primary 9703 (tournament 9105): 3 fighters
  (9703, 9201, 1, 'normal', NULL, 1, 1),
  (9703, 9202, 2, 'normal', NULL, 1, 1),
  (9703, 9203, 3, 'normal', NULL, 1, 1),
  -- Primary 9704 (tournament 9106): 4 fighters
  (9704, 9201, 1, 'normal', NULL, 1, 1),
  (9704, 9202, 2, 'normal', NULL, 1, 1),
  (9704, 9203, 3, 'normal', NULL, 1, 1),
  (9704, 9274, 4, 'normal', NULL, 1, 1),
  -- Secondary 9705 (tournament 9106): 2 fighters
  (9705, 9203, 1, 'normal', NULL, 1, 1),
  (9705, 9274, 2, 'normal', NULL, 1, 1),
  -- Primary 9710 (tournament 9103, event 9008): 4 fighters
  (9710, 9280, 1, 'normal', 9280, 1, 1),
  (9710, 9281, 2, 'normal', 9281, 1, 1),
  (9710, 9282, 3, 'normal', 9282, 1, 1),
  (9710, 9283, 4, 'normal', 9283, 1, 1),
  -- Primary 9711 (tournament 9104, event 9004 archived): 4 fighters
  (9711, 9290, 1, 'normal', 9290, 1, 1),
  (9711, 9291, 2, 'normal', 9291, 1, 1),
  (9711, 9292, 3, 'normal', 9292, 1, 1),
  (9711, 9293, 4, 'normal', 9293, 1, 1);

-- Bracket matches.
-- Bracket 9701 (8 fighters, 3 levels): 7 real matches + 1 placeholder + 1 ignored = 9 rows
INSERT INTO eventMatches (matchID, groupID, matchNumber, fighter1ID, fighter2ID, winnerID, fighter1Score, fighter2Score, matchComplete, signOff1, signOff2, ignoreMatch, matchTime, bracketLevel, bracketPosition, isPlaceholder, placeholderMatchID) VALUES
  -- Quarterfinals (level 3)
  (12101, 9701, 1, 9201, 9275, 9201, 5, 2, 1, 1, 1, 0, 180, 3, 1, 0, NULL),
  (12102, 9701, 2, 9202, 9274, 9202, 5, 4, 1, 1, 1, 0, 200, 3, 2, 0, NULL),
  (12103, 9701, 3, 9203, 9273, 9203, 5, 1, 1, 1, 1, 0, 150, 3, 3, 0, NULL),
  (12104, 9701, 4, 9271, 9272, 9271, 5, 3, 1, 1, 1, 0, 170, 3, 4, 0, NULL),
  -- Semifinals (level 2)
  (12105, 9701, 5, 9201, 9202, 9201, 5, 3, 1, 1, 1, 0, 220, 2, 1, 0, NULL),
  (12106, 9701, 6, 9203, 9271, 9203, 5, 4, 1, 1, 1, 0, 240, 2, 2, 0, NULL),
  -- Final (level 1)
  (12107, 9701, 7, 9201, 9203, 9201, 5, 3, 1, 1, 1, 0, 260, 1, 1, 0, NULL),
  -- Placeholder slot (hypothetical rematch)
  (12108, 9701, 99, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, 1, 2, 1, 12107),
  -- Ignored match (reshoot simulation at SF position 1)
  (12109, 9701, 50, 9201, 9202, NULL, 0, 0, 1, 0, 0, 1,  60, 2, 1, 0, NULL),
  -- Bracket 9702 (1 match): level 1, position 1
  (12120, 9702, 8, 9202, 9203, 9202, 5, 2, 1, 1, 1, 0, 150, 1, 1, 0, NULL),
  -- Bracket 9703 (tournament 9105, 3 fighters, 2 matches)
  (12130, 9703, 1, 9202, 9203, 9202, 5, 1, 1, 1, 1, 0, 120, 2, 1, 0, NULL),
  (12131, 9703, 2, 9201, 9202, 9201, 5, 3, 1, 1, 1, 0, 180, 1, 1, 0, NULL),
  -- Bracket 9704 (tournament 9106, true_double): 3 matches at bracketLevel=1
  -- 12141 has fighter2=NULL to exercise the "unresolved feeder" null-fighter path
  (12140, 9704, 1, 9201, 9202, 9201, 5, 2, 1, 1, 1, 0, 150, 1, 1, 0, NULL),
  (12141, 9704, 2, 9203, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, 1, 2, 0, NULL),
  (12142, 9704, 3, 9202, 9203, 9202, 5, 4, 1, 1, 1, 0, 200, 1, 3, 0, NULL),
  -- Bracket 9705 (secondary, 1 match)
  (12150, 9705, 1, 9203, 9274, NULL, 0, 0, 0, 0, 0, 0, NULL, 1, 1, 0, NULL),
  -- Bracket 9710 (event 9008 tournament 9103): 3 matches
  (12160, 9710, 1, 9280, 9283, 9280, 5, 2, 1, 1, 1, 0, 150, 2, 1, 0, NULL),
  (12161, 9710, 2, 9281, 9282, 9281, 5, 3, 1, 1, 1, 0, 170, 2, 2, 0, NULL),
  (12162, 9710, 3, 9280, 9281, 9280, 5, 4, 1, 1, 1, 0, 220, 1, 1, 0, NULL),
  -- Bracket 9711 (event 9004 archived): 3 matches
  (12170, 9711, 1, 9290, 9293, 9290, 5, 1, 1, 1, 1, 0, 150, 2, 1, 0, NULL),
  (12171, 9711, 2, 9291, 9292, 9291, 5, 4, 1, 1, 1, 0, 170, 2, 2, 0, NULL),
  (12172, 9711, 3, 9290, 9291, 9290, 5, 3, 1, 1, 1, 0, 220, 1, 1, 0, NULL);

-- Match-location assignment on bracket 9701's final
INSERT INTO logisticsLocationsMatches (locationID, matchID) VALUES
  (6001, 12107);

-- Match-option override on a bracket match
INSERT INTO eventMatchOptions (matchID, optionID, optionValue) VALUES
  (12101, 21, 1);

-- Exchanges on bracket match 12101 (QF-1). Mirror the Group 5 rich pattern.
INSERT INTO eventExchanges (matchID, exchangeType, scoringID, receivingID, scoreValue, scoreDeduction, exchangeNumber, exchangeTime, refPrefix, refType, refTarget) VALUES
  (12101, 'clean',     9201, 9275, 2.0, 0,   1, 20, NULL, 5, 1),    -- cut to head
  (12101, 'afterblow', 9275, 9201, 1.0, 0.5, 2, 35, 13,   5, 3),    -- afterblow cut to arm
  (12101, 'double',    9201, 9275, 0,   0,   3, 50, NULL, NULL, NULL),
  (12101, 'clean',     9201, 9275, 1.0, 0,   4, 70, 9,    6, 2);    -- controlled thrust to torso

-- Placings
-- Tournament 9101: 8 rows. 1st/2nd firm; 3rd-4th tie; 5th-8th tie.
INSERT INTO eventPlacings (tournamentID, rosterID, placing, placeType, highBound, lowBound) VALUES
  (9101, 9201, 1, 'final', NULL, NULL),
  (9101, 9203, 2, 'final', NULL, NULL),
  (9101, 9202, 3, 'tie',      3,    4),
  (9101, 9271, 3, 'tie',      3,    4),
  (9101, 9272, 5, 'tie',      5,    8),
  (9101, 9273, 5, 'tie',      5,    8),
  (9101, 9274, 5, 'tie',      5,    8),
  (9101, 9275, 5, 'tie',      5,    8),
  -- Tournament 9104 (archived event): 4 rows
  (9104, 9290, 1, 'final', NULL, NULL),
  (9104, 9291, 2, 'final', NULL, NULL),
  (9104, 9292, 3, 'tie',      3,    4),
  (9104, 9293, 3, 'tie',      3,    4);

SELECT
    (SELECT COUNT(*) FROM eventGroups     WHERE groupID BETWEEN 9700 AND 9799) AS brackets,
    (SELECT COUNT(*) FROM eventMatches    WHERE matchID BETWEEN 12100 AND 12199) AS matches,
    (SELECT COUNT(*) FROM eventExchanges  WHERE matchID BETWEEN 12100 AND 12199) AS exchanges,
    (SELECT COUNT(*) FROM eventPlacings   WHERE tournamentID IN (9101, 9104))    AS placings;
