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

-- Schools. IDs 1 and 2 are the Unknown/Unaffiliated placeholders, as in
-- production (no short name). School 5 is a real row that happens to be
-- named "Unaffiliated"; the roster school picker must hide it.
INSERT INTO `systemSchools`
	(`schoolID`, `schoolFullName`, `schoolShortName`, `schoolAbbreviation`, `countryIso2`)
VALUES
	(1, 'Unknown', NULL, NULL, NULL),
	(2, 'Unaffiliated', NULL, NULL, NULL),
	(3, 'Test Fencing School', 'Test School', 'TFS', 'US'),
	(4, 'Other Fencing Club', 'Other Club', 'OFC', 'US'),
	(5, 'Unaffiliated', 'Unaffiliated', NULL, 'US');

-- Six fighters with fixed IDs and predictable names, plus fighters who exist
-- in the system but are NOT entered in the event: roster-entry tests pick
-- Greta, and two Ivo Ibsens at different schools, from the autocomplete; the
-- CSV import test imports Hugo.
INSERT INTO `systemRoster` (`systemRosterID`, `firstName`, `lastName`, `schoolID`) VALUES
	(1, 'Alice',  'Applegate', 3),
	(2, 'Brett',  'Bowman',    3),
	(3, 'Carol',  'Chandler',  3),
	(4, 'Dmitri', 'Dukas',     3),
	(5, 'Erin',   'Eastwood',  3),
	(6, 'Frank',  'Fischer',   3),
	(7, 'Greta',  'Gable',     3),
	(8, 'Ivo',    'Ibsen',     3),
	(9, 'Ivo',    'Ibsen',     4),
	(10, 'Hugo',  'Haldane',   3);

INSERT INTO `eventRoster` (`rosterID`, `systemRosterID`, `eventID`, `schoolID`, `isTeam`, `eventCheckIn`, `eventWaiver`) VALUES
	(1, 1, 1, 3, 0, 1, 1),
	(2, 2, 1, 3, 0, 1, 1),
	(3, 3, 1, 3, 0, 1, 1),
	(4, 4, 1, 3, 0, 1, 1),
	(5, 5, 1, 3, 0, 1, 1),
	(6, 6, 1, 3, 0, 1, 1);

-- No pools/groups/matches are seeded: E2E journey tests create those through
-- the UI, keeping this fixture decoupled from eventGroups/eventMatches FKs.

-- Event 3: ten tournaments with weapon/prefix/gender attributes, for the
-- roster entry card's weapon groups (more than eight collapse unfocused rows).
-- Same organizer password as event 1. (Event 2 is config.php's TEST_EVENT_ID,
-- which the login list hides.) systemTournaments IDs: weapons
-- Longsword 1, Saber 7, Smallsword 8, Longsword Cutting 32, Sidesword 56;
-- prefixes Tier A 95, Tier B 96, U18 111; genders Open 20, URG 125.
INSERT INTO `systemEvents`
	(`eventID`, `eventName`, `eventAbbreviation`, `eventYear`, `eventStartDate`, `eventEndDate`, `countryIso2`, `eventCity`, `eventStatus`, `isArchived`, `isMetaEvent`)
VALUES
	(3, 'Playwright Grouped Event', 'PGE', 2026, '2026-07-01', '2026-07-02', 'US', 'Testville', 'active', 0, 0);

INSERT INTO `eventSettings`
	(`eventSettingID`, `eventID`, `organizerEmail`, `termsOfUseAccepted`, `staffPassword`, `organizerPassword`)
VALUES
	(3, 3, 'test@example.com', 1, '$2y$10$IsEUmPgLPbAF0/bNTSAiE.xXxQFcH0HOY3lseV0iK4164YxPTkQkq', '$2y$10$rbFiSVNG9R4QJ9engQclSuhPjFqa36s7ixB.AfoiS/mM1GmMTAN8i');

INSERT INTO `eventDefaults` (`tableID`, `eventID`) VALUES (3, 3);

INSERT INTO `eventPublication`
	(`publicationID`, `eventID`, `publishDescription`, `publishRoster`, `publishSchedule`, `publishMatches`, `publishRules`)
VALUES
	(3, 3, 1, 1, 1, 1, 1);

INSERT INTO `eventTournaments`
	(`tournamentID`, `eventID`, `tournamentWeaponID`, `tournamentPrefixID`, `tournamentGenderID`, `tournamentRankingID`, `doubleTypeID`, `formatID`, `color1ID`, `color2ID`, `maxPoolSize`, `maxDoubleHits`)
VALUES
	(2,  3, 1,  95,   20,   1, 2, 2, 1, 2, 5, 3),
	(3,  3, 1,  95,   125,  1, 2, 2, 1, 2, 5, 3),
	(4,  3, 1,  96,   20,   1, 2, 2, 1, 2, 5, 3),
	(5,  3, 1,  96,   125,  1, 2, 2, 1, 2, 5, 3),
	(6,  3, 1,  111,  NULL, 1, 2, 2, 1, 2, 5, 3),
	(7,  3, 32, 95,   20,   1, 2, 2, 1, 2, 5, 3),
	(8,  3, 7,  95,   20,   1, 2, 2, 1, 2, 5, 3),
	(9,  3, 7,  NULL, 125,  1, 2, 2, 1, 2, 5, 3),
	(10, 3, 8,  NULL, NULL, 1, 2, 2, 1, 2, 5, 3),
	(11, 3, 56, NULL, NULL, 1, 2, 2, 1, 2, 5, 3);

-- One fighter in event 3's roster, for the inline editor's grouped chips.
INSERT INTO `eventRoster` (`rosterID`, `systemRosterID`, `eventID`, `schoolID`, `isTeam`, `eventCheckIn`, `eventWaiver`) VALUES
	(7, 8, 3, 3, 0, 1, 1);
