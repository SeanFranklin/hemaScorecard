-- phpMyAdmin SQL Dump
-- version 4.4.15.9
-- https://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2019 at 10:47 PM
-- Server version: 5.6.37
-- PHP Version: 7.1.8

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

CREATE TABLE IF NOT EXISTS `eventAttacks` (
  `tableID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `attackPrefix` int(10) unsigned DEFAULT NULL,
  `attackTarget` int(10) unsigned DEFAULT NULL,
  `attackType` int(10) unsigned DEFAULT NULL,
  `attackPoints` float NOT NULL DEFAULT '0',
  `attackNumber` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventAttributes`
--

CREATE TABLE IF NOT EXISTS `eventAttributes` (
  `attributeID` int(10) unsigned NOT NULL,
  `attributeBool` tinyint(1) DEFAULT NULL,
  `attributeText` text,
  `tournamentID` int(10) unsigned NOT NULL,
  `attributeType` varchar(255) NOT NULL,
  `attributeValue` float DEFAULT NULL,
  `attributeGroupSet` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventComponents`
--

CREATE TABLE IF NOT EXISTS `eventComponents` (
  `componentID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `componentTournamentID` int(10) unsigned NOT NULL,
  `useResult` tinyint(1) NOT NULL DEFAULT '0',
  `useRoster` tinyint(1) NOT NULL DEFAULT '0',
  `isExclusive` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventCutStandards`
--

CREATE TABLE IF NOT EXISTS `eventCutStandards` (
  `qualID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `standardID` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `qualValue` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventDefaults`
--

CREATE TABLE IF NOT EXISTS `eventDefaults` (
  `tableID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `color1ID` int(10) unsigned NOT NULL DEFAULT '1',
  `color2ID` int(10) unsigned NOT NULL DEFAULT '2',
  `maxPoolSize` int(10) unsigned NOT NULL DEFAULT '5',
  `maxDoubleHits` int(10) unsigned NOT NULL DEFAULT '3',
  `normalizePoolSize` int(10) unsigned NOT NULL DEFAULT '0',
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

CREATE TABLE IF NOT EXISTS `eventExchanges` (
  `exchangeID` int(10) unsigned NOT NULL,
  `matchID` int(10) unsigned DEFAULT NULL,
  `exchangeType` varchar(255) NOT NULL,
  `scoringID` int(10) unsigned DEFAULT NULL,
  `receivingID` int(10) unsigned DEFAULT NULL,
  `scoreValue` float DEFAULT NULL,
  `scoreDeduction` float DEFAULT NULL,
  `exchangeNumber` int(11) NOT NULL DEFAULT '0',
  `exchangeTime` int(11) DEFAULT NULL,
  `refPrefix` int(10) unsigned DEFAULT NULL,
  `refTarget` int(10) unsigned DEFAULT NULL,
  `refType` int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroupRankings`
--

CREATE TABLE IF NOT EXISTS `eventGroupRankings` (
  `groupRankingID` int(10) unsigned NOT NULL,
  `groupID` int(10) unsigned NOT NULL,
  `groupRank` int(11) NOT NULL,
  `overlapSize` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroupRoster`
--

CREATE TABLE IF NOT EXISTS `eventGroupRoster` (
  `tableID` int(10) unsigned NOT NULL,
  `groupID` int(10) unsigned DEFAULT NULL,
  `rosterID` int(10) unsigned DEFAULT NULL,
  `poolPosition` int(10) unsigned DEFAULT NULL,
  `participantStatus` varchar(255) DEFAULT 'normal',
  `tournamentTableID` int(10) unsigned DEFAULT NULL,
  `groupCheckIn` tinyint(1) NOT NULL DEFAULT '0',
  `groupGearCheck` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroups`
--

CREATE TABLE IF NOT EXISTS `eventGroups` (
  `groupID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `groupType` varchar(255) NOT NULL,
  `groupNumber` int(11) DEFAULT NULL,
  `groupName` varchar(255) DEFAULT NULL,
  `groupSet` int(11) NOT NULL DEFAULT '1',
  `bracketLevels` tinyint(4) DEFAULT NULL,
  `numFighters` int(10) unsigned DEFAULT NULL,
  `groupStatus` varchar(255) DEFAULT NULL,
  `groupComplete` tinyint(1) NOT NULL DEFAULT '0',
  `locationID` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventHemaRatingsInfo`
--

CREATE TABLE IF NOT EXISTS `eventHemaRatingsInfo` (
  `hemaRatingInfoID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `organizingSchool` int(10) unsigned DEFAULT NULL,
  `socialMediaLink` text,
  `photoLink` text,
  `submitterName` varchar(255) DEFAULT NULL,
  `submitterEmail` varchar(255) DEFAULT NULL,
  `organizerName` varchar(255) DEFAULT NULL,
  `eventConform` tinyint(1) DEFAULT NULL,
  `allMatchesFought` tinyint(1) DEFAULT NULL,
  `missingMatches` tinyint(1) DEFAULT NULL,
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventIgnores`
--

CREATE TABLE IF NOT EXISTS `eventIgnores` (
  `ignoreID` int(11) NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `rosterID` int(10) unsigned NOT NULL,
  `ignoreAtSet` int(11) NOT NULL DEFAULT '0',
  `stopAtSet` int(11) NOT NULL DEFAULT '0',
  `soloAtSet` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventLivestreamMatches`
--

CREATE TABLE IF NOT EXISTS `eventLivestreamMatches` (
  `tableID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `matchNumber` int(11) NOT NULL,
  `matchID` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventLivestreams`
--

CREATE TABLE IF NOT EXISTS `eventLivestreams` (
  `tableID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `isLive` tinyint(1) NOT NULL DEFAULT '0',
  `chanelName` varchar(255) NOT NULL,
  `platform` varchar(255) NOT NULL,
  `useOverlay` tinyint(1) NOT NULL DEFAULT '0',
  `matchID` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventMatches`
--

CREATE TABLE IF NOT EXISTS `eventMatches` (
  `matchID` int(10) unsigned NOT NULL,
  `groupID` int(10) unsigned DEFAULT NULL,
  `matchNumber` int(10) unsigned DEFAULT NULL,
  `fighter1ID` int(10) unsigned DEFAULT NULL,
  `fighter2ID` int(10) unsigned DEFAULT NULL,
  `winnerID` int(10) unsigned DEFAULT NULL,
  `fighter1Score` float DEFAULT NULL,
  `fighter2Score` float DEFAULT NULL,
  `bracketPosition` int(10) unsigned DEFAULT NULL,
  `bracketLevel` int(10) unsigned DEFAULT NULL,
  `matchComplete` tinyint(1) DEFAULT '0',
  `signOff1` tinyint(1) NOT NULL DEFAULT '0',
  `signOff2` tinyint(1) NOT NULL DEFAULT '0',
  `ignoreMatch` tinyint(1) DEFAULT '0',
  `YouTubeLink` text,
  `reversedColors` tinyint(1) NOT NULL DEFAULT '0',
  `matchTime` int(11) DEFAULT NULL,
  `isPlaceholder` tinyint(1) NOT NULL DEFAULT '0',
  `placeholderMatchID` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventPlacings`
--

CREATE TABLE IF NOT EXISTS `eventPlacings` (
  `placeID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `rosterID` int(10) unsigned NOT NULL,
  `placing` int(11) NOT NULL,
  `highBound` int(11) DEFAULT NULL,
  `lowBound` int(11) DEFAULT NULL,
  `placeType` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventRoster`
--

CREATE TABLE IF NOT EXISTS `eventRoster` (
  `rosterID` int(10) unsigned NOT NULL,
  `systemRosterID` int(10) unsigned DEFAULT NULL,
  `eventID` int(10) unsigned DEFAULT NULL,
  `schoolID` int(10) unsigned DEFAULT NULL,
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

CREATE TABLE IF NOT EXISTS `eventStandings` (
  `standingID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned DEFAULT NULL,
  `groupID` int(10) unsigned DEFAULT NULL,
  `rosterID` int(10) unsigned DEFAULT NULL,
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
  `AbsPointsFor` float DEFAULT '0',
  `AbsPointsAgainst` float DEFAULT '0',
  `AbsPointsAwarded` float NOT NULL DEFAULT '0',
  `numPenalties` float DEFAULT '0',
  `penaltiesAgainstOpponents` float DEFAULT '0',
  `penaltiesAgainst` float DEFAULT '0',
  `doubleOuts` float DEFAULT '0',
  `ignoreForBracket` tinyint(1) NOT NULL DEFAULT '0',
  `basePointValue` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTeamRoster`
--

CREATE TABLE IF NOT EXISTS `eventTeamRoster` (
  `tableID` int(10) unsigned NOT NULL,
  `teamID` int(10) unsigned DEFAULT NULL,
  `rosterID` int(10) unsigned DEFAULT NULL,
  `tournamentRosterID` int(10) unsigned DEFAULT NULL,
  `memberRole` varchar(255) NOT NULL DEFAULT 'member',
  `memberName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournamentRoster`
--

CREATE TABLE IF NOT EXISTS `eventTournamentRoster` (
  `tableID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned DEFAULT NULL,
  `rosterID` int(10) unsigned DEFAULT NULL,
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

CREATE TABLE IF NOT EXISTS `eventTournaments` (
  `tournamentID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `tournamentWeaponID` int(10) unsigned NOT NULL,
  `tournamentPrefixID` int(10) unsigned DEFAULT NULL,
  `tournamentGenderID` int(10) unsigned DEFAULT NULL,
  `tournamentMaterialID` int(10) unsigned DEFAULT NULL,
  `tournamentSuffixID` int(10) unsigned DEFAULT NULL,
  `tournamentRankingID` int(10) unsigned DEFAULT NULL,
  `doubleTypeID` int(10) unsigned DEFAULT '2',
  `formatID` int(10) unsigned DEFAULT '2',
  `numGroupSets` int(11) NOT NULL DEFAULT '1',
  `numParticipants` int(10) unsigned DEFAULT '0',
  `normalizePoolSize` int(11) DEFAULT '0',
  `color1ID` int(10) unsigned DEFAULT '1',
  `color2ID` int(10) unsigned DEFAULT '2',
  `maxPoolSize` int(10) unsigned NOT NULL DEFAULT '5',
  `maxDoubleHits` int(10) unsigned NOT NULL DEFAULT '3',
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

CREATE TABLE IF NOT EXISTS `logisticsLocations` (
  `locationID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `locationName` varchar(255) NOT NULL,
  `hasMatches` tinyint(1) NOT NULL DEFAULT '1',
  `hasClasses` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsLocationsBlocks`
--

CREATE TABLE IF NOT EXISTS `logisticsLocationsBlocks` (
  `blockLocationID` int(10) unsigned NOT NULL,
  `blockID` int(10) unsigned NOT NULL,
  `locationID` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsLocationsMatches`
--

CREATE TABLE IF NOT EXISTS `logisticsLocationsMatches` (
  `matchLocationID` int(10) unsigned NOT NULL,
  `locationID` int(10) unsigned DEFAULT NULL,
  `matchID` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsScheduleBlocks`
--

CREATE TABLE IF NOT EXISTS `logisticsScheduleBlocks` (
  `blockID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `dayNum` int(11) NOT NULL,
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL,
  `blockTypeID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned DEFAULT NULL,
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

CREATE TABLE IF NOT EXISTS `logisticsScheduleShifts` (
  `shiftID` int(10) unsigned NOT NULL,
  `blockID` int(10) unsigned NOT NULL,
  `locationID` int(10) unsigned NOT NULL,
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffCompetency`
--

CREATE TABLE IF NOT EXISTS `logisticsStaffCompetency` (
  `staffCompetencyID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `logisticsRoleID` int(10) unsigned NOT NULL,
  `staffCompetency` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffMatches`
--

CREATE TABLE IF NOT EXISTS `logisticsStaffMatches` (
  `matchStaffID` int(10) unsigned NOT NULL,
  `matchID` int(10) unsigned NOT NULL,
  `rosterID` int(10) unsigned NOT NULL,
  `logisticsRoleID` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffShifts`
--

CREATE TABLE IF NOT EXISTS `logisticsStaffShifts` (
  `staffShiftID` int(10) unsigned NOT NULL,
  `shiftID` int(10) unsigned NOT NULL,
  `rosterID` int(10) unsigned NOT NULL,
  `logisticsRoleID` int(10) unsigned DEFAULT NULL,
  `checkedIn` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffTemplates`
--

CREATE TABLE IF NOT EXISTS `logisticsStaffTemplates` (
  `staffTemplateID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `logisticsRoleID` int(10) unsigned NOT NULL,
  `numStaff` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemAttacks`
--

CREATE TABLE IF NOT EXISTS `systemAttacks` (
  `attackID` int(10) unsigned NOT NULL,
  `attackClass` varchar(255) NOT NULL,
  `attackCode` varchar(255) NOT NULL,
  `attackText` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemBlockTypes`
--

CREATE TABLE IF NOT EXISTS `systemBlockTypes` (
  `blockTypeID` int(10) unsigned NOT NULL,
  `typeName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemColors`
--

CREATE TABLE IF NOT EXISTS `systemColors` (
  `colorID` int(10) unsigned NOT NULL,
  `colorName` varchar(255) NOT NULL,
  `colorCode` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemCutQualifications`
--

CREATE TABLE IF NOT EXISTS `systemCutQualifications` (
  `qualID` int(10) unsigned NOT NULL,
  `systemRosterID` int(10) unsigned DEFAULT NULL,
  `standardID` int(10) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `qualValue` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemCutStandards`
--

CREATE TABLE IF NOT EXISTS `systemCutStandards` (
  `standardID` int(10) unsigned NOT NULL,
  `standardName` varchar(255) NOT NULL,
  `standardCode` varchar(255) NOT NULL,
  `standardText` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemDoubleTypes`
--

CREATE TABLE IF NOT EXISTS `systemDoubleTypes` (
  `doubleTypeID` int(10) unsigned NOT NULL,
  `doubleTypeName` varchar(255) NOT NULL,
  `doublesDisabled` tinyint(1) NOT NULL,
  `afterblowDisabled` tinyint(1) NOT NULL,
  `afterblowType` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemEvents`
--

CREATE TABLE IF NOT EXISTS `systemEvents` (
  `eventID` int(10) unsigned NOT NULL,
  `eventName` varchar(255) NOT NULL,
  `eventAbbreviation` varchar(255) DEFAULT NULL,
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
  `limitStaffConflicts` int(11) NOT NULL DEFAULT '0',
  `isLeague` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemFormats`
--

CREATE TABLE IF NOT EXISTS `systemFormats` (
  `formatID` int(10) unsigned NOT NULL,
  `formatName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemLogisticsRoles`
--

CREATE TABLE IF NOT EXISTS `systemLogisticsRoles` (
  `logisticsRoleID` int(10) unsigned NOT NULL,
  `roleName` varchar(255) NOT NULL,
  `roleSortImportance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemMatchOrder`
--

CREATE TABLE IF NOT EXISTS `systemMatchOrder` (
  `tableID` int(10) unsigned NOT NULL,
  `numberOfFighters` tinyint(4) DEFAULT NULL,
  `matchNumber` tinyint(4) DEFAULT NULL,
  `fighter1` tinyint(4) DEFAULT NULL,
  `fighter2` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemRankings`
--

CREATE TABLE IF NOT EXISTS `systemRankings` (
  `tournamentRankingID` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `formatID` int(10) unsigned NOT NULL,
  `numberOfInstances` int(10) unsigned NOT NULL DEFAULT '0',
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

-- --------------------------------------------------------

--
-- Table structure for table `systemRoster`
--

CREATE TABLE IF NOT EXISTS `systemRoster` (
  `systemRosterID` int(10) unsigned NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `middleName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `schoolID` int(10) unsigned DEFAULT NULL,
  `HemaRatingsID` int(10) unsigned DEFAULT NULL,
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

CREATE TABLE IF NOT EXISTS `systemRosterNotDuplicate` (
  `tableID` int(10) unsigned NOT NULL,
  `rosterID1` int(10) unsigned NOT NULL,
  `rosterID2` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemSchools`
--

CREATE TABLE IF NOT EXISTS `systemSchools` (
  `schoolID` int(10) unsigned NOT NULL,
  `schoolFullName` varchar(255) NOT NULL,
  `schoolShortName` varchar(255) DEFAULT NULL,
  `schoolBranch` varchar(255) DEFAULT NULL,
  `schoolAbbreviation` varchar(255) DEFAULT NULL,
  `schoolCity` varchar(255) DEFAULT NULL,
  `schoolProvince` varchar(255) DEFAULT NULL,
  `schoolCountry` varchar(255) DEFAULT NULL,
  `schoolAddress` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemTournaments`
--

CREATE TABLE IF NOT EXISTS `systemTournaments` (
  `tournamentTypeID` int(10) unsigned NOT NULL,
  `tournamentTypeMeta` varchar(255) DEFAULT NULL,
  `tournamentType` varchar(255) DEFAULT NULL,
  `Pool_Bracket` tinyint(1) NOT NULL DEFAULT '1',
  `Pool_Sets` tinyint(1) NOT NULL DEFAULT '1',
  `Scored_Event` tinyint(1) NOT NULL DEFAULT '1',
  `numberOfInstances` int(10) unsigned DEFAULT NULL,
  `description` text,
  `functionName` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemUserEvents`
--

CREATE TABLE IF NOT EXISTS `systemUserEvents` (
  `userTournamentID` int(11) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemUsers`
--

CREATE TABLE IF NOT EXISTS `systemUsers` (
  `userID` int(10) unsigned NOT NULL,
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
  ADD PRIMARY KEY (`schoolID`),
  ADD FULLTEXT KEY `schoolFullName` (`schoolFullName`);

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
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventAttributes`
--
ALTER TABLE `eventAttributes`
  MODIFY `attributeID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventComponents`
--
ALTER TABLE `eventComponents`
  MODIFY `componentID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventCutStandards`
--
ALTER TABLE `eventCutStandards`
  MODIFY `qualID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventDefaults`
--
ALTER TABLE `eventDefaults`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  MODIFY `exchangeID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventGroupRankings`
--
ALTER TABLE `eventGroupRankings`
  MODIFY `groupRankingID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventGroupRoster`
--
ALTER TABLE `eventGroupRoster`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventGroups`
--
ALTER TABLE `eventGroups`
  MODIFY `groupID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventIgnores`
--
ALTER TABLE `eventIgnores`
  MODIFY `ignoreID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventLivestreamMatches`
--
ALTER TABLE `eventLivestreamMatches`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventLivestreams`
--
ALTER TABLE `eventLivestreams`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventMatches`
--
ALTER TABLE `eventMatches`
  MODIFY `matchID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  MODIFY `placeID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventRoster`
--
ALTER TABLE `eventRoster`
  MODIFY `rosterID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventStandings`
--
ALTER TABLE `eventStandings`
  MODIFY `standingID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventTeamRoster`
--
ALTER TABLE `eventTeamRoster`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventTournamentRoster`
--
ALTER TABLE `eventTournamentRoster`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventTournaments`
--
ALTER TABLE `eventTournaments`
  MODIFY `tournamentID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logisticsLocations`
--
ALTER TABLE `logisticsLocations`
  MODIFY `locationID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logisticsLocationsBlocks`
--
ALTER TABLE `logisticsLocationsBlocks`
  MODIFY `blockLocationID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logisticsLocationsMatches`
--
ALTER TABLE `logisticsLocationsMatches`
  MODIFY `matchLocationID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logisticsScheduleBlocks`
--
ALTER TABLE `logisticsScheduleBlocks`
  MODIFY `blockID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logisticsScheduleShifts`
--
ALTER TABLE `logisticsScheduleShifts`
  MODIFY `shiftID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logisticsStaffCompetency`
--
ALTER TABLE `logisticsStaffCompetency`
  MODIFY `staffCompetencyID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logisticsStaffMatches`
--
ALTER TABLE `logisticsStaffMatches`
  MODIFY `matchStaffID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logisticsStaffShifts`
--
ALTER TABLE `logisticsStaffShifts`
  MODIFY `staffShiftID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logisticsStaffTemplates`
--
ALTER TABLE `logisticsStaffTemplates`
  MODIFY `staffTemplateID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemAttacks`
--
ALTER TABLE `systemAttacks`
  MODIFY `attackID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemBlockTypes`
--
ALTER TABLE `systemBlockTypes`
  MODIFY `blockTypeID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemColors`
--
ALTER TABLE `systemColors`
  MODIFY `colorID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemCutQualifications`
--
ALTER TABLE `systemCutQualifications`
  MODIFY `qualID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemCutStandards`
--
ALTER TABLE `systemCutStandards`
  MODIFY `standardID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemDoubleTypes`
--
ALTER TABLE `systemDoubleTypes`
  MODIFY `doubleTypeID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemEvents`
--
ALTER TABLE `systemEvents`
  MODIFY `eventID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemFormats`
--
ALTER TABLE `systemFormats`
  MODIFY `formatID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemLogisticsRoles`
--
ALTER TABLE `systemLogisticsRoles`
  MODIFY `logisticsRoleID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemMatchOrder`
--
ALTER TABLE `systemMatchOrder`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemRankings`
--
ALTER TABLE `systemRankings`
  MODIFY `tournamentRankingID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemRoster`
--
ALTER TABLE `systemRoster`
  MODIFY `systemRosterID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemRosterNotDuplicate`
--
ALTER TABLE `systemRosterNotDuplicate`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemSchools`
--
ALTER TABLE `systemSchools`
  MODIFY `schoolID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemTournaments`
--
ALTER TABLE `systemTournaments`
  MODIFY `tournamentTypeID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemUserEvents`
--
ALTER TABLE `systemUserEvents`
  MODIFY `userTournamentID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemUsers`
--
ALTER TABLE `systemUsers`
  MODIFY `userID` int(10) unsigned NOT NULL AUTO_INCREMENT;
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
  ADD CONSTRAINT `systemUserEvents_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `systemUsers` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `systemUserEvents_ibfk_2` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
