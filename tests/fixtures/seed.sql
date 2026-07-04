-- ============================================================================
-- Deterministic seed data for E2E testing (Playwright).
--
-- Loaded after the schema (Tables - ScorecardV9.sql) and the docker admin
-- user (Tables - Setup_Docker.sql), either by /docker-entrypoint-initdb.d
-- ordering in docker-compose.test.yml or by tests/reset-db.sh.
--
-- Test credentials (see tests/fixtures/credentials.md):
--   event organizer password: organizer-test-pw
--   event staff password:     staff-test-pw
-- Hashes below are bcrypt via PHP password_hash(..., PASSWORD_DEFAULT).
-- ============================================================================

-- Test event (eventID 1 = DEFAULT_EVENT in includes/config.php)
INSERT INTO `systemEvents`
	(`eventID`, `eventName`, `eventAbbreviation`, `eventYear`, `eventStartDate`, `eventEndDate`, `countryIso2`, `eventCity`, `eventStatus`, `isArchived`, `isMetaEvent`)
VALUES
	(1, 'Playwright Test Event', 'PTE', 2026, '2026-06-01', '2026-06-02', 'US', 'Testville', 'active', 0, 0);

INSERT INTO `eventSettings`
	(`eventSettingID`, `eventID`, `organizerEmail`, `termsOfUseAccepted`, `staffPassword`, `organizerPassword`)
VALUES
	(1, 1, 'test@example.com', 1, '$2y$10$IsEUmPgLPbAF0/bNTSAiE.xXxQFcH0HOY3lseV0iK4164YxPTkQkq', '$2y$10$rbFiSVNG9R4QJ9engQclSuhPjFqa36s7ixB.AfoiS/mM1GmMTAN8i');

INSERT INTO `eventDefaults` (`tableID`, `eventID`) VALUES (1, 1);

INSERT INTO `eventPublication`
	(`publicationID`, `eventID`, `publishDescription`, `publishRoster`, `publishSchedule`, `publishMatches`, `publishRules`)
VALUES
	(1, 1, 1, 1, 1, 1, 1);

-- Tournament: FORMAT_MATCH (2), Longsword (systemTournaments ID 1),
-- Franklin 2014 ranking (systemRankings ID 1). Other columns keep schema defaults.
INSERT INTO `eventTournaments`
	(`tournamentID`, `eventID`, `tournamentWeaponID`, `tournamentRankingID`, `doubleTypeID`, `formatID`, `color1ID`, `color2ID`, `maxPoolSize`, `maxDoubleHits`)
VALUES
	(1, 1, 1, 1, 2, 2, 1, 2, 5, 3);

-- School for the roster
INSERT INTO `systemSchools`
	(`schoolID`, `schoolFullName`, `schoolShortName`, `schoolAbbreviation`, `countryIso2`)
VALUES
	(1, 'Test Fencing School', 'Test School', 'TFS', 'US');

-- Six fighters with fixed IDs and predictable names
INSERT INTO `systemRoster` (`systemRosterID`, `firstName`, `lastName`, `schoolID`) VALUES
	(1, 'Alice',  'Applegate', 1),
	(2, 'Brett',  'Bowman',    1),
	(3, 'Carol',  'Chandler',  1),
	(4, 'Dmitri', 'Dukas',     1),
	(5, 'Erin',   'Eastwood',  1),
	(6, 'Frank',  'Fischer',   1);

INSERT INTO `eventRoster` (`rosterID`, `systemRosterID`, `eventID`, `schoolID`, `isTeam`, `eventCheckIn`, `eventWaiver`) VALUES
	(1, 1, 1, 1, 0, 1, 1),
	(2, 2, 1, 1, 0, 1, 1),
	(3, 3, 1, 1, 0, 1, 1),
	(4, 4, 1, 1, 0, 1, 1),
	(5, 5, 1, 1, 0, 1, 1),
	(6, 6, 1, 1, 0, 1, 1);

-- No pools/groups/matches are seeded: E2E journey tests create those through
-- the UI, keeping this fixture decoupled from eventGroups/eventMatches FKs.
