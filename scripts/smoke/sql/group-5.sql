-- Group 5 seed fixtures — extracted from docs/superpowers/plans/2026-04-22-tournament-pools.md
-- Idempotent: run as many times as needed; DELETE blocks clear prior state before re-inserting.

-- Clear prior seed in all managed ranges owned by this group
DELETE FROM eventExchanges           WHERE matchID            BETWEEN 9601 AND 9699;
DELETE FROM eventMatchOptions        WHERE matchID            BETWEEN 9601 AND 9699;
DELETE FROM logisticsLocationsMatches WHERE matchID           BETWEEN 9601 AND 9699;
DELETE FROM eventMatches             WHERE matchID            BETWEEN 9601 AND 9699;
DELETE FROM eventStandings           WHERE groupID            BETWEEN 9501 AND 9599;
DELETE FROM eventGroupRankings       WHERE groupID            BETWEEN 9501 AND 9599;
DELETE FROM eventGroupRoster         WHERE groupID            BETWEEN 9501 AND 9599;
DELETE FROM eventGroups              WHERE groupID            BETWEEN 9501 AND 9599;
DELETE FROM eventTournamentRoster    WHERE tournamentRosterID BETWEEN 9270 AND 9299;
DELETE FROM eventRoster              WHERE rosterID           BETWEEN 9270 AND 9299;
DELETE FROM eventTournaments         WHERE tournamentID       IN (9103, 9104);

-- Extra fighters so we can build 3-fighter pools (the minimal interesting pool size).
-- Event 9001 (publishMatches=1): 9271,9272,9273 = Pool B; 9274,9275,9276 unused.
-- Event 9008 (publishMatches=0 non-archived): 9280,9281,9282.
-- Event 9004 (archived):                        9290,9291,9292.
-- Re-use existing systemRosterIDs 8001-8003 (from group 3 seed).
INSERT INTO eventRoster (rosterID, systemRosterID, eventID, schoolID, publicNotes, privateNotes, isTeam, eventCheckIn, eventWaiver) VALUES
  (9271, 8001, 9001, 7001, NULL, NULL, 0, 1, 1),
  (9272, 8002, 9001, NULL, NULL, NULL, 0, 1, 1),
  (9273, 8003, 9001, 7002, NULL, NULL, 0, 1, 1),
  (9280, 8001, 9008, 7001, NULL, NULL, 0, 1, 1),
  (9281, 8002, 9008, NULL, NULL, NULL, 0, 1, 1),
  (9282, 8003, 9008, 7001, NULL, NULL, 0, 1, 1),
  (9290, 8001, 9004, 7001, NULL, NULL, 0, 1, 1),
  (9291, 8002, 9004, NULL, NULL, NULL, 0, 1, 1),
  (9292, 8003, 9004, 7001, NULL, NULL, 0, 1, 1);

-- Tournaments on events 9008 and 9004 so we can test the publishMatches=0 and archived paths.
INSERT INTO eventTournaments (tournamentID, eventID, tournamentWeaponID, formatID, numParticipants, maxPoolSize) VALUES
  (9103, 9008, 8701, 2, 3, 5),
  (9104, 9004, 8701, 2, 3, 5);

-- Tournament-scoped rosters so fighters in 9103/9104 pools have the cross-link row.
-- (Group 3 seed already covers tournament 9101: tournamentRoster 9201/9202.)
INSERT INTO eventTournamentRoster (tournamentRosterID, tournamentID, rosterID) VALUES
  (9270, 9101, 9271),  -- Pool B on tournament 9101
  (9271, 9101, 9272),
  (9272, 9101, 9273),
  (9280, 9103, 9280),  -- event 9008 tournament
  (9281, 9103, 9281),
  (9282, 9103, 9282),
  (9290, 9104, 9290),  -- event 9004 archived tournament
  (9291, 9104, 9291),
  (9292, 9104, 9292);

-- Pools (eventGroups) — groupType='pool'
-- Tournament 9101 on event 9001:
--   set 1:   9501 (Pool A, location 6001), 9502 (Pool B, location 6002) - fully scored + ranked
--   set 2:   9503 (Finals, location 6001) - mid-scoring, no rankings yet
-- Tournament 9103 on event 9008:  9510 (Pool A, location NULL) - fully scored
-- Tournament 9104 on event 9004:  9511 (Pool A, location NULL) - fully scored
INSERT INTO eventGroups (groupID, tournamentID, groupType, groupNumber, groupName, groupSet, numFighters, groupStatus, groupComplete, locationID) VALUES
  (9501, 9101, 'pool', 1, 'Pool A',  1, 3, 'complete', 1, 6001),
  (9502, 9101, 'pool', 2, 'Pool B',  1, 3, 'complete', 1, 6002),
  (9503, 9101, 'pool', 1, 'Finals',  2, 3, 'active',   0, 6001),
  (9510, 9103, 'pool', 1, 'Pool A',  1, 3, 'complete', 1, NULL),
  (9511, 9104, 'pool', 1, 'Pool A',  1, 3, 'complete', 1, NULL);

-- Pool rosters (eventGroupRoster) — position 1..3 in each pool
INSERT INTO eventGroupRoster (groupID, rosterID, poolPosition, participantStatus, tournamentTableID, groupCheckIn, groupGearCheck) VALUES
  -- Pool A, tournament 9101 set 1: existing rosters 9201,9202,9203
  (9501, 9201, 1, 'normal', 9201, 1, 1),
  (9501, 9202, 2, 'normal', 9202, 1, 1),
  (9501, 9203, 3, 'normal', NULL, 1, 0),
  -- Pool B, tournament 9101 set 1: new rosters 9271,9272,9273
  (9502, 9271, 1, 'normal', 9270, 1, 1),
  (9502, 9272, 2, 'normal', 9271, 1, 1),
  (9502, 9273, 3, 'normal', 9272, 1, 1),
  -- Finals, tournament 9101 set 2: top finishers 9201,9271,9202
  (9503, 9201, 1, 'normal', 9201, 1, 1),
  (9503, 9271, 2, 'normal', 9270, 1, 1),
  (9503, 9202, 3, 'normal', 9202, 0, 0),
  -- Pool on event 9008 (hidden): rosters 9280,9281,9282
  (9510, 9280, 1, 'normal', 9280, 1, 1),
  (9510, 9281, 2, 'normal', 9281, 1, 1),
  (9510, 9282, 3, 'normal', 9282, 1, 1),
  -- Pool on event 9004 (archived): rosters 9290,9291,9292
  (9511, 9290, 1, 'normal', 9290, 1, 1),
  (9511, 9291, 2, 'normal', 9291, 1, 1),
  (9511, 9292, 3, 'normal', 9292, 1, 1);

-- Pool A (9501) 3 matches — all complete, all signed off, match 9601 has rich exchanges
INSERT INTO eventMatches (matchID, groupID, matchNumber, fighter1ID, fighter2ID, winnerID, fighter1Score, fighter2Score, matchComplete, signOff1, signOff2, ignoreMatch, matchTime, isPlaceholder) VALUES
  (9601, 9501, 1, 9201, 9202, 9201, 3, 1, 1, 1, 1, 0, 120, 0),
  (9602, 9501, 2, 9201, 9203, 9201, 2, 0, 1, 1, 1, 0,  95, 0),
  (9603, 9501, 3, 9202, 9203, 9202, 2, 1, 1, 1, 1, 0, 110, 0),
  -- Pool B (9502) 3 matches — one is ignored (DQ/reshoot simulation)
  (9604, 9502, 4, 9271, 9272, 9271, 3, 2, 1, 1, 1, 0, 100, 0),
  (9605, 9502, 5, 9271, 9273, 9271, 2, 1, 1, 1, 1, 0,  80, 0),
  (9606, 9502, 6, 9272, 9273, NULL, 0, 0, 1, 0, 0, 1,  60, 0),  -- ignoreMatch=1
  -- Finals (9503) 3 matches — 1 complete, 2 pending
  (9607, 9503, 7, 9201, 9271, 9201, 3, 1, 1, 1, 1, 0, 150, 0),
  (9608, 9503, 8, 9201, 9202, NULL, 0, 0, 0, 0, 0, 0, NULL, 0),
  (9609, 9503, 9, 9271, 9202, NULL, 0, 0, 0, 0, 0, 0, NULL, 0),
  -- Placeholder row in Pool A (must be filtered out of list + counts)
  (9620, 9501, 99, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, 1),
  -- Pool on event 9008 (3 matches fully scored)
  (9630, 9510, 1, 9280, 9281, 9280, 3, 2, 1, 1, 1, 0,  90, 0),
  (9631, 9510, 2, 9280, 9282, 9280, 2, 0, 1, 1, 1, 0,  70, 0),
  (9632, 9510, 3, 9281, 9282, 9281, 2, 1, 1, 1, 1, 0,  85, 0),
  -- Pool on event 9004 (archived, 3 matches fully scored)
  (9640, 9511, 1, 9290, 9291, 9290, 3, 2, 1, 1, 1, 0,  90, 0),
  (9641, 9511, 2, 9290, 9292, 9290, 2, 0, 1, 1, 1, 0,  70, 0),
  (9642, 9511, 3, 9291, 9292, 9291, 2, 1, 1, 1, 1, 0,  85, 0);

-- Match -> location assignments (Pool A matches on Main Hall)
INSERT INTO logisticsLocationsMatches (locationID, matchID) VALUES
  (6001, 9601), (6001, 9602), (6001, 9603);

-- A per-match option override on match 9601
-- (option 21 = TOURNAMENT_MATCH_MAX_SPREAD in config; arbitrary — we just need a row.)
INSERT INTO eventMatchOptions (matchID, optionID, optionValue) VALUES
  (9601, 21, 1);

-- Exchanges on match 9601: clean / afterblow / double / clean + two penalties
-- systemAttacks values: target head=1, torso=2, arm=3; type cut=5, thrust=6; prefix control=9, afterblow=13.
-- Penalty card IDs: yellowCard=34, redCard=35 (see includes/config.php PENALTY_CARD_*).
INSERT INTO eventExchanges (matchID, exchangeType, scoringID, receivingID, scoreValue, scoreDeduction, exchangeNumber, exchangeTime, refPrefix, refType, refTarget) VALUES
  (9601, 'clean',     9201, 9202, 2.0, 0,   1, 15, NULL, 5,  1),   -- Alice cuts Bob's head
  (9601, 'afterblow', 9202, 9201, 1.0, 0.5, 2, 22, 13,   5,  3),   -- Afterblow cut to Alice's arm
  (9601, 'double',    9201, 9202, 0,   0,   3, 38, NULL, NULL, NULL), -- Double, no attribution
  (9601, 'clean',     9201, 9202, 1.0, 0,   4, 55, 9,    6,  2),   -- Controlled thrust to Bob's torso
  (9601, 'penalty',   9202, NULL, -1,  0,   5, 60, NULL, 34, 1),   -- Yellow card on Bob
  (9601, 'penalty',   9202, NULL, -1,  0,   6, 90, NULL, 35, 1);   -- Red card on Bob

-- Group rankings + standings for ranked pools only (9501, 9502, 9510, 9511).
-- Finals 9503 is deliberately un-ranked + partially-scored to exercise null-rank path.
INSERT INTO eventGroupRankings (groupID, groupRank, overlapSize) VALUES
  (9501, 1, 0),  -- (group rankings track the pool's position, not individual fighters)
  (9502, 2, 0),
  (9510, 1, 0),
  (9511, 1, 0);

-- eventStandings: one row per (groupID, rosterID). Minimal-but-plausible numbers;
-- we only care about shape + rank ordering for smoke tests.
INSERT INTO eventStandings (tournamentID, groupID, rosterID, groupType, groupSet, `rank`, score, matches, wins, losses, ties, pointsFor, pointsAgainst, hitsFor, hitsAgainst, afterblowsFor, afterblowsAgainst, doubles, noExchanges, AbsPointsFor, AbsPointsAgainst, AbsPointsAwarded, numPenalties, numYellowCards, numRedCards, penaltiesAgainstOpponents, penaltiesAgainst, doubleOuts, numCleanHits, basePointValue, ignoreForBracket) VALUES
  -- Pool A (9501)
  (9101, 9501, 9201, 'pool', 1, 1, 5.0, 2, 2, 0, 0, 5.0, 1.0, 5, 1, 0, 1, 1, 0, 5.0, 1.0, 5.0, 0, 0, 0, 0, 0, 0, 4, 0, 0),
  (9101, 9501, 9202, 'pool', 1, 2, 2.0, 2, 1, 1, 0, 3.0, 3.0, 3, 3, 1, 0, 1, 0, 3.0, 3.0, 2.0, 0, 0, 0, 0, 0, 0, 2, 0, 0),
  (9101, 9501, 9203, 'pool', 1, 3, 0.0, 2, 0, 2, 0, 1.0, 4.0, 1, 4, 0, 0, 0, 0, 1.0, 4.0, 0.0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
  -- Pool B (9502) — match 9606 is ignored so 9272 vs 9273 isn't counted
  (9101, 9502, 9271, 'pool', 1, 1, 5.0, 2, 2, 0, 0, 5.0, 3.0, 5, 3, 0, 0, 0, 0, 5.0, 3.0, 5.0, 0, 0, 0, 0, 0, 0, 5, 0, 0),
  (9101, 9502, 9272, 'pool', 1, 2, 1.0, 1, 0, 1, 0, 2.0, 3.0, 2, 3, 0, 0, 0, 0, 2.0, 3.0, 1.0, 0, 0, 0, 0, 0, 0, 2, 0, 0),
  (9101, 9502, 9273, 'pool', 1, 3, 0.0, 1, 0, 1, 0, 1.0, 2.0, 1, 2, 0, 0, 0, 0, 1.0, 2.0, 0.0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
  -- Pool on event 9008 (hidden)
  (9103, 9510, 9280, 'pool', 1, 1, 5.0, 2, 2, 0, 0, 5.0, 2.0, 5, 2, 0, 0, 0, 0, 5.0, 2.0, 5.0, 0, 0, 0, 0, 0, 0, 5, 0, 0),
  (9103, 9510, 9281, 'pool', 1, 2, 2.0, 2, 1, 1, 0, 4.0, 3.0, 4, 3, 0, 0, 0, 0, 4.0, 3.0, 2.0, 0, 0, 0, 0, 0, 0, 4, 0, 0),
  (9103, 9510, 9282, 'pool', 1, 3, 0.0, 2, 0, 2, 0, 1.0, 5.0, 1, 5, 0, 0, 0, 0, 1.0, 5.0, 0.0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
  -- Pool on event 9004 (archived)
  (9104, 9511, 9290, 'pool', 1, 1, 5.0, 2, 2, 0, 0, 5.0, 2.0, 5, 2, 0, 0, 0, 0, 5.0, 2.0, 5.0, 0, 0, 0, 0, 0, 0, 5, 0, 0),
  (9104, 9511, 9291, 'pool', 1, 2, 2.0, 2, 1, 1, 0, 4.0, 3.0, 4, 3, 0, 0, 0, 0, 4.0, 3.0, 2.0, 0, 0, 0, 0, 0, 0, 4, 0, 0),
  (9104, 9511, 9292, 'pool', 1, 3, 0.0, 2, 0, 2, 0, 1.0, 5.0, 1, 5, 0, 0, 0, 0, 1.0, 5.0, 0.0, 0, 0, 0, 0, 0, 0, 1, 0, 0);

SELECT
    (SELECT COUNT(*) FROM eventGroups     WHERE groupID BETWEEN 9501 AND 9599) AS pools,
    (SELECT COUNT(*) FROM eventMatches    WHERE matchID BETWEEN 9601 AND 9699) AS matches,
    (SELECT COUNT(*) FROM eventExchanges  WHERE matchID BETWEEN 9601 AND 9699) AS exchanges,
    (SELECT COUNT(*) FROM eventStandings  WHERE groupID BETWEEN 9501 AND 9599) AS standings;
