-- Group 4 seed fixtures — extracted from docs/superpowers/plans/2026-04-22-tournaments-location-schedule.md
-- Idempotent: run as many times as needed; UPDATE statements clear prior state before re-inserting.

-- Tournament 9101: used by happy-path detail test. Set ranking + explicit
-- doubleType + numParticipants so smoke tests have deterministic values.
-- tournamentRankingID=1 ("Franklin 2014") ships with the schema's seed.
UPDATE eventTournaments
SET tournamentRankingID = 1,
    doubleTypeID = 2,
    numParticipants = 12
WHERE tournamentID = 9101;

-- Tournament 9102: used by "empty rulesets" detail test.
UPDATE eventTournaments
SET numParticipants = 8
WHERE tournamentID = 9102;

SELECT tournamentID, tournamentRankingID, doubleTypeID, numParticipants
FROM eventTournaments
WHERE tournamentID IN (9101, 9102);
