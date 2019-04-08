-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 08, 2019 at 11:22 PM
-- Server version: 5.7.25-0ubuntu0.16.04.2
-- PHP Version: 7.0.33-0ubuntu0.16.04.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ScorecardV5`
--

-- --------------------------------------------------------

--
-- Table structure for table `eventAttacks`
--

CREATE TABLE `eventAttacks` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `attackPrefix` int(10) UNSIGNED DEFAULT NULL,
  `attackTarget` int(10) UNSIGNED DEFAULT NULL,
  `attackType` int(10) UNSIGNED DEFAULT NULL,
  `attackPoints` float NOT NULL DEFAULT '0',
  `attackNumber` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventAttributes`
--

CREATE TABLE `eventAttributes` (
  `attributeID` int(10) UNSIGNED NOT NULL,
  `attributeBool` tinyint(1) DEFAULT NULL,
  `attributeText` text,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `attributeType` varchar(255) NOT NULL,
  `attributeValue` float DEFAULT NULL,
  `attributeGroupSet` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventComponents`
--

CREATE TABLE `eventComponents` (
  `componentID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `componentTournamentID` int(10) UNSIGNED NOT NULL,
  `useResult` tinyint(1) NOT NULL DEFAULT '0',
  `useRoster` tinyint(1) NOT NULL DEFAULT '0',
  `isExclusive` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventCutStandards`
--

CREATE TABLE `eventCutStandards` (
  `qualID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `standardID` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `qualValue` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventDefaults`
--

CREATE TABLE `eventDefaults` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `color1ID` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `color2ID` int(10) UNSIGNED NOT NULL DEFAULT '2',
  `maxPoolSize` int(10) UNSIGNED NOT NULL DEFAULT '5',
  `maxDoubleHits` int(10) UNSIGNED NOT NULL DEFAULT '3',
  `normalizePoolSize` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `allowTies` tinyint(1) NOT NULL DEFAULT '0',
  `nameDisplay` varchar(255) NOT NULL DEFAULT 'firstName',
  `tournamentDisplay` varchar(255) NOT NULL DEFAULT 'weapon',
  `tournamentSorting` varchar(255) NOT NULL DEFAULT 'numGrouped',
  `useTimer` tinyint(1) NOT NULL DEFAULT '0',
  `useControlPoint` int(11) NOT NULL DEFAULT '0',
  `staffCompetency` int(11) NOT NULL DEFAULT '0',
  `addStaff` tinyint(1) NOT NULL DEFAULT '0',
  `staffHoursTarget` int(11) NOT NULL DEFAULT '0',
  `limitStaffConflicts` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventExchanges`
--

CREATE TABLE `eventExchanges` (
  `exchangeID` int(10) UNSIGNED NOT NULL,
  `matchID` int(10) UNSIGNED DEFAULT NULL,
  `exchangeType` varchar(255) NOT NULL,
  `scoringID` int(10) UNSIGNED DEFAULT NULL,
  `receivingID` int(10) UNSIGNED DEFAULT NULL,
  `scoreValue` float DEFAULT NULL,
  `scoreDeduction` float DEFAULT NULL,
  `exchangeNumber` int(11) NOT NULL DEFAULT '0',
  `exchangeTime` int(11) DEFAULT NULL,
  `refPrefix` int(10) UNSIGNED DEFAULT NULL,
  `refTarget` int(10) UNSIGNED DEFAULT NULL,
  `refType` int(10) UNSIGNED DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroupRankings`
--

CREATE TABLE `eventGroupRankings` (
  `groupRankingID` int(10) UNSIGNED NOT NULL,
  `groupID` int(10) UNSIGNED NOT NULL,
  `groupRank` int(11) NOT NULL,
  `overlapSize` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroupRoster`
--

CREATE TABLE `eventGroupRoster` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `groupID` int(10) UNSIGNED DEFAULT NULL,
  `rosterID` int(10) UNSIGNED DEFAULT NULL,
  `poolPosition` int(10) UNSIGNED DEFAULT NULL,
  `participantStatus` varchar(255) DEFAULT 'normal',
  `tournamentTableID` int(10) UNSIGNED DEFAULT NULL,
  `groupCheckIn` tinyint(1) NOT NULL DEFAULT '0',
  `groupGearCheck` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroups`
--

CREATE TABLE `eventGroups` (
  `groupID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `groupType` varchar(255) NOT NULL,
  `groupNumber` int(11) DEFAULT NULL,
  `groupName` varchar(255) DEFAULT NULL,
  `groupSet` int(11) NOT NULL DEFAULT '1',
  `bracketLevels` tinyint(4) DEFAULT NULL,
  `numFighters` int(10) UNSIGNED DEFAULT NULL,
  `groupStatus` varchar(255) DEFAULT NULL,
  `groupComplete` tinyint(1) NOT NULL DEFAULT '0',
  `locationID` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventIgnores`
--

CREATE TABLE `eventIgnores` (
  `ignoreID` int(11) NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `rosterID` int(10) UNSIGNED NOT NULL,
  `ignoreAtSet` int(11) NOT NULL DEFAULT '0',
  `stopAtSet` int(11) NOT NULL DEFAULT '0',
  `soloAtSet` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventLivestreamMatches`
--

CREATE TABLE `eventLivestreamMatches` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `matchNumber` int(11) NOT NULL,
  `matchID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventLivestreams`
--

CREATE TABLE `eventLivestreams` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `isLive` tinyint(1) NOT NULL DEFAULT '0',
  `chanelName` varchar(255) NOT NULL,
  `platform` varchar(255) NOT NULL,
  `useOverlay` tinyint(1) NOT NULL DEFAULT '0',
  `matchID` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventMatches`
--

CREATE TABLE `eventMatches` (
  `matchID` int(10) UNSIGNED NOT NULL,
  `groupID` int(10) UNSIGNED DEFAULT NULL,
  `matchNumber` int(10) UNSIGNED DEFAULT NULL,
  `fighter1ID` int(10) UNSIGNED DEFAULT NULL,
  `fighter2ID` int(10) UNSIGNED DEFAULT NULL,
  `winnerID` int(10) UNSIGNED DEFAULT NULL,
  `fighter1Score` float DEFAULT NULL,
  `fighter2Score` float DEFAULT NULL,
  `bracketPosition` int(10) UNSIGNED DEFAULT NULL,
  `bracketLevel` int(10) UNSIGNED DEFAULT NULL,
  `matchComplete` tinyint(1) DEFAULT '0',
  `signOff1` tinyint(1) NOT NULL DEFAULT '0',
  `signOff2` tinyint(1) NOT NULL DEFAULT '0',
  `ignoreMatch` tinyint(1) DEFAULT '0',
  `YouTubeLink` text,
  `reversedColors` tinyint(1) NOT NULL DEFAULT '0',
  `matchTime` int(11) DEFAULT NULL,
  `isPlaceholder` tinyint(1) NOT NULL DEFAULT '0',
  `placeholderMatchID` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventPlacings`
--

CREATE TABLE `eventPlacings` (
  `placeID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `rosterID` int(10) UNSIGNED NOT NULL,
  `placing` int(11) NOT NULL,
  `highBound` int(11) DEFAULT NULL,
  `lowBound` int(11) DEFAULT NULL,
  `placeType` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventRoster`
--

CREATE TABLE `eventRoster` (
  `rosterID` int(10) UNSIGNED NOT NULL,
  `systemRosterID` int(10) UNSIGNED DEFAULT NULL,
  `eventID` int(10) UNSIGNED DEFAULT NULL,
  `schoolID` int(10) UNSIGNED DEFAULT NULL,
  `publicNotes` text,
  `privateNotes` text,
  `isTeam` tinyint(1) NOT NULL DEFAULT '0',
  `staffCompetency` int(11) NOT NULL DEFAULT '0',
  `staffHoursTarget` int(11) DEFAULT NULL,
  `eventCheckIn` tinyint(1) NOT NULL DEFAULT '0',
  `eventWaiver` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventStandings`
--

CREATE TABLE `eventStandings` (
  `standingID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED DEFAULT NULL,
  `groupID` int(10) UNSIGNED DEFAULT NULL,
  `rosterID` int(10) UNSIGNED DEFAULT NULL,
  `groupType` varchar(255) NOT NULL,
  `groupSet` int(11) NOT NULL DEFAULT '1',
  `normalized` tinyint(1) DEFAULT '0',
  `rank` int(11) DEFAULT NULL,
  `score` float DEFAULT '0',
  `matches` float DEFAULT '0',
  `wins` float DEFAULT '0',
  `losses` float DEFAULT '0',
  `ties` float NOT NULL DEFAULT '0',
  `pointsFor` float DEFAULT '0',
  `pointsAgainst` float DEFAULT '0',
  `hitsFor` float DEFAULT '0',
  `hitsAgainst` float DEFAULT '0',
  `afterblowsFor` float DEFAULT '0',
  `afterblowsAgainst` float DEFAULT '0',
  `doubles` float DEFAULT '0',
  `noExchanges` float DEFAULT '0',
  `AbsPointsFor` int(11) DEFAULT '0',
  `AbsPointsAgainst` int(11) DEFAULT '0',
  `AbsPointsAwarded` int(11) NOT NULL DEFAULT '0',
  `numPenalties` float DEFAULT '0',
  `penaltiesAgainstOpponents` float DEFAULT '0',
  `penaltiesAgainst` float DEFAULT '0',
  `doubleOuts` float DEFAULT '0',
  `ignoreForBracket` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTeamRoster`
--

CREATE TABLE `eventTeamRoster` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `teamID` int(10) UNSIGNED DEFAULT NULL,
  `rosterID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentRosterID` int(10) UNSIGNED DEFAULT NULL,
  `memberRole` varchar(255) NOT NULL DEFAULT 'member',
  `memberName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournamentRoster`
--

CREATE TABLE `eventTournamentRoster` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED DEFAULT NULL,
  `rosterID` int(10) UNSIGNED DEFAULT NULL,
  `rating` int(11) NOT NULL DEFAULT '0',
  `subGroupNum` int(11) NOT NULL DEFAULT '0',
  `rating2` int(11) DEFAULT NULL,
  `tournamentCheckIn` tinyint(1) NOT NULL DEFAULT '0',
  `tournamentGearCheck` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournaments`
--

CREATE TABLE `eventTournaments` (
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `tournamentWeaponID` int(10) UNSIGNED NOT NULL,
  `tournamentPrefixID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentGenderID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentMaterialID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentSuffixID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentRankingID` int(10) UNSIGNED DEFAULT NULL,
  `doubleTypeID` int(10) UNSIGNED DEFAULT '2',
  `formatID` int(10) UNSIGNED DEFAULT '2',
  `numGroupSets` int(11) NOT NULL DEFAULT '1',
  `numParticipants` int(10) UNSIGNED DEFAULT '0',
  `normalizePoolSize` int(11) DEFAULT '0',
  `color1ID` int(10) UNSIGNED DEFAULT '1',
  `color2ID` int(10) UNSIGNED DEFAULT '2',
  `maxPoolSize` int(10) UNSIGNED NOT NULL DEFAULT '5',
  `maxDoubleHits` int(10) UNSIGNED NOT NULL DEFAULT '3',
  `maximumExchanges` int(11) DEFAULT NULL,
  `maximumPoints` int(11) DEFAULT NULL,
  `maxPointSpread` int(11) NOT NULL DEFAULT '0',
  `basePointValue` int(11) NOT NULL DEFAULT '0',
  `allowTies` tinyint(1) NOT NULL DEFAULT '0',
  `tournamentStatus` varchar(255) DEFAULT NULL,
  `isCuttingQual` tinyint(1) NOT NULL DEFAULT '0',
  `isFinalized` tinyint(1) NOT NULL DEFAULT '0',
  `useTimer` tinyint(1) NOT NULL DEFAULT '0',
  `timeLimit` int(11) NOT NULL DEFAULT '0',
  `useControlPoint` int(11) NOT NULL DEFAULT '0',
  `isNotNetScore` tinyint(1) NOT NULL DEFAULT '0',
  `isReverseScore` int(11) NOT NULL DEFAULT '0',
  `overrideDoubleType` tinyint(1) NOT NULL DEFAULT '0',
  `isPrivate` tinyint(1) NOT NULL DEFAULT '0',
  `isTeams` tinyint(1) NOT NULL DEFAULT '0',
  `logicMode` varchar(255) DEFAULT NULL,
  `poolWinnersFirst` int(11) NOT NULL DEFAULT '0',
  `limitPoolMatches` int(11) NOT NULL DEFAULT '0',
  `checkInStaff` int(11) NOT NULL DEFAULT '0',
  `hideFinalResults` tinyint(1) NOT NULL DEFAULT '0',
  `numSubMatches` int(11) NOT NULL DEFAULT '0',
  `subMatchMode` int(11) NOT NULL DEFAULT '0',
  `requireSignOff` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsLocations`
--

CREATE TABLE `logisticsLocations` (
  `locationID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `locationName` varchar(255) NOT NULL,
  `hasMatches` tinyint(1) NOT NULL DEFAULT '1',
  `hasClasses` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsLocationsBlocks`
--

CREATE TABLE `logisticsLocationsBlocks` (
  `blockLocationID` int(10) UNSIGNED NOT NULL,
  `blockID` int(10) UNSIGNED NOT NULL,
  `locationID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsLocationsMatches`
--

CREATE TABLE `logisticsLocationsMatches` (
  `matchLocationID` int(10) UNSIGNED NOT NULL,
  `locationID` int(10) UNSIGNED DEFAULT NULL,
  `matchID` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsScheduleBlocks`
--

CREATE TABLE `logisticsScheduleBlocks` (
  `blockID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `dayNum` int(11) NOT NULL,
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL,
  `blockTypeID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED DEFAULT NULL,
  `blockTitle` varchar(255) DEFAULT NULL,
  `blockSubtitle` varchar(255) DEFAULT NULL,
  `blockDescription` text,
  `blockLink` text,
  `blockLinkDescription` varchar(255) DEFAULT NULL,
  `suppressConflicts` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsScheduleShifts`
--

CREATE TABLE `logisticsScheduleShifts` (
  `shiftID` int(10) UNSIGNED NOT NULL,
  `blockID` int(10) UNSIGNED NOT NULL,
  `locationID` int(10) UNSIGNED NOT NULL,
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffCompetency`
--

CREATE TABLE `logisticsStaffCompetency` (
  `staffCompetencyID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `logisticsRoleID` int(10) UNSIGNED NOT NULL,
  `staffCompetency` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffMatches`
--

CREATE TABLE `logisticsStaffMatches` (
  `matchStaffID` int(10) UNSIGNED NOT NULL,
  `matchID` int(10) UNSIGNED NOT NULL,
  `rosterID` int(10) UNSIGNED NOT NULL,
  `logisticsRoleID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffShifts`
--

CREATE TABLE `logisticsStaffShifts` (
  `staffShiftID` int(10) UNSIGNED NOT NULL,
  `shiftID` int(10) UNSIGNED NOT NULL,
  `rosterID` int(10) UNSIGNED NOT NULL,
  `logisticsRoleID` int(10) UNSIGNED DEFAULT NULL,
  `checkedIn` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffTemplates`
--

CREATE TABLE `logisticsStaffTemplates` (
  `staffTemplateID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `logisticsRoleID` int(10) UNSIGNED NOT NULL,
  `numStaff` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemAttacks`
--

CREATE TABLE `systemAttacks` (
  `attackID` int(10) UNSIGNED NOT NULL,
  `attackClass` varchar(255) NOT NULL,
  `attackCode` varchar(255) NOT NULL,
  `attackText` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemAttacks`
--

INSERT INTO `systemAttacks` (`attackID`, `attackClass`, `attackCode`, `attackText`) VALUES
(1, 'target', 'head', 'Head'),
(2, 'target', 'torso', 'Torso'),
(3, 'target', 'arm', 'Arm'),
(4, 'target', 'leg', 'Leg'),
(5, 'type', 'cut', 'Cut'),
(6, 'type', 'thrust', 'Thrust'),
(7, 'type', 'throw', 'Throw'),
(8, 'type', 'slice', 'Slice'),
(9, 'prefix', 'control', 'Controlled'),
(10, 'type', 'throw', 'Throw'),
(11, 'type', 'pommel', 'Pommel'),
(12, 'prefix', 'wDouble', 'Weighted Double'),
(13, 'prefix', 'afterblow', 'Afterblow'),
(14, 'type', 'ringOut', 'Ring Out'),
(15, 'type', 'buckler', 'Buckler Punch'),
(16, 'prefix', 'open', 'Open'),
(17, 'prefix', 'closed', 'Closed'),
(18, 'type', 'double', 'Double'),
(19, 'target', 'hand', 'Hand'),
(20, 'type', 'disarm', 'Disarm'),
(21, 'target', 'unarmored', 'Unarmored'),
(22, 'target', 'lightArmor', 'Light Armor'),
(23, 'type', 'mord', 'Mordschlag');

-- --------------------------------------------------------

--
-- Table structure for table `systemBlockTypes`
--

CREATE TABLE `systemBlockTypes` (
  `blockTypeID` int(10) UNSIGNED NOT NULL,
  `typeName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemBlockTypes`
--

INSERT INTO `systemBlockTypes` (`blockTypeID`, `typeName`) VALUES
(1, 'Tournament'),
(2, 'Class'),
(3, 'Staffing'),
(4, 'Misc');

-- --------------------------------------------------------

--
-- Table structure for table `systemColors`
--

CREATE TABLE `systemColors` (
  `colorID` int(10) UNSIGNED NOT NULL,
  `colorName` varchar(255) NOT NULL,
  `colorCode` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemColors`
--

INSERT INTO `systemColors` (`colorID`, `colorName`, `colorCode`) VALUES
(1, 'BLACK', '#778899'),
(2, 'GOLD', '#E7B923'),
(3, 'RED', '#EB5757'),
(4, 'BLUE', '#1C6CD8'),
(5, 'WHITE', '#FFF'),
(6, 'GREEN', '#3CB371');

-- --------------------------------------------------------

--
-- Table structure for table `systemCutQualifications`
--

CREATE TABLE `systemCutQualifications` (
  `qualID` int(10) UNSIGNED NOT NULL,
  `systemRosterID` int(10) UNSIGNED DEFAULT NULL,
  `standardID` int(10) UNSIGNED DEFAULT NULL,
  `date` date DEFAULT NULL,
  `qualValue` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemCutStandards`
--

CREATE TABLE `systemCutStandards` (
  `standardID` int(10) UNSIGNED NOT NULL,
  `standardName` varchar(255) NOT NULL,
  `standardCode` varchar(255) NOT NULL,
  `standardText` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemCutStandards`
--

INSERT INTO `systemCutStandards` (`standardID`, `standardName`, `standardCode`, `standardText`) VALUES
(1, 'West Coast Qualification', 'westCoast', 'Either:\r\n4 total cuts, 2 on each side of the mat\r\n\r\nor\r\n\r\n3 unique cuts performed on the mat\r\n\r\nTime Limit: 40 seconds.'),
(2, 'Longpoint HFL', 'LHFL', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `systemDoubleTypes`
--

CREATE TABLE `systemDoubleTypes` (
  `doubleTypeID` int(10) UNSIGNED NOT NULL,
  `doubleTypeName` varchar(255) NOT NULL,
  `doublesDisabled` tinyint(1) NOT NULL,
  `afterblowDisabled` tinyint(1) NOT NULL,
  `afterblowType` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemDoubleTypes`
--

INSERT INTO `systemDoubleTypes` (`doubleTypeID`, `doubleTypeName`, `doublesDisabled`, `afterblowDisabled`, `afterblowType`) VALUES
(1, 'No Afterblow', 0, 1, 'none'),
(2, 'Deductive Afterblow', 0, 0, 'deductive'),
(3, 'Full Afterblow', 0, 0, 'full');

-- --------------------------------------------------------

--
-- Table structure for table `systemEvents`
--

CREATE TABLE `systemEvents` (
  `eventID` int(10) UNSIGNED NOT NULL,
  `eventName` varchar(255) NOT NULL,
  `eventAbreviation` varchar(255) DEFAULT NULL,
  `eventLeague` varchar(255) DEFAULT NULL,
  `eventYear` smallint(6) DEFAULT NULL,
  `eventStartDate` date DEFAULT NULL,
  `eventEndDate` date DEFAULT NULL,
  `regionCode` int(11) DEFAULT NULL,
  `eventCountry` varchar(255) DEFAULT NULL,
  `eventProvince` varchar(255) DEFAULT NULL,
  `eventCity` varchar(255) DEFAULT NULL,
  `staffPassword` varchar(255) DEFAULT NULL,
  `organizerPassword` varchar(255) DEFAULT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `eventStatus` varchar(255) NOT NULL DEFAULT 'active',
  `organizerEmail` varchar(255) DEFAULT NULL,
  `termsOfUseAccepted` tinyint(1) NOT NULL DEFAULT '0',
  `limitStaffConflicts` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemFormats`
--

CREATE TABLE `systemFormats` (
  `formatID` int(10) UNSIGNED NOT NULL,
  `formatName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemFormats`
--

INSERT INTO `systemFormats` (`formatID`, `formatName`) VALUES
(1, 'Results Only'),
(2, 'Sparring Matches'),
(3, 'Solo Scored'),
(4, 'Composite Event');

-- --------------------------------------------------------

--
-- Table structure for table `systemLogisticsRoles`
--

CREATE TABLE `systemLogisticsRoles` (
  `logisticsRoleID` int(10) UNSIGNED NOT NULL,
  `roleName` varchar(255) NOT NULL,
  `roleSortImportance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemLogisticsRoles`
--

INSERT INTO `systemLogisticsRoles` (`logisticsRoleID`, `roleName`, `roleSortImportance`) VALUES
(1, 'Director', 30),
(2, 'Judge', 20),
(3, 'Table', 10),
(4, '*Unspecified*', 0),
(5, 'Instructor', 60),
(6, 'General Staff', 5),
(7, 'Participant', 1),
(8, 'Ring Boss', 25),
(9, 'Director - Alternate', 29);

-- --------------------------------------------------------

--
-- Table structure for table `systemMatchOrder`
--

CREATE TABLE `systemMatchOrder` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `numberOfFighters` tinyint(4) DEFAULT NULL,
  `matchNumber` tinyint(4) DEFAULT NULL,
  `fighter1` tinyint(4) DEFAULT NULL,
  `fighter2` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemMatchOrder`
--

INSERT INTO `systemMatchOrder` (`tableID`, `numberOfFighters`, `matchNumber`, `fighter1`, `fighter2`) VALUES
(1, 2, 1, 1, 2),
(2, 3, 1, 1, 2),
(3, 3, 2, 1, 3),
(4, 3, 3, 2, 3),
(5, 4, 1, 1, 2),
(6, 4, 2, 3, 4),
(7, 4, 3, 3, 2),
(8, 4, 4, 4, 2),
(9, 4, 5, 4, 1),
(10, 4, 6, 3, 1),
(11, 5, 1, 2, 1),
(12, 5, 2, 4, 3),
(13, 5, 3, 5, 1),
(14, 5, 4, 2, 3),
(15, 5, 5, 5, 4),
(16, 5, 6, 1, 3),
(17, 5, 7, 5, 2),
(18, 5, 8, 1, 4),
(19, 5, 9, 5, 3),
(20, 5, 10, 2, 4),
(21, 6, 1, 2, 1),
(22, 6, 2, 3, 6),
(23, 6, 3, 4, 5),
(24, 6, 4, 6, 1),
(25, 6, 5, 2, 5),
(26, 6, 6, 3, 4),
(27, 6, 7, 1, 5),
(28, 6, 8, 6, 4),
(29, 6, 9, 2, 3),
(30, 6, 10, 4, 1),
(31, 6, 11, 5, 3),
(32, 6, 12, 6, 2),
(33, 6, 13, 1, 3),
(34, 6, 14, 4, 2),
(35, 6, 15, 5, 6),
(36, 7, 1, 7, 6),
(37, 7, 2, 1, 5),
(38, 7, 3, 2, 4),
(39, 7, 4, 6, 5),
(40, 7, 5, 7, 4),
(41, 7, 6, 1, 3),
(42, 7, 7, 5, 4),
(43, 7, 8, 6, 3),
(44, 7, 9, 7, 2),
(45, 7, 10, 4, 3),
(46, 7, 11, 5, 2),
(47, 7, 12, 6, 1),
(48, 7, 13, 3, 2),
(49, 7, 14, 4, 1),
(50, 7, 15, 5, 7),
(51, 7, 16, 2, 1),
(52, 7, 17, 3, 7),
(53, 7, 18, 4, 6),
(54, 7, 19, 1, 7),
(55, 7, 20, 2, 6),
(56, 7, 21, 3, 5),
(57, 8, 1, 7, 6),
(58, 8, 2, 1, 5),
(59, 8, 3, 2, 4),
(60, 8, 4, 3, 8),
(61, 8, 5, 6, 5),
(62, 8, 6, 7, 4),
(63, 8, 7, 1, 3),
(64, 8, 8, 2, 8),
(65, 8, 9, 5, 4),
(66, 8, 10, 6, 3),
(67, 8, 11, 7, 2),
(68, 8, 12, 1, 8),
(69, 8, 13, 4, 3),
(70, 8, 14, 5, 2),
(71, 8, 15, 6, 1),
(72, 8, 16, 7, 8),
(73, 8, 17, 3, 2),
(74, 8, 18, 4, 1),
(75, 8, 19, 5, 7),
(76, 8, 20, 6, 8),
(77, 8, 21, 2, 1),
(78, 8, 22, 3, 7),
(79, 8, 23, 4, 6),
(80, 8, 24, 5, 8),
(81, 8, 25, 1, 7),
(82, 8, 26, 2, 6),
(83, 8, 27, 3, 5),
(84, 8, 28, 4, 8),
(85, 10, 1, 2, 1),
(86, 10, 2, 3, 10),
(87, 10, 3, 4, 9),
(88, 10, 4, 5, 8),
(89, 10, 5, 6, 7),
(90, 10, 6, 2, 3),
(91, 10, 7, 1, 7),
(92, 10, 8, 8, 6),
(93, 10, 9, 9, 5),
(94, 10, 10, 10, 4),
(95, 10, 11, 6, 9),
(96, 10, 12, 7, 8),
(97, 10, 13, 3, 1),
(98, 10, 14, 4, 2),
(99, 10, 15, 5, 10),
(100, 10, 16, 10, 6),
(101, 10, 17, 2, 5),
(102, 10, 18, 3, 4),
(103, 10, 19, 1, 8),
(104, 10, 20, 9, 7),
(105, 10, 21, 5, 3),
(106, 10, 22, 6, 2),
(107, 10, 23, 7, 10),
(108, 10, 24, 8, 9),
(109, 10, 25, 4, 1),
(110, 10, 26, 1, 9),
(111, 10, 27, 10, 8),
(112, 10, 28, 2, 7),
(113, 10, 29, 3, 6),
(114, 10, 30, 4, 5),
(115, 10, 31, 5, 1),
(116, 10, 32, 6, 4),
(117, 10, 33, 7, 3),
(118, 10, 34, 8, 2),
(119, 10, 35, 9, 10),
(120, 10, 36, 5, 6),
(121, 10, 37, 1, 10),
(122, 10, 38, 2, 9),
(123, 10, 39, 3, 8),
(124, 10, 40, 4, 7),
(125, 10, 41, 9, 3),
(126, 10, 42, 10, 2),
(127, 10, 43, 6, 1),
(128, 10, 44, 7, 5),
(129, 10, 45, 8, 4),
(130, 9, 1, 2, 1),
(131, 9, 2, 4, 9),
(132, 9, 3, 5, 8),
(133, 9, 4, 6, 7),
(134, 9, 5, 2, 3),
(135, 9, 6, 1, 7),
(136, 9, 7, 8, 6),
(137, 9, 8, 9, 5),
(138, 9, 9, 9, 6),
(139, 9, 10, 7, 8),
(140, 9, 11, 3, 1),
(141, 9, 12, 4, 2),
(142, 9, 13, 5, 2),
(143, 9, 14, 4, 3),
(144, 9, 15, 1, 8),
(145, 9, 16, 9, 7),
(146, 9, 17, 5, 3),
(147, 9, 18, 6, 2),
(148, 9, 19, 8, 9),
(149, 9, 20, 1, 4),
(150, 9, 21, 1, 9),
(151, 9, 22, 2, 7),
(152, 9, 23, 3, 6),
(153, 9, 24, 4, 5),
(154, 9, 25, 1, 5),
(155, 9, 26, 4, 6),
(156, 9, 27, 7, 3),
(157, 9, 28, 8, 2),
(158, 9, 29, 5, 6),
(159, 9, 30, 9, 2),
(160, 9, 31, 3, 8),
(161, 9, 32, 4, 7),
(162, 9, 33, 9, 3),
(163, 9, 34, 6, 1),
(164, 9, 35, 7, 5),
(165, 9, 36, 8, 4),
(166, 11, 1, 1, 10),
(167, 11, 2, 6, 4),
(168, 11, 3, 2, 11),
(169, 11, 4, 7, 5),
(170, 11, 5, 3, 1),
(171, 11, 6, 8, 6),
(172, 11, 7, 4, 2),
(173, 11, 8, 9, 7),
(174, 11, 9, 5, 3),
(175, 11, 10, 10, 8),
(176, 11, 11, 11, 9),
(177, 11, 12, 2, 9),
(178, 11, 13, 7, 3),
(179, 11, 14, 3, 10),
(180, 11, 15, 8, 4),
(181, 11, 16, 4, 11),
(182, 11, 17, 9, 5),
(183, 11, 18, 5, 1),
(184, 11, 19, 10, 6),
(185, 11, 20, 6, 2),
(186, 11, 21, 11, 7),
(187, 11, 22, 1, 8),
(188, 11, 23, 3, 8),
(189, 11, 24, 8, 2),
(190, 11, 25, 4, 9),
(191, 11, 26, 9, 3),
(192, 11, 27, 5, 10),
(193, 11, 28, 10, 4),
(194, 11, 29, 6, 11),
(195, 11, 30, 11, 5),
(196, 11, 31, 7, 1),
(197, 11, 32, 1, 6),
(198, 11, 33, 2, 7),
(199, 11, 34, 4, 7),
(200, 11, 35, 9, 1),
(201, 11, 36, 5, 8),
(202, 11, 37, 10, 2),
(203, 11, 38, 6, 9),
(204, 11, 39, 11, 3),
(205, 11, 40, 7, 10),
(206, 11, 41, 1, 4),
(207, 11, 42, 8, 11),
(208, 11, 43, 2, 5),
(209, 11, 44, 3, 6),
(210, 11, 45, 5, 6),
(211, 11, 46, 10, 11),
(212, 11, 47, 6, 7),
(213, 11, 48, 11, 1),
(214, 11, 49, 7, 8),
(215, 11, 50, 1, 2),
(216, 11, 51, 8, 9),
(217, 11, 52, 2, 3),
(218, 11, 53, 9, 10),
(219, 11, 54, 3, 4),
(220, 11, 55, 4, 5),
(221, 12, 1, 2, 1),
(222, 12, 2, 12, 2),
(223, 12, 3, 5, 8),
(224, 12, 4, 8, 2),
(225, 12, 5, 12, 8),
(226, 12, 6, 4, 2),
(227, 12, 7, 8, 1),
(228, 12, 8, 11, 12),
(229, 12, 9, 4, 7),
(230, 12, 10, 7, 12),
(231, 12, 11, 11, 7),
(232, 12, 12, 3, 12),
(233, 12, 13, 1, 7),
(234, 12, 14, 6, 7),
(235, 12, 15, 9, 12),
(236, 12, 16, 2, 7),
(237, 12, 17, 5, 12),
(238, 12, 18, 9, 7),
(239, 12, 19, 6, 1),
(240, 12, 20, 5, 6),
(241, 12, 21, 8, 11),
(242, 12, 22, 12, 6),
(243, 12, 23, 4, 11),
(244, 12, 24, 8, 6),
(245, 12, 25, 12, 1),
(246, 12, 26, 10, 11),
(247, 12, 27, 3, 6),
(248, 12, 28, 6, 11),
(249, 12, 29, 10, 6),
(250, 12, 30, 7, 5),
(251, 12, 31, 1, 11),
(252, 12, 32, 9, 10),
(253, 12, 33, 2, 5),
(254, 12, 34, 5, 10),
(255, 12, 35, 9, 5),
(256, 12, 36, 2, 11),
(257, 12, 37, 1, 5),
(258, 12, 38, 4, 5),
(259, 12, 39, 7, 10),
(260, 12, 40, 11, 5),
(261, 12, 41, 8, 4),
(262, 12, 42, 12, 10),
(263, 12, 43, 4, 1),
(264, 12, 44, 3, 4),
(265, 12, 45, 6, 9),
(266, 12, 46, 10, 4),
(267, 12, 47, 3, 10),
(268, 12, 48, 6, 4),
(269, 12, 49, 10, 1),
(270, 12, 50, 8, 9),
(271, 12, 51, 12, 4),
(272, 12, 52, 9, 3),
(273, 12, 53, 2, 9),
(274, 12, 54, 5, 3),
(275, 12, 55, 1, 9),
(276, 12, 56, 7, 8),
(277, 12, 57, 11, 3),
(278, 12, 58, 4, 9),
(279, 12, 59, 7, 3),
(280, 12, 60, 11, 9),
(281, 12, 61, 1, 3),
(282, 12, 62, 2, 3),
(283, 12, 63, 10, 2),
(284, 12, 64, 3, 8),
(285, 12, 65, 6, 2),
(286, 12, 66, 10, 8),
(287, 13, 1, 1, 2),
(288, 13, 2, 8, 9),
(289, 13, 3, 2, 3),
(290, 13, 4, 9, 10),
(291, 13, 5, 3, 4),
(292, 13, 6, 10, 11),
(293, 13, 7, 4, 5),
(294, 13, 8, 11, 12),
(295, 13, 9, 5, 6),
(296, 13, 10, 12, 13),
(297, 13, 11, 6, 7),
(298, 13, 12, 1, 13),
(299, 13, 13, 7, 8),
(300, 13, 14, 7, 9),
(301, 13, 15, 1, 3),
(302, 13, 16, 8, 10),
(303, 13, 17, 2, 4),
(304, 13, 18, 9, 11),
(305, 13, 19, 3, 5),
(306, 13, 20, 10, 12),
(307, 13, 21, 4, 6),
(308, 13, 22, 11, 13),
(309, 13, 23, 5, 7),
(310, 13, 24, 1, 12),
(311, 13, 25, 6, 8),
(312, 13, 26, 2, 13),
(313, 13, 27, 3, 13),
(314, 13, 28, 7, 10),
(315, 13, 29, 1, 4),
(316, 13, 30, 8, 11),
(317, 13, 31, 2, 5),
(318, 13, 32, 9, 12),
(319, 13, 33, 3, 6),
(320, 13, 34, 10, 13),
(321, 13, 35, 4, 7),
(322, 13, 36, 1, 11),
(323, 13, 37, 5, 8),
(324, 13, 38, 2, 12),
(325, 13, 39, 6, 9),
(326, 13, 40, 6, 10),
(327, 13, 41, 4, 13),
(328, 13, 42, 7, 11),
(329, 13, 43, 1, 5),
(330, 13, 44, 8, 12),
(331, 13, 45, 2, 6),
(332, 13, 46, 9, 13),
(333, 13, 47, 3, 7),
(334, 13, 48, 1, 10),
(335, 13, 49, 4, 8),
(336, 13, 50, 2, 11),
(337, 13, 51, 5, 9),
(338, 13, 52, 3, 12),
(339, 13, 53, 4, 12),
(340, 13, 54, 6, 11),
(341, 13, 55, 5, 13),
(342, 13, 56, 7, 12),
(343, 13, 57, 1, 6),
(344, 13, 58, 8, 13),
(345, 13, 59, 2, 7),
(346, 13, 60, 1, 9),
(347, 13, 61, 3, 8),
(348, 13, 62, 2, 10),
(349, 13, 63, 4, 9),
(350, 13, 64, 3, 11),
(351, 13, 65, 5, 10),
(352, 13, 66, 5, 11),
(353, 13, 67, 5, 12),
(354, 13, 68, 6, 12),
(355, 13, 69, 6, 13),
(356, 13, 70, 7, 13),
(357, 13, 71, 1, 7),
(358, 13, 72, 1, 8),
(359, 13, 73, 2, 8),
(360, 13, 74, 2, 9),
(361, 13, 75, 3, 9),
(362, 13, 76, 3, 10),
(363, 13, 77, 4, 10),
(364, 13, 78, 4, 11);

-- --------------------------------------------------------

--
-- Table structure for table `systemRankings`
--

CREATE TABLE `systemRankings` (
  `tournamentRankingID` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `formatID` int(10) UNSIGNED NOT NULL,
  `numberOfInstances` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `description` text,
  `displayFunction` varchar(255) DEFAULT NULL,
  `scoringFunction` varchar(255) DEFAULT NULL,
  `scoreFormula` text,
  `orderByField1` varchar(255) NOT NULL DEFAULT 'score',
  `orderBySort1` varchar(255) NOT NULL DEFAULT 'DESC',
  `orderByField2` varchar(255) DEFAULT NULL,
  `orderBySort2` varchar(255) DEFAULT NULL,
  `orderByField3` varchar(255) DEFAULT NULL,
  `orderBySort3` varchar(255) DEFAULT NULL,
  `orderByField4` varchar(255) DEFAULT NULL,
  `orderBySort4` varchar(255) DEFAULT NULL,
  `displayTitle1` varchar(255) DEFAULT 'Score',
  `displayField1` varchar(255) DEFAULT 'score',
  `displayTitle2` varchar(255) DEFAULT NULL,
  `displayField2` varchar(255) DEFAULT NULL,
  `displayTitle3` varchar(255) DEFAULT NULL,
  `displayField3` varchar(255) DEFAULT NULL,
  `displayTitle4` varchar(255) DEFAULT NULL,
  `displayField4` varchar(255) DEFAULT NULL,
  `displayTitle5` varchar(255) DEFAULT NULL,
  `displayField5` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemRankings`
--

INSERT INTO `systemRankings` (`tournamentRankingID`, `name`, `formatID`, `numberOfInstances`, `description`, `displayFunction`, `scoringFunction`, `scoreFormula`, `orderByField1`, `orderBySort1`, `orderByField2`, `orderBySort2`, `orderByField3`, `orderBySort3`, `orderByField4`, `orderBySort4`, `displayTitle1`, `displayField1`, `displayTitle2`, `displayField2`, `displayTitle3`, `displayField3`, `displayTitle4`, `displayField4`, `displayTitle5`, `displayField5`) VALUES
(1, 'Franklin 2014', 2, 95, 'Calculation:\n +[Points For]\n +(5 * [Wins])\n -[Points Against]\n -(Doubles Penalty)\n\nDoubles Penalty\n1 Double -> 1 = 1\n2 Doubles -> 1+2 = 3\n3 Doubles -> 1+2+3 = 6 etc...\n\nRanking:\n1) Pool winners first\n2) Score\n3) Wins\n4) Doubles', NULL, NULL, '(5*wins) + pointsFor - pointsAgainst - ((doubles * (doubles+1))/2)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(2, 'RSS Cutting', 3, 11, 'Root Sum Square Cutting\n\nScoring\nTotal Deduction = sqrt([Cut Deduction]^2 + [Form Deduction]^2)\nScore = 20 - Cut Deduction\n\nRanking\n1) By Score\n2) Least deductions', 'RSScutting', 'RSScutting', NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Results Only', 1, 13, NULL, NULL, NULL, NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Deduction Based', 3, 7, 'Scoring\r\n100 point base score\r\nDeductions from the base score', 'DeductionBased', 'DeductionBased', 'pointsFor', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'FNY 2017', 2, 6, 'Fechtshule New York 2017\r\n\r\nScoring:\r\nOne exchange matches\r\n+ 1*Wins\r\n- 2*[Losses]\r\n- 2*[Doubles]\r\n\r\nRanking:\r\nCumulative across multiple pools', NULL, NULL, 'pointsFor - 2 * (losses + doubles)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Pushes', 'matches - hitsFor - losses - doubles', 'Losses', 'losses', 'Doubles', 'doubles', 'Score', 'score'),
(7, 'Total Points Scored', 2, 18, 'Scoring\n +[Points For]\n\nRanking\n1) Score\n2) Wins\n3) Doubles', NULL, NULL, 'pointsFor', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Points Scored', 'score', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Hit Ratio', 2, 2, 'Score\r\n[Points For] / [Total Times Hit]\r\n\r\nRanking\r\n1) Score\r\n2) Wins', NULL, NULL, 'case \n	when (hitsAgainst + afterblowsAgainst + doubles) > 0 then\n		pointsFor /  (hitsAgainst + afterblowsAgainst + doubles)\n	else\n		9001\nend', 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Points For', 'pointsFor', 'Total Times Hit', 'hitsAgainst + afterblowsAgainst + doubles', 'Score', 'score', NULL, NULL, NULL, NULL),
(9, 'Sandstorm 2017', 2, 2, 'Scoring:\r\n3 Points - Controlled Win/Artful Exchange\r\n2 Points - Win\r\n1 Point - Win w/ Afterblow\r\n\r\nRanking:\r\nBy Score\r\n', NULL, NULL, 'pointsFor - doubles', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Control Wins', 'score + doubles - (2*wins) - (3*afterblowsAgainst)', 'Wins', '(3 * wins) - (2 * afterblowsAgainst) - score + doubles', 'Afterblow Wins', 'afterblowsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(10, '2 Point Cumulative', 2, 1, 'Used for Singlestick in Helsinki Open 2018\r\n\r\nScoring:\r\n2 Points for Win\r\n1 Point for Tie\r\n\r\nRanking:\r\nBy Score', NULL, NULL, '(2 * wins) + ties', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Ties', 'ties', 'Losses', 'losses', 'Score', 'score', NULL, NULL),
(11, 'Flowerpoint', 2, 5, 'Score\r\n-1 Point for every time hit\r\n(Scoring action or double)\r\n\r\nRanking\r\nBy score', NULL, NULL, '0 - hitsAgainst - doubles', 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Number of Times Hit', 'hitsAgainst', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL, NULL, NULL),
(13, 'Thokk Continuous', 2, 1, NULL, NULL, NULL, '0 - AbsPointsAgainst', 'hitsAgainst', 'ASC', 'hitsFor', 'DESC', 'score', 'DESC', NULL, NULL, 'Bouts Won', 'hitsFor', 'Bouts Lost', 'hitsAgainst', 'Points Against', 'pointsAgainst', NULL, NULL, NULL, NULL),
(14, 'Alls Fair', 2, 2, 'Ranking:\r\n1) Wins\r\n2) Doubles\r\n3) Points +/-', NULL, NULL, 'pointsFor - pointsAgainst', 'wins', 'DESC', 'doubles', 'ASC', 'score', 'DESC', NULL, NULL, 'Wins', 'wins', 'Doubles', 'doubles', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Points +/-', 'score'),
(15, 'JNCR', 3, 1, 'Julian\'s Nameless Cutting Rules\r\n\r\nCuts are assigned scored as follows:\r\n8 points cut quality\r\n4 points upper body form\r\n4 points lower body form\r\n\r\n0 in cut quality or 0 in combined form is 0 for the entire cut.\r\n\r\nA negative score in any of the three becomes the final score.\r\n\r\nA cut with perfect scores earns an additional +4 points.', 'JNCR', 'JNCR', NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Aussie Reversed', 2, 25, '<u>This score mode is meant to be used with reverse scores!</u>\n\nPoints are assigned to the fighter who was hit.\n\nRanking:\nTop 2 from each pool ranked ahead of 3rd place and lower\n1) Wins\n2) Least points hit with (this is the points you give to the fighter!)', NULL, NULL, 'AbsPointsAgainst', 'wins', 'DESC', 'AbsPointsAgainst', 'ASC', '', '', NULL, NULL, 'Wins', 'wins', 'Points Against', 'score', 'Mutual Hits', 'doubles + afterblowsFor + afterblowsAgainst', NULL, NULL, NULL, NULL),
(17, 'AHWG 2018', 2, 1, 'Austin Historical Weapons Guild\r\n\r\nFor use with single hit matches\r\nScore = Wins - Losses - Double Outs', NULL, NULL, 'wins - losses - doubleOuts', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Losses', 'losses', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL),
(18, 'MART', 2, 1, 'Mid Atlantic Rookie Tournament: Fighty McFightface\r\n\r\nScore:\r\n2 * Wins + Ties\r\n\r\nRanking:\r\n1) Score\r\n2) Doubles (fewest outranks most)\r\n3) Points allowed (Fewest outranks most)\r\n4) Points scored (Most outranks fewest)\r\n\r\n\r\n', NULL, NULL, '(2 * Wins) + Ties', 'score', 'DESC', '(doubles + afterblowsFor + afterblowsAgainst)', 'ASC', 'AbsPointsAgainst', 'ASC', 'AbsPointsFor', 'DESC', 'Wins', 'wins', 'Ties', 'ties', 'Doubles', '(doubles + afterblowsFor + afterblowsAgainst)', 'Points Against', 'AbsPointsAgainst', 'Points For', 'AbsPointsFor'),
(19, 'Franklin 2014 (x25)', 2, 7, 'Franklin 2014 with even stronger doubles penalty\r\n\r\nCalculation:\r\n +[Points For]\r\n +(5 * [Wins])\r\n -[Points Against]\r\n -(Doubles Penalty) * 1.25\r\n\r\nDoubles Penalty\r\n1 Double -> 1 = 1\r\n2 Doubles -> 1+2 = 3\r\n3 Doubles -> 1+2+3 = 6 etc...\r\n\r\nRanking:\r\n1) Pool winners first\r\n2) Score\r\n3) Wins\r\n4) Doubles', NULL, NULL, '(5*wins) + pointsFor - pointsAgainst - (1.25*(doubles * (doubles+1))/2)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(20, 'Baer Score', 2, 4, 'Sort By:\r\n1) Wins\r\n2) Points Against\r\n3) Doubles', NULL, NULL, '0', 'wins', 'DESC', 'AbsPointsAgainst', 'ASC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Points Against', 'AbsPointsAgainst', 'Doubles', 'doubles', NULL, NULL, NULL, NULL),
(21, 'Wins | Plus/Minus', 2, 33, 'Score:\r\npointsFor - pointsAgainst\r\n\r\nRanking\r\n1) Wins\r\n2) Score', NULL, NULL, 'pointsFor - pointsAgainst', 'wins', 'DESC', 'score', 'DESC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Points +/-', 'score', NULL, NULL),
(22, 'Ram Rules', 2, 6, 'Score:\r\nPoints For - (2 * Doubles)', NULL, NULL, 'pointsFor - (2 * Doubles)', 'score', 'DESC', 'doubles', 'ASC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points For', 'pointsFor', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL),
(23, 'Swiss League', 2, 1, 'Ranking\r\n--------------\r\n\r\n1) Pool Indicator Score\r\n2) Most points scored\r\n\r\nPool Indicator Scores\r\n-------------\r\nMatch Score for Winner = (Winner Pts - Loser Pts) / Winner Pts\r\nMatch Score for Lower = 0\r\nPool Indicator Score = Sum of Match Indicator Scores', NULL, NULL, '#SwissScore', 'score', 'DESC', 'AbsPointsFor', 'DESC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points For', 'AbsPointsFor', 'Points Against', 'AbsPointsAgainst', 'Indicator Score', 'score', NULL, NULL),
(24, 'Wins & Aggregate Score', 2, 5, 'Wins & Aggregate Score\r\n\r\nRanking:\r\n1) Wins\r\n2) Total Points Scored', NULL, NULL, 'AbsPointsFor', 'wins', 'DESC', 'pointsFor', 'DESC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points Scored', 'pointsFor', 'Points Against', 'pointsAgainst', 'Bilateral Hits', 'doubles + afterblowsFor + afterblowsAgainst', NULL, NULL),
(25, 'Wessex League', 2, 5, 'Score:\r\n+ 3 * Wins\r\n+ 1 * Ties\r\n- 1 * Floor(Doubles/2)\r\n\r\nRanking:\r\n1) Score\r\n2) Doubles', NULL, NULL, '#Wessex', 'score', 'DESC', 'doubles', 'ASC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Losses', 'losses', 'Draws', 'ties', 'Doubles', 'doubles', 'Score', 'score'),
(26, 'WEIRD 2019', 2, 6, '-- Score ----\n+ (10 * Wins)\n- (10 * Losses)\n- (10 * Double Outs)\n+ pointsFor\n\n-- Ranking ----\n1) Score\n2) Points Against', NULL, NULL, '(10 * wins) - (10 * losses) - (10 * doubleOuts) + pointsFor', 'score', 'DESC', 'pointsAgainst', 'ASC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Losses', 'losses', 'Doubles', 'doubleOuts', 'Points For', 'pointsFor', 'Score', 'score'),
(27, 'Cut & Deduction', 3, 3, 'Each cut is input with a score and deduction', 'PureScore', 'PureScore', 'pointsFor', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'Flat Score', 3, 1, 'Only a score value is input for each cut', 'PureScore', 'PureScore', NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 'Longpoint Meta', 4, 5, 'Score:\r\n\r\nTournament Score\r\n100 * (numEntries - (place -1))/numEntries\r\n\r\nOverall Score:\r\nSUM[tournamentScores] - Standard Deviation[tournamentScores]', NULL, NULL, '#LpMeta', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Component Scores', 'pointsFor', 'Inconsistency Penalty', '-pointsAgainst', 'Score', 'score', NULL, NULL, NULL, NULL),
(30, 'LP Hit Ratio', 2, 3, 'Score --------\r\n[Absolute Points For + Win Bonus]/[Total Times Hit]\r\n\r\nAbsolute Points For\r\nPoints scored *before* the afterblow is deducted.\r\n\r\nWin Bonus\r\n2 Points for every win\r\n\r\nTotal Times Hit\r\n[# Clean Hits Against] + [# Doubles] + [# Afterblows Hit With]\r\n\r\n\r\nTie Breakers ----------\r\n1) Low Doubles\r\n2) High Wins\r\n3) Least hits for', NULL, NULL, 'case \n	when (hitsAgainst + afterblowsAgainst + doubles) > 0 then\n		(AbsPointsAwarded + 2 * wins) /  (hitsAgainst + afterblowsAgainst + doubles)\n	else\n		9001\nend', 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Wins', 'wins', 'Target Points', 'absPointsAwarded', 'Total Times Hit', 'hitsAgainst + afterblowsAgainst + doubles', 'Score', 'score', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `systemRoster`
--

CREATE TABLE `systemRoster` (
  `systemRosterID` int(10) UNSIGNED NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `middleName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `schoolID` int(10) UNSIGNED DEFAULT NULL,
  `HemaRatingsID` int(10) UNSIGNED DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `rosterCountry` varchar(255) DEFAULT NULL,
  `rosterProvince` varchar(255) DEFAULT NULL,
  `rosterCity` varchar(255) DEFAULT NULL,
  `eMail` varchar(255) DEFAULT NULL,
  `publicNotes` text,
  `privateNotes` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemRosterNotDuplicate`
--

CREATE TABLE `systemRosterNotDuplicate` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `rosterID1` int(10) UNSIGNED NOT NULL,
  `rosterID2` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemSchools`
--

CREATE TABLE `systemSchools` (
  `schoolID` int(10) UNSIGNED NOT NULL,
  `schoolFullName` varchar(255) NOT NULL,
  `schoolShortName` varchar(255) DEFAULT NULL,
  `schoolBranch` varchar(255) DEFAULT NULL,
  `schoolAbreviation` varchar(255) DEFAULT NULL,
  `schoolCity` varchar(255) DEFAULT NULL,
  `schoolProvince` varchar(255) DEFAULT NULL,
  `schoolCountry` varchar(255) DEFAULT NULL,
  `schoolAddress` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemTournaments`
--

CREATE TABLE `systemTournaments` (
  `tournamentTypeID` int(10) UNSIGNED NOT NULL,
  `tournamentTypeMeta` varchar(255) DEFAULT NULL,
  `tournamentType` varchar(255) DEFAULT NULL,
  `Pool_Bracket` tinyint(1) NOT NULL DEFAULT '1',
  `Pool_Sets` tinyint(1) NOT NULL DEFAULT '1',
  `Scored_Event` tinyint(1) NOT NULL DEFAULT '1',
  `numberOfInstances` int(10) UNSIGNED DEFAULT NULL,
  `description` text,
  `functionName` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemUserEvents`
--

CREATE TABLE `systemUserEvents` (
  `userTournamentID` int(11) NOT NULL,
  `userID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemUsers`
--

CREATE TABLE `systemUsers` (
  `userID` int(10) UNSIGNED NOT NULL,
  `userName` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `userEmail` varchar(255) NOT NULL,
  `EVENT_YOUTUBE` tinyint(1) NOT NULL DEFAULT '0',
  `EVENT_SCOREKEEP` tinyint(1) NOT NULL DEFAULT '0',
  `EVENT_MANAGEMENT` tinyint(1) NOT NULL DEFAULT '0',
  `SOFTWARE_EVENT_SWITCHING` tinyint(1) NOT NULL DEFAULT '0',
  `SOFTWARE_ASSIST` tinyint(1) NOT NULL DEFAULT '0',
  `SOFTWARE_ADMIN` tinyint(1) NOT NULL DEFAULT '0',
  `STATS_EVENT` tinyint(1) NOT NULL DEFAULT '0',
  `STATS_ALL` tinyint(1) NOT NULL DEFAULT '0',
  `VIEW_HIDDEN` tinyint(1) NOT NULL DEFAULT '0',
  `VIEW_SETTINGS` tinyint(1) NOT NULL DEFAULT '0',
  `VIEW_EMAIL` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `eventAttacks`
--
ALTER TABLE `eventAttacks`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `tournamentID` (`tournamentID`,`attackTarget`,`attackType`),
  ADD KEY `attackTarget` (`attackTarget`),
  ADD KEY `attackType` (`attackType`),
  ADD KEY `attackPrefix` (`attackPrefix`);

--
-- Indexes for table `eventAttributes`
--
ALTER TABLE `eventAttributes`
  ADD PRIMARY KEY (`attributeID`),
  ADD KEY `tournamentID` (`tournamentID`);

--
-- Indexes for table `eventComponents`
--
ALTER TABLE `eventComponents`
  ADD PRIMARY KEY (`componentID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `componentTournamentID` (`componentTournamentID`);

--
-- Indexes for table `eventCutStandards`
--
ALTER TABLE `eventCutStandards`
  ADD PRIMARY KEY (`qualID`),
  ADD KEY `tournamentID` (`tournamentID`,`standardID`);

--
-- Indexes for table `eventDefaults`
--
ALTER TABLE `eventDefaults`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `color1ID` (`color1ID`),
  ADD KEY `color2ID` (`color2ID`);

--
-- Indexes for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  ADD PRIMARY KEY (`exchangeID`),
  ADD KEY `matchID` (`matchID`),
  ADD KEY `scorringID` (`scoringID`),
  ADD KEY `recievingID` (`receivingID`),
  ADD KEY `refPrefix` (`refPrefix`),
  ADD KEY `refTarget` (`refTarget`),
  ADD KEY `refType` (`refType`);

--
-- Indexes for table `eventGroupRankings`
--
ALTER TABLE `eventGroupRankings`
  ADD PRIMARY KEY (`groupRankingID`),
  ADD KEY `groupID` (`groupID`);

--
-- Indexes for table `eventGroupRoster`
--
ALTER TABLE `eventGroupRoster`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `rosterID` (`rosterID`),
  ADD KEY `groupID` (`groupID`),
  ADD KEY `tournamentTableID` (`tournamentTableID`);

--
-- Indexes for table `eventGroups`
--
ALTER TABLE `eventGroups`
  ADD PRIMARY KEY (`groupID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `locationID` (`locationID`);

--
-- Indexes for table `eventIgnores`
--
ALTER TABLE `eventIgnores`
  ADD PRIMARY KEY (`ignoreID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `rosterID` (`rosterID`);

--
-- Indexes for table `eventLivestreamMatches`
--
ALTER TABLE `eventLivestreamMatches`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `eventID` (`eventID`,`matchID`),
  ADD KEY `matchID` (`matchID`);

--
-- Indexes for table `eventLivestreams`
--
ALTER TABLE `eventLivestreams`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `matchID` (`matchID`);

--
-- Indexes for table `eventMatches`
--
ALTER TABLE `eventMatches`
  ADD PRIMARY KEY (`matchID`),
  ADD KEY `groupID` (`groupID`),
  ADD KEY `fighter1ID` (`fighter1ID`),
  ADD KEY `fighter2ID` (`fighter2ID`),
  ADD KEY `winnerID` (`winnerID`),
  ADD KEY `placeholderMatchID` (`placeholderMatchID`);

--
-- Indexes for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  ADD PRIMARY KEY (`placeID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `rosterID` (`rosterID`);

--
-- Indexes for table `eventRoster`
--
ALTER TABLE `eventRoster`
  ADD PRIMARY KEY (`rosterID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `schoolID` (`schoolID`),
  ADD KEY `rosterID` (`systemRosterID`);

--
-- Indexes for table `eventStandings`
--
ALTER TABLE `eventStandings`
  ADD PRIMARY KEY (`standingID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `groupID` (`groupID`),
  ADD KEY `rosterID` (`rosterID`);

--
-- Indexes for table `eventTeamRoster`
--
ALTER TABLE `eventTeamRoster`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `teamID` (`teamID`,`rosterID`),
  ADD KEY `rosterID` (`rosterID`),
  ADD KEY `tournamentRosterID` (`tournamentRosterID`);

--
-- Indexes for table `eventTournamentRoster`
--
ALTER TABLE `eventTournamentRoster`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `eventRosterID` (`rosterID`);

--
-- Indexes for table `eventTournaments`
--
ALTER TABLE `eventTournaments`
  ADD PRIMARY KEY (`tournamentID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `tournamentWeaponID` (`tournamentWeaponID`),
  ADD KEY `tournamentPrefixID` (`tournamentPrefixID`),
  ADD KEY `tournamentSuffixID` (`tournamentSuffixID`),
  ADD KEY `tournamentGenderID` (`tournamentGenderID`),
  ADD KEY `tournamentMaterialID` (`tournamentMaterialID`),
  ADD KEY `tournamentRankingID` (`tournamentRankingID`),
  ADD KEY `doubleTypeID` (`doubleTypeID`),
  ADD KEY `elimTypeID` (`formatID`),
  ADD KEY `color1ID` (`color1ID`),
  ADD KEY `color2ID` (`color2ID`),
  ADD KEY `tournamentElimID` (`formatID`);

--
-- Indexes for table `logisticsLocations`
--
ALTER TABLE `logisticsLocations`
  ADD PRIMARY KEY (`locationID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `logisticsLocationsBlocks`
--
ALTER TABLE `logisticsLocationsBlocks`
  ADD PRIMARY KEY (`blockLocationID`),
  ADD KEY `scheduleID` (`blockID`),
  ADD KEY `locationID` (`locationID`);

--
-- Indexes for table `logisticsLocationsMatches`
--
ALTER TABLE `logisticsLocationsMatches`
  ADD PRIMARY KEY (`matchLocationID`),
  ADD UNIQUE KEY `matchID_2` (`matchID`),
  ADD KEY `placeID` (`locationID`),
  ADD KEY `matchID` (`matchID`);

--
-- Indexes for table `logisticsScheduleBlocks`
--
ALTER TABLE `logisticsScheduleBlocks`
  ADD PRIMARY KEY (`blockID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `blockTypeID` (`blockTypeID`);

--
-- Indexes for table `logisticsScheduleShifts`
--
ALTER TABLE `logisticsScheduleShifts`
  ADD PRIMARY KEY (`shiftID`),
  ADD KEY `blockID` (`blockID`),
  ADD KEY `shiftLocationID` (`locationID`);

--
-- Indexes for table `logisticsStaffCompetency`
--
ALTER TABLE `logisticsStaffCompetency`
  ADD PRIMARY KEY (`staffCompetencyID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `logisticsRoleID` (`logisticsRoleID`);

--
-- Indexes for table `logisticsStaffMatches`
--
ALTER TABLE `logisticsStaffMatches`
  ADD PRIMARY KEY (`matchStaffID`),
  ADD UNIQUE KEY `matchStaffID` (`rosterID`,`matchID`) USING BTREE,
  ADD KEY `matchID` (`matchID`),
  ADD KEY `rosterID` (`rosterID`),
  ADD KEY `logisticsRoleID` (`logisticsRoleID`);

--
-- Indexes for table `logisticsStaffShifts`
--
ALTER TABLE `logisticsStaffShifts`
  ADD PRIMARY KEY (`staffShiftID`),
  ADD KEY `rosterID` (`rosterID`),
  ADD KEY `logisticsRoleID` (`logisticsRoleID`),
  ADD KEY `schedultID` (`shiftID`);

--
-- Indexes for table `logisticsStaffTemplates`
--
ALTER TABLE `logisticsStaffTemplates`
  ADD PRIMARY KEY (`staffTemplateID`),
  ADD KEY `logisticsRoleID` (`logisticsRoleID`),
  ADD KEY `tournamentID` (`tournamentID`);

--
-- Indexes for table `systemAttacks`
--
ALTER TABLE `systemAttacks`
  ADD PRIMARY KEY (`attackID`);

--
-- Indexes for table `systemBlockTypes`
--
ALTER TABLE `systemBlockTypes`
  ADD PRIMARY KEY (`blockTypeID`);

--
-- Indexes for table `systemColors`
--
ALTER TABLE `systemColors`
  ADD PRIMARY KEY (`colorID`);

--
-- Indexes for table `systemCutQualifications`
--
ALTER TABLE `systemCutQualifications`
  ADD PRIMARY KEY (`qualID`),
  ADD KEY `systemRosterID` (`systemRosterID`),
  ADD KEY `standardID` (`standardID`);

--
-- Indexes for table `systemCutStandards`
--
ALTER TABLE `systemCutStandards`
  ADD PRIMARY KEY (`standardID`);

--
-- Indexes for table `systemDoubleTypes`
--
ALTER TABLE `systemDoubleTypes`
  ADD PRIMARY KEY (`doubleTypeID`);

--
-- Indexes for table `systemEvents`
--
ALTER TABLE `systemEvents`
  ADD PRIMARY KEY (`eventID`);

--
-- Indexes for table `systemFormats`
--
ALTER TABLE `systemFormats`
  ADD PRIMARY KEY (`formatID`);

--
-- Indexes for table `systemLogisticsRoles`
--
ALTER TABLE `systemLogisticsRoles`
  ADD PRIMARY KEY (`logisticsRoleID`);

--
-- Indexes for table `systemMatchOrder`
--
ALTER TABLE `systemMatchOrder`
  ADD PRIMARY KEY (`tableID`);

--
-- Indexes for table `systemRankings`
--
ALTER TABLE `systemRankings`
  ADD PRIMARY KEY (`tournamentRankingID`),
  ADD KEY `formatID` (`formatID`);

--
-- Indexes for table `systemRoster`
--
ALTER TABLE `systemRoster`
  ADD PRIMARY KEY (`systemRosterID`),
  ADD UNIQUE KEY `HemaRatingsID` (`HemaRatingsID`),
  ADD KEY `schoolID` (`schoolID`);

--
-- Indexes for table `systemRosterNotDuplicate`
--
ALTER TABLE `systemRosterNotDuplicate`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `rosterID1` (`rosterID1`),
  ADD KEY `rosterID2` (`rosterID2`);

--
-- Indexes for table `systemSchools`
--
ALTER TABLE `systemSchools`
  ADD PRIMARY KEY (`schoolID`);
ALTER TABLE `systemSchools` ADD FULLTEXT KEY `schoolFullName` (`schoolFullName`);

--
-- Indexes for table `systemTournaments`
--
ALTER TABLE `systemTournaments`
  ADD PRIMARY KEY (`tournamentTypeID`);

--
-- Indexes for table `systemUserEvents`
--
ALTER TABLE `systemUserEvents`
  ADD PRIMARY KEY (`userTournamentID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `tournamentID` (`eventID`);

--
-- Indexes for table `systemUsers`
--
ALTER TABLE `systemUsers`
  ADD PRIMARY KEY (`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `eventAttacks`
--
ALTER TABLE `eventAttacks`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2060;
--
-- AUTO_INCREMENT for table `eventAttributes`
--
ALTER TABLE `eventAttributes`
  MODIFY `attributeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=835;
--
-- AUTO_INCREMENT for table `eventComponents`
--
ALTER TABLE `eventComponents`
  MODIFY `componentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;
--
-- AUTO_INCREMENT for table `eventCutStandards`
--
ALTER TABLE `eventCutStandards`
  MODIFY `qualID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `eventDefaults`
--
ALTER TABLE `eventDefaults`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;
--
-- AUTO_INCREMENT for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  MODIFY `exchangeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92336;
--
-- AUTO_INCREMENT for table `eventGroupRankings`
--
ALTER TABLE `eventGroupRankings`
  MODIFY `groupRankingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;
--
-- AUTO_INCREMENT for table `eventGroupRoster`
--
ALTER TABLE `eventGroupRoster`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11164;
--
-- AUTO_INCREMENT for table `eventGroups`
--
ALTER TABLE `eventGroups`
  MODIFY `groupID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2972;
--
-- AUTO_INCREMENT for table `eventIgnores`
--
ALTER TABLE `eventIgnores`
  MODIFY `ignoreID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;
--
-- AUTO_INCREMENT for table `eventLivestreamMatches`
--
ALTER TABLE `eventLivestreamMatches`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventLivestreams`
--
ALTER TABLE `eventLivestreams`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `eventMatches`
--
ALTER TABLE `eventMatches`
  MODIFY `matchID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27475;
--
-- AUTO_INCREMENT for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  MODIFY `placeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4770;
--
-- AUTO_INCREMENT for table `eventRoster`
--
ALTER TABLE `eventRoster`
  MODIFY `rosterID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3873;
--
-- AUTO_INCREMENT for table `eventStandings`
--
ALTER TABLE `eventStandings`
  MODIFY `standingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125998;
--
-- AUTO_INCREMENT for table `eventTeamRoster`
--
ALTER TABLE `eventTeamRoster`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;
--
-- AUTO_INCREMENT for table `eventTournamentRoster`
--
ALTER TABLE `eventTournamentRoster`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7770;
--
-- AUTO_INCREMENT for table `eventTournaments`
--
ALTER TABLE `eventTournaments`
  MODIFY `tournamentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=426;
--
-- AUTO_INCREMENT for table `logisticsLocations`
--
ALTER TABLE `logisticsLocations`
  MODIFY `locationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;
--
-- AUTO_INCREMENT for table `logisticsLocationsBlocks`
--
ALTER TABLE `logisticsLocationsBlocks`
  MODIFY `blockLocationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=314;
--
-- AUTO_INCREMENT for table `logisticsLocationsMatches`
--
ALTER TABLE `logisticsLocationsMatches`
  MODIFY `matchLocationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1347;
--
-- AUTO_INCREMENT for table `logisticsScheduleBlocks`
--
ALTER TABLE `logisticsScheduleBlocks`
  MODIFY `blockID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;
--
-- AUTO_INCREMENT for table `logisticsScheduleShifts`
--
ALTER TABLE `logisticsScheduleShifts`
  MODIFY `shiftID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=306;
--
-- AUTO_INCREMENT for table `logisticsStaffCompetency`
--
ALTER TABLE `logisticsStaffCompetency`
  MODIFY `staffCompetencyID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `logisticsStaffMatches`
--
ALTER TABLE `logisticsStaffMatches`
  MODIFY `matchStaffID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3114;
--
-- AUTO_INCREMENT for table `logisticsStaffShifts`
--
ALTER TABLE `logisticsStaffShifts`
  MODIFY `staffShiftID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1269;
--
-- AUTO_INCREMENT for table `logisticsStaffTemplates`
--
ALTER TABLE `logisticsStaffTemplates`
  MODIFY `staffTemplateID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
--
-- AUTO_INCREMENT for table `systemAttacks`
--
ALTER TABLE `systemAttacks`
  MODIFY `attackID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `systemBlockTypes`
--
ALTER TABLE `systemBlockTypes`
  MODIFY `blockTypeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `systemColors`
--
ALTER TABLE `systemColors`
  MODIFY `colorID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `systemCutQualifications`
--
ALTER TABLE `systemCutQualifications`
  MODIFY `qualID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=246;
--
-- AUTO_INCREMENT for table `systemCutStandards`
--
ALTER TABLE `systemCutStandards`
  MODIFY `standardID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `systemDoubleTypes`
--
ALTER TABLE `systemDoubleTypes`
  MODIFY `doubleTypeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `systemEvents`
--
ALTER TABLE `systemEvents`
  MODIFY `eventID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;
--
-- AUTO_INCREMENT for table `systemFormats`
--
ALTER TABLE `systemFormats`
  MODIFY `formatID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `systemLogisticsRoles`
--
ALTER TABLE `systemLogisticsRoles`
  MODIFY `logisticsRoleID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `systemMatchOrder`
--
ALTER TABLE `systemMatchOrder`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=365;
--
-- AUTO_INCREMENT for table `systemRankings`
--
ALTER TABLE `systemRankings`
  MODIFY `tournamentRankingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `systemRoster`
--
ALTER TABLE `systemRoster`
  MODIFY `systemRosterID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2011;
--
-- AUTO_INCREMENT for table `systemRosterNotDuplicate`
--
ALTER TABLE `systemRosterNotDuplicate`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `systemSchools`
--
ALTER TABLE `systemSchools`
  MODIFY `schoolID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=379;
--
-- AUTO_INCREMENT for table `systemTournaments`
--
ALTER TABLE `systemTournaments`
  MODIFY `tournamentTypeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;
--
-- AUTO_INCREMENT for table `systemUserEvents`
--
ALTER TABLE `systemUserEvents`
  MODIFY `userTournamentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `systemUsers`
--
ALTER TABLE `systemUsers`
  MODIFY `userID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `eventAttacks`
--
ALTER TABLE `eventAttacks`
  ADD CONSTRAINT `eventAttacks_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventAttacks_ibfk_2` FOREIGN KEY (`attackTarget`) REFERENCES `systemAttacks` (`attackID`),
  ADD CONSTRAINT `eventAttacks_ibfk_3` FOREIGN KEY (`attackType`) REFERENCES `systemAttacks` (`attackID`);

--
-- Constraints for table `eventAttributes`
--
ALTER TABLE `eventAttributes`
  ADD CONSTRAINT `eventAttributes_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventComponents`
--
ALTER TABLE `eventComponents`
  ADD CONSTRAINT `eventComponents_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventComponents_ibfk_2` FOREIGN KEY (`componentTournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON UPDATE CASCADE;

--
-- Constraints for table `eventCutStandards`
--
ALTER TABLE `eventCutStandards`
  ADD CONSTRAINT `eventCutStandards_ibfk_2` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventDefaults`
--
ALTER TABLE `eventDefaults`
  ADD CONSTRAINT `eventdefaults_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventdefaults_ibfk_2` FOREIGN KEY (`color1ID`) REFERENCES `systemColors` (`colorID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `eventdefaults_ibfk_3` FOREIGN KEY (`color2ID`) REFERENCES `systemColors` (`colorID`) ON UPDATE CASCADE;

--
-- Constraints for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  ADD CONSTRAINT `eventExchanges_ibfk_1` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventExchanges_ibfk_2` FOREIGN KEY (`scoringID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventExchanges_ibfk_3` FOREIGN KEY (`receivingID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventExchanges_ibfk_4` FOREIGN KEY (`refPrefix`) REFERENCES `systemAttacks` (`attackID`),
  ADD CONSTRAINT `eventExchanges_ibfk_5` FOREIGN KEY (`refTarget`) REFERENCES `systemAttacks` (`attackID`),
  ADD CONSTRAINT `eventExchanges_ibfk_6` FOREIGN KEY (`refType`) REFERENCES `systemAttacks` (`attackID`);

--
-- Constraints for table `eventGroupRankings`
--
ALTER TABLE `eventGroupRankings`
  ADD CONSTRAINT `eventGroupRankings_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `eventGroups` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventGroupRoster`
--
ALTER TABLE `eventGroupRoster`
  ADD CONSTRAINT `eventGroupRoster_ibfk_2` FOREIGN KEY (`groupID`) REFERENCES `eventGroups` (`groupID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventGroupRoster_ibfk_3` FOREIGN KEY (`tournamentTableID`) REFERENCES `eventTournamentRoster` (`tableID`) ON DELETE CASCADE;

--
-- Constraints for table `eventGroups`
--
ALTER TABLE `eventGroups`
  ADD CONSTRAINT `eventGroups_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE;

--
-- Constraints for table `eventIgnores`
--
ALTER TABLE `eventIgnores`
  ADD CONSTRAINT `eventIgnores_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventIgnores_ibfk_2` FOREIGN KEY (`rosterID`) REFERENCES `eventTournamentRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventLivestreamMatches`
--
ALTER TABLE `eventLivestreamMatches`
  ADD CONSTRAINT `eventLivestreamMatches_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventLivestreamMatches_ibfk_2` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventLivestreams`
--
ALTER TABLE `eventLivestreams`
  ADD CONSTRAINT `eventLivestreams_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventMatches`
--
ALTER TABLE `eventMatches`
  ADD CONSTRAINT `eventMatches_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `eventGroups` (`groupID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_2` FOREIGN KEY (`fighter1ID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_3` FOREIGN KEY (`fighter2ID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_4` FOREIGN KEY (`winnerID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_5` FOREIGN KEY (`placeholderMatchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  ADD CONSTRAINT `eventPlacings_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`);

--
-- Constraints for table `eventRoster`
--
ALTER TABLE `eventRoster`
  ADD CONSTRAINT `eventRoster_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventRoster_ibfk_2` FOREIGN KEY (`schoolID`) REFERENCES `systemSchools` (`schoolID`),
  ADD CONSTRAINT `eventRoster_ibfk_3` FOREIGN KEY (`systemRosterID`) REFERENCES `systemRoster` (`systemRosterID`);

--
-- Constraints for table `eventStandings`
--
ALTER TABLE `eventStandings`
  ADD CONSTRAINT `eventStandings_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventStandings_ibfk_2` FOREIGN KEY (`groupID`) REFERENCES `eventGroups` (`groupID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventStandings_ibfk_3` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE;

--
-- Constraints for table `eventTeamRoster`
--
ALTER TABLE `eventTeamRoster`
  ADD CONSTRAINT `eventTeamRoster_ibfk_1` FOREIGN KEY (`teamID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventTeamRoster_ibfk_2` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventTeamRoster_ibfk_3` FOREIGN KEY (`tournamentRosterID`) REFERENCES `eventTournamentRoster` (`tableID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventTournamentRoster`
--
ALTER TABLE `eventTournamentRoster`
  ADD CONSTRAINT `eventTournamentRoster_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventTournamentRoster_ibfk_2` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE;

--
-- Constraints for table `eventTournaments`
--
ALTER TABLE `eventTournaments`
  ADD CONSTRAINT `eventTournaments_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventTournaments_ibfk_10` FOREIGN KEY (`color1ID`) REFERENCES `systemColors` (`colorID`),
  ADD CONSTRAINT `eventTournaments_ibfk_11` FOREIGN KEY (`color2ID`) REFERENCES `systemColors` (`colorID`),
  ADD CONSTRAINT `eventTournaments_ibfk_14` FOREIGN KEY (`formatID`) REFERENCES `systemFormats` (`formatID`),
  ADD CONSTRAINT `eventTournaments_ibfk_15` FOREIGN KEY (`tournamentRankingID`) REFERENCES `systemRankings` (`tournamentRankingID`),
  ADD CONSTRAINT `eventTournaments_ibfk_2` FOREIGN KEY (`tournamentWeaponID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_3` FOREIGN KEY (`tournamentPrefixID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_4` FOREIGN KEY (`tournamentSuffixID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_5` FOREIGN KEY (`tournamentGenderID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_6` FOREIGN KEY (`tournamentMaterialID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_8` FOREIGN KEY (`doubleTypeID`) REFERENCES `systemDoubleTypes` (`doubleTypeID`);

--
-- Constraints for table `logisticsLocations`
--
ALTER TABLE `logisticsLocations`
  ADD CONSTRAINT `logisticsLocations_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsLocationsBlocks`
--
ALTER TABLE `logisticsLocationsBlocks`
  ADD CONSTRAINT `logisticsLocationsBlocks_ibfk_1` FOREIGN KEY (`blockID`) REFERENCES `logisticsScheduleBlocks` (`blockID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsLocationsBlocks_ibfk_2` FOREIGN KEY (`locationID`) REFERENCES `logisticsLocations` (`locationID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsLocationsMatches`
--
ALTER TABLE `logisticsLocationsMatches`
  ADD CONSTRAINT `logisticsLocationsMatches_ibfk_1` FOREIGN KEY (`locationID`) REFERENCES `logisticsLocations` (`locationID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsLocationsMatches_ibfk_4` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsScheduleBlocks`
--
ALTER TABLE `logisticsScheduleBlocks`
  ADD CONSTRAINT `logisticsScheduleBlocks_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsScheduleBlocks_ibfk_2` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsScheduleBlocks_ibfk_3` FOREIGN KEY (`blockTypeID`) REFERENCES `systemBlockTypes` (`blockTypeID`);

--
-- Constraints for table `logisticsScheduleShifts`
--
ALTER TABLE `logisticsScheduleShifts`
  ADD CONSTRAINT `logisticsScheduleShifts_ibfk_1` FOREIGN KEY (`blockID`) REFERENCES `logisticsScheduleBlocks` (`blockID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsScheduleShifts_ibfk_2` FOREIGN KEY (`locationID`) REFERENCES `logisticsLocations` (`locationID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsStaffCompetency`
--
ALTER TABLE `logisticsStaffCompetency`
  ADD CONSTRAINT `logisticsStaffCompetency_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsStaffCompetency_ibfk_2` FOREIGN KEY (`logisticsRoleID`) REFERENCES `systemLogisticsRoles` (`logisticsRoleID`);

--
-- Constraints for table `logisticsStaffMatches`
--
ALTER TABLE `logisticsStaffMatches`
  ADD CONSTRAINT `logisticsStaffMatches_ibfk_1` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsStaffMatches_ibfk_2` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsStaffMatches_ibfk_3` FOREIGN KEY (`logisticsRoleID`) REFERENCES `systemLogisticsRoles` (`logisticsRoleID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsStaffShifts`
--
ALTER TABLE `logisticsStaffShifts`
  ADD CONSTRAINT `logisticsStaffShifts_ibfk_2` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsStaffShifts_ibfk_3` FOREIGN KEY (`logisticsRoleID`) REFERENCES `systemLogisticsRoles` (`logisticsRoleID`),
  ADD CONSTRAINT `logisticsStaffShifts_ibfk_4` FOREIGN KEY (`shiftID`) REFERENCES `logisticsScheduleShifts` (`shiftID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsStaffTemplates`
--
ALTER TABLE `logisticsStaffTemplates`
  ADD CONSTRAINT `logisticsStaffTemplates_ibfk_1` FOREIGN KEY (`logisticsRoleID`) REFERENCES `systemLogisticsRoles` (`logisticsRoleID`);

--
-- Constraints for table `systemCutQualifications`
--
ALTER TABLE `systemCutQualifications`
  ADD CONSTRAINT `cuttingqualifications_ibfk_1` FOREIGN KEY (`systemRosterID`) REFERENCES `systemRoster` (`systemRosterID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cuttingqualifications_ibfk_2` FOREIGN KEY (`standardID`) REFERENCES `systemCutStandards` (`standardID`) ON UPDATE CASCADE;

--
-- Constraints for table `systemRankings`
--
ALTER TABLE `systemRankings`
  ADD CONSTRAINT `systemRankings_ibfk_1` FOREIGN KEY (`formatID`) REFERENCES `systemFormats` (`formatID`) ON UPDATE CASCADE;

--
-- Constraints for table `systemRoster`
--
ALTER TABLE `systemRoster`
  ADD CONSTRAINT `systemRoster_ibfk_1` FOREIGN KEY (`schoolID`) REFERENCES `systemSchools` (`schoolID`);

--
-- Constraints for table `systemRosterNotDuplicate`
--
ALTER TABLE `systemRosterNotDuplicate`
  ADD CONSTRAINT `systemRosterNotDuplicate_ibfk_1` FOREIGN KEY (`rosterID1`) REFERENCES `systemRoster` (`systemRosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `systemRosterNotDuplicate_ibfk_2` FOREIGN KEY (`rosterID2`) REFERENCES `systemRoster` (`systemRosterID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `systemUserEvents`
--
ALTER TABLE `systemUserEvents`
  ADD CONSTRAINT `systemUserEvents_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `systemUsers` (`userID`),
  ADD CONSTRAINT `systemUserEvents_ibfk_2` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
