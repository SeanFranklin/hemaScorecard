-- Group 3 seed fixtures — extracted from docs/superpowers/plans/2026-04-22-schedules-workshops.md
-- Idempotent: run as many times as needed; DELETE blocks clear prior state before re-inserting.

-- Clear prior seed in all managed ID ranges
DELETE FROM logisticsStaffShifts      WHERE shiftID      BETWEEN 4500 AND 4599;
DELETE FROM logisticsScheduleShifts   WHERE shiftID      BETWEEN 4500 AND 4599;
DELETE FROM logisticsInstructors      WHERE instructorID BETWEEN 4600 AND 4699;
DELETE FROM logisticsBlockAttributes  WHERE blockAttributeID BETWEEN 4700 AND 4799;
DELETE FROM logisticsLocationsBlocks  WHERE blockLocationID  BETWEEN 4800 AND 4899;
DELETE FROM logisticsScheduleBlocks   WHERE blockID      BETWEEN 4400 AND 4499;
DELETE FROM logisticsLocations        WHERE locationID   BETWEEN 6001 AND 6099;
DELETE FROM eventTournamentRoster     WHERE rosterID     BETWEEN 9200 AND 9299;

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

-- Lookup tables
INSERT INTO systemSchools (schoolID, schoolFullName, schoolShortName, schoolBranch, schoolAbbreviation, schoolCity, schoolProvince, countryIso2) VALUES
  (7001, 'Cascadia Longsword School', 'Cascadia Longsword', NULL, 'CLS', 'Portland', 'OR', 'US'),
  (7002, "Boston Academie d'Armes", 'Boston Armes', NULL, 'BAA', 'Boston', 'MA', 'US');

INSERT INTO systemTournaments (tournamentTypeID, tournamentType, tournamentTypeMeta) VALUES
  (8701, 'Longsword', 'weapon'),
  (8702, 'Rapier',    'weapon');

INSERT INTO systemRoster (systemRosterID, firstName, middleName, lastName, nickname, gender, schoolID, HemaRatingsID, birthdate, rosterCountry, rosterProvince, rosterCity, eMail, publicNotes, privateNotes) VALUES
  (8001, 'Alex',   NULL, 'Harper', NULL, 'Nonbinary', 7001, 5678, '1990-03-14', 'US', 'OR', 'Portland',  'alex@example.invalid',   'Longsword + rapier', 'DO NOT EXPOSE'),
  (8002, 'Ingrid', NULL, 'Olsen',  NULL, 'Female',    7001, 5679, '1988-11-02', 'US', 'OR', 'Portland',  'ingrid@example.invalid', NULL,                  'DO NOT EXPOSE'),
  (8003, 'Marcus', 'Lee','Chen',   'Mc', 'Male',      7002, NULL, '1992-07-21', 'US', 'MA', 'Cambridge', 'marcus@example.invalid', 'Prefers steel',      'DO NOT EXPOSE'),
  (8004, 'Sofia',  NULL, 'Rivera', NULL, 'Female',    7001, 5680, '1994-05-09', 'US', 'CA', 'Oakland',   'sofia@example.invalid',  NULL,                  'DO NOT EXPOSE');

-- Events
INSERT INTO systemEvents (eventID, eventName, eventAbbreviation, eventYear, eventStartDate, eventEndDate, regionCode, countryIso2, eventProvince, eventCity, eventStatus, isArchived, isMetaEvent) VALUES
  (9001, 'Test Event Today',        'TET26', 2026, CURDATE(),                   CURDATE(),                   NULL, 'US', 'MD',           'Baltimore', 'active',   0, 0),
  (9002, 'Test Event Upcoming',     'TUE26', 2026, CURDATE() + INTERVAL 3 DAY,  CURDATE() + INTERVAL 5 DAY,  NULL, 'US', 'CA',           'Berkeley',  'active',   0, 0),
  (9003, 'Test Event Recent',       'TRE26', 2026, CURDATE() - INTERVAL 5 DAY,  CURDATE() - INTERVAL 3 DAY,  NULL, 'DE', NULL,           'Berlin',    'complete', 0, 0),
  (9004, 'Test Archived 2025',      'TAE25', 2025, '2025-06-01',                '2025-06-05',                NULL, 'GB', 'England',      'London',    'complete', 1, 0),
  (9005, 'Test Meta League 2026',   'TML26', 2026, NULL,                        NULL,                        NULL, 'AQ', NULL,           NULL,        'active',   0, 1),
  (9006, 'Test Hidden Event',       'THE26', 2026, CURDATE() + INTERVAL 20 DAY, CURDATE() + INTERVAL 22 DAY, NULL, 'US', 'TX',           'Austin',    'active',   0, 0),
  (9007, 'Test Future Event',       'TFE26', 2026, CURDATE() + INTERVAL 30 DAY, CURDATE() + INTERVAL 32 DAY, NULL, 'FR', 'Île-de-France','Paris',     'active',   0, 0),
  (9008, 'Test Partial-Publish',    'TPP26', 2026, CURDATE() + INTERVAL 1 DAY,  CURDATE() + INTERVAL 2 DAY,  NULL, 'US', 'IL',           'Chicago',   'active',   0, 0),
  (9009, 'Test Rich Roster',        'TRR26', 2026, CURDATE() - INTERVAL 1 DAY,  CURDATE() - INTERVAL 1 DAY,  NULL, 'US', 'WA',           'Seattle',   'active',   0, 0);

INSERT INTO eventPublication (publicationID, eventID, publishDescription, publishRoster, publishSchedule, publishMatches, publishRules) VALUES
  (9001, 9001, 1, 1, 1, 1, 1),
  (9002, 9002, 1, 1, 1, 0, 1),
  (9003, 9003, 1, 1, 1, 1, 1),
  (9005, 9005, 1, 0, 0, 0, 0),
  (9006, 9006, 0, 0, 0, 0, 0),
  (9007, 9007, 1, 0, 0, 0, 1),
  (9008, 9008, 1, 0, 0, 0, 0),
  (9009, 9009, 1, 1, 1, 0, 1);

INSERT INTO eventDescriptions (eventDescriptionID, eventID, description) VALUES
  (9001, 9001, 'Welcome to Test Event Today. Single-day fighting tournament happening right now.'),
  (9002, 9002, 'Test Event Upcoming happens in three days.'),
  (9007, 9007, 'Test Future Event description.'),
  (9008, 9008, 'Partial-publish test event.'),
  (9009, 9009, 'Rich roster test event.');

INSERT INTO eventTournaments (tournamentID, eventID, tournamentWeaponID, formatID) VALUES
  (9101, 9001, 8701, 2),
  (9102, 9001, 8702, 2);

INSERT INTO eventRoster (rosterID, systemRosterID, eventID, schoolID, publicNotes, privateNotes, isTeam, eventCheckIn, eventWaiver) VALUES
  (9201, 8001, 9001, 7001, 'Registered for longsword and rapier', 'private note',   0, 1, 1),
  (9202, 8002, 9001, NULL, NULL,                                 NULL,             0, 1, 0),
  (9203, 8003, 9001, NULL, NULL,                                 'staff-eyes only',0, 0, 1),
  (9240, 8004, 9008, 7001, 'should never appear',                NULL,             0, 0, 0),
  (9250, 8001, 9009, 7001, 'Team captain of Thunder Squad',      NULL,             0, 1, 1),
  (9251, 8002, 9009, NULL, NULL,                                 NULL,             0, 1, 0),
  (9252, 8003, 9009, NULL, NULL,                                 NULL,             0, 0, 1),
  (9253, NULL, 9009, 7002, NULL,                                 NULL,             1, 1, 1),
  (9260, 8001, 9004, 7001, 'Archived event participant',         NULL,             0, 1, 1);

INSERT INTO eventTeamRoster (tableID, teamID, rosterID, tournamentRosterID, memberRole, memberName, teamOrder) VALUES
  (9501, 9253, NULL, NULL, 'teamName', 'Thunder Squad', NULL),
  (9502, 9253, 9250, NULL, 'member',   NULL,            1),
  (9503, 9253, 9251, NULL, 'member',   NULL,            2);

INSERT INTO logisticsAnnouncements (announcementID, eventID, message, displayUntil, visibility) VALUES
  (9401, 9001, 'Opening ceremony at 9am sharp.',              UNIX_TIMESTAMP(NOW() + INTERVAL 1 DAY), 'all'),
  (9402, NULL, 'System-wide maintenance Sunday 02:00 UTC.',   UNIX_TIMESTAMP(NOW() + INTERVAL 3 DAY), 'all'),
  (9403, 9001, 'Old expired notice',                          UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY), 'all'),
  (9404, 9001, 'Staff-only briefing at 07:00',                UNIX_TIMESTAMP(NOW() + INTERVAL 1 DAY), 'staff');

INSERT INTO eventRules (rulesID, eventID, rulesName, rulesOrder, rulesText) VALUES
  (9301, 9001, 'Longsword Competition Rules', 1, '1. Matches are first to 5 points.'),
  (9302, 9001, 'Rapier Competition Rules',    2, 'Rapier ruleset text.'),
  (9310, 9008, 'Hidden Rules',                1, 'Should not be returned while publishRules=0.'),
  (9320, 9004, 'Archived Event Rules 2025',   1, 'Archived event rules text.');

INSERT INTO eventRulesLinks (rulesLinkID, rulesID, tournamentID) VALUES
  (9301, 9301, 9101),
  (9302, 9301, 9102);

-- NEW for group 3: locations, schedule blocks, shifts, staff shifts, instructors, block attrs, tournament roster

INSERT INTO logisticsLocations (locationID, eventID, locationName, locationNameShort, hasMatches, hasClasses, locationOrder) VALUES
  (6001, 9001, 'Main Hall',   'MH', 1, 1, 1),
  (6002, 9001, 'Classroom A', 'CA', 0, 1, 2);

INSERT INTO logisticsScheduleBlocks
  (blockID, eventID, dayNum, startTime, endTime, blockTypeID, tournamentID, blockTitle, blockSubtitle, blockDescription, blockLink, blockLinkDescription, suppressConflicts)
VALUES
  (4401, 9001, 1, 540,  720,  1, 9101, 'Longsword Open',       NULL,               NULL,                                  NULL,                          NULL,     0),
  (4402, 9001, 2, 780,  870,  2, NULL, 'Fiore Basics',         'Intro level',      'Learn the basic guards of Fiore.',    'https://example.com/slides', 'Slides', 0),
  (4403, 9001, 1, 480,  540,  3, NULL, 'Judges Briefing',      NULL,               'Pre-event briefing for judges.',      NULL,                          NULL,     0),
  (4404, 9008, 1, 600,  660,  2, NULL, 'Hidden Workshop',      NULL,               NULL,                                  NULL,                          NULL,     0),
  (4405, 9004, 1, 600,  720,  2, NULL, 'Archived Workshop',    NULL,               'Historical.',                         NULL,                          NULL,     0);

INSERT INTO logisticsLocationsBlocks (blockLocationID, blockID, locationID) VALUES
  (4801, 4401, 6001),
  (4802, 4402, 6002),
  (4803, 4403, 6001);

INSERT INTO logisticsScheduleShifts (shiftID, blockID, locationID, startTime, endTime) VALUES
  (4501, 4402, 6002, 780, 870),
  (4502, 4401, 6001, 540, 660),
  (4503, 4401, 6001, 660, 720);

INSERT INTO logisticsStaffShifts (staffShiftID, rosterID, shiftID, logisticsRoleID, checkedIn) VALUES
  (4551, 9201, 4501, 5, 0),   -- Alex, Instructor on workshop shift
  (4552, 9203, 4502, 2, 0),   -- Marcus, Judge on tournament shift A
  (4553, 9201, 4503, 2, 0);   -- Alex, Judge on tournament shift B

-- Note: logisticsStaffShifts schema columns — verify the exact column names at implement time.
-- If it's staffShiftID/rosterID/shiftID/logisticsRoleID/checkedIn, the above matches. If a column
-- name differs, the implementer adjusts and flags.

INSERT INTO logisticsInstructors (instructorID, rosterID, eventID, instructorBio) VALUES
  (4601, 9201, 9001, 'Teaching HEMA since 2014.');

INSERT INTO logisticsBlockAttributes (blockAttributeID, blockID, blockAttributeType, blockAttributeText) VALUES
  (4701, 4402, 'experience', 'Beginner'),
  (4702, 4402, 'equipment',  'Longsword, mask, jacket');

INSERT INTO eventTournamentRoster (tournamentRosterID, tournamentID, rosterID) VALUES
  (9201, 9101, 9201),  -- Alex (school 7001) in tournament 9101
  (9202, 9101, 9202);  -- Ingrid (school 7001) in tournament 9101

SELECT
  (SELECT COUNT(*) FROM logisticsLocations        WHERE locationID     BETWEEN 6001 AND 6099) AS locations,
  (SELECT COUNT(*) FROM logisticsScheduleBlocks   WHERE blockID        BETWEEN 4400 AND 4499) AS blocks,
  (SELECT COUNT(*) FROM logisticsLocationsBlocks  WHERE blockLocationID BETWEEN 4800 AND 4899) AS block_locations,
  (SELECT COUNT(*) FROM logisticsScheduleShifts   WHERE shiftID        BETWEEN 4500 AND 4599) AS schedule_shifts,
  (SELECT COUNT(*) FROM logisticsStaffShifts      WHERE staffShiftID   BETWEEN 4550 AND 4599) AS staff_shifts,
  (SELECT COUNT(*) FROM logisticsInstructors      WHERE instructorID   BETWEEN 4600 AND 4699) AS instructors,
  (SELECT COUNT(*) FROM logisticsBlockAttributes  WHERE blockAttributeID BETWEEN 4700 AND 4799) AS block_attrs,
  (SELECT COUNT(*) FROM eventTournamentRoster     WHERE rosterID       BETWEEN 9200 AND 9299) AS tournament_roster;
